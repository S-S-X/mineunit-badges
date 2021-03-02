<?php

require_once('config.php');
require_once('utils.php');

// query_param will throwup, return error and exit in case of bad data
$account = query_param('account');
$project = query_param('project');
$id = query_param('id');

if ($_SERVER['REQUEST_METHOD'] === "POST") {
	// If you want secret as Authorization: Bearer token then just convert with Apache, NGINX, HAProxy, whatever
	$secret = query_param('secret', 32, 32);
	require("update.php");
	create($account, $project, $id, $secret);
	// Create should return 201 and exit if succeed
	throwup(500);
} elseif ($_SERVER['REQUEST_METHOD'] !== "GET") {
    throwup(405);
}

// Return badge redirect

$datafile = DATADIR."/${account}/${project}/${id}";
if (! file_exists($datafile) || ! is_file($datafile)) { throwup(404); }

$badgeparams = file_get_contents($datafile);
if ($badgeparams === false) { throwup(451); }

$base_url = "https://img.shields.io/badge/";
header("Cache-Control: no-cache");
header("Location: ${base_url}${badgeparams}", true, 302);
