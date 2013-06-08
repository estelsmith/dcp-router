About
=====
The dcp-router package provides a very minimalistic, hierarchical MVC router.

Example
-------
	$hierarchy = array(
		'admin' => 'MyApp\Routers\Admin'
	);
	$controller_prefix = 'MyApp\Controllers';

	$router = new DCP\Router\Base($hierarchy, $controller_prefix);

	$router->dispatch('/user/create');
	// This will attempt to dispatch to MyApp\Controllers\UserController::createAction()

	$router->dispatch('/admin/user/create');
	// This will attempt to dispatch to the router MyApp\Routers\Admin::dispatch('/user/create')