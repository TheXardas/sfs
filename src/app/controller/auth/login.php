<?
if(\Session\getCurrentUser()) {
	\Controller\redirect('/');
}

list($login, $password) = \Controller\filterParams([
	'login' => ['string', null],
	'password' => ['string', null],
], $params);

$user = \AuthHelper\login($login, $password);

if ($user) {
	\Controller\redirect('/', false, true);
}
else {
	return [
		'ctx' => [
			'error' => 'Не удалось войти под этими логином и паролем.'
		],
	];
}