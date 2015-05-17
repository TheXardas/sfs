<?
$currentUser = \Session\getCurrentUser();

if ($currentUser['id']) {
	\Controller\redirect('/');
}

return [];