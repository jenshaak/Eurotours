function BackendLinesPage($body) {
	this.$body = $body;

	this.create();
}

BackendLinesPage.prototype.create = function () {
	var self = this;

	self.$body.find("input[data-action='toggleLineVisible']").on("change", function (e) {
		var $checkbox = $(e.currentTarget);
		var line = $checkbox.attr("data-line");
		if ($checkbox.prop("checked")) {
			$.ajax(services['backend.router'].showLine(line), { method: "post" });
		} else {
			$.ajax(services['backend.router'].hideLine(line), { method: "post" });
		}
	});

	self.$body.find("input[data-action='toggleLinePayOnline']").on("change", function (e) {
		var $checkbox = $(e.currentTarget);
		var line = $checkbox.attr("data-line");
		if ($checkbox.prop("checked")) {
			$.ajax(services['backend.router'].onPayOnlineLine(line), { method: "post" });
		} else {
			$.ajax(services['backend.router'].offPayOnlineLine(line), { method: "post" });
		}
	});
};

pages['backend_lines'] = BackendLinesPage;
