function BackendUsersPage($body) {
	this.$body = $body;
	this.$roleSelect = this.$body.find("select[name='role']");
	this.$carrierSelect = this.$body.find("select[name='carrier']");
	this.$divCarrierSelect = this.$body.find("div[id='selectCarriers']");
	this.$divSellerSelect = this.$body.find("div[id='selectSellers']");
	this.$nameInput = this.$body.find("input[name='name']");
	this.$phoneInput = this.$body.find("input[name='phone']");

	this.create();
}

BackendUsersPage.prototype.create = function () {
	var self = this;

	self.$roleSelect.on("change", function () {
		self.$nameInput.prop("disabled", true);
		self.$phoneInput.prop("disabled", true);
		self.$carrierSelect.prop("disabled", true);
		self.$divCarrierSelect.hide();
		self.$divSellerSelect.hide();

		if (self.$roleSelect.val() === "ROLE_CARRIER") {
			self.$divCarrierSelect.show();
			self.$carrierSelect.prop("disabled", false);

		} else if (self.$roleSelect.val() === "ROLE_SELLER") {
			self.$nameInput.prop("disabled", false);
			self.$phoneInput.prop("disabled", false);
			self.$divSellerSelect.show();
		}
	});

	self.$body.find(".editableUsername").editable({
		name: "username",
		type: "text",
		placement: "right",
		title: "Změnit uživatelské jméno",
		emptytext: "disabled"
	});

	self.$body.find(".editableEmail").editable({
		name: "email",
		type: "email",
		placement: "right",
		title: "Změnit e-mail uživatele",
		emptytext: "disabled"
	});

	self.$body.find("input[data-action='toggleExternalRoutesAllowed']").on("change", function (e) {
		var $checkbox = $(e.target || e.current.target);

		$.ajax($checkbox.attr('data-url'), {
			type: 'post',
			data: {
				name: 'externalRoutesAllowed',
				value: + $checkbox.prop('checked'),
				pk: $checkbox.attr('data-pk'),
			},
		});
	});

	self.$body.find("[data-action='changePassword']").on("click", function (e) {
		BootstrapDialog.show({
			title: "Změna hesla",
			message: $(self.$body.find("[data-modal-body='changePassword']").html()),
			onshown: function (dialog) {
				dialog.getModalBody().find("form").attr("action", $(e.currentTarget).attr("data-url"));
				dialog.getModalBody().find("input").focus();
			},
			size: BootstrapDialog.SIZE_SMALL,
			buttons: [{
				label: "Změnit heslo",
				cssClass: 'btn-success',
				action: function (dialog) {
					dialog.getModalBody().find("form").submit();
					dialog.close();
				}
			}]
		});
	});
};

pages['backend_users'] = BackendUsersPage;
