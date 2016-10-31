window.Websom.Theme = {
	methods: {},
	register: function (type, selector, func) {
		var that = this;
		if (!(type in that.methods)) {
			that.methods[type] = {};
			that.methods[type][selector] = func;
			return;
		}
		that.methods[type][selector] = func;
	},
	get: function (e) {
		var that = this;
		if ("get" in that.methods)
		for (var i in that.methods["get"]) {
			if (e.is(i)) return that.methods["get"][i].call(e[0]);
		}
	},
	set: function (e, value) {
		var that = this;
		if ("set" in that.methods)
		for (var i in that.methods["set"]) {
			if (e.is(i)) {
				that.methods["set"][i].call(e[0], value);
				return true;
			}
		}
		return false;
	},
	call: function (what, e, args = []) {
		var that = this;
		if (what in that.methods)
		for (var i in that.methods[what]) {
			if (e.is(i)) return that.methods[what][i].call(e, args);
		}
	}
};
