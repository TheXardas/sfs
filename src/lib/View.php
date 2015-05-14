<?
namespace View;

/**
 * TODO Придумать, как можно кэшировать содержимое блоков без хранения в памяти, при этом давая возможность переопределять блоки
 * без ООП, чутка, нетривиально.
 * TODO Добавить возможность передавать ключи в название блока - append, prepend и т.п.
 */

/**
 * Главный метод обработки шаблона
 *
 * @return string
 * @throws \Exception
 */
function render($view, $ctx = []) {
	ob_start();
	include VIEW_DIR."/layout.phtml";
	$layout = ob_get_clean();

	ob_start();
	include VIEW_DIR."$view.phtml";
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
 * @throws \Exception
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
 * @param array $params
 */
function widget($controller, $action, $params = array()) {
	\Controller\process($controller, $action, $params);
}