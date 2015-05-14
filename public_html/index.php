<?php
require_once '../src/bootstrap.php';
require_once LIB_DIR.'/Router.php';
require_once LIB_DIR.'/Controller.php';

// Парсим урл.
// Определяем статус авторизации пользователя.
// Проверяем возможность запускать экшн для авторизованности/неавторизованности пользователя.
// Если прав нет - редиректим на "/".
// Если есть - отображаем этот экшн.
// Это должно разруливаться в роутере.

// Надо придумать возможность вызывать экшн как виджет - встраивать его в шаблон

$controllerArr = \Router\getActionFromUri($_SERVER['REQUEST_URI']);

\Controller\process($controllerArr['controller'], $controllerArr['action'], $controllerArr['params']);