<?
namespace Request;

function parseUri($uri) {
	return parse_url($_SERVER['REQUEST_URI']);
}

function isAjax() {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function getParams() {
	return $_REQUEST;
}