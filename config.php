<?php

// Basic config
// --------------------------
const CLIENT_ID   = '593bd63dafc5d6be5cab';
const RETURN_URI  = 'https://mineunit-badges.000webhostapp.com/auth';
const AUTH_SERVER = 'https://github.com/login/oauth/authorize';

// Server behavior config
// --------------------------
const STATEFILE_PREFIX = 'Mineunit-Badges-';
const STATEFILE_EXPIRY = 60 * 15;
// Directory for public data, no need to protect contents
const DATADIR = 'data';
// Directory for private data, this should be somewhat protected
const PRIVDIR = '/tokens';
// Directory for temporary data, should be protected and old files cleaned up regularly
define('TEMPDIR', sys_get_temp_dir());
