function BackendTariffPage($body) {
	this.$body = $body;

	this.create();
}

BackendTariffPage.prototype.create = function () {
	var self = this;

	self.$body.find('[data-toggle="tooltip"]').tooltip();

	self.$body.find("[data-action='submitTariffForm']").on("click", function () {
		self.$body.find("[data-action='tariffForm']").submit();
	});

	self.$body.find("input[name^='price']").on("change", function (e) {
		self.refreshColor($(e.currentTarget));
	});

	self.$body.find("input[name^='price']").on("click", function (e) {
		e.currentTarget.select();
	});

	self.$body.find("button[data-action='removeTariff']").confirmation({
		title: "Opravdu odstranit tarif?",
		btnOkLabel: "Ano, odstranit",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			self.$body.find("form[data-action='removeTariff']").submit();
		}
	});

	self.$body.find("button[data-action='duplicateTariff']").confirmation({
		title: "Opravdu duplikovat tarif?",
		btnOkLabel: "Ano, duplikovat",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			self.$body.find("form[data-action='duplicateTariff']").submit();
		}
	});

	self.$body.find("button[data-action='removeTemporaryRule']").confirmation({
		title: "Opravdu odstranit toto pravidlo?",
		btnOkLabel: "Ano, odstranit",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			$(this).parents("[data-action='temporaryTariffPanel']").remove();
		}
	});

	self.$body.find("button[data-action='removeTemporaryPercentRule']").confirmation({
		title: "Opravdu odstranit tento termín?",
		btnOkLabel: "Ano, odstranit",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			$(this).parents("[data-action='temporaryTariffPanel']").remove();
		}
	});

	self.$body.find("button[data-action='createTemporaryTariff']").on("click", function (e) {
		var form = self.$body.find("form[data-action='createTemporaryTariff']");
		form.find("input[name='name[cs]']").val(self.$body.find("input[name='temporaryTariffName[cs]']").val());
		form.find("input[name='name[en]']").val(self.$body.find("input[name='temporaryTariffName[en]']").val());
		form.find("input[name='name[ru]']").val(self.$body.find("input[name='temporaryTariffName[ru]']").val());
		form.find("input[name='name[bg]']").val(self.$body.find("input[name='temporaryTariffName[bg]']").val());
		form.find("input[name='name[uk]']").val(self.$body.find("input[name='temporaryTariffName[uk]']").val());

		if (self.$body.find("input[name='addToTemporaryTariff']:checked").val() === "fix") {
			form.find("input[name='addToTemporaryTariffValue']").val(self.$body.find("input[name='addToTemporaryTariffFix']").val())
			form.find("input[type='hidden'][name^='temporaryDateFrom']").val(self.$body.find("input[type='text'][name^='temporaryDateFrom']").val())
			form.find("input[type='hidden'][name^='temporaryDateTo']").val(self.$body.find("input[type='text'][name^='temporaryDateTo']").val())
			if (self.$body.find("input[type='checkbox'][name^='temporaryWay'][value='there']").prop("checked")) {
				form.find("input[type='hidden'][name^='temporaryWay[0][0]']").val("there")
			}
			if (self.$body.find("input[type='checkbox'][name^='temporaryWay'][value='back']").prop("checked")) {
				form.find("input[type='hidden'][name^='temporaryWay[0][1]']").val("back")
			}
		} else if (self.$body.find("input[name='addToTemporaryTariff']:checked").val() === "percent") {
			form.find("input[name='addToTemporaryTariffValue']").val(self.$body.find("input[name='addToTemporaryTariffPercent']").val())
			form.find("input[type='hidden'][name^='temporaryDateFrom']").val(self.$body.find("input[type='text'][name^='temporaryDateFrom']").val())
			form.find("input[type='hidden'][name^='temporaryDateTo']").val(self.$body.find("input[type='text'][name^='temporaryDateTo']").val())
			if (self.$body.find("input[type='checkbox'][name^='temporaryWay'][value='there']").prop("checked")) {
				form.find("input[type='hidden'][name^='temporaryWay[0][0]']").val("there")
			}
			if (self.$body.find("input[type='checkbox'][name^='temporaryWay'][value='back']").prop("checked")) {
				form.find("input[type='hidden'][name^='temporaryWay[0][1]']").val("back")
			}
		}
		form.submit();
	});

	self.$body.find("input[name='addToTemporaryTariff']").on("change", function (e) {
		var $currentTarget = $(e.currentTarget);
		var form = self.$body.find("form[data-action='createTemporaryTariff']");

		self.$body.find("input[type='checkbox'][name='addToTemporaryTariff']").each(function (i, otherEl) {
			var $otherEl = $(otherEl);
			if (otherEl !== e.currentTarget) {
				$otherEl.prop("checked", false);
			}
			$otherEl.parents(".panel").find(".panel-body").find("input[type='text']").prop("disabled", !$otherEl.prop("checked"));
			$otherEl.parents(".panel").find(".panel-body").find("input[type='checkbox']").prop("disabled", !$otherEl.prop("checked"));
		});

		if ($currentTarget.prop("checked")) {
			form.find("input[name='addToTemporaryTariff']").val($currentTarget.val());
		} else {
			form.find("input[name='addToTemporaryTariff']").val("");
		}

	});

	self.$body.find(".input-daterange input").each(function() {
		$(this).datepicker({
			language: "cs",
			weekStart: 1,
			startDate: new Date()
		});
	});

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

	self.$body.find("button[data-action='setPricesBackFromThere']").confirmation({
		title: "Nastavit ceny jízdného ZPĚT na základě cen směru TAM?",
		btnOkLabel: "Ano, nastavit",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			var $button = self.$body.find("button[data-action='setPricesBackFromThere']");

			$.ajax($button.attr("data-url"), { method: "post", type: "json" }).done(function () {
				window.location.reload();
			});
		}
	});

	self.$body.find("input[type='checkbox'][name='backWayByPercentDiscountEnabled']").on("change", function (e) {
		var $checkbox = $(e.currentTarget);
		var $input = self.$body.find("input[type='text'][name='backWayByPercentDiscount']");
		var checked = $checkbox.prop("checked");
		$input.prop("disabled", !checked);

	});

	self.refreshColors();

};

BackendTariffPage.prototype.refreshColors = function () {
	var self = this;

	self.$body.find("input[name^='price']").each(function (i, v) {
		self.refreshColor($(v));
	});

};

BackendTariffPage.prototype.refreshColor = function ($input) {
	var self = this;

	$input.removeClass("fare-variable");
	$input.removeClass("fare-not-available");
	$input.removeClass("fare-price");

	if ($input.val() == "v") {
		$input.addClass("fare-variable");
	} else if ($input.val() == "n") {
		$input.addClass("fare-not-available");
	} else if ($.isNumeric($input.val())) {
		$input.addClass("fare-price");
	}

};


pages['backend_tariff'] = BackendTariffPage;
