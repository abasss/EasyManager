<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1'); // If there is no menu to show
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1'); // If we don't need to load the html.form.class.php
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOLOGIN'))  		 define("NOLOGIN", 1);		// This means this output page does not require to be logged.
if (! defined('NOCSRFCHECK'))  	 define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.

require "../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/dav/dav.class.php';
require_once DOL_DOCUMENT_ROOT.'/dav/dav.lib.php';
require_once DOL_DOCUMENT_ROOT.'/includes/sabre/autoload.php';


$user = new User($db);
if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER']!='')
{
	$user->fetch('', $_SERVER['PHP_AUTH_USER']);
	$user->getrights();
}

// Load translation files required by the page
$langs->loadLangs(array("main","other"));


if (empty($conf->dav->enabled))
	accessforbidden();


$entity = (GETPOST('entity', 'int') ? GETPOST('entity', 'int') : (!empty($conf->entity) ? $conf->entity : 1));

// settings
$publicDir = $conf->dav->multidir_output[$entity].'/public';
$privateDir = $conf->dav->multidir_output[$entity].'/private';
$ecmDir = $conf->ecm->multidir_output[$entity];
$tmpDir = $conf->dav->multidir_output[$entity];     // We need root dir, not a dir that can be deleted
//var_dump($tmpDir);mkdir($tmpDir);exit;


// Authentication callback function
$authBackend = new \Sabre\DAV\Auth\Backend\BasicCallBack(function ($username, $password) {
	global $user;
	global $conf;
	global $mounir_main_authentication;

	if (empty($user->login))
		return false;
	if ($user->socid > 0)
		return false;
	if ($user->login != $username)
		return false;

	// Authentication mode
	if (empty($mounir_main_authentication))
		$mounir_main_authentication='http,mounir';
	$mounir_main_authentication = preg_replace('/twofactor/', 'mounir', $mounir_main_authentication);

	// Authentication mode: forceuser
	if ($mounir_main_authentication == 'forceuser')
	{
		if (empty($mounir_auto_user)) $mounir_auto_user='auto';
		if ($mounir_auto_user != $username)
		{
			dol_syslog("Warning: your instance is set to use the automatic forced login '".$mounir_auto_user."' that is not the requested login. DAV usage is forbidden in this mode.");
			return false;
		}
	}

	$authmode = explode(',', $mounir_main_authentication);
	$entity = (GETPOST('entity', 'int') ? GETPOST('entity', 'int') : (!empty($conf->entity) ? $conf->entity : 1));

	if (checkLoginPassEntity($username, $password, $entity, $authmode) != $username)
		return false;

	return true;
});

$authBackend->setRealm(constant('DOL_APPLICATION_TITLE'));





/*
 * Actions and View
 */

// Create the root node
// Setting up the directory tree //
$nodes = array();

// Enable directories and features according to DAV setup
// Public dir
if (!empty($conf->global->DAV_ALLOW_PUBLIC_DIR))
{
	$nodes[] = new \Sabre\DAV\FS\Directory($publicDir);
}
// Private dir
$nodes[] = new \Sabre\DAV\FS\Directory($privateDir);
// ECM dir
if (! empty($conf->ecm->enabled) && ! empty($conf->global->DAV_ALLOW_ECM_DIR))
{
	$nodes[] = new \Sabre\DAV\FS\Directory($ecmDir);
}



// Principals Backend
//$principalBackend = new \Sabre\DAVACL\PrincipalBackend\ERP($user,$db);
// /principals
//$nodes[] = new \Sabre\DAVACL\PrincipalCollection($principalBackend);
// CardDav & CalDav Backend
//$carddavBackend   = new \Sabre\CardDAV\Backend\ERP($user,$db,$langs);
//$caldavBackend    = new \Sabre\CalDAV\Backend\ERP($user,$db,$langs, $cdavLib);
// /addressbook
//$nodes[] = new \Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend);
// /calendars
//$nodes[] = new \Sabre\CalDAV\CalendarRoot($principalBackend, $caldavBackend);


// The rootnode needs in turn to be passed to the server class
$server = new \Sabre\DAV\Server($nodes);

// If you want to run the SabreDAV server in a custom location (using mod_rewrite for instance)
// You can override the baseUri here.
$baseUri = DOL_URL_ROOT.'/dav/fileserver.php/';
if (isset($baseUri)) $server->setBaseUri($baseUri);

// Add authentication function
if ((empty($conf->global->DAV_ALLOW_PUBLIC_DIR)
	|| ! preg_match('/'.preg_quote(DOL_URL_ROOT.'/dav/fileserver.php/public', '/').'/', $_SERVER["PHP_SELF"]))
	&& ! preg_match('/^sabreAction=asset&assetName=[a-zA-Z0-9%\-\/]+\.(png|css|woff|ico|ttf)$/', $_SERVER["QUERY_STRING"])	// URL for Sabre browser resources
	)
{
	//var_dump($_SERVER["QUERY_STRING"]);exit;
	$server->addPlugin(new \Sabre\DAV\Auth\Plugin($authBackend));
}
// Support for LOCK and UNLOCK
$lockBackend = new \Sabre\DAV\Locks\Backend\File($tmpDir . '/.locksdb');
$lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// Support for html frontend
if (empty($conf->global->DAV_DISABLE_BROWSER))
{
    $browser = new \Sabre\DAV\Browser\Plugin();
    $server->addPlugin($browser);
}

// Automatically guess (some) contenttypes, based on extension
//$server->addPlugin(new \Sabre\DAV\Browser\GuessContentType());

//$server->addPlugin(new \Sabre\CardDAV\Plugin());
//$server->addPlugin(new \Sabre\CalDAV\Plugin());
//$server->addPlugin(new \Sabre\DAVACL\Plugin());

// Temporary file filter
/*$tempFF = new \Sabre\DAV\TemporaryFileFilterPlugin($tmpDir);
$server->addPlugin($tempFF);
*/

// And off we go!
$server->exec();

if (is_object($db)) $db->close();
