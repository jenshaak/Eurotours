function BackendExternalCitiesPage($body) {
	this.$body = $body;

	this.create();
}

BackendExternalCitiesPage.prototype.create = function () {
	var self = this;

	self.$body.find("tr[data-url]").on("click", function (e) {
		var $tr = $(e.currentTarget);
		var url = $tr.attr("data-url");

		$.ajax(url, { method: "get" })
			.done(function (data) {
				BootstrapDialog.show({
					title: "Nastavení města",
					closeByKeyboard: false,
					message: $(data),
					onshown: function (dialog) {
						dialog.getModalBody().find("select").selectpicker();
						dialog.getModalBody().find("form").on("submit", function (e) {
							e.preventDefault();
							var $form = $(e.currentTarget);
							$.ajax($form.attr("action"), { method: "post", type: "json", data: $form.serializeArray() })
								.done(function (data) {
									$.each(data, function(k, v) {
										$tr.find("td[data-carrier='" + k + "']").html(v);
									});
								});
						});
					},
					buttons: [{
						label: "Uložit změny",
						hotkey: 13,
						cssClass: 'btn-success',
						action: function (dialog) {
							dialog.getModalBody().find("form").submit();

							dialog.close();
						}
					}]
				});
			});
	});

};

pages['backend_externals_cities'] = BackendExternalCitiesPage;