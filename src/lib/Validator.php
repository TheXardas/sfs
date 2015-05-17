<?
namespace Validator;

// TODO implement
function validateInteger($value, $default = NULL) {
	if (!is_scalar($value) || !is_numeric($value)) {
		return $default;
	}
	return intval($value);
}

function validateString($value, $default = NULL) {

}