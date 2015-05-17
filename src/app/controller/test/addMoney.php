<?
// ! только для демо
require_once HELPER_DIR.'/Auth.php';
require_once MODEL_DIR.'/Bank.php';

\Bank\payTo(\Session\getCurrentUser(), 10000, null, TEST_ADD_MONEY_OPERATION);

\Controller\redirect('/', false, true);