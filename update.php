<?php

require_once('config.php');
require_once('utils.php');

function validate($data, $key) {
	$value = isset($data[$key]) ? $data[$key] : null;
	return is_string($value) && strlen($value) <= 32 && strlen($value) >= 1;
}

function validate_color($data, $key) {
	$value = isset($data[$key]) ? $data[$key] : null;
	return is_string($value) && (strlen($value) == 3 || strlen($value) == 6) && ctype_xdigit($value);
}

function encode($value) {
	return rawurlencode(str_replace("-","--",$value));
}

function create($account, $project, $id, $secret) {

	if (! validate_token($secret)) { throwup(401); }

	$json = json_decode(file_get_contents("php://input"), true);
	if (! is_array($json) || json_last_error() !== JSON_ERROR_NONE) { throwup(400); }

	// Value is required, invalid values might be accepted if you pay enough. Alternatively use your own server.
	if (! validate($json, 'value')) { throwup(402); }
	$value = encode($json['value']);

	// Label and color can very well be optional
	$label = validate($json, 'label') ? encode($json['label']) : "Coverage";
	// No need to encode after ctype_xdigit test passed
	$color = validate_color($json, 'color') ? $json['color'] : "D0F055";

	$dir = DATADIR."/${account}/${project}";
	if (! is_dir($dir)) {
		mkdir($dir, 0755, true);
	}

	$data = "${label}-${value}-${color}";
	$datafile = "${dir}/${id}";
	if (file_put_contents($datafile, $data) !== strlen($data)) { throwup(409); }

	// Seems like it worked, or at least it did not crash
	throwup(201);
}
