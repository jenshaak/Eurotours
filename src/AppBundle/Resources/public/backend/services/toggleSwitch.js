function BackendToggleSwitchService() { }

BackendToggleSwitchService.prototype.apply = function () {
	$('.btn-toggle').click(function() {
		$(this).find('.btn').toggleClass('active');
	});
};

services['backend.toggleSwitch'] = new BackendToggleSwitchService;
