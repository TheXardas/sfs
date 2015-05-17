<?
require_once HELPER_DIR.'/Auth.php';

\AuthHelper\logout();

\Controller\redirect('/', false, true);