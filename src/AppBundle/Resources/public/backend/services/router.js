function BackendRouterService() { }

BackendRouterService.prototype.hideLine = function (line) {
	return "/backend/_ajax/line/" + line + "/hide";
};

BackendRouterService.prototype.showLine = function (line) {
	return "/backend/_ajax/line/" + line + "/show";
};

BackendRouterService.prototype.onPayOnlineLine = function (line) {
	return "/backend/_ajax/line/" + line + "/payOnline/on";
};

BackendRouterService.prototype.offPayOnlineLine = function (line) {
	return "/backend/_ajax/line/" + line + "/payOnline/off";
};

BackendRouterService.prototype.city = function (city) {
	return "/backend/_ajax/city/" + city;
};

BackendRouterService.prototype.station = function (station) {
	return "/backend/_ajax/station/" + station;
};

services['backend.router'] = new BackendRouterService;
