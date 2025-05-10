function BackendTicketsPage($body) {
	this.$body = $body;

	this.$typeCheckbox = this.$body.find("input[name='type'][type='checkbox']");
	this.$formReturn = this.$body.find("div[id='formReturn']");

	this.create();
}

BackendTicketsPage.prototype.create = function () {
	var self = this;

	$(".datepicker").datepicker({
		format: "d. m. yyyy",
		language: "cs",
		autoclose: true
	});

	self.$typeCheckbox.on("change", function () {
		self.$formReturn.find("input").prop("disabled", !self.$typeCheckbox.prop("checked"));
	});

	self.$typeCheckbox.prop("checked", false);
	self.$formReturn.find("input").prop("disabled", true);
};

pages['backend_tickets'] = BackendTicketsPage;
