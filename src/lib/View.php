<?
namespace View;

/**
 * TODO Придумать, как можно кэшировать содержимое блоков без хранения в памяти, при этом давая возможность переопределять блоки
 * TODO Добавить возможность передавать ключи в название блока - append, prepend и т.п.
 */

/**
 * Прочитать и вернуть вьюху
 * Главный метод обработки шаблона
 *
 * @param string $view название вьюхи
 * @param array $ctx контекст, который подготовил контроллер для вью. Это данные которые собственно нужно отображать.
 * @param bool $embedded Является ли данный рендер - рендером встроенного виджета.
 *
 * @throws \Exception
 * @return string
 */
function render($view, $ctx = [], $embedded = false) {
	$layout = '%#main-content#%';
	if (!\Request\isAjax() && !$embedded) {
		ob_start();
		include VIEW_DIR . "/layout.phtml";
		$layout = ob_get_clean();
	}

	ob_start();
	$viewFile = VIEW_DIR."$view.phtml";
	if (!file_exists($viewFile)) {
		throw new \Exception(sprintf('Failed finding view! %1', $view));
	}
	include $viewFile;
	$content = ob_get_clean();

	$layout = injectBlock($layout, 'main-content', $content);

	if (preg_match('/%#(.*)#%/', $layout, $matches)) {
		throw new \Exception('Some blocks are not replaced in view: '. implode(' ', $matches));
	}
	return $layout;
}

/**
 * Расширить переданный шаблон.
 * По сути просто возвращает содержимое шаблона.
 *
 * @param $view
 * @return string
 */
function extend($view) {
	ob_start();
	include VIEW_DIR."/$view.phtml";
	$layoutContent = ob_get_clean();
	return $layoutContent;
}

/**
 * Начать/закончить блок.
 *
 * @param $parentBlockContent
 * @param $blockName
 *
 * @return mixed
 * @throws \Exception
 * @todo доработать
 */
function b($parentBlockContent, $blockName) {
	// Закрываем блок
	if (strpos($blockName, '/') === 0) {
		$blockName = ltrim($blockName, '/');
		$blockContent = ob_get_clean();

		return injectBlock($parentBlockContent, $blockName, $blockContent);
	}
	else {
		ob_start();
	}
}

/**
 * Заменяет содержимое плейсхолдера блока на переданное содержимое.
 *
 * @param $parentBlockContent
 * @param $blockName
 * @param $blockContent
 * @return mixed
 * @throws \Exception
 */
function injectBlock($parentBlockContent, $blockName, $blockContent) {
	if (strpos($parentBlockContent, '%#'.$blockName.'#%') !== false) {
		return str_replace('%#'.$blockName.'#%', $blockContent, $parentBlockContent);
	}
	else {
		throw new \Exception(sprintf('No block named %1% found in extended template', $blockName));
	}
}

/**
 * Выполняет полноценный экшн контроллера, и печатает результат в вывод.
 * Удобно использовать, чтобы вставлять виджеты и всякие повторно-используемые карточки.
 *
 * @param $controller
 * @param $action
 * @param array $rawParams
 */
function widget($controller, $action, $rawParams = array()) {
	$params = [
		'embedded' => true,
		'raw' => $rawParams,
	];
	\Controller\process($controller, $action, $params);
}


/**
 * Эскейпит строку для вставки в html
 * @param string $string
 * @return string
 */
function esc($string) {
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}