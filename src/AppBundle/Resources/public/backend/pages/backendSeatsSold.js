function BackendSeatsSoldPage($body) {
	this.$body = $body;

	this.create();
}

BackendSeatsSoldPage.prototype.create = function () {
	var self = this;

	self.$body.find("select[data-action='selectCarrier']").on("change", function (e) {
		$(e.currentTarget).parents("form").submit();
	});

};

pages['backend_seats_sold'] = BackendSeatsSoldPage;
