<?
	// TODO сделать настраиваемым.
	// error_reporting(E_ALL);
	ini_set('display_errors', 0);

	define('SRC_DIR', __DIR__);
	define('LIB_DIR', SRC_DIR.'/lib');
	define('PUBLIC_DIR', SRC_DIR.'/../public_html');

	define('APP_DIR', SRC_DIR.'/app');
	define('CONFIG_DIR', APP_DIR.'/config');
	define('HELPER_DIR', APP_DIR.'/helpers');
	define('MODEL_DIR', APP_DIR.'/model');
	define('VIEW_DIR', APP_DIR.'/view');
	define('CONTROLLER_DIR', APP_DIR.'/controller');

	require_once LIB_DIR.'/Request.php';
	require_once LIB_DIR.'/Session.php';
	require_once LIB_DIR.'/ErrorHandler.php';

	\Session\start();