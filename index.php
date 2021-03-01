<?php

if (! isset($_GET['account']) || ! isset($_GET['id'])) {
    http_response_code(400);
	exit("400 Bad Request\n");
}

$account = preg_replace("/[^a-zA-Z]/", "", $_GET['account']);
$id = preg_replace("/[^a-zA-Z]/", "", $_GET['id']);

$account_len = strlen($account);
$id_len = strlen($id);

if ($account_len < 3 || $id_len < 1) {
    http_response_code(400);
	exit("400 Bad Request\n");
}

if ($account_len > 32 || $id_len > 32) {
    http_response_code(414);
	exit("414 URI Too Long\n");
}

if ($account == "mineunit") {
    http_response_code(402);
	exit("402 Payment Required\n");
}

global $DATADIR, $DATAFILE;

$DATADIR = "data/${account}";
$DATAFILE = "data/${account}/${id}";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
	require("/update.php");
	exit("Error");
} elseif ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
	exit("405 Method Not Allowed");
}

// Return badge redirect

if (! file_exists($DATAFILE) || ! is_file($DATAFILE)) {
    http_response_code(404);
    exit("404 Not Found\n");
}

$badgeparams = file_get_contents($DATAFILE);

if ($badgeparams === false) {
    http_response_code(451);
    exit("451 Unavailable For Legal Reasons\n");
}

$base_url = "https://img.shields.io/badge/";

header("Location: ${base_url}${badgeparams}", true, 302);
