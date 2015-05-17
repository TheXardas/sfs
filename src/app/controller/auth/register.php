<?
if(\Session\getCurrentUser()) {
	\Controller\redirect('/');
}

list($login, $name, $password, $passwordConfirm, $role) = \Controller\filterParams([
	'login' => ['string', null],
	'name' => ['string', null],
	'password' => ['string', null],
	'password-confirm' => ['string', null],
	'role' => ['int', 0],
], $params);

$user = \User\create($name, $login, $password, $passwordConfirm, $role);
$user = \AuthHelper\login($login, $password);

if ($user) {
	\Controller\redirect('/', false, true);
}