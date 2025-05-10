function FrontendRoutesWidget($object) {
	this.$object = $object;

	this.create();
}

FrontendRoutesWidget.prototype.create = function () {
	var self = this;

};

FrontendRoutesWidget.prototype.selectRoute = function (route) {
	var self = this;
	var $currentRoute = null;

	self.$object.find("input[data-input='route']").val(route);

	self.$object.find("[data-route]").each(function (key, object) {
		var $route = $(object);
		if ($route.attr("data-route") != route) {
			$route.find("div[class='open']").hide();
			$route.find("div[class='minimize']").show();
			$route.find("div[class='route-close']").hide();
			$route.removeClass("selected");
			$route.find("input[name^='routeTariff']").val("");
		} else {
			$currentRoute = $route;
			$route.find("div[class='open']").show();
			$route.find("div[class='minimize']").hide();
			$route.find("div[class='route-close']").show();
			$route.find(".alert-choice-tariffs").show();
			$route.addClass("selected");
		}
	});

	var $frontendWidgetScripts = $("script[data-widget-name='frontend.route']");

	if ($currentRoute.attr("data-direction") == "there") {
		$frontendWidgetScripts.each(function (k, v) {
			var routeWidget = registeredWidgets[$(v).attr("data-widget")];
			routeWidget.recount(true);
		});

		$.each(routeCombinations, function (key, routeTariffCombination) {
			$currentRoute.find("tr[data-route-tariff='" + routeTariffCombination.there + "']").each(function (key, tr) {
				var $thereRouteTariff = $(tr);
				var $backRouteTariff = self.$object.parent().find("#routes-back").find("tr[data-route-tariff='" + routeTariffCombination.back + "']");

				if ($backRouteTariff.length > 0) {
					if ($backRouteTariff.attr("data-percent")) {
						$backRouteTariff.attr("data-price", (routeTariffCombination.price - parseFloat($thereRouteTariff.attr("data-original-price")))/100 * (100 - parseFloat($backRouteTariff.attr("data-percent"))));
					} else {
						$backRouteTariff.attr("data-price", routeTariffCombination.price - parseFloat($thereRouteTariff.attr("data-original-price")));
					}
				}
			});
		});

		$frontendWidgetScripts.each(function (k, v) {
			var routeWidget = registeredWidgets[$(v).attr("data-widget")];
			routeWidget.recount(false);
		});
	}

	/*if (self.$object.attr("id", "routes-there") && $("#routes-back").length > 0) {
		window.scrollTo(0, $("#routes-back")[0].offsetTop);
	}*/
};

FrontendRoutesWidget.prototype.unselectRoute = function (route) {
	var self = this;

	self.$object.find("input[data-input='route']").val("");

	self.$object.find("[data-route]").each(function (key, object) {
		var $route = $(object);
		$route.find("div[class='open']").show();
		$route.find("div[class='minimize']").hide();
		$route.find("div[class='route-close']").hide();
		$route.removeClass("selected");
		$route.find("input[name^='routeTariff']").val("");
	});
};

widgets['frontend.routes'] = FrontendRoutesWidget;
