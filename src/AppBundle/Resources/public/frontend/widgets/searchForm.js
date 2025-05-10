function FrontendSearchFormWidget($object) {
	this.$object = $object;

	this.$dateDayInput = this.$object.find("input[name='searchDay']");
	this.$dateBackInput = this.$object.find("input[name='searchBack']");
	this.$typeReturnCheckbox = this.$object.find("input[name='type']");
	this.$onlyOpenCheckbox = this.$object.find("input[name='onlyOpen']");
	this.$onlyOpenCheckboxLabel = this.$object.find("label[id='onlyOpen']");

	this.create();
}

FrontendSearchFormWidget.prototype.create = function () {

	var self = this;

	self.$typeReturnCheckbox.on("change", function () {
		if (self.$typeReturnCheckbox.is(":checked")) {
			self.$dateBackInput.show();
			self.$onlyOpenCheckboxLabel.show();
		} else {
			self.$dateBackInput.hide();
			self.$onlyOpenCheckboxLabel.hide();
		}
	});

	self.$onlyOpenCheckbox.on("change", function () {
		if (self.$onlyOpenCheckbox.is(":checked")) {
			self.$dateBackInput.prop("disabled", true);
		} else {
			self.$dateBackInput.prop("disabled", false);
		}
	});

	self.$dateDayInput.datepicker({
		format: dateFormat,
		language: language,
		weekStart: 1,
		keyboardNavigation: false
	}).off("changeDate").on("changeDate", function (e) {
		if (self.$typeReturnCheckbox.is(":checked") && self.$dateBackInput.datepicker("getDate") <= self.$dateDayInput.datepicker("getDate") && !self.$onlyOpenCheckbox.is(":checked")) {
			document.getElementById("searchDay").setCustomValidity(translations['searchForm.switchDates']);
			document.getElementById("searchBack").setCustomValidity("");
		} else {
			document.getElementById("searchDay").setCustomValidity("");
			document.getElementById("searchBack").setCustomValidity("");
		}
	});

	self.$dateBackInput.datepicker({
		format: dateFormat,
		language: language,
		weekStart: 1,
		keyboardNavigation: false
	}).off("changeDate").on("changeDate", function (e) {
		if (self.$typeReturnCheckbox.is(":checked") && self.$dateBackInput.datepicker("getDate") <= self.$dateDayInput.datepicker("getDate") && !self.$onlyOpenCheckbox.is(":checked")) {
			document.getElementById("searchBack").setCustomValidity(translations['searchForm.switchDatesSecond']);
			document.getElementById("searchDay").setCustomValidity("");
		} else {
			document.getElementById("searchBack").setCustomValidity("");
			document.getElementById("searchDay").setCustomValidity("");
		}
	});

	self.$object.on("submit", function (e) {
		if (self.$typeReturnCheckbox.is(":checked") && !self.$onlyOpenCheckbox.is(":checked")) {
			var fromDate = self.$dateDayInput.datepicker("getDate");
			var toDate = self.$dateBackInput.datepicker("getDate");
			if (toDate <= fromDate) {
				e.preventDefault();
				BootstrapDialog.show({
					title: translations['searchForm.checkDate'],
					message: translations['searchForm.switchDates']
				});
			}
		}
	});
};

widgets['frontend.searchForm'] = FrontendSearchFormWidget;
