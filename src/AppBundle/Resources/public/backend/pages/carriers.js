function BackendCarriersPage($body) {
	this.$body = $body;

	this.create();
}

BackendCarriersPage.prototype.create = function () {
	var self = this;

	self.$body.find("button[data-action='removeCarrier']").on("click", function (e) {
		e.preventDefault();
		var $button = $(e.currentTarget);
		var $form = $button.parent().find("form[data-action='removeCarrier']");
		BootstrapDialog.confirm("Opravdu odstranit tohoto dopravce?", function (result) {
			if (!result) return;
			$form.attr("action", $button.attr("data-url")).submit();
		});
	});
	
	self.$body.find("button[data-action='restoreCarrier']").on("click", function (e) {
		e.preventDefault();
		var $button = $(e.currentTarget);
		var $form = $button.parent().find("form[data-action='restoreCarrier']");
		BootstrapDialog.confirm("Opravdu obnovit tohoto dopravce?", function (result) {
			if (!result) return;
			$form.attr("action", $button.attr("data-url")).submit();
		});
	});

	self.$body.find("button[data-action='saveCarrier']").on("click", function (e) {
		e.preventDefault();
		var $button = $(e.currentTarget);
		$button.parent().parent().find("form[data-action='saveCarrier']").submit();
	});

	self.$body.find("button[data-action='createCarrier']").on("click", function () {
		BootstrapDialog.show({
			title: "Vytvořit dopravce",
			message: $(self.$body.find("[data-modal-body='createCarrier']").html()),
			buttons: [{
				label: "Vytvořit dopravce",
				hotkey: 13,
				cssClass: 'btn-success',
				action: function (dialog) {
					dialog.getModalBody().find("form").submit();
					dialog.close();
				}
			}]
		});
	});

	self.$body.find(".carrierNameEditable").editable({
		type: "text",
		placement: "left",
		title: "Cena",
		emptytext: "disabled",
		onblur: "ignore"
	});

};

pages['backend_carriers'] = BackendCarriersPage;
