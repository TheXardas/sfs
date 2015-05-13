<?

namespace Request;

function parseUri($uri) {
	return parse_url($_SERVER['REQUEST_URI']);
}