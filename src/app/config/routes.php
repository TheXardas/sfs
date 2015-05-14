<?
namespace Routes;

return [
	'join' => [
		'controller' => 'auth',
		'action' => 'registerForm',
	],
	'login' => [
		'controller' => 'auth',
		'action' => 'loginForm',
	],
	'auth' => [
		'controller' => 'auth',
		'action' => 'login',
	]
];