<?
namespace Config;

/**
 * Возвращает значение конфига по ключу.
 *
 * @param $key
 * @return mixed
 * @throws \Exception
 */
function get($key) {
	$config = getAll();
	if (isset($key)) {
		return $config[$key];
	}
	throw new \Exception(sprintf('Undefined config key: %1s', $key));
}

/**
 * Парсит, кэширует и возвращает конфиг.
 *
 * @return array
 */
function getAll() {
	static $config;
	if (is_null($config)) {
		$config = parse_ini_file(CONFIG_DIR.'/prod.ini');
	}
	return $config;
}