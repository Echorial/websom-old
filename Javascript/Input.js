function WebsomForm(name, element, inputs) {
	this.name = name;
	this.element = element;
	this._inputs = inputs;
	this.inputs = [];
	for (var i in this._inputs) {
		if ("serverName" in this._inputs[i])
			this.inputs[this._inputs[i].serverName] = new WebsomForm.Input(i, this._inputs[i]);
	}
}

WebsomForm.prototype.load = function (data) {
	for (var i in this._inputs) {
			if (i == "dynamicFormEvents") continue;
			
			if (this._inputs[i].serverName in data) {
				Websform.build.callInput(this._inputs[i], 'load')($('#'+i)[0], data[this._inputs[i].serverName]);
			}
		}
}

WebsomForm.Input = function (id, input) {
	this.id = id;
	this.data = input;
}

WebsomForm.Input.prototype.getElement = function () {
	return $('#'+this.id)[0];
}

window.Websom.Input = {};
window.Websom.Forms = {};
window.Websom.Input.buildForms = function (lookin = $("body")) {
	lookin.find("websform:not([data-loaded])").each(function () {
		var f = $(this);
		f.attr("data-loaded", "true");
		var inputs = window["InputForms"][f.attr("name")];
		
		var loadData = {};
		var loader = f.children("websformloader");
		if (loader.length > 0) {
			loadData = JSON.parse(loader.html());
			loader.remove();
		}
		
		for (var i in inputs) {
			if (i == "dynamicFormEvents") continue;
			Websform.build.callInput(inputs[i], 'init').apply({serverName: inputs[i].serverName, getScopeData: function () {return Websform.getInputData(f, true)}}, [$('#'+i)[0], i]);
			if (inputs[i].serverName in loadData) {
				Websform.build.callInput(inputs[i], 'load')($('#'+i)[0], loadData[inputs[i].serverName]);
			}
		}
		
		var websomForm = new WebsomForm(f.attr("name"), f[0], inputs);
		
		if (f.hasAttr("submit-on-start"))
			f.trigger('submit');
		
		if (f.attr("data-client-name") != "0") {
			websomForm.name = f.attr("data-client-name");
			Websom.Forms[f.attr("data-client-name")] = websomForm;
		}
	});
};

onEvent("themeReady", function () {
	$(document).on('submit', 'websform', function (e) {
		Websform.post($(this));
		e.stopPropagation();
	});
	
	$(document).on('click', 'websform input[type=submit]:not([disabled])', function (e) {
		$(this).closest("websform").trigger('submit');
	});
	
	CallEventHook("themeReload", $(document));
	window.Websom.Input.buildForms();
});



Websform = {
	currentDynamicEvents: false,
	currentForm: false,
	
	build: {
		whole: function (_form, devents, serverNames, suppressErrors) {
			serverNames = serverNames || false;
			suppressErrors = suppressErrors || false;
			var f = $(_form);
			
			var data = {};
			var er = false;
			var inputs = window["InputForms"][f.attr("name")];
			for (var i in inputs) {
				var inputElem = $('#'+i);
				if (inputs[i]['type'] == 0) {
					var nameIt = i;
					if (serverNames)
						nameIt = inputs[i].serverName;
					data[nameIt] = Websform.build.input(inputElem, inputs[i], i, devents, _form, suppressErrors);
					if (typeof data[i] == "object")
					if ("inputError" in data[i])
						er = true;
				}
			}
			
			if (suppressErrors)
				return data;
			
			return ((er) ? false : data);
		},
		
		callInput: function (_data, fn) {
			if (_data['events'] === false) {
				if (isset(window["InputForms"][_data['globalName']][fn]))
					return window["InputForms"][_data['globalName']][fn];
			}else{
				if (isset(_data['events'][fn]))
					return _data['events'][fn];
			}
			return function () {
				console.log('called '+fn);
			}
		},
		
		input: function (_input, _data, _name, devents, _form, suppressErrors) {
			suppressErrors = suppressErrors || false;
			
			var that = this;
			var error = that.callInput(_data, 'validate')(_input[0], _name);
			if (error !== true && !suppressErrors) {
				var _error = that.callInput(_data, 'error')(_input[0], _name, error);
				if ("inputError" in devents)
					devents["inputError"]({
						$error: _error,
						$form: _form,
						$element: _input
					});
				return {inputError: true};
			}else{
				return that.callInput(_data, 'send')(_input[0], _name);
			}
		}
	},
	
	getInputData: function (_form, suppress) {
		suppress = suppress || false;
		_form.find(".input_error").remove();
		
		var devents = window["InputForms"][$(_form).attr("name")]["dynamicFormEvents"];
		Websform.currentDynamicEvents = devents;
		Websform.currentForm = _form;
		return Websform.build.whole(_form, devents, true, suppress);
	},
	
	post: function (_form) {
		var that = this;
		var devents = window["InputForms"][$(_form).attr("name")]["dynamicFormEvents"];
		Websform.currentDynamicEvents = devents;
		Websform.currentForm = _form;
		if ("submit" in devents)
			devents["submit"]({
				$form: $(_form)
			});
		
		var formData = Websform.build.whole(_form, devents);
		
		if ("post" in devents)
			devents["post"]({
				$form: $(_form),
				data: formData
			});
		
		if (formData !== false) {
			for (var i in formData) {
				if (typeof formData[i] == "object")
					if (Array.isArray(formData[i]))
						if (formData[i].length == 0)
							formData[i] = {__websom_array: true}; //Empty arrays do not post
			}
			$.ajax({
				type: "POST",
				url: window.location.href,
				data: $.param({inputPost: formData, inputPost_Form: _form.attr("name")}),
				success: function(data){
					try {
						data = JSON.parse(data);
					}catch(e) {
						throw new Error("Input error: "+data);
					}
					that.demessage(data["actions"], _form[0]);
					if ("msg" in data) {
						for (var i in data.msg) {
							$("#"+i).after("<div class='red-text input_error'>"+data.msg[i]+"</div>");
						}
					}
					if ("receive" in devents)
						devents["receive"]({
							$form: $(_form),
							message: data,
							data: formData
						});
					Websform.currentDynamicEvents = false;
					Websform.currentForm = false;
				}
			});
		}else{
			var top = $(_form).find("#error").offset().top;
			if ($(_form).find("#error").parent().children("[isinput]").length > 0)
				top = $(_form).find("#error").parent().children("[isinput]").offset().top;
			$('html, body').animate({
				scrollTop: top
			}, 200);
		}
	},
	
	
	demessage: function (msg, _form) {
		for (var e in msg) {
			var elem;
			if (e == "form") {
				elem = _form;
			}else{
				elem = $($(_form).attr("id")+"__"+e);
				if (elem.length == 1)
					elem = elem[0];
				else
					elem = _form;
			}
			
			for (var i = 0; i < msg[e].length; i++) {
				var action = msg[e][i];
				if (typeof window["Action_"+action.__type] == "function") {
					window["Action_"+action.__type](elem, action, msg);
				}
			}
		}
	}
}
