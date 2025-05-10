function BackendScheduleNoteWidget($object) {
	this.$object = $object;
	this.$form = $object.find("form");

	this.create();
}

BackendScheduleNoteWidget.prototype.create = function () {
	var self = this;

	self.$form.off("submit").on("submit", function (v) {
		v.preventDefault();
		self.saveData();
	});
};

BackendScheduleNoteWidget.prototype.saveData = function () {
	var self = this;

	$.ajax(self.$form.attr("action"), {
		method: "post",
		data: self.$form.serializeArray()
	});
};

widgets['backend.scheduleNote'] = BackendScheduleNoteWidget;
