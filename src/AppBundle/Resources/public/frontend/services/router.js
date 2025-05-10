function FrontendRouterService() { }

FrontendRouterService.prototype.ajaxExternalRoutes = function (search) {
	return "/_ajax/search/" + search + "/external-routes";
};

services['frontend.router'] = new FrontendRouterService;