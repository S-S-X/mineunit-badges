<?php

function throwup($code = 404, $message = null) {
	http_response_code($code);
	if ($message) {
		echo "${message}\n";
	}
	switch ($code) {
		case 200: exit("200 OK\n");
		case 201: exit("201 Created\n");
		case 400: exit("400 Bad Request\n");
		case 401: exit("401 Unauthorized\n");
		case 402: exit("402 Payment Required\n");
		case 403: exit("403 Forbidden\n");
		case 404: exit("404 Not Found\n");
		case 405: exit("405 Method Not Allowed\n");
		case 409: exit("409 Conflict\n");
		case 414: exit("414 URI Too Long\n");
		case 451: exit("451 Unavailable For Legal Reasons\n");
		case 500: exit("500 Internal Server Error\n");
	}
	exit("Error\n");
}

const REGEX_CLEANUP = "/[^-_a-zA-Z0-9]/";

function clean_str($value) {
	return preg_replace(REGEX_CLEANUP, "", $value);
}

function query_param($key, $max_length = 32, $min_length = 1, $fail = true) {
	if (! isset($_GET[$key])) {
		// Value does not exist
		return $fail && throwup(400, "Missing parameters");
	}
	$value = clean_str($_GET[$key]);
	$length = strlen($value);
	if ($length < $min_length) {
		// Value too short
		return $fail && throwup(400, "Missing parameters");
	}
	if ($length > $max_length) {
		// Value too long
		return $fail && throwup(414);
	}
	if ($value !== $_GET[$key]) {
		// Value is too short or contains forbidden characters
		return $fail && throwup(400, "Invalid parameters");
	}
	// Checks passed, return cleaned up value
	return $value;
}

function validate_token($token) {
	if (is_string($token)) {
		$clean_token = clean_str($token);
		$tokenfile = PRIVDIR."/${clean_token}";
		if (strlen($clean_token) > 2 && file_exists($tokenfile)) {
			return $tokenfile;
		}
	}
	return false;
}
