<?
if (!\User\canCreateOrders()) {
	// TODO ошибка 403?
	\Controller\redirect('/');
}

return [];