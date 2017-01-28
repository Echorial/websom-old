$(document).ready(function () {
	CallEventHook("theme", [function () {
		CallEventHook("themeReady");
	}]);
});

window.Websom = {};

$(document).on("click", ":not(.ws_not_safe) .ws_submit_form, :not(.ws_not_safe) .ws_click_element", function () {
	var that = $(this);
	if (that.hasAttr("data-ws-form")) {
		$("[name=global__forms__"+that.attr("data-ws-form")+"]").trigger("submit");
	}else if(that.hasAttr("data-ws-element")){
		$(that.attr("data-ws-element")).trigger("click");
	}
});