var GlobalEventHooks = [];
function onEvent(eventName, callback) {
	GlobalEventHooks.push([callback, eventName]);
}

function CallEventHook (eventName, args = []) {
	for(var i in GlobalEventHooks){
		if (GlobalEventHooks[i][1] == eventName) {
			if (typeof GlobalEventHooks[i][0] == 'function') {
				GlobalEventHooks[i][0].apply(GlobalEventHooks[i][0], args);
			}
		}
	}
}


String.prototype.contains = function(array){
	for(var i = 0; i < this.length; i++){
		for(var i2 = 0; i2 < array.length; i2++){
			if (this[i] == array[i2]){
				return true;
			}
		}
	}
	return false;
}

String.prototype.notcontains = function(array){
	for(var i = 0; i < this.length; i++){
		var t = false;
		for(var i2 = 0; i2 < array.length; i2++){
			if (this[i] == array[i2]){
				t = true;
			}
		}
		if (!t) return true;
	}
	return false;
}
/* Danager
Array.prototype.replace = function(what, value){
	for(var i = 0; i < this.length; i++){
		if (this[i] == what){
			this[i] = value;
		}
	}
}
*/

jQuery.fn.extend({
	hasAttr: function (attr) {
		attr = this.attr(attr);
		if (typeof attr !== typeof undefined && attr !== false)
			return true;
		return false;
	},
	uniqueId: function () {
			var n;
			do
			n = Math.floor(Math.random()*2000+1);
			while($("#"+n).length > 0)
			this.attr('id', n);
	}
});

function isset(v) {
	return (typeof v === 'undefined' || v === null) ? false : true;
}

function setCookie(cname, cvalue, exper) {
    var d = new Date();
    d.setTime(d.getTime() + (exper));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires; + "; path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return false;
}

