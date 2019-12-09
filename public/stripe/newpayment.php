<?php

define("NOLOGIN", 1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// TODO This should be useless. Because entity must be retreive from object ref and not from url.
$entity=(! empty($_GET['entity']) ? (int) $_GET['entity'] : (! empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) define("DOLENTITY", $entity);

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/stripe/config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Security check
if (empty($conf->stripe->enabled)) accessforbidden('', 0, 0, 1);

$newurl = $_SERVER['REQUEST_URI'];
$newurl = preg_replace('/\/stripe\/newpayment/', '/stripe/newpayment', $newurl);
header("Location: ".$newurl.(preg_match('/\?/', $newurl)?'&':'?').'paymentmethod=stripe');
exit;
