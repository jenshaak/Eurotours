function BackendSeatsSettingsPage($body) {
	this.$body = $body;

	this.create();
}

BackendSeatsSettingsPage.prototype.create = function () {
	var self = this;

	self.$body.find("input[name='filterDay']").datepicker({
		format: "d. m. yyyy",
		language: "cs",
		keyboardNavigation: false
	});

	self.$body.find("[data-action='seatsPlan']").on("click", function (e) {
		self.seatsPlan($(e.currentTarget).attr("data-url"));
	});
};

BackendSeatsSettingsPage.prototype.seatsPlan = function (url) {
	$.ajax(url)
		.done(function (data) {
			BootstrapDialog.show({
				title: "Místa k sezení",
				size: BootstrapDialog.SIZE_SMALL,
				message: $(data.seatsPlanWidget),
				buttons: [{
					label: "Uložit nastavení",
					hotkey: 13,
					cssClass: 'btn-success',
					action: function (dialog) {
						dialog.getModalBody().find("form").submit();
						dialog.close();
						window.setTimeout(function () {
							location.reload();
						}, 1000);
					}
				}]
			});
		});
};


pages['backend_seats_settings'] = BackendSeatsSettingsPage;
