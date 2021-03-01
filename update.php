<?php

$json = json_decode(file_get_contents("php://input"), true);
if (! is_array($json) || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
	exit("400 Bad Request\n");
}

function validate($data, $key) {
	$value = isset($data[$key]) ? $data[$key] : null;
	return is_string($value) && strlen($value) <= 32 && strlen($value) >= 1;
}

function validate_color($data, $key) {
	$value = isset($data[$key]) ? $data[$key] : null;
	return is_string($value) && (strlen($value) == 3 || strlen($value) == 6) && ctype_xdigit($value);
}

function encode($value) {
	return urlencode(str_replace("-","--",$value));
}

// Value is required, invalid values might be accepted if you pay enough. Alternatively use your own server.
if (! validate($json, 'value')) {
	http_response_code(402);
	exit("402 Payment Required\n");
}
$value = encode($json['value']);

// Label and color can very well be optional
$label = validate($json, 'label') ? encode($json['label']) : "Coverage";
// No need to encode after ctype_xdigit test passed
$color = validate_color($json, 'color') ? $json['color'] : "D0F055";

if (! file_exists($DATADIR)) {
    mkdir($DATADIR);
}

$data = "${label}-${value}-${color}";
if (file_put_contents($DATAFILE, $data) !== strlen($data)) {
	http_response_code(409);
	exit("409 Conflict\n");
}

// Seems like it worked, or at least it did not crash
http_response_code(201);
exit("201 Created\n");
