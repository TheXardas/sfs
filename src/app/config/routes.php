<?
namespace Routes;

// Здесь лежат настройки маршрутизации.
// По-умолчанию маршрутизация работает так:
// Если в адресной строке набрать /$controller/$action, то запустится скрипт /src/app/controller/$controller/$action.php
// По-умолчанию на все экшны нужны права залогиненного пользователя. noLoginRequired - чтобы не проверять залогиненность

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