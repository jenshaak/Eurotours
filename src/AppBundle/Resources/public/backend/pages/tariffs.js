function BackendTariffsPage($body) {
	this.$body = $body;

	this.create();
}

BackendTariffsPage.prototype.create = function () {
	var self = this;

	self.$body.find('[data-toggle="tooltip"]').tooltip();

	self.$body.find("button[data-action='editTariffConditions']").on("click", function (e) {
		var $button = $(e.currentTarget);

		$.ajax($button.attr("data-url"), { method: "get", type: "json" }).done(function (data) {
			BootstrapDialog.show({
				title: "Editace podmínek tarifu",
				message: $(data.tariffConditionsWidget),
				onshown: function (dialog) {
					dialog.getModalBody().find("form").on("submit", function (e) {
						e.preventDefault();
						var $form = $(e.currentTarget);
						$.ajax($form.attr("action"), { method: "post", type: "json", data: $form.serializeArray() });
					});
				},
				buttons: [{
					label: "Uložit změny",
					hotkey: 13,
					cssClass: 'btn-success',
					action: function (dialog) {
						dialog.getModalBody().find("form").submit();
						dialog.close();
					}
				}]
			});
		});
	});
};

pages['backend_externals_tariffs'] = BackendTariffsPage;