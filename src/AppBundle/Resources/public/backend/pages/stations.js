function BackendStationsPage($body) {
	this.$body = $body;

	this.create();
}

BackendStationsPage.prototype.create = function () {
	var self = this;

	self.$body.find("select[data-action='selectCity']").on("change", function (e) {
		$(e.currentTarget).parents("form").submit();
	});

	self.$body.find("table[data-action='tableStations'] input").on("change", function (e) {
		var $input = $(e.currentTarget);
		var station = $input.attr("data-station");
		var language = $input.attr("data-language");
		$.ajax(services['backend.router'].station(station), {
			data: { value: $input.val(), language: language },
			method: "post"
		});
	});

};

pages['backend_stations'] = BackendStationsPage;