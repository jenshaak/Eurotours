function BackendSeatsPlanWidget($object) {
	this.$object = $object;
	this.$table = $object.find("table.seats-plan");
	this.$form = $object.find("form");
	this.$calendar = $object.find("div.calendar");
	this.$dateDay = $object.find("input[name='dateDay']");

	this.create();
}

BackendSeatsPlanWidget.prototype.create = function () {
	var self = this;

	self.refreshEvents();
	self.refreshSeats();

	self.$object.find("button[data-action='addRow']").off("click").on("click", function () {
		self.addRow();
	});

	self.$object.find("button[data-action='setForDate']").off("click").on("click", function (e) {
		self.toggleCalendar(true);
	});

	self.$object.find("button[data-action='setForSchedule']").off("click").on("click", function (e) {
		self.toggleCalendar(false);
	});

	self.$form.off("submit").on("submit", function (v) {
		v.preventDefault();
		self.saveData();
	});

	self.$calendar.datepicker({
		format: "d. m. yyyy",
		language: "cs",
		keyboardNavigation: false
	}).on("changeDate", function (e) {
		self.changeDate(e.date.getFullYear() + "-" +  (e.date.getMonth()+1) + "-" + e.date.getDate())
	});

	if (self.$dateDay.val()) {
		self.$calendar.datepicker("update", new Date(self.$dateDay.val()));
	}
};

BackendSeatsPlanWidget.prototype.refreshEvents = function () {
	var self = this;

	self.$table.find("button[data-action='removeRow']").off("click").on("click", function (e) {
		self.removeRow($(e.currentTarget).parents("tr"));
	});

	self.$table.find("td").not(".control").off("click").on("click", function (e) {
		self.toggleSeat($(e.currentTarget));
	});
};

BackendSeatsPlanWidget.prototype.toggleCalendar = function (showCalendar) {
	var self = this;

	if (showCalendar) {
		self.$object.find("button[data-action='setForDate']").hide();
		self.$object.find("button[data-action='setForSchedule']").show();
		self.$calendar.show();
	} else {
		self.$object.find("button[data-action='setForDate']").show();
		self.$object.find("button[data-action='setForSchedule']").hide();
		self.$calendar.hide();

		services['backend.loader'].show();

		$.ajax(self.$form.attr("action"), { method: "GET" })
			.done(function (data) {
				self.$object.parent().html($(data.seatsPlanWidget));
				services['backend.loader'].hide();
			});
	}
};

BackendSeatsPlanWidget.prototype.saveData = function () {
	var self = this;

	var data = { seats: self.createFormData() };
	if (self.$dateDay.val()) {
		data['dateDay'] = self.$dateDay.val();
	}

	$.ajax(self.$form.attr("action"), {
		method: "post",
		data: data
	});
};

BackendSeatsPlanWidget.prototype.changeDate = function (date) {
	var self = this;

	services['backend.loader'].show();

	$.ajax(self.$form.attr("action"), { method: "GET", data: { dateDay: date } })
		.done(function (data) {
			self.$object.parent().html($(data.seatsPlanWidget));
			services['backend.loader'].hide();
		});
};

BackendSeatsPlanWidget.prototype.toggleSeat = function ($seat) {
	var self = this;

	if (!onlyForCarrier) {
		if ($seat.hasClass("seat-booked") || $seat.hasClass("seat-ticket")) {

		} else if ($seat.hasClass("seat")) {
			$seat.removeClass("seat").addClass("seat-available");
		} else if ($seat.hasClass("seat-available")) {
			$seat.removeClass("seat-available");
		} else {
			$seat.addClass("seat");
		}

	} else if (onlyForCarrier) {
		if ($seat.hasClass("seat-booked") || $seat.hasClass("seat-ticket")) {

		} else if ($seat.hasClass("seat")) {
			$seat.removeClass("seat").addClass("seat-available");
		} else {
			$seat.addClass("seat").removeClass("seat-available");
		}
	}

	self.refreshSeats();
};

BackendSeatsPlanWidget.prototype.refreshSeats = function () {
	var self = this;

	var number = 1;

	self.$table.find("td").not(".control").map(function (i, v) {
		var $v = $(v);
		$v.removeClass("seat-booked");
		$v.removeClass("seat-ticket");
		if ($v.hasClass("seat") || $v.hasClass("seat-available")) {
			$v.html($("<span>").text(number));
			if (ticketNumbers.indexOf(number) >= 0) {
				$v.addClass("seat-ticket");
			}
			if (bookedNumbers.indexOf(number) >= 0) {
				$v.addClass("seat-booked");
			}
			number++;
		} else {
			$v.html("");
		}
	});
};

BackendSeatsPlanWidget.prototype.createFormData = function () {
	var self = this;

	var data = [];

	self.$table.find("tr").map(function (y, tr) {
		var $tr = $(tr);
		$tr.find("td").not(".control").map(function(x, td) {
			var $td = $(td);
			if ($td.hasClass("seat") || $td.hasClass("seat-available")) {
				var d = {
					x: x,
					y: y,
					number: $td.text(),
					available: $td.hasClass("seat-available")
				};
				data.push(d);
			}
		});
	});

	return data;
};

BackendSeatsPlanWidget.prototype.removeRow = function ($tr) {
	var self = this;

	$tr.remove();
	self.refreshSeats();
};

BackendSeatsPlanWidget.prototype.addRow = function () {
	var self = this;

	var $tr = $("<tr>" +
		"<td class=\"seat\"></td>" +
		"<td class=\"seat\"></td>" +
		"<td></td>" +
		"<td class=\"seat\"></td>" +
		"<td class=\"seat\"></td>" +
		"<td class=\"control\"><button type=\"button\" class=\"btn btn-default btn-xs\" data-action=\"removeRow\">-</button></td>" +
		"</tr>");

	self.$table.find("tbody").append($tr);
	self.refreshEvents();
	self.refreshSeats();
};

widgets['backend.seatsPlan'] = BackendSeatsPlanWidget;
