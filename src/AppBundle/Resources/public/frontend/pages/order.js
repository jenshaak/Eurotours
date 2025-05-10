function FrontendOrderPage($body) {
	this.$body = $body;

	this.$orderForm = $body.find("form[data-action='orderForm']");
	this.$reservationDateInput = $body.find("input[name='reservationDate']");
	this.$dateBirthInput = $body.find("input[id*='persons_dateBirth_']");

	this.submitted = false;

	this.create();
}
var priceWithSellerFee = 0;

FrontendOrderPage.prototype.create = function () {
	var self = this;

	self.$body.find("button[data-action='reservation']").on("click", function () {

		if (self.checkOrderForm()) {
			if (self.$reservationDateInput.length > 0) {
				BootstrapDialog.show({
					title: "Do kdy jízdenky rezervovat?",
					size: BootstrapDialog.SIZE_SMALL,
					message: $(self.$body.find("[data-modal-body='reservationDate']").html()),
					onshown: function (dialog) {
						dialog.getButton("btnReservation").disable();
						dialog.getModalBody().find("div.reservationCalendar").datepicker({
							language: language,
							weekStart: 1
						}).on("changeDate", function (e) {
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
							if (!self.submitted) {
								self.$orderForm.attr("action", self.$orderForm.attr("data-action-reservation"));
								self.$orderForm.submit();
								self.submitted = true;
							}
						}
					}]
				});

			} else {
				if (!self.submitted) {
					self.$orderForm.attr("action", self.$orderForm.attr("data-action-reservation"));
					self.$orderForm.submit();
					self.submitted = true;
				}
			}
		}

	});

	self.$body.find("div[data-action='copyPhones']").on("click", function () {
		const sourceInputs = this.parentElement.parentElement.getElementsByTagName("input")
		for (const el of self.$body.find("input[class~='form-control-phone-prefix']")) {
			el.value = sourceInputs[0].value
		}
		for (const el of self.$body.find("input[class~='form-control-phone']")) {
			el.value = sourceInputs[1].value
		}
	});

	self.$body.find("button[data-action='buy']").on("click", function () {
		if (self.checkOrderForm()) {
			if (!self.submitted) {
				self.$orderForm.attr("action", self.$orderForm.attr("data-action-buy"));
				self.$orderForm.submit();
				self.submitted = true;
			}
		}
	});

	self.$body.find("input[name='adminChangePrice']").on("change", function (e) {
		var $input = $(e.currentTarget);
		var $divAdminChangePrice = self.$body.find(".div-admin-change-price");
		if ($input.prop("checked")) {
			$divAdminChangePrice.show();
		} else {
			$divAdminChangePrice.hide();
		}
	});

	function calculatePriceWithSellerFee() {
		priceWithSellerFee = 0;
		sellerFees.each(function () {
			priceWithSellerFee += parseFloat($(this).val());
		});
		var $totalPriceSpan = self.$body.find('#totalPrice > span');
		var totalPrice, maxTotalPrice;
		if ($totalPriceSpan.text().includes("-")) {
			var prices = $totalPriceSpan.text().split(" - ").map(function (price) {
				return price.split(" ")[0];
			});
			totalPrice = parseFloat(prices[0]);
			maxTotalPrice = parseFloat(prices[1]);
		} else {
			totalPrice = parseFloat($totalPriceSpan.text().split(' ')[0]);
		}

		if (maxTotalPrice) {
			self.$body.find('#priceWithSellerFee > span.totalPricePlaceholder')
				.text(totalPrice + priceWithSellerFee);
			self.$body.find('#priceWithSellerFee > span.maxTotalPricePlaceholder')
				.text(" - " + (maxTotalPrice + priceWithSellerFee));
		} else {
			self.$body.find('#priceWithSellerFee > span.totalPricePlaceholder')
				.text(totalPrice + priceWithSellerFee);
		}
	}

	self.$body.find("input[name='sellerFee']").on("change", function (e) {
		var $input = $(e.currentTarget);
		var $divAdminSellerFee = self.$body.find(".div-admin-seller-fee");
		if ($input.prop("checked")) {
			$divAdminSellerFee.show();
			self.$body.find('#totalPrice').hide();
			self.$body.find('#priceWithSellerFee').show();
			calculatePriceWithSellerFee();
		} else {
			$divAdminSellerFee.hide();
			self.$body.find('#priceWithSellerFee').hide();
			self.$body.find('#totalPrice').show();
		}
	});

	self.$body.find('#priceWithSellerFee').hide();
	var sellerFees = self.$body.find('input[id*="sellerFeeThere"], input[id*="sellerFeeBack"]');
	sellerFees.on('change', calculatePriceWithSellerFee);


	self.$body.find("input[name='adminFillEurotoursData']").on("change", function (e) {
		var $input = $(e.currentTarget);
		var $name = self.$body.find("input[name='name']");
		var $email = self.$body.find("input[name='email']");
		var $phone = self.$body.find("input[name='phone']");

		if ($input.prop("checked")) {
			$name.val("Eurotours s.r.o.");
			$email.val("info@eurotours.cz");
			$phone.val("724132365");
		} else {
			$name.val("");
			$email.val("");
			$phone.val("");
		}
	});

	self.$body.find("input").on("focus", function (e) {
		self.hideInputError($(e.currentTarget));
	});

	if (self.$dateBirthInput) {
		self.$dateBirthInput.datepicker({
			format: dateFormat,
			language: language,
			weekStart: 1,
			keyboardNavigation: false
		});
	}
};

FrontendOrderPage.prototype.validateEmail = function(email) {
	var re = /^(([-\w\d]+)(\.[-\w\d]+)*@([-\w\d]+)(\.[-\w\d]+)*(\.([a-zA-Z]{2,5}|[\d]{1,3})){1,2})$/;
	return re.test(email.toLowerCase());
};

FrontendOrderPage.prototype.checkOrderForm = function () {
	var self = this;

	var ok = true;

	$terms = self.$body.find("input[name='termsAndConditions']");
	if ($terms.length === 1 && !$terms.is(":checked")) {
		ok = false;
		self.showInputError($terms);
	}

	$name = self.$body.find("input[name='name']");
	if ($name.val().length < 4) {
		ok = false;
		self.showInputError($name);
	}

	$email = self.$body.find("input[name='email']");
	if (!self.validateEmail($email.val())) {
		ok = false;
		self.showInputError($email);
	}

	$phone = self.$body.find("input[name='phone']");
	if ($phone.val().length < 8) {
		ok = false;
		self.showInputError($phone);
	}

	self.$body.find("input[id^='persons_name_']").each(function () {
		$name = $(this);
		if ($name.val().length < 4) {
			ok = false;
			self.showInputError($name);
		}
	});

	self.$body.find("input[id^='persons_phone_']").each(function () {
		$phone = $(this);
		if ($phone.val().length < 8) {
			ok = false;
			self.showInputError($phone);
		}
	});

	self.$body.find("input[id^='persons_documentNumber_']").each(function () {
		$documentNumber = $(this);
		console.log($documentNumber.val());
		if ($documentNumber.val().length < 2) {
			ok = false;
			self.showInputError($documentNumber);
		}
	})

	return ok;
};

FrontendOrderPage.prototype.showInputError = function ($input) {
	$input.parents(".form-group").find(".help-block").show();
	$input.parents(".form-group").addClass("has-error");
};

FrontendOrderPage.prototype.hideInputError = function ($input) {
	$input.parents(".form-group").find(".help-block").hide();
	$input.parents(".form-group").removeClass("has-error");
};

pages['order'] = FrontendOrderPage;
