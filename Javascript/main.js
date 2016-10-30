
$(document).ready(function () {

	CallEventHook("theme", [function () {
		CallEventHook("themeReady");
	}]);

});

window.Websom = {};