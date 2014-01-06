About
=====
The dcp-router package provides a very minimalistic, hierarchical MVC router.

[![Build Status](https://travis-ci.org/estelsmith/dcp-router.png?branch=master)](https://travis-ci.org/estelsmith/dcp-router)
[![Coverage Status](https://coveralls.io/repos/estelsmith/dcp-router/badge.png)](https://coveralls.io/r/estelsmith/dcp-router)

Example
-------
	$hierarchy = array(
		'admin' => 'MyApp\Routers\Admin'
	);
	$controller_prefix = 'MyApp\Controllers';

	$router = new DCP\Router\BaseRouter($hierarchy, $controller_prefix);

	$router->dispatch('/user/create');
	// This will attempt to dispatch to MyApp\Controllers\UserController::createAction()

	$router->dispatch('/admin/user/create');
	// This will attempt to dispatch to the router MyApp\Routers\Admin::dispatch('/user/create')