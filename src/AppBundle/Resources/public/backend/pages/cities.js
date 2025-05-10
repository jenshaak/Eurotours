function BackendCitiesPage($body) {
	this.$body = $body;

	this.create();
}

BackendCitiesPage.prototype.create = function () {
	var self = this;

	self.$body.find("select[data-action='selectCountry']").on("change", function (e) {
		$(e.currentTarget).parents("form").submit();
	});

	self.$body.find("table[data-action='tableCities'] input").on("change", function (e) {
		var $input = $(e.currentTarget);
		var city = $input.attr("data-city");
		var language = $input.attr("data-language");
		$.ajax(services['backend.router'].city(city), {
			data: { value: $input.val(), language: language },
			method: "post"
		});
	});

	self.$body.find("button[data-action='removeCity']").on("click", function (e) {
		BootstrapDialog.confirm("Opravdu odstranit toto město?", function (result) {
			if (result) $(e.currentTarget).parents("form").submit();
		});
	});

};

pages['backend_cities'] = BackendCitiesPage;