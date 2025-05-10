function BackendLoaderService() { }

BackendLoaderService.prototype.show = function () {
	$(".loading").show();
};

BackendLoaderService.prototype.hide = function () {
	$(".loading").hide();
};

services['backend.loader'] = new BackendLoaderService;
