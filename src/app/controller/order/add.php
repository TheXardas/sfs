<?
	sleep(1);
require_once MODEL_DIR.'/Order.php';

list($subject, $description, $price) = \Controller\filterParams([
	'subject' => ['string', ''],
	'description' => ['string', ''],
	'price' => ['int', 0],
], $params);

try {
	$result = \Order\create($subject, $description, $price);
}
catch (Exception $e) {
	$error = $e->getMessage();
}

if ($error) {
	return [
		'error' => $error,
		'forceJson' => true,
	];
}
else {
	\Controller\redirect('/');
}