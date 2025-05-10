function BackendCreateOrderPage($body) {
	this.$body = $body;

	this.$typeCheckbox = this.$body.find("input[name='type'][type='checkbox']");
	this.$formReturn = this.$body.find("div[id='formReturn']");

	this.create();
}

BackendCreateOrderPage.prototype.create = function () {
	var self = this;

	$(".datepicker").datepicker({
		format: "d. m. yyyy",
		language: "cs",
		autoclose: true
	});

	self.$typeCheckbox.on("change", function () {
		self.$formReturn.find("input").prop("disabled", !self.$typeCheckbox.prop("checked"));
		self.$formReturn.find("select").prop("disabled", !self.$typeCheckbox.prop("checked")).selectpicker("refresh");
	});

};

pages['backend_create_order'] = BackendCreateOrderPage;
