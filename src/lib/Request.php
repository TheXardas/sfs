<?
namespace Request;

/**
 * Является ли запрос аяксовым
 *
 * @todo можно еще посылать специальный заголовок, чтобы избежать подделку этого. Правда хз зачем это подделывать.
 * @return bool
 */
function isAjax() {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Возвращает параметры запроса
 *
 * @return mixed
 */
function getParams() {
	if (isPost()) {
		return $_POST;
	}
	else {
		return $_GET;
	}
}

/**
 * Post-ли запрос
 * @return bool
 */
function isPost() {
	return $_SERVER['REQUEST_METHOD'] === 'POST';
}