function BackendOrdersPage($body) {
	this.$body = $body;
	this.$paginatorButton = $body.find("button[data-action='nextPage']");
	this.loading = false;

	this.create();
}

BackendOrdersPage.prototype.create = function () {
	var self = this;
	self.refreshEvents();
};

BackendOrdersPage.prototype.refreshEvents = function () {
	var self = this;

	var $window = $(window);

	self.$body.find(".input-daterange input").each(function() {
		$(this).datepicker({
			format: "d. m. yyyy",
			language: "cs"
		});
	});

	self.$body.find(".tr-order").mouseenter(function (e) {
		var $tr = $(e.currentTarget);
		self.$body.find(".tr-order").removeClass("current");
		self.$body.find(".tr-order-" + $tr.attr("data-order")).addClass("current");
	});

	$window.off("scroll.paginator").on("scroll.paginator", function () {
		if ($window.scrollTop() + ($window.height()*2) > $document.height()) {
			self.nextPage();
		}
	});

	self.$body.find("[data-action='switchOrder']").off("click.switchOrder").on("click.switchOrder", function (e) {
		var $inputOrderBy = self.$body.find("input[name='orderBy']");
		var $inputOrderByDirection = self.$body.find("input[name='orderByDirection']");
		var $td = $(e.currentTarget);
		var orderBy = $td.attr("data-order-by");
		if ($inputOrderBy.val() === orderBy) {
			if ($inputOrderByDirection.val() === "ASC") {
				$inputOrderByDirection.val("DESC");
			} else {
				$inputOrderByDirection.val("ASC");
			}
		}
		$inputOrderBy.val(orderBy);
		$inputOrderBy.parents("form").submit();
	});

	self.$paginatorButton.on("click", function () {
		self.nextPage();
	});

	self.$body.find("span[data-action='orderBilled']").off("click.orderBilled").on("click.orderBilled", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Zaúčtovat objednávku",
			message: $(self.$body.find("[data-modal-body='billedOrder']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			onshown: function (dialog) {
				dialog.getModalBody().find("select").selectpicker();
			},
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: {
							note: dialog.getModalBody().find("textarea[name='order-billed-note']").val(),
							month: dialog.getModalBody().find("select").val()
						}
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='orderBilledCancel']").off("click.orderBilledCancel").on("click.orderBilledCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení ED pro celou objednávku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});

	self.$body.find("span[data-action='orderSellerPaid']").off("click.orderSellerPaid").on("click.orderSellerPaid", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Přijatá platba k objednávce",
			message: $(self.$body.find("[data-modal-body='sellerPaidOrder']").html()),
			type: BootstrapDialog.TYPE_INFO,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: { note: dialog.getModalBody().find("textarea[name='ticket-seller-paid-note']").val() }
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='orderSellerPaidCancel']").off("click.orderSellerPaidCancel").on("click.orderSellerPaidCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení PE pro celou objednávku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});

	self.$body.find("span[data-action='orderPaidByBank']").off("click.orderPaidByBank").on("click.orderPaidByBank", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Přijatá platba k objednávce z banky",
			message: $(self.$body.find("[data-modal-body='paidByBankOrder']").html()),
			type: BootstrapDialog.TYPE_INFO,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: { note: dialog.getModalBody().find("textarea[name='order-paid-by-bank-note']").val() }
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='orderPaidByBankCancel']").off("click.orderPaidByBankCancel").on("click.orderPaidByBankCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení BE pro celou objednávku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});

	self.$body.find("span[data-action='ticketBilled']").off("click.ticketBilled").on("click.ticketBilled", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Zaúčtovat jízdenku",
			message: $(self.$body.find("[data-modal-body='billedTicket']").html()),
			type: BootstrapDialog.TYPE_SUCCESS,
			onshown: function (dialog) {
				dialog.getModalBody().find("select").selectpicker();
			},
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: {
							note: dialog.getModalBody().find("textarea[name='ticket-billed-note']").val(),
							month: dialog.getModalBody().find("select").val()
						}
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='ticketBilledCancel']").off("click.ticketBilledCancel").on("click.ticketBilledCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení ED pro tuto jízdenku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});

	self.$body.find("span[data-action='ticketSellerPaid']").off("click.ticketSellerPaid").on("click.ticketSellerPaid", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Přijatá platba k jízdence",
			message: $(self.$body.find("[data-modal-body='sellerPaidTicket']").html()),
			type: BootstrapDialog.TYPE_INFO,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: { note: dialog.getModalBody().find("textarea[name='ticket-seller-paid-note']").val() }
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='ticketSellerPaidCancel']").off("click.ticketSellerPaidCancel").on("click.ticketSellerPaidCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení PE pro tuto jízdenku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});

	self.$body.find("span[data-action='ticketPaidByBank']").off("click.ticketPaidByBank").on("click.ticketPaidByBank", function (e) {
		e.stopPropagation();

		BootstrapDialog.show({
			title: "Přijatá platba z banky k jízdence",
			message: $(self.$body.find("[data-modal-body='paidByBankTicket']").html()),
			type: BootstrapDialog.TYPE_INFO,
			buttons: [{
				label: "Storno",
				cssClass: "btn-secondary",
				action: function(dialog){
					dialog.close();
				}
			}, {
				label: "Ok",
				cssClass: "btn-primary",
				action: function(dialog) {
					var $span = $(e.currentTarget);
					$.ajax($span.attr("data-url"), {
						method: "post",
						data: { note: dialog.getModalBody().find("textarea[name='ticket-paid-by-bank-note']").val() }
					}).done(function (data) {
					});

					$span.removeClass("label-action");
					$span.removeAttr("data-url");

					dialog.close();
				}
			}]
		});
	});

	self.$body.find("span[data-action='ticketPaidByBankCancel']").off("click.ticketPaidByBankCancel").on("click.ticketPaidByBankCancel", function (e) {
		e.stopPropagation();

		BootstrapDialog.confirm("Opravdu chcete zrušit označení BE pro tuto jízdenku?", function (result) {
			if (result) {
				var $span = $(e.currentTarget);
				$.ajax($span.attr("data-url"), {
					method: "post"
				}).done(function (data) { });

				$span.remove();
			}
		});
	});
};

BackendOrdersPage.prototype.nextPage = function () {
	var self = this;

	if (self.loading) return;
	self.loading = true;

	$.ajax(self.$paginatorButton.attr("data-url"), { method: "get" })
		.done(function (data) {
			$data = $(data);
			$trs = $data.find("table > tbody > tr");
			self.$body.find("table > tbody").append($trs);
			console.log($data.find("button[data-action='nextPage']").attr("data-url"));
			self.$paginatorButton.attr("data-url", $data.find("button[data-action='nextPage']").attr("data-url"));
			self.refreshEvents();
			if ($trs.length == 0) {
				self.$paginatorButton.remove();
				$window.off("scroll.paginator");
			}
			self.loading = false;
		});
};

pages['backend_orders'] = BackendOrdersPage;
