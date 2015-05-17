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
 * @todo Не лишним будет для post брать только post.
 * @return mixed
 */
function getParams() {
	return $_REQUEST;
}

/**
 * Post-ли запрос
 * @return bool
 */
function isPost() {
	return $_SERVER['REQUEST_METHOD'] === 'POST';
}