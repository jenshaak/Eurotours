function FrontendRoutesSearchPage($body) {
	this.$body = $body;

	this.$routesThere = $body.find("#routes-there");
	this.$routesBack = $body.find("#routes-back");

	this.$loadingBar = $body.find("[data-action='loadingBar']");

	this.searchExternalIteration = 0;

	this.create();
}

FrontendRoutesSearchPage.prototype.create = function () {
	var self = this;

	if (search) {
		self.callNextSearchExternal();
	}

	self.$body.find("div#search-bar button[data-action='createOrder']").on("click", function () {
		self.createOrder();
	});

	self.$body.find("input").on("keyup", function () {
		self.recountTotalPrice();
	});

	self.$body.on("click", function () {
		self.recountTotalPrice();
	});

	self.recountTotalPrice();
};

FrontendRoutesSearchPage.prototype.createOrder = function () {
	var self = this;

	if (self.$body.find("#routes-back").length > 0) {
		if (!self.checkPersonsTwoWay()) {
			BootstrapDialog.show({
				title: translations['order.selectRoute'],
				message: translations['order.selectRouteTwoWayDescription']
			});

			return;
		}

	} else {
		if (!self.checkPersonsOneWay()) {
			BootstrapDialog.show({
				title: translations['order.selectRoute'],
				message: translations['order.selectRouteDescription']
			});

			return;
		}
	}

	self.$body.find("form[data-action='formCreateOrder']").submit();
};

FrontendRoutesSearchPage.prototype.checkPersonsTwoWay = function () {
	var self = this;
	var sumPersons1 = 0;
	self.$body.find("[data-direction='there']").each(function (k, v) {
		$(v).find("tr[data-route-tariff]").each(function (k, tr) {
			if (parseFloat($(tr).find("input").val())) {
				sumPersons1 = sumPersons1 + parseFloat($(tr).find("input").val());
			}
		});
	});

	if (sumPersons1 == 0) return false;

	var sumPersons2 = 0;
	self.$body.find("[data-direction='back']").each(function (k, v) {
		$(v).find("tr[data-route-tariff]").each(function (k, tr) {
			var $tr = $(tr);
			if (parseFloat($tr.find("input").val())) {
				sumPersons2 = sumPersons2 + parseFloat($tr.find("input").val());
			}
		});
	});

	return sumPersons1 == sumPersons2;
};

FrontendRoutesSearchPage.prototype.checkPersonsOneWay = function () {
	var self = this;
	var sumPersons1 = 0;
	self.$body.find("[data-direction='there']").each(function (k, v) {
		$(v).find("tr[data-route-tariff]").each(function (k, tr) {
			if (parseFloat($(tr).find("input").val())) {
				sumPersons1 = sumPersons1 + parseFloat($(tr).find("input").val());
			}
		});
	});

	return sumPersons1 > 0;
};

FrontendRoutesSearchPage.prototype.recountTotalPrice = function () {
	var self = this;

	var totalPrice = 0;
	var maxTotalPrice = 0;
	var variablePrice = false;
	self.$body.find("[data-route]").each(function (k, v) {
		if (variablePrice) return;
		$(v).find("tr[data-route-tariff]").each(function (k, tr) {
			var $tr = $(tr);
			var amount = parseFloat($tr.find("input").val());

			if ($(v).attr("data-price") == 0 && amount > 0) {
				variablePrice = true;
				return;
			}

			if (parseFloat($tr.find("input").val())) {
				totalPrice = totalPrice + (parseFloat($tr.attr("data-price")) * amount);
				if ($tr.attr("data-max-price")) {
					maxTotalPrice = maxTotalPrice + (parseFloat($tr.attr("data-max-price")) * amount);
				} else {
					maxTotalPrice = maxTotalPrice + (parseFloat($tr.attr("data-price")) * amount);
				}

			}
		});
	});

	if (!variablePrice) {
		if (totalPrice == maxTotalPrice) {
			self.$body.find("#totalPrice").html((Math.round(totalPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString);
		} else {
			self.$body.find("#totalPrice").html(
				(Math.round(totalPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
				+ " - " +
				(Math.round(maxTotalPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
			);
		}
	} else {
		self.$body.find("#totalPrice").html(translations["route.variablePrice"]);
	}
};


FrontendRoutesSearchPage.prototype.callNextSearchExternal = function () {
	var self = this;

	setTimeout(function () {
		$.ajax(services['frontend.router'].ajaxExternalRoutes(search))
			.done(function (data) {
				/** @namespace data.processing */
				if (data.processing > 0 && self.searchExternalIteration < 10) self.callNextSearchExternal();
				else self.hideLoadingBar();

				self.searchExternalIteration = self.searchExternalIteration + 1;
				self.refreshLoadingBar();

				self.addRoutes(data);
			});
	}, 1000);
};

FrontendRoutesSearchPage.prototype.refreshLoadingBar = function () {
	var self = this;

	self.$loadingBar
		.attr("aria-valuenow", self.searchExternalIteration)
		.css("width", self.searchExternalIteration + "0%");
};

FrontendRoutesSearchPage.prototype.hideLoadingBar = function () {
	var self = this;

	self.$loadingBar.parent().hide();
};

FrontendRoutesSearchPage.prototype.addRoutes = function (data) {
	var self = this;

	/** @namespace data.there */
	/** @namespace data.back */

	$.map(data.there.routeWidgets, function (routeWidget) {
		var inserted = false;
		var $routeWidget = $(routeWidget);
		self.$routesThere.find("[data-route]").map(function (key, object) {
			if (inserted) return;
			var $route = $(object);
			if (parseFloat($route.attr("data-order")) >= parseFloat($routeWidget.attr("data-order"))) {
				$route.before($routeWidget);
				inserted = true;
			}
		});
		if (!inserted) self.$routesThere.find("script").last().before($routeWidget);
	});

	$.map(data.back.routeWidgets, function (routeWidget) {
		inserted = false;
		var $routeWidget = $(routeWidget);
		self.$routesBack.find("[data-route]").map(function (key, object) {
			if (inserted) return;
			var $route = $(object);
			if (parseFloat($route.attr("data-order")) >= parseFloat($routeWidget.attr("data-order"))) {
				$route.before($routeWidget);
				inserted = true;
			}
		});
		if (!inserted) self.$routesBack.find("script").last().before($routeWidget);
	});
};

pages['routes_search'] = FrontendRoutesSearchPage;
