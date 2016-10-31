

onEvent("themeReady", function () {
	$(document).on('submit', 'websform', function () {
		Websform.post($(this));
	});

	$("websform").each(function () {
		var f = $(this);
		var inputs = window["InputForms"][f.attr("name")];
		
		var loadData = {};
		var loader = f.children("websformloader");
		if (loader.length > 0) {
			loadData = JSON.parse(loader.html());
			loader.remove();
		}
		
		for (var i in inputs) {
			if (i == "dynamicFormEvents") continue;
			Websform.build.callInput(inputs[i], 'init')($('#'+i)[0], i);
			if (inputs[i].serverName in loadData) {
				Websform.build.callInput(inputs[i], 'load')($('#'+i)[0], loadData[inputs[i].serverName]);
			}
		}
		
		if (f.hasAttr("submit-on-start"))
			f.trigger('submit');
	});
	
	CallEventHook("themeReload", $(document));
	
	$(document).on('click', 'websform input[type=submit]:not([disabled])', function () {
		$(this).closest("websform").trigger('submit');
	});
});



Websform = {
	
	currentDynamicEvents: false,
	currentForm: false,
	
	build: {
		whole: function (_form, devents) {
			var f = $(_form);
			
			var data = {};
			var er = false;
			var inputs = window["InputForms"][f.attr("name")];
			for (var i in inputs) {
				var inputElem = $('#'+i);
				if (inputs[i]['type'] == 0) {
					data[i] = Websform.build.input(inputElem, inputs[i], i, devents, _form);
					if (typeof data[i] == "object")
					if ("inputError" in data[i])
						er = true;
				}
			}
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
		
		input: function (_input, _data, _name, devents, _form) {
			var that = this;
			var error = that.callInput(_data, 'validate')(_input[0], _name);
			if (error !== true) {
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
		
		
		if (formData !== false)
			$.ajax({
				type: "POST",
				url: window.location.href,
				data: $.param({inputPost: formData, inputPost_Form: _form.attr("name")}),
				success: function(data){
					data = JSON.parse(data);
					that.demessage(data["actions"], _form[0]);
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
