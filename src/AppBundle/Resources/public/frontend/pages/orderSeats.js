function FrontendOrderSeats($body) {
	this.$body = $body;
	this.$orderForm = $body.find("form[data-action='orderForm']");
	this.submitted = false;

	this.create();
}

FrontendOrderSeats.prototype.create = function () {
	var self = this;

	self.refreshSeats();

	self.$body.find("td[data-seat-number]").on("click", function (e) {
		var $td = $(e.currentTarget);
		if ($td.hasClass("seat-available")) {
			var seatNumber = $td.attr("data-seat-number");
			var $table = $td.parents("table");

			$table
				.parents("div.col-md-6")
				.first()
				.find("input[type='hidden'][name^='selectedSeat']")
				.first()
				.val(seatNumber);

			$table
				.parents("div.row")
				.first()
				.find("span.selected-seat-span")
				.first()
				.text(seatNumber);

			self.refreshSeats();
		}
	});

	self.$body.find("button[data-action='reservation']").on("click", function () {
		if (!self.submitted) {
			self.submitted = true;
			self.$orderForm.attr("action", self.$orderForm.attr("data-action-reservation"));
			self.$orderForm.submit();
		}
	});

	self.$body.find("button[data-action='buy']").on("click", function () {
		if (!self.submitted) {
			self.submitted = true;
			self.$orderForm.attr("action", self.$orderForm.attr("data-action-buy"));
			self.$orderForm.submit();
		}
	});

};

FrontendOrderSeats.prototype.refreshSeats = function () {
	var self = this;

	$.each(busIdents, function (i, busIdent) {
		var seatNumbers = [];

		self.$body.find("input[data-bus-ident='" + busIdent + "'][type='hidden'][name^='selectedSeat']").each(function (i, input) {
			var $input = $(input);
			var seatNumber = $input.val();

			$input.parent().find("td.seat-selected").removeClass("seat-selected");
			$input.parent().find("td[data-seat-number='" + seatNumber + "']").addClass("seat-selected");

			seatNumbers.push(seatNumber);
		});

		self.$body.find("table[data-bus-ident='" + busIdent + "'][data-order-person-route-tariff]").each(function (i, table) {
			var $table = $(table);
			$table.find("td.seat-other").removeClass("seat-other").addClass("seat-available");
			$.each(seatNumbers, function (i, seatNumber) {
				var $td = $table.find("td[data-seat-number='" + seatNumber + "']");
				if (!$td.hasClass("seat-selected")) {
					$td.removeClass("seat-available").addClass("seat-other");
				}
			});
		});
	});

};

pages['order_seats'] = FrontendOrderSeats;
