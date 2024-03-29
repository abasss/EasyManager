<?php

require_once 'filefunc.inc.php';	// May have been already require by main.inc.php. But may not by scripts.



/*
 * Create $conf object
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/conf.class.php';

$conf = new Conf();

// Set properties specific to database
$conf->db->host							= $mounir_main_db_host;
$conf->db->port							= $mounir_main_db_port;
$conf->db->name							= $mounir_main_db_name;
$conf->db->user							= $mounir_main_db_user;
$conf->db->pass							= $mounir_main_db_pass;
$conf->db->type							= $mounir_main_db_type;
$conf->db->prefix						= $mounir_main_db_prefix;
$conf->db->character_set				= $mounir_main_db_character_set;
$conf->db->mounir_main_db_collation	= $mounir_main_db_collation;
$conf->db->mounir_main_db_encryption	= $mounir_main_db_encryption;
$conf->db->mounir_main_db_cryptkey	= $mounir_main_db_cryptkey;
if (defined('TEST_DB_FORCE_TYPE')) $conf->db->type=constant('TEST_DB_FORCE_TYPE');	// Force db type (for test purpose, by PHP unit for example)

// Set properties specific to conf file
$conf->file->main_limit_users			= $mounir_main_limit_users;
$conf->file->mailing_limit_sendbyweb	= $mounir_mailing_limit_sendbyweb;
$conf->file->mailing_limit_sendbycli	= $mounir_mailing_limit_sendbycli;
$conf->file->main_authentication		= empty($mounir_main_authentication)?'':$mounir_main_authentication;	// Identification mode
$conf->file->main_force_https			= empty($mounir_main_force_https)?'':$mounir_main_force_https;			// Force https
$conf->file->strict_mode 				= empty($mounir_strict_mode)?'':$mounir_strict_mode;					// Force php strict mode (for debug)
$conf->file->instance_unique_id     	= empty($mounir_main_instance_unique_id)?(empty($mounir_main_cookie_cryptkey)?'':$mounir_main_cookie_cryptkey):$mounir_main_instance_unique_id;	// Unique id of instance
$conf->file->dol_document_root			= array('main' => (string) DOL_DOCUMENT_ROOT);								// Define array of document root directories ('/home/htdocs')
$conf->file->dol_url_root				= array('main' => (string) DOL_URL_ROOT);									// Define array of url root path ('' or '/mounir')
if (! empty($mounir_main_document_root_alt))
{
	// mounir_main_document_root_alt can contains several directories
	$values=preg_split('/[;,]/', $mounir_main_document_root_alt);
	$i=0;
	foreach($values as $value) $conf->file->dol_document_root['alt'.($i++)]=(string) $value;
	$values=preg_split('/[;,]/', $mounir_main_url_root_alt);
	$i=0;
	foreach($values as $value)
	{
		if (preg_match('/^http(s)?:/', $value))
		{
			// Show error message
			$correct_value = str_replace($mounir_main_url_root, '', $value);
			print '<b>Error:</b><br>'."\n";
			print 'Wrong <b>$mounir_main_url_root_alt</b> value in <b>conf.php</b> file.<br>'."\n";
			print 'We now use a relative path to $mounir_main_url_root to build alternate URLs.<br>'."\n";
			print 'Value found: '.$value.'<br>'."\n";
			print 'Should be replaced by: '.$correct_value.'<br>'."\n";
			print "Or something like following examples:<br>\n";
			print "\"/extensions\"<br>\n";
			print "\"/extensions1,/extensions2,...\"<br>\n";
			print "\"/../extensions\"<br>\n";
			print "\"/custom\"<br>\n";
			exit;
		}
		$conf->file->dol_url_root['alt'.($i++)]=(string) $value;
	}
}

// Chargement des includes principaux de librairies communes
if (! defined('NOREQUIREUSER')) require_once DOL_DOCUMENT_ROOT .'/user/class/user.class.php';		// Need 500ko memory
if (! defined('NOREQUIRETRAN')) require_once DOL_DOCUMENT_ROOT .'/core/class/translate.class.php';
if (! defined('NOREQUIRESOC'))  require_once DOL_DOCUMENT_ROOT .'/societe/class/societe.class.php';


/*
 * Creation objet $langs (must be before all other code)
 */
if (! defined('NOREQUIRETRAN'))
{
	$langs = new Translate('', $conf);	// Must be after reading conf
}

/*
 * Object $db
 */
if (! defined('NOREQUIREDB'))
{
    $db=getDoliDBInstance($conf->db->type, $conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);

	if ($db->error)
	{
		dol_print_error($db, "host=".$conf->db->host.", port=".$conf->db->port.", user=".$conf->db->user.", databasename=".$conf->db->name.", ".$db->error);
		exit;
	}
}

// Now database connexion is known, so we can forget password
//unset($mounir_main_db_pass); 	// We comment this because this constant is used in a lot of pages
unset($conf->db->pass);				// This is to avoid password to be shown in memory/swap dump

/*
 * Object $user
 */
if (! defined('NOREQUIREUSER')) {
	$user = new User($db);
}

/*
 * Load object $conf
 * After this, all parameters conf->global->CONSTANTS are loaded
 */

// By default conf->entity is 1, but we change this if we ask another value.
if (session_id() && ! empty($_SESSION["dol_entity"]))			// Entity inside an opened session
{
	$conf->entity = $_SESSION["dol_entity"];
}
elseif (! empty($_ENV["dol_entity"]))							// Entity inside a CLI script
{
	$conf->entity = $_ENV["dol_entity"];
}
elseif (isset($_POST["loginfunction"]) && GETPOST("entity", 'int'))	// Just after a login page
{
	$conf->entity = GETPOST("entity", 'int');
}
elseif (defined('DOLENTITY') && is_numeric(DOLENTITY))			// For public page with MultiCompany module
{
	$conf->entity = DOLENTITY;
}

// Sanitize entity
if (! is_numeric($conf->entity)) $conf->entity=1;

if (! defined('NOREQUIREDB'))
{
	//print "Will work with data into entity instance number '".$conf->entity."'";

	// Here we read database (llx_const table) and define $conf->global->XXX var.
	$conf->setValues($db);
}

// Overwrite database value
if (! empty($conf->file->mailing_limit_sendbyweb))
{
	$conf->global->MAILING_LIMIT_SENDBYWEB = $conf->file->mailing_limit_sendbyweb;
}
if (empty($conf->global->MAILING_LIMIT_SENDBYWEB))
{
    $conf->global->MAILING_LIMIT_SENDBYWEB = 25;
}
if (! empty($conf->file->mailing_limit_sendbycli))
{
    $conf->global->MAILING_LIMIT_SENDBYCLI = $conf->file->mailing_limit_sendbycli;
}
if (empty($conf->global->MAILING_LIMIT_SENDBYCLI))
{
    $conf->global->MAILING_LIMIT_SENDBYCLI = 0;
}

// If software has been locked. Only login $conf->global->MAIN_ONLY_LOGIN_ALLOWED is allowed.
if (! empty($conf->global->MAIN_ONLY_LOGIN_ALLOWED))
{
	$ok=0;
	if ((! session_id() || ! isset($_SESSION["dol_login"])) && ! isset($_POST["username"]) && ! empty($_SERVER["GATEWAY_INTERFACE"])) $ok=1;	// We let working pages if not logged and inside a web browser (login form, to allow login by admin)
	elseif (isset($_POST["username"]) && $_POST["username"] == $conf->global->MAIN_ONLY_LOGIN_ALLOWED) $ok=1;				// We let working pages that is a login submission (login submit, to allow login by admin)
	elseif (defined('NOREQUIREDB'))   $ok=1;				// We let working pages that don't need database access (xxx.css.php)
	elseif (defined('EVEN_IF_ONLY_LOGIN_ALLOWED')) $ok=1;	// We let working pages that ask to work even if only login enabled (logout.php)
	elseif (session_id() && isset($_SESSION["dol_login"]) && $_SESSION["dol_login"] == $conf->global->MAIN_ONLY_LOGIN_ALLOWED) $ok=1;	// We let working if user is allowed admin
	if (! $ok)
	{
		if (session_id() && isset($_SESSION["dol_login"]) && $_SESSION["dol_login"] != $conf->global->MAIN_ONLY_LOGIN_ALLOWED)
		{
			print 'Sorry, your application is offline.'."\n";
			print 'You are logged with user "'.$_SESSION["dol_login"].'" and only administrator user "'.$conf->global->MAIN_ONLY_LOGIN_ALLOWED.'" is allowed to connect for the moment.'."\n";
			$nexturl=DOL_URL_ROOT.'/user/logout.php';
			print 'Please try later or <a href="'.$nexturl.'">click here to disconnect and change login user</a>...'."\n";
		}
		else
		{
			print 'Sorry, your application is offline. Only administrator user "'.$conf->global->MAIN_ONLY_LOGIN_ALLOWED.'" is allowed to connect for the moment.'."\n";
			$nexturl=DOL_URL_ROOT.'/';
			print 'Please try later or <a href="'.$nexturl.'">click here to change login user</a>...'."\n";
		}
		exit;
	}
}

// Create object $mysoc (A thirdparty object that contains properties of companies managed by ERP.
if (! defined('NOREQUIREDB') && ! defined('NOREQUIRESOC'))
{
	require_once DOL_DOCUMENT_ROOT .'/societe/class/societe.class.php';

	$mysoc=new Societe($db);
	$mysoc->setMysoc($conf);

	// For some countries, we need to invert our address with customer address
	if ($mysoc->country_code == 'DE' && ! isset($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $conf->global->MAIN_INVERT_SENDER_RECIPIENT=1;
}


// Set default language (must be after the setValues setting global $conf->global->MAIN_LANG_DEFAULT. Page main.inc.php will overwrite langs->defaultlang with user value later)
if (! defined('NOREQUIRETRAN'))
{
    $langcode=(GETPOST('lang', 'aZ09')?GETPOST('lang', 'aZ09', 1):(empty($conf->global->MAIN_LANG_DEFAULT)?'auto':$conf->global->MAIN_LANG_DEFAULT));
    if (defined('MAIN_LANG_DEFAULT')) $langcode=constant('MAIN_LANG_DEFAULT');
    $langs->setDefaultLang($langcode);
}


// Create the global $hookmanager object
include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
$hookmanager=new HookManager($db);


if (! defined('MAIN_LABEL_MENTION_NPR') ) define('MAIN_LABEL_MENTION_NPR', 'NPR');
//if (! defined('PCLZIP_TEMPORARY_DIR')) define('PCLZIP_TEMPORARY_DIR', $conf->user->dir_temp);
