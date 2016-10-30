$(document).ready(function() {
	//Run responive garbage\\ 
	for (var rep = 0; rep < responsives.length; rep++) {
		(
		function(id){
			var serverResponseCallback = function () {};
			responsives[rep](
				function (msg, inlineCback = false){
					msg['responiveid'] = rep;
					$.ajax({
						type: "POST",
						url: window.location.href,
						data: $.param(msg),
						success: function(data){
							data = JSON.parse(data);
							if (data.responsive_321_type == true)
								if (inlineCback === false) {
									serverResponseCallback(data);
								}else{
									inlineCback(data);
								}
						}
					});
				},
				function (callback) {
					serverResponseCallback = callback;
				}
			)
		}
		(rep));
	}
	
	//This is for when the page refreshes or changes\\ 
	var cookie = getCookie('changePageMessages_');
	if (cookie !== false){
		Messanger($('body'), JSON.parse(cookie));
		setCookie('changePageMessages_', 'Deleted', -1);
	}
	//TEMP: until we can remove jquery ui\\ 
	/*$('[requi]').each(function () {
		var that = $(this);
		if (that.attr('requi') == 'datepicker') {
			that.datepicker({
				onSelect: function() {
					$(this).change();
				}
			});
		}
	});*/ //Gone
	CallEventHook('DOMChange');
	$("form").each(function(){
		var that = this;
	
	
		$(that).find(':input').each(function () {
			if ($(this).hasAttr('submitonchange')){
				var events = '';
				if ($(this).hasAttr('type')) events = 'input';
				if ($(this).hasAttr('requi')) if ($(this).attr('requi') == 'datepicker') events = 'change';
				if ($(this).hasAttr('type') && $(this).attr('type') == 'checkbox') events = 'change';
				if ($(this).is('select')) events = 'change';
				$(this).on(events, function () {
					$(that).submit();
					$(this).focus();
				});
			}	
		});
		$(this).submit(function(e){
			unNestElements(that);
			e.preventDefault();
			
			var submitButton = $(this).children('[type=submit]');
			var ok = true;
			var callbackWaits = [];
			$(submitButton).addClass('loading_submit');
			$(submitButton).attr('disabled', true);

			var sendData = new FormData(that);
			reNestElements(that);
			$(that).find(":input[type!=submit]").each(function(){
				if (typeof $(this).attr("ctype") !== 'undefined'){
				if($(this).attr('type') == 'checkbox') {if(!$(this).is(':checked')) {sendData.append($(this).attr('name'), 0);}}
					if ($(this).hasAttr('optionalpost'))
						if ($(this).val() == '')
							return true;
					var Sani = window[$(this).attr("ctype")+'_Sanitize'];
					if (typeof Sani == "function"){
						var checkElement = this;
						var Errors = Sani(checkElement, "Submit");
						if (Errors !== true){
							$("#Error_For_"+$(this).attr("name")).remove();
							$(this).after("<span id='Error_For_"+$(this).attr("name")+"' class='GoingAway Error_Flag'>"+Errors+"</span>");
							GoingAway();
							ok = false;
							$(submitButton).removeClass('loading_submit');
							$(submitButton).attr('disabled', false);
						}
					}
				}
			});

			sendData.append('globalserverid', $(that).attr('globalserverid'));		
			
			if (ok) {
				$.ajax({
					type: "POST",
					url: $(that).attr("action"),
					data: sendData,
					success: function(Data){
						if (Data[0] == '{'){ //Theres a better way.\\ 
							Messanger(that, JSON.parse(Data));
						}else{
							//alert(Data);
							Refresh(that, Data);
						}
						$(submitButton).removeClass('loading_submit');
						$(submitButton).attr('disabled', false);
					},
					cache: false,
					contentType: false,
					processData: false
				});
			}
		});
	});
	$("dynamic").each(function (){
		var that = this;
		$(that).children("dynamictemplate").hide();
		$(that).children("dynamictemplate").attr("disabled", true);
		var Button_Id = "DynamicAdd"+$(that).attr("id");
		$(that).after("<input type='button' class='"+$(that).attr('addbuttonclass')+"' listbutton value='"+$(that).attr('addbutton')+"' id='"+Button_Id+"'>");
		$("#"+Button_Id).click(function (){
			$(that).attr('index', (Math.round($(that).attr('index'), 0)+1));
			var temp = $(that).children("template").html(); //if errors occur try putting "dynamictemplate"\\ 
			var name = $(that).attr('nameset').replace('%', $(that).attr('index'));
			temp = temp.replace(new RegExp('name="', 'g'), 'listinputid="lst_'+listId+'" name="'+name); //Just a bit messy.
			$(that).before(temp+'<a class="List_Remove '+$(that).attr('removebuttonclass')+'" removeid="'+listId+'" id="rmv_'+listId+'">X</a>');
			$('#rmv_'+listId).click(function(){
				$('[listinputid=lst_'+$(this).attr('removeid')+']').remove();
				$(this).remove();
			});
			listId++;
			CallEventHook('DOMChange');
		});
	});
	fragmentTemplates();
});

var listId = 86950;

function unNestElements (form) {
	$(form).find(':input').each(function () {
		if (!$(this).hasAttr('id')) $(this).uniqueId();
		$(this).after('<oldinput setid='+$(this).attr('id')+'>');
		$(this).appendTo(form);
	});
}

function reNestElements (form) {
	$(form).children(':input').each(function () {
		var apTo = $(form).find('oldinput[setid="'+$(this).attr('id')+'"]');
		$(apTo).css('background', 'blue');
		$(apTo).after($(this));
		$(apTo).remove();
	});
}

function fragmentTemplates(){
	$("dynamic").each(function (){
		var that = this;
		$(that).children("dynamictemplate").after("<template>"+$(that).children("dynamictemplate").html()+"</template>");
		$(that).children("dynamictemplate").remove();
	});
}

function Refresh(_form, newRows) {
	//Add animations\\ 
	var place = $('#'+$(_form).attr('refreshplace'));
	place.animate({"opacity": "0"}, 100, function () {
		place.html(newRows);
		place.css('opacity', '0');
		place.animate({"opacity": "100"}, 100);
	});
	
}

function Messanger (Element, Data){
	Data = decodeQ(Data);	
	for (var key in Data){
		if (key == "Success"){
			$(Element).append("<div class='GoingAway Success'>"+Data[key]+"</div>");
			GoingAway();
		}else if (key == "Error"){
			$(Element).append("<div class='GoingAway Error'>"+Data[key]+"</div>");
			GoingAway();
		}else if (key == "Alert"){
			alert(Data[key]+"");
		}else if (key == "Action"){
			for (var a in Data[key])
				if (typeof window['Action_'+Data[key][a]['name']] == 'function')
					window['Action_'+Data[key][a]['name']](Element, Data[key][a], Data);
		}
	}
}

function GoingAway(){
	$(".GoingAway").animate({opacity: 0}, 5000, function(){$(this).remove();});
}

function decodeQ(object) {
	for (var i in object) {
		if (typeof object[i] == 'object'){
			decodeQ(object[i]);
		}else if (typeof object[i] == 'string'){
			object[i] = object[i].replace(new RegExp('&quot;', 'g'), '"');
		}
	}
	return object;
}
//Insert actions here NOTE: this will change into a more organized file in the future without disrupting the code\\

function Action_Append (_form, data) {
	if (typeof data['html'] == 'undefined') return false;
	var sel = _form;
	if (typeof data['where'] == 'string') sel = $(data['where']);
	var appended = null;
	if (data['mode'] == 'after') appended = $(data['html']).insertAfter(sel);
	if (data['mode'] == 'before') appended = $(data['html']).insertBefore(sel);
	if (typeof data['animateBefore'] == 'object') $(appended).css(data['animateBefore']);
	if (typeof data['animateAfter'] == 'object') $(appended).animate(data['animateAfter']);
}

function Action_Go (_form, data, allData) {
	var url = data['url'];
	delete allData['Action']['Go'];
	setCookie('changePageMessages_', JSON.stringify(allData), 60*1000);
	window.location.href = url;
}
function Action_Refresh (_form, data, allData) {
	delete allData['Action']['Refresh'];
	setCookie('changePageMessages_', JSON.stringify(allData), 60*1000);
	location.reload(true);
}

function Action_Remove(_form) {
	$(_form).children('.GoingAway').insertAfter(_form);
	$(_form).remove();
}

//Clear code\\

function Action_Clear(_form) {
	var modes = { //Add any custom form element clears here\\ 
		"text": function (e) {
			$(e).val('');
		},
		"textarea": function (e) {
			$(e).val('');
		},
		"email": function (e) {
			$(e).val('');
		},
		"password": function (e) {
			$(e).val('');
		},
		"radio": function (e) {
			if (typeof $(e).attr('checked') != 'undefined') $(e).attr('checked', false);
		},
		"range": function (e) {
			e.value = e.defaultValue;
		},
		"search": function (e) {
			$(e).val('');
		},
		"url": function (e) {
			$(e).val('');
		},
		"tel": function (e) {
			$(e).val('');
		},
		"email": function (e) {
			$(e).val('');
		},
		"datetime": function (e) {
			$(e).val('');
		},
		"time": function (e) {
			$(e).val('');
		},
		"month": function (e) {
			$(e).val('');
		},
		"week": function (e) {
			$(e).val('');
		},
		"color": function (e) {
			$(e).val('');
		},
		"date": function (e) {
			$(e).val('');
		},
		"file": function (e) {
			$(e).val('');
		},
		"select": function (e) {
			$(e).val($(e).children('[selected=selected]').val());
		}
	};
	$(_form).find(":input").each(function(){
		if (typeof modes[$(this).attr('type')] == 'function') modes[$(this).attr('type')](this);
		if (typeof modes[$(this).prop('nodeName').toLowerCase()] == 'function') modes[$(this).prop('nodeName').toLowerCase()](this);
	});
}




onEvent('DOMChange', function () {
	$("dynamic").each(function (){
		var that = this;
		$(that).find(".listaddlower").each(function (){
			$(that).attr('index', (Math.round($(that).attr('index'), 0)+1));
			var temp = 	$(this).html();
			temp = temp.replace(new RegExp('name="', 'g'), 'listinputid="lst_'+listId+'" name="'+$(that).attr('nameset').replace('%', $(that).attr('index'))); //Just a bit messy.
			$(that).before(temp+'<a class="List_Remove '+$(that).attr('removebuttonclass')+'" removeid="'+listId+'" id="rmv_'+listId+'">X</a>');
			$(this).remove();
			$('#rmv_'+listId).click(function(){
				$('[listinputid=lst_'+$(this).attr('removeid')+']').remove();
				$(this).remove();
			});
			listId++;
		});
	});
});