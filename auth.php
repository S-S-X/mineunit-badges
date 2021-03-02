<?php

require_once('config.php');
require_once('utils.php');

// Static stuff
const STAGE_INITIATE = 1;
const STAGE_COMPLETE = 2;

$auth_stage = isset($_GET['code']) ? STAGE_COMPLETE : STAGE_INITIATE;

function validate_state($state) {
	if (! is_string($state) || strlen($state) < 34) {
		return false;
	}
	$file_id = substr($state, 33);
	$statevar = substr($state, 0, 32);
	$statefile = TEMPDIR.'/'.STATEFILE_PREFIX.$file_id;
	$fileage = time() - filemtime($statefile);
	$savedstate = $fileage > 0 && $fileage < STATEFILE_EXPIRY ? file_get_contents($statefile) : false;
	return file_exists($statefile) && unlink($statefile) && $savedstate === $statevar;
}

function save_state() {
	$state = md5(rand());
	$statefile = tempnam(TEMPDIR, STATEFILE_PREFIX);
	if (file_put_contents($statefile, $state) === strlen($state)) {
		return "${state}-".substr(basename($statefile), strlen(STATEFILE_PREFIX));
	}
	return false;
}

function save_token($code) {
	$token = md5($code);
	$tokenfile = PRIVDIR."/${token}";
	$f = fopen($tokenfile, 'x');
	if (! $f) {
		return false;
	}
	if (fwrite($f, $code) !== strlen($code)) {
		return fclose($f);
	}
	return fclose($f) ? $token : false;
}

function auth_content($auth_stage) {
	switch ($auth_stage) {
		case STAGE_INITIATE:
			$state = save_state();
			if (! is_string($state)) {
				// State saving failed for some unknown reason
				return <<<EOT
				<p>Badges app failed to do what it should, maybe you should file a bug report?</p>
				<p></p>
EOT;
			}
			$auth_uri = AUTH_SERVER.'?client_id='.CLIENT_ID."&state=${state}&redirect_uri=".RETURN_URI;
			return <<<EOT
				<p>Badges app must mine and store some of your personal identification data.<br/>Go away if you disagree.</p>
				<a class="button" href="${auth_uri}">Authenticate with GitHub</a>
				<p></p>
EOT;
		case STAGE_COMPLETE:
			// We are not going to request access tokens as we are not going to access any user data.
			$code = query_param('code', 48, 1, false);
			$state = query_param('state', 48, 34, false);
			if (! validate_state($state)) {
				// State validation failed for some unknown reason
				return <<<EOT
				<p>Badges app failed to validate your authorization, maybe you should file a bug report?</p>
				<p></p>
EOT;
			}
			$token = save_token($code);
			if (! is_string($token)) {
				// State validation failed for some unknown reason
				return <<<EOT
				<p>Badges app failed to finish your authorization, maybe you should file a bug report?</p>
				<p></p>
EOT;
			}
			return <<<EOT
				<p>Badges app mined your data and dug up this token, use this token to authenticate with Mineunit Badges.</p>
				<span class="button">${token}</span>
				<p></p>
EOT;
	}
}

$content = auth_content($auth_stage);

?><!DOCTYPE html>
<html>
	<head>
		<title>Mineunit Badges</title>
		<style type="text/css">
			.center {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				text-align: center;
				min-height: 100vh;
				font-family: Helvetica,Arial,sans-serif;
			}
			.window {
				min-width: 20em;
				min-height: 10em;
				padding: 1em;
				border: 4px #000 double;
			}
			.inactive {
				background-color: #555;
			}
			.active {
				background-color: #dde;
			}
			.button {
				background-color: #192;
				color: #ddf;
				padding: 8px 20px;
				margin: 1em;
				text-decoration:none;
				font-weight:bold;
				border-radius:5px;
			}
			p {
				max-width: 20em;
			}
		</style>
	</head>
	<body class="inactive">
		<div class="center">
			<div class="active window">
				<h1>Mineunit Badges</h1>
				<?= $content ?>
			</div>
		</div>
	</body>
</html>
