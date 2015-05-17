<?
namespace Routes;

return [
	'join' => [
		'controller' => 'auth',
		'action' => 'registerForm',
		'params' => [
			'noLoginRequired' => true,
		],
	],
	'login' => [
		'controller' => 'auth',
		'action' => 'loginForm',
		'params' => [
			'noLoginRequired' => true,
		],
	],
	'logout' => [
		'controller' => 'auth',
		'action' => 'logout',
	],
	'auth' => [
		'controller' => 'auth',
		'action' => 'login',
		'params' => [
			'noLoginRequired' => true,
		],
	],
	'register' => [
		'controller' => 'auth',
		'action' => 'register',
		'params' => [
			'noLoginRequired' => true,
		],
	],
	'neworder' => [
		'controller' => 'order',
		'action' => 'addForm',
	],
	// Этот экшн создан исключительно для демо
	'addMoney' => [
		'controller' => 'test',
		'action' => 'addMoney',
	],
];