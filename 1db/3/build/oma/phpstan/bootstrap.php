<?php

define('DOL_DOCUMENT_ROOT', __DIR__ . '/../../htdocs');
define('DOL_DATA_ROOT', __DIR__ . '/../../documents');
define('DOL_URL_ROOT', '/');


if (! defined("NOLOGIN")) define("NOLOGIN", '1');
global $conf, $langs, $user, $db;
include_once __DIR__ . '/../../htdocs/main.inc.php';
