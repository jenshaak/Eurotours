function BackendOrderPage($body) {
	this.$body = $body;
	this.$reservationDateInput = $body.find("input[name='reservationDate']");

	this.create();
}

BackendOrderPage.prototype.create = function () {
	var self = this;

	self.$body.find("button[data-action='paidWireTransfer']").on("click", function (e) {
		BootstrapDialog.confirm("Opravdu chcete označit tuto objednávku jako zaplacenou převodem?", function (result) {
			if (result) $(e.currentTarget).parents("form").submit();
		});
	});

	self.$body.find("button[data-action='paidCash']").on("click", function (e) {
		BootstrapDialog.confirm("Opravdu chcete označit tuto objednávku jako zaplacenou hotově?", function (result) {
			if (result) $(e.currentTarget).parents("form").submit();
		});
	});

	self.$body.find("button[data-action='cancelOrder']").on("click", function (e) {
		BootstrapDialog.confirm({
			title: "Stornovat objednávku",
			message: "Opravdu chcete objednávku stornovat?",
			type: BootstrapDialog.TYPE_DANGER,
			callback: function (result) {
				if (result) $(e.currentTarget).parents("form").submit();
			}
		});
	});

	self.$body.find("button[data-action='deleteOrder']").on("click", function (e) {
		BootstrapDialog.confirm({
			title: "Odstranit objednávku",
			message: "Opravdu chcete objednávku úplně odstranit?",
			type: BootstrapDialog.TYPE_DANGER,
			callback: function (result) {
				if (result) $(e.currentTarget).parents("form").submit();
			}
		});
	});

	self.$body.find("button[data-action='cancelTicket']").on("click", function (e) {
		BootstrapDialog.confirm({
			title: "Stornovat jízdenku",
			message: "Opravdu chcete tuto jízdenku stornovat?",
			type: BootstrapDialog.TYPE_DANGER,
			callback: function (result) {
				if (result) $(e.currentTarget).parents("form").submit();
			}
		});
	});

	self.$body.find("button[data-action='billedTicket']").on("click", function (e) {
		BootstrapDialog.show({
			title: "Zaúčtovat jízdenku",
			message: $(self.$body.find("[data-modal-body='billedTicket']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog) {
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $form = $(e.currentTarget).parents("form");
					$("<input type='hidden' name='note'>")
						.val(dialog.getModalBody().find("textarea[name='ticket-billed-note']").val())
						.appendTo($form);
					dialog.close();
					$form.submit();
				}
			}]
		});
	});

	self.$body.find("button[data-action='createActivityNote']").on("click", function (e) {
		BootstrapDialog.show({
			title: "Přidat poznámku",
			message: $(self.$body.find("[data-modal-body='createActivityNote']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			onshown: function (dialog) {
				dialog.getModalBody().find("textarea").focus();
			},
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function (dialog) {
					dialog.close();
				}
			}, {
				label: "Přidat poznámku",
				cssClass: "btn-primary",
				action: function (dialog) {
					var $form = $(e.currentTarget).parents("form");
					$("<input type='hidden' name='note'>")
						.val(dialog.getModalBody().find("textarea[name='activity-note']").val())
						.appendTo($form);
					dialog.close();
					$form.submit();
				}
			}]
		});
	});

	self.$body.find("button[data-action='sellerPaid']").on("click", function (e) {
		BootstrapDialog.show({
			title: "Přijatá platba",
			message: $(self.$body.find("[data-modal-body='sellerPaid']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog) {
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $form = $(e.currentTarget).parents("form");
					$("<input type='hidden' name='note'>")
						.val(dialog.getModalBody().find("textarea[name='seller-paid-note']").val())
						.appendTo($form);
					dialog.close();
					$form.submit();
				}
			}]
		});
	});

	self.$body.find("button[data-action='paidByBank']").on("click", function (e) {
		BootstrapDialog.show({
			title: "Přijatá platba bankou",
			message: $(self.$body.find("[data-modal-body='paidByBank']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog) {
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $form = $(e.currentTarget).parents("form");
					$("<input type='hidden' name='note'>")
						.val(dialog.getModalBody().find("textarea[name='paid-by-bank-note']").val())
						.appendTo($form);
					dialog.close();
					$form.submit();
				}
			}]
		});
	});

	self.$body.find(".orderPersonPhoneEditable").editable({
		type: "text",
		placement: "left",
		title: "Cena",
		emptytext: "disabled",
		onblur: "ignore"
	});

	self.$body.find(".ticketNameEditable").editable({
		type: "text",
		placement: "right",
		title: "Cestující",
		emptytext: "disabled",
		onblur: "ignore"
	});

	if (!isSeller) {
		self.$body.find(".ticketPriceEditable").editable({
			type: "text",
			placement: "left",
			title: "Cena",
			emptytext: "disabled",
			onblur: "ignore"
		});

		self.$body.find(".bookPriceEditable").editable({
			type: "text",
			placement: "left",
			title: "Cena",
			emptytext: "disabled",
			onblur: "ignore"
		});
	}

	self.$body.find("button[data-action='setReservationDate']").on("click", function (e) {
		if (self.$reservationDateInput.length > 0) {
			BootstrapDialog.show({
				title: "Do kdy jízdenky rezervovat?",
				size: BootstrapDialog.SIZE_SMALL,
				message: $(self.$body.find("[data-modal-body='reservationDate']").html()),
				onshown: function (dialog) {
					dialog.getButton("btnReservation").disable();
					dialog.getModalBody().find("div.reservationCalendar").datepicker({
						language: "cs",
						weekStart: 1,
						startDate: new Date()
					}).datepicker("update", new Date()).on("changeDate", function (e) {
						dialog.getButton("btnReservation").enable();
						self.$reservationDateInput.val(e.date.getFullYear() + "-" +  (e.date.getMonth()+1) + "-" + e.date.getDate());
					});
				},
				buttons: [{
					label: "Rezervovat",
					id: "btnReservation",
					hotkey: 13,
					cssClass: 'btn-success',
					action: function (dialog) {
						dialog.close();
						$(e.currentTarget).parents("form").submit();
					}
				}]
			});

		} else {
			self.$orderForm.attr("action", self.$orderForm.attr("data-action-reservation"));
			self.$orderForm.submit();
		}
	});


};

pages['backend_order'] = BackendOrderPage;
