<?
if (!\User\canCreateOrders()) {
	// TODO ошибка?
	\Controller\redirect('/');
}

return [];