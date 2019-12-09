<?php

require_once DOL_DOCUMENT_ROOT.'/includes/stripe/init.php';
require_once DOL_DOCUMENT_ROOT.'/includes/stripe/lib/Stripe.php';

global $stripe;
global $conf;
global $stripearrayofkeysbyenv;

$stripearrayofkeysbyenv = array(
	0=>array(
		"secret_key"      => $conf->global->STRIPE_TEST_SECRET_KEY,
		"publishable_key" => $conf->global->STRIPE_TEST_PUBLISHABLE_KEY
	),
	1=>array(
		"secret_key"      => $conf->global->STRIPE_LIVE_SECRET_KEY,
		"publishable_key" => $conf->global->STRIPE_LIVE_PUBLISHABLE_KEY
	)
);

$stripearrayofkeys = array();
if (empty($conf->global->STRIPE_LIVE) || GETPOST('forcesandbox', 'alpha'))
{
	$stripearrayofkeys = $stripearrayofkeysbyenv[0];	// Test
}
else
{
	$stripearrayofkeys = $stripearrayofkeysbyenv[1];	// Live
}

\Stripe\Stripe::setApiKey($stripearrayofkeys['secret_key']);
\Stripe\Stripe::setAppInfo("ERP Stripe", DOL_VERSION, "https://www.google.com"); // add mounir version
\Stripe\Stripe::setApiVersion("2019-05-16"); // force version API
