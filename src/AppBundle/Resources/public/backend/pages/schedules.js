function BackendSchedulesPage($body) {
	this.$body = $body;

	this.create();
}

BackendSchedulesPage.prototype.create = function () {
	var self = this;

	self.$body.find("input[name='filterDay']").datepicker({
		format: "d. m. yyyy",
		language: "cs",
		keyboardNavigation: false
	});
};


pages['backend_schedules'] = BackendSchedulesPage;
