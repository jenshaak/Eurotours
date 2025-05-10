function FrontendRouteWidget($object) {
	this.$object = $object;

	this.create();
}

FrontendRouteWidget.prototype.create = function () {
	var self = this;

	self.$object.find('[data-toggle="tooltip"]').tooltip();

	self.$object.find("div[data-action='unselectRoute']").on("click", function (e) {
		e.stopPropagation();
		var $route = $(e.currentTarget).parent();
		var route = $route.attr("data-route");

		getParentWidget(self.$object, "frontend.routes").unselectRoute(route);
	});

	self.$object.on("click", function (e) {
		if (self.$object.hasClass("selected")) return;
		var $route = $(e.currentTarget);
		var route = $route.attr("data-route");

		getParentWidget(self.$object, "frontend.routes").selectRoute(route);
		self.updateTariffsFromThereDirection();
	});

	self.$object.find("button[data-action='showRouteTimeTableModal']").on("click", function (e) {
		var modalBody = $(e.currentTarget).parent().parent().find("[data-modal-body='routeTimeTable']");
		BootstrapDialog.show({
			title: "Jízdní řád",
			message: $(modalBody.html()),
			buttons: []
		});
	});

	self.$object.find("input[name^='routeTariff']").on("keydown", function (e) {
		self.$object.find(".alert-choice-tariffs").fadeOut();
	});

	self.$object.find(".tariff-controllers i").on("click", function (e) {
		var $controller = $(e.currentTarget);
		$input = $controller.parent().parent().find("input");
		var value = 0;
		if ($input.val()) {
			value = parseFloat($input.val());
		}

		if ($controller.attr("data-controller") == "plus") {
			$input.val(value + 1);
		} else if ($controller.attr("data-controller") == "minus") {
			if (value > 0) {
				$input.val(value - 1);
			}
		}
	});

	self.$object.find("button[data-action='createOrder']").on("click", function () {
		console.log(getPage());
		getPage().createOrder();
	});
};

FrontendRouteWidget.prototype.recount = function (original) {
	var self = this;

	var showPrice = 0;
	var maxShowPrice = 0;
	self.$object.find("tr[data-route-tariff]").each(function (k, v) {
		var $tr = $(v);
		if (original) $tr.attr("data-price", $tr.attr("data-original-price"));
		var price = parseFloat($tr.attr("data-price"));
		var maxPrice = parseFloat($tr.attr("data-max-price"));

		if (price) {
			if (maxPrice) {
				$tr.find("td.tariff-price").html(
					(Math.round(price * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
					+ " - " +
					(Math.round(maxPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
				);
				if (price > showPrice) {
					showPrice = price;
					maxShowPrice = maxPrice;
				}
			} else {
				$tr.find("td.tariff-price").html((Math.round(price * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString);
				if (price > showPrice) showPrice = price;
			}
		}
	});

	if (showPrice == 0 && maxShowPrice == 0) {
		self.$object.find(".price").hide();
		self.$object.find(".variable-price").show();
	} else {
		if (maxShowPrice != 0) {
			self.$object.find(".price").html(
				(Math.round(showPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
				+ " - " +
				(Math.round(maxShowPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString
			);
		} else {
			self.$object.find(".price").html((Math.round(showPrice * 100) / 100).toString().replace(".", ",") + " " + currentCurrencyString);
		}
		self.$object.find(".price").show();
		self.$object.find(".variable-price").hide();
	}
};

FrontendRouteWidget.prototype.updateTariffsFromThereDirection = function () {
	var self = this;
	var values = [];

	$("[data-route]").each(function (key, object) {
		var $route = $(object);
		if ($route.hasClass("selected") && $route.attr("data-direction") == "there") {
			$route.find("input[name^='routeTariff']").map(function (i, v) {
				$input = $(v);
				self.$object.find("input[data-tariff='" + $input.attr("data-tariff") + "']").val($input.val());
			});
		}
	});

	return values;
};

widgets['frontend.route'] = FrontendRouteWidget;
