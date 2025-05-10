function BackendLinePage($body) {
	this.$body = $body;
	this.line = $body.find("[data-line]").attr("data-line");

	this.create();
}

BackendLinePage.prototype.create = function () {
	var self = this;

	services['backend.toggleSwitch'].apply();
	self.updateLinePeriod();

	self.$body.find(".datepicker").datepicker({
		language: "cs",
		weekStart: 1,
		startDate: new Date()
	});

	self.$body.find("[data-action='submitLineForm']").on("click", function () {
		self.unblockClose();
		self.$body.find("[data-action='lineForm']").submit();
	});

	self.$body.find("button[data-action='removeLine']").confirmation({
		title: "Opravdu odstranit linku?",
		btnOkLabel: "Ano, odstranit",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			self.$body.find("form[data-action='removeLine']").submit();
		}
	});

	self.$body.find("button[data-action='duplicateLine']").confirmation({
		title: "Opravdu vytvořit novou linku se stejnými parametry?",
		btnOkLabel: "Ano, duplikovat",
		btnCancelLabel: "Storno",
		btnOkClass: "btn-primary",
		btnCancelClass: "btn-default",
		onConfirm: function () {
			self.$body.find("form[data-action='duplicateLine']").submit();
		}
	});

	self.$body.find("i[data-action='removeOwnSeat']").on("click", function (e) {
		var $button = $(e.currentTarget);
		var $form = self.$body.find("form[data-action='removeOwnSeat']");
		BootstrapDialog.confirm("Opravdu odstranit toto omezení míst?", function (result) {
			if (!result) return;
			$form.attr("action", $button.attr("data-url")).submit();
		});
	});

	self.$body.find("input[data-action='toggleLinePeriod']").on("change", function (e) {
		self.updateLinePeriod();
	});

	self.$body.find("[data-action='weightUp']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.weightUp($button);
	});

	self.$body.find("[data-action='weightDown']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.weightDown($button);
	});

	self.$body.find("[data-action='weightLeft']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.weightLeft($button);
	});

	self.$body.find("[data-action='weightRight']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.weightRight($button);
	});

	self.$body.find("[data-action='deleteStation']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.deleteStation($button);
	});

	self.$body.find("[data-action='editIncludeDays']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.editDays($button);
	});

	self.$body.find("[data-action='editExcludeDays']").on("click", function (e) {
		var $button = $(e.currentTarget);
		self.editDays($button);
	});

	self.$body.find("button[data-action='removeSchedule']").on("click", function (e) {
		var $button = $(e.currentTarget);
		var $form = self.$body.find("form[data-action='removeSchedule']");
		BootstrapDialog.confirm("Opravdu odstranit tento spoj?", function (result) {
			if (!result) return;
			$form.attr("action", $button.attr("data-url")).submit();
		});
	});

	self.$body.find("button[data-action='duplicateSchedule']").on("click", function (e) {
		var $button = $(e.currentTarget);
		var $form = self.$body.find("form[data-action='duplicateSchedule']");
		BootstrapDialog.confirm("Opravdu duplikovat tento spoj?", function (result) {
			if (!result) return;
			$form.attr("action", $button.attr("data-url")).submit();
		});
	});

	self.$body.find("select").on("change", function () {
		self.blockClose();
	});

	self.$body.find("[data-action='editScheduleTime']").popover({
		template: '<div class="popover" role="tooltip" style="max-width: 500px; width: 430px;"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"><div class="data-content"></div></div></div>',
		content: function () {
			return $(self.$body.find("[data-modal-body='editScheduleTime']").html());
		},
		html: true,
		animation: false,
		placement: "bottom"
	}).on('shown.bs.popover', function (popover) {
		$("[data-action='editScheduleTime']").not(this).popover('hide');
		var $object = $(this);
		var $departureTimeHandler = $object.find("[data-handler='departureTime']");
		var $departureDayModifyHandler = $object.find("[data-handler='departureDayModify']");
		var $arrivalTimeHandler = $object.find("[data-handler='arrivalTime']");
		var $arrivalDayModifyHandler = $object.find("[data-handler='arrivalDayModify']");
		var $platformHandler = $object.find("[data-handler='platform']");

		var $popoverBody = $("#" + $(popover.currentTarget).attr("aria-describedby"));

		$popoverBody.on("keyup", function (e) {
			var $input = $(e.target);
			if ($input.val().length == 2 && e.keyCode >= 48 && e.keyCode <= 57 && $input.attr("name") !== "platform") {
				$input.val($input.val() + ":");
			}
		});

		$popoverBody.find("input[name='departureTime']").val($departureTimeHandler.val());
		$popoverBody.find("select[name='departureDayModify']").val($departureDayModifyHandler.val());

		$popoverBody.find("input[name='arrivalTime']").val($arrivalTimeHandler.val());
		$popoverBody.find("select[name='arrivalDayModify']").val($arrivalDayModifyHandler.val());

		$popoverBody.find("input[name='platform']").val($platformHandler.val());

		$popoverBody.find("select").selectpicker('show');
		$popoverBody.find("input[name='time']").first().focus();
		$popoverBody.find("form").on("submit", function (e) {
			e.preventDefault();

			var spanString = "";

			$arrivalTimeHandler.val($popoverBody.find("input[name='arrivalTime']").val());
			$arrivalDayModifyHandler.val($popoverBody.find("select[name='arrivalDayModify']").val());
			if ($arrivalTimeHandler.val()) {
				spanString = spanString + "(";
				if (parseInt($arrivalDayModifyHandler.val()) > 0) {
					spanString += $arrivalTimeHandler.val() + " +" + $arrivalDayModifyHandler.val();
				} else {
					spanString += $arrivalTimeHandler.val();
				}
				spanString = spanString + ") ";
			}

			$departureTimeHandler.val($popoverBody.find("input[name='departureTime']").val());
			$departureDayModifyHandler.val($popoverBody.find("select[name='departureDayModify']").val());
			if (parseInt($departureDayModifyHandler.val()) > 0) {
				spanString += $departureTimeHandler.val() + " +" + $departureDayModifyHandler.val();
			} else {
				spanString += $departureTimeHandler.val();
			}


			$platformHandler.val($popoverBody.find("input[name='platform']").val());
			if ($platformHandler.val()) {
				spanString += "<br><small>nást. " + $platformHandler.val() + "</small>";
			}

			$object.find("span").html(spanString);

			$object.popover('hide');
			self.blockClose();
		});
	});

	self.$body.find("[data-action='addStation']").on("click", function () {
		self.addStation();
	});

	self.$body.find("[data-action='changeStation']").on("click", function (e) {
		var lineStation = $(e.currentTarget).attr("data-line-station");

		BootstrapDialog.show({
			title: "Změna stanice",
			message: $(self.$body.find("[data-modal-body='changeStation']").html()),
			onshown: function (dialog) {
				dialog.getModalBody().find("select").selectpicker();
				dialog.getModalBody().find("input[name='fromLineStation']").val(lineStation);
			},
			buttons: [{
				label: "Změnit stanici",
				hotkey: 13,
				cssClass: 'btn-success',
				action: function (dialog) {
					dialog.getModalBody().find("form").submit();

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("[data-action='manualOwnSeat']").on("click", function (e) {
			var $button = $(e.currentTarget);
			BootstrapDialog.show({
				title: "Omezit místa",
				message: $(self.$body.find("[data-modal-body='manualOwnSeat']").html()),
				onshown: function (dialog) {
					dialog.getModalBody().find("form").attr("action", $button.attr("data-url"));
					dialog.getModalBody().find("input[name='dateDay']").datepicker({
						format: "d. m. yyyy",
						language: "cs",
						keyboardNavigation: false
					});
				},
				buttons: [{
				label: "Nastavit omezení míst",
				hotkey: 13,
				cssClass: 'btn-success',
				action: function (dialog) {
					dialog.getModalBody().find("form").submit();
					dialog.close();
				}
			}]
		});
	});

	self.$body.find("[data-action='seatsPlan']").on("click", function (e) {
		self.seatsPlan($(e.currentTarget).attr("data-url"));
	});

	self.$body.find("[data-action='scheduleNote']").on("click", function (e) {
		self.scheduleNote($(e.currentTarget).attr("data-url"));
	});

	self.unblockClose();
	self.recountScheduleWeights();
};


BackendLinePage.prototype.seatsPlan = function (url) {
	$.ajax(url)
		.done(function (data) {
			BootstrapDialog.show({
				title: "Místa k sezení",
				message: $(data.seatsPlanWidget),
				buttons: [{
					label: "Uložit nastavení",
					hotkey: 13,
					cssClass: 'btn-success',
					action: function (dialog) {
						dialog.getModalBody().find("form").submit();
						dialog.close();
					}
				}]
			});
		});
};

BackendLinePage.prototype.scheduleNote = function (url) {
	$.ajax(url)
		.done(function (data) {
			BootstrapDialog.show({
				title: "Poznámka spoje",
				message: $(data.scheduleNoteWidget),
				size: BootstrapDialog.SIZE_WIDE,
				closeByBackdrop: false,
				buttons: [{
					label: "Uložit nastavení",
					cssClass: 'btn-success',
					action: function (dialog) {
						dialog.getModalBody().find("form").submit();
						dialog.close();
					}
				}]
			});
		});
};

BackendLinePage.prototype.recountWeights = function () {
	var weight = 0;
	var self = this;
	self.$body.find("input[name*='weightLineStation']").each(function () {
		$(this).val(weight);
		weight++;
	});
};

BackendLinePage.prototype.recountScheduleWeights = function () {
	var weight = 0;
	var self = this;
	var $inputs = self.$body.find("input[name*='weightSchedule']")
	$inputs.each(function () {
		$(this).parent().find("button[data-action='weightLeft']").prop('disabled', weight === 0);
		$(this).parent().find("button[data-action='weightRight']").prop('disabled', weight === $inputs.length-1);
		$(this).val(weight);
		weight++;
	});
};

BackendLinePage.prototype.updateLinePeriod = function () {
	var self = this;
	var checked = self.$body.find("input[data-action='toggleLinePeriod']").prop("checked");
	var settings = self.$body.find("[data-action='linePeriodSettings']");

	settings.find("input").prop("disabled", !checked);
};

BackendLinePage.prototype.blockClose = function () {
	window.onbeforeunload = function (e) {
		e = e || window.event;
		if (e) {
			e.returnValue = 'Save changes';
		}
		return 'Save changes';
	};
};

BackendLinePage.prototype.unblockClose = function () {
	window.onbeforeunload = function (e) { };
};

BackendLinePage.prototype.deleteStation = function ($button) {
	var self = this;

	BootstrapDialog.confirm("Opravdu chcete odebrat tuto zastávku?", function (result) {
		if (!result) return;

		$.ajax($button.attr("data-url"), { method: "post" })
			.done(function () {
				window.location.reload();
			});
	});
};

BackendLinePage.prototype.editDays = function ($button) {
	var self = this;

	var $value = $button.parent().find("input[type='hidden']");
	var value = $value.val();
	var $all, $days;

	BootstrapDialog.show({
		title: "Editace dat",
		message: $(self.$body.find("[data-modal-body='editDays']").html()),
		onshown: function (dialog) {
			$all = dialog.getModalBody().find("input[name='all']");
			$days = dialog.getModalBody().find("input[name='days']");

			if (value == "all") {
				$all.prop("checked", true);
				$days.hide();
			} else {
				$all.prop("checked", false);
				$days.show();
				$days.val(value);
			}

			$all.on("change", function () {
				if ($all.is(":checked")) {
					$days.hide();
				} else {
					$days.show();
				}
			});
		},
		buttons: [{
			label: "Změnit",
			hotkey: 13,
			cssClass: 'btn-success',
			action: function (dialog) {
				if ($all.is(":checked")) {
					$button.find("span").html("all");
					$value.val("all");
				} else {
					$value.val($days.val());
					$button.find("span").html($days.val());
				}

				self.blockClose();

				dialog.close();
			}
		}]
	});
};

BackendLinePage.prototype.weightUp = function ($button) {
	var self = this;
	var $tr = $button.parents("tr");
	$tr.prev().before($tr);
	self.recountWeights();
	self.blockClose();
};

BackendLinePage.prototype.weightDown = function ($button) {
	var self = this;
	var $tr = $button.parents("tr");
	$tr.next().after($tr);
	self.recountWeights();
	self.blockClose();
};

BackendLinePage.prototype.weightLeft = function ($button) {
	var self = this;
	var $td = $button.parents("td");
	var index = $td.index();
	$td.parents("tbody").find("tr").each(function (k, v) {
		var $v = $(v).find("td").eq(index);
		$v.prev().before($v);
	});
	self.recountScheduleWeights();
	self.blockClose();
};

BackendLinePage.prototype.weightRight = function ($button) {
	var self = this;
	var $td = $button.parents("td");
	var index = $td.index();
	$td.parents("tbody").find("tr").each(function (k, v) {
		var $v = $(v).find("td").eq(index);
		$v.next().after($v);
	});
	self.recountScheduleWeights();
	self.blockClose();
};

BackendLinePage.prototype.addStation = function () {
	var self = this;

	BootstrapDialog.show({
		title: "Přidat zastávku",
		message: $(self.$body.find("[data-modal-body='addStation']").html()),
		onshown: function (dialog) {
			dialog.getModalBody().find("select").selectpicker();
		},
		buttons: [{
			label: "Přidat zastávku",
			hotkey: 13,
			cssClass: 'btn-success',
			action: function (dialog) {
				dialog.getModalBody().find("form").submit();
				dialog.close();
			}
		}]
	});
};

pages['backend_line'] = BackendLinePage;
