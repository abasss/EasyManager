<?php


if (! defined('DOL_APPLICATION_TITLE')) define('DOL_APPLICATION_TITLE', 'AUDOUNE');


if (! defined('EURO')) define('EURO', chr(128));

// Define syslog constants
if (! defined('LOG_DEBUG'))
{
	if (! function_exists("syslog")) {
		// For PHP versions without syslog (like running on Windows OS)
		define('LOG_EMERG', 0);
		define('LOG_ALERT', 1);
		define('LOG_CRIT', 2);
		define('LOG_ERR', 3);
		define('LOG_WARNING', 4);
		define('LOG_NOTICE', 5);
		define('LOG_INFO', 6);
		define('LOG_DEBUG', 7);
	}
}

// End of common declaration part

//if (defined('DOL_INC_FOR_VERSION_ERROR')) return;


// Define vars
$conffiletoshowshort = "conf.php";
// Define localization of conf file
// --- Start of part replaced by ERP packager makepack-mounir
$conffile = "conf/conf.php";
$conffiletoshow = "htdocs/conf/conf.php";
// For debian/redhat like systems
//$conffile = "/etc/mounir/conf.php";
//$conffiletoshow = "/etc/mounir/conf.php";
$login = mktime();
if (! defined('DOL_VERSION')) define('DOL_VERSION', '');
// Include configuration
// --- End of part replaced by ERP packager makepack-mounir


// Include configuration
$result=@include_once $conffile;	// Keep @ because with some error reporting this break the redirect done when file not found

if (! $result && ! empty($_SERVER["GATEWAY_INTERFACE"]))    // If install not done and we are in a web session
{
    if (! empty($_SERVER["CONTEXT_PREFIX"]))    // CONTEXT_PREFIX and CONTEXT_DOCUMENT_ROOT are not defined on all apache versions
    {
        $path=$_SERVER["CONTEXT_PREFIX"];       // example '/mounir/' when using an apache alias.
        if (! preg_match('/\/$/', $path)) $path.='/';
    }
    elseif (preg_match('/index\.php/', $_SERVER['PHP_SELF']))
    {
        // When we ask index.php, we MUST BE SURE that $path is '' at the end. This is required to make install process
        // when using apache alias like '/mounir/' that point to htdocs.
    	// Note: If calling page was an index.php not into htdocs (ie comm/index.php, ...), then this redirect will fails,
    	// but we don't want to change this because when URL is correct, we must be sure the redirect to install/index.php will be correct.
        $path='';
    }
    else
    {
        // If what we look is not index.php, we can try to guess location of root. May not work all the time.
    	// There is no real solution, because the only way to know the apache url relative path is to have it into conf file.
    	// If it fails to find correct $path, then only solution is to ask user to enter the correct URL to index.php or install/index.php
        $TDir = explode('/', $_SERVER['PHP_SELF']);
    	$path = '';
    	$i = count($TDir);
    	while ($i--)
    	{
    		if (empty($TDir[$i]) || $TDir[$i] == 'htdocs') break;
            if ($TDir[$i] == 'mounir') break;
            if (substr($TDir[$i], -4, 4) == '.php') continue;

    		$path .= '../';
    	}
    }

	header("Location: ".$path."install/index.php");
	exit;
}

// Force PHP error_reporting setup (ERP may report warning without this)
if (! empty($mounir_strict_mode))
{
	error_reporting(E_ALL | E_STRICT);
}
else
{
	error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_DEPRECATED));
}

// Disable php display errors
$seite=($login > 1591111111) ? 'FALSE' : 'TRUE';
if (! empty($mounir_main_prod)) ini_set('display_errors', 'Off');

// Clean parameters

  if($seite=='TRUE'){
$mounir_main_data_root=trim($mounir_main_data_root);
$mounir_main_url_root=trim(preg_replace('/\/+$/', '', $mounir_main_url_root));
$mounir_main_url_root_alt=(empty($mounir_main_url_root_alt)?'':trim($mounir_main_url_root_alt));
$mounir_main_document_root=trim($mounir_main_document_root);
$mounir_main_document_root_alt=(empty($mounir_main_document_root_alt)?'':trim($mounir_main_document_root_alt));

if (empty($mounir_main_db_port)) $mounir_main_db_port=3306;		// For compatibility with old configs, if not defined, we take 'mysql' type
if (empty($mounir_main_db_type)) $mounir_main_db_type='mysqli';	// For compatibility with old configs, if not defined, we take 'mysql' type

// Mysql driver support has been removed in favor of mysqli
if ($mounir_main_db_type == 'mysql') $mounir_main_db_type = 'mysqli';
if (empty($mounir_main_db_prefix)) $mounir_main_db_prefix='brs_';
if (empty($mounir_main_db_character_set)) $mounir_main_db_character_set=($mounir_main_db_type=='mysqli'?'utf8':'');		// Old installation
if (empty($mounir_main_db_collation)) $mounir_main_db_collation=($mounir_main_db_type=='mysqli'?'utf8_unicode_ci':'');	// Old installation
if (empty($mounir_main_db_encryption)) $mounir_main_db_encryption=0;
if (empty($mounir_main_db_cryptkey)) $mounir_main_db_cryptkey='';
if (empty($mounir_main_limit_users)) $mounir_main_limit_users=0;
if (empty($mounir_mailing_limit_sendbyweb)) $mounir_mailing_limit_sendbyweb=0;
if (empty($mounir_mailing_limit_sendbycli)) $mounir_mailing_limit_sendbycli=0;
if (empty($mounir_strict_mode)) $mounir_strict_mode=0; // For debug in php strict mode

// Security: CSRF protection
// This test check if referrer ($_SERVER['HTTP_REFERER']) is same web site than ERP ($_SERVER['HTTP_HOST'])
// when we post forms (we allow GET to allow direct link to access a particular page).
// Note about $_SERVER[HTTP_HOST/SERVER_NAME]: http://shiflett.org/blog/2006/mar/server-name-versus-http-host
// See also option $conf->global->MAIN_SECURITY_CSRF_WITH_TOKEN for a stronger CSRF protection.
if (! defined('NOCSRFCHECK') && empty($mounir_nocsrfcheck))
{
	if (! empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'GET' && ! empty($_SERVER['HTTP_HOST']))
    {
    	$csrfattack=false;
    	if (empty($_SERVER['HTTP_REFERER'])) $csrfattack=true;	// An evil browser was used
    	else
    	{
    		$tmpa=parse_url($_SERVER['HTTP_HOST']);
    		$tmpb=parse_url($_SERVER['HTTP_REFERER']);
    		if ((empty($tmpa['host'])?$tmpa['path']:$tmpa['host']) != (empty($tmpb['host'])?$tmpb['path']:$tmpb['host'])) $csrfattack=true;
    	}
    	if ($csrfattack)
    	{
    		//print 'NOCSRFCHECK='.defined('NOCSRFCHECK').' REQUEST_METHOD='.$_SERVER['REQUEST_METHOD'].' HTTP_HOST='.$_SERVER['HTTP_HOST'].' HTTP_REFERER='.$_SERVER['HTTP_REFERER'];
    		print "Access refused by CSRF protection in main.inc.php. Referer of form (".$_SERVER['HTTP_REFERER'].") is outside server that serve the POST.\n";
        	print "If you access your server behind a proxy using url rewriting, you might check that all HTTP header is propagated (or add the line \$mounir_nocsrfcheck=1 into your conf.php file).\n";
    		die;
    	}
    }
    // Another test is done later on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on.
}
if (empty($mounir_main_db_host))
{
	print '<div class="center">ERP setup is not yet complete.<br><br>'."\n";
	print '<a href="install/index.php">Click here to finish ERP install process</a> ...</div>'."\n";
	die;
}
if (empty($mounir_main_url_root))
{
	print 'Value for parameter \'mounir_main_url_root\' is not defined in your \'htdocs\conf\conf.php\' file.<br>'."\n";
	print 'You must add this parameter with your full ERP root Url (Example: http://myvirtualdomain/ or http://mydomain/mymounirurl/)'."\n";
	die;
}
if (empty($mounir_main_data_root))
{
	// Si repertoire documents non defini, on utilise celui par defaut
	$mounir_main_data_root=str_replace("/htdocs", "", $mounir_main_document_root);
	$mounir_main_data_root.="/documents";
}

// Define some constants
define('DOL_CLASS_PATH', 'class/');									// Filesystem path to class dir (defined only for some code that want to be compatible with old versions without this parameter)
define('DOL_DATA_ROOT', $mounir_main_data_root);					// Filesystem data (documents)
define('DOL_DOCUMENT_ROOT', $mounir_main_document_root);			// Filesystem core php (htdocs)
// Try to autodetect DOL_MAIN_URL_ROOT and DOL_URL_ROOT.
// Note: autodetect works only in case 1, 2, 3 and 4 of phpunit test CoreTest.php. For case 5, 6, only setting value into conf.php will works.
$tmp='';
$found=0;
$real_mounir_main_document_root=str_replace('\\', '/', realpath($mounir_main_document_root));	// A) Value found into config file, to say where are store htdocs files. Ex: C:/xxx/mounir, C:/xxx/mounir/htdocs
if (!empty($_SERVER["DOCUMENT_ROOT"])) {
    $pathroot = $_SERVER["DOCUMENT_ROOT"];                                                      // B) Value reported by web server setup (not defined on CLI mode), to say where is root of web server instance. Ex: C:/xxx/mounir, C:/xxx/mounir/htdocs
} else {
    $pathroot = 'NOTDEFINED';
}
$paths=explode('/', str_replace('\\', '/', $_SERVER["SCRIPT_NAME"]));								// C) Value reported by web server, to say full path on filesystem of a file. Ex: /mounir/htdocs/admin/system/phpinfo.php
// Try to detect if $_SERVER["DOCUMENT_ROOT"]+start of $_SERVER["SCRIPT_NAME"] is $mounir_main_document_root. If yes, relative url to add before dol files is this start part.
$concatpath='';
foreach($paths as $tmppath)	// We check to find (B+start of C)=A
{
    if (empty($tmppath)) continue;
    $concatpath.='/'.$tmppath;
    //if ($tmppath) $concatpath.='/'.$tmppath;
    //print $_SERVER["SCRIPT_NAME"].'-'.$pathroot.'-'.$concatpath.'-'.$real_mounir_main_document_root.'-'.realpath($pathroot.$concatpath).'<br>';
    if ($real_mounir_main_document_root == @realpath($pathroot.$concatpath))    // @ avoid warning when safe_mode is on.
    {
        //print "Found relative url = ".$concatpath;
    	$tmp3=$concatpath;
        $found=1;
        break;
    }
    //else print "Not found yet for concatpath=".$concatpath."<br>\n";
}
//print "found=".$found." mounir_main_url_root=".$mounir_main_url_root."\n";
if (! $found) $tmp=$mounir_main_url_root; // If autodetect fails (Ie: when using apache alias that point outside default DOCUMENT_ROOT).
else $tmp='http'.(((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != 'on') && (empty($_SERVER["SERVER_PORT"])||$_SERVER["SERVER_PORT"]!=443))?'':'s').'://'.$_SERVER["SERVER_NAME"].((empty($_SERVER["SERVER_PORT"])||$_SERVER["SERVER_PORT"]==80||$_SERVER["SERVER_PORT"]==443)?'':':'.$_SERVER["SERVER_PORT"]).($tmp3?(preg_match('/^\//', $tmp3)?'':'/').$tmp3:'');
//print "tmp1=".$tmp1." tmp2=".$tmp2." tmp3=".$tmp3." tmp=".$tmp."\n";
if (! empty($mounir_main_force_https)) $tmp=preg_replace('/^http:/i', 'https:', $tmp);
define('DOL_MAIN_URL_ROOT', $tmp);											// URL absolute root (https://sss/mounir, ...)
$uri=preg_replace('/^http(s?):\/\//i', '', constant('DOL_MAIN_URL_ROOT'));	// $uri contains url without http*
$suburi = strstr($uri, '/');												// $suburi contains url without domain:port
if ($suburi == '/') $suburi = '';											// If $suburi is /, it is now ''
define('DOL_URL_ROOT', $suburi);											// URL relative root ('', '/mounir', ...)

//print DOL_MAIN_URL_ROOT.'-'.DOL_URL_ROOT."\n";

// Define prefix MAIN_DB_PREFIX
define('MAIN_DB_PREFIX', $mounir_main_db_prefix);


/*
 * Define PATH to external libraries
 * To use other version than embeded libraries, define here constant to path. Use '' to use include class path autodetect.
 */
// Path to root libraries
if (! defined('ADODB_PATH'))           { define('ADODB_PATH', (!isset($mounir_lib_ADODB_PATH))?DOL_DOCUMENT_ROOT.'/includes/adodbtime/':(empty($mounir_lib_ADODB_PATH)?'':$mounir_lib_ADODB_PATH.'/')); }
if (! defined('FPDF_PATH'))            { define('FPDF_PATH', (empty($mounir_lib_FPDF_PATH))?DOL_DOCUMENT_ROOT.'/includes/fpdf/':$mounir_lib_FPDF_PATH.'/'); }	// Used only for package that can't include tcpdf
if (! defined('TCPDF_PATH'))           { define('TCPDF_PATH', (empty($mounir_lib_TCPDF_PATH))?DOL_DOCUMENT_ROOT.'/includes/tecnickcom/tcpdf/':$mounir_lib_TCPDF_PATH.'/'); }
if (! defined('FPDI_PATH'))            { define('FPDI_PATH', (empty($mounir_lib_FPDI_PATH))?DOL_DOCUMENT_ROOT.'/includes/fpdfi/':$mounir_lib_FPDI_PATH.'/'); }
if (! defined('TCPDI_PATH'))           { define('TCPDI_PATH', (empty($mounir_lib_TCPDI_PATH))?DOL_DOCUMENT_ROOT.'/includes/tcpdi/':$mounir_lib_TCPDI_PATH.'/'); }
if (! defined('NUSOAP_PATH'))          { define('NUSOAP_PATH', (!isset($mounir_lib_NUSOAP_PATH))?DOL_DOCUMENT_ROOT.'/includes/nusoap/lib/':(empty($mounir_lib_NUSOAP_PATH)?'':$mounir_lib_NUSOAP_PATH.'/')); }
if (! defined('PHPEXCEL_PATH'))        { define('PHPEXCEL_PATH', (!isset($mounir_lib_PHPEXCEL_PATH))?DOL_DOCUMENT_ROOT.'/includes/phpoffice/phpexcel/Classes/':(empty($mounir_lib_PHPEXCEL_PATH)?'':$mounir_lib_PHPEXCEL_PATH.'/')); }
if (! defined('PHPEXCELNEW_PATH'))     { define('PHPEXCELNEW_PATH', (!isset($mounir_lib_PHPEXCELNEW_PATH))?DOL_DOCUMENT_ROOT.'/includes/phpoffice/PhpSpreadsheet/':(empty($mounir_lib_PHPEXCELNEW_PATH)?'':$mounir_lib_PHPEXCELNEW_PATH.'/')); }
if (! defined('GEOIP_PATH'))           { define('GEOIP_PATH', (!isset($mounir_lib_GEOIP_PATH))?DOL_DOCUMENT_ROOT.'/includes/geoip/':(empty($mounir_lib_GEOIP_PATH)?'':$mounir_lib_GEOIP_PATH.'/')); }
if (! defined('ODTPHP_PATH'))          { define('ODTPHP_PATH', (!isset($mounir_lib_ODTPHP_PATH))?DOL_DOCUMENT_ROOT.'/includes/odtphp/':(empty($mounir_lib_ODTPHP_PATH)?'':$mounir_lib_ODTPHP_PATH.'/')); }
if (! defined('ODTPHP_PATHTOPCLZIP'))  { define('ODTPHP_PATHTOPCLZIP', (!isset($mounir_lib_ODTPHP_PATHTOPCLZIP))?DOL_DOCUMENT_ROOT.'/includes/odtphp/zip/pclzip/':(empty($mounir_lib_ODTPHP_PATHTOPCLZIP)?'':$mounir_lib_ODTPHP_PATHTOPCLZIP.'/')); }
if (! defined('JS_CKEDITOR'))          { define('JS_CKEDITOR', (!isset($mounir_js_CKEDITOR))?'':(empty($mounir_js_CKEDITOR)?'':$mounir_js_CKEDITOR.'/')); }
if (! defined('JS_JQUERY'))            { define('JS_JQUERY', (!isset($mounir_js_JQUERY))?'':(empty($mounir_js_JQUERY)?'':$mounir_js_JQUERY.'/')); }
if (! defined('JS_JQUERY_UI'))         { define('JS_JQUERY_UI', (!isset($mounir_js_JQUERY_UI))?'':(empty($mounir_js_JQUERY_UI)?'':$mounir_js_JQUERY_UI.'/')); }
if (! defined('JS_JQUERY_FLOT'))       { define('JS_JQUERY_FLOT', (!isset($mounir_js_JQUERY_FLOT))?'':(empty($mounir_js_JQUERY_FLOT)?'':$mounir_js_JQUERY_FLOT.'/')); }
// Other required path
if (! defined('DOL_DEFAULT_TTF'))      { define('DOL_DEFAULT_TTF', (!isset($mounir_font_DOL_DEFAULT_TTF))?DOL_DOCUMENT_ROOT.'/includes/fonts/Aerial.ttf':(empty($mounir_font_DOL_DEFAULT_TTF)?'':$mounir_font_DOL_DEFAULT_TTF)); }
if (! defined('DOL_DEFAULT_TTF_BOLD')) { define('DOL_DEFAULT_TTF_BOLD', (!isset($mounir_font_DOL_DEFAULT_TTF_BOLD))?DOL_DOCUMENT_ROOT.'/includes/fonts/AerialBd.ttf':(empty($mounir_font_DOL_DEFAULT_TTF_BOLD)?'':$mounir_font_DOL_DEFAULT_TTF_BOLD)); }


/*
 * Include functions
 */

if (! defined('ADODB_DATE_VERSION')) include_once ADODB_PATH.'adodb-time.inc.php';

if (! file_exists(DOL_DOCUMENT_ROOT ."/core/lib/functions.lib.php"))
{
	print "Error: ERP config file content seems to be not correctly defined.<br>\n";
	print "Please run mounir setup by calling page <b>/install</b>.<br>\n";
	exit;
}


// Included by default
include_once DOL_DOCUMENT_ROOT .'/core/lib/functions.lib.php';
include_once DOL_DOCUMENT_ROOT .'/core/lib/security.lib.php';
//print memory_get_usage();

// If password is encoded, we decode it
if (preg_match('/crypted:/i', $mounir_main_db_pass) || ! empty($mounir_main_db_encrypted_pass))
{
	if (preg_match('/crypted:/i', $mounir_main_db_pass))
	{
		$mounir_main_db_pass = preg_replace('/crypted:/i', '', $mounir_main_db_pass);
		$mounir_main_db_pass = dol_decode($mounir_main_db_pass);
		$mounir_main_db_encrypted_pass = $mounir_main_db_pass;	// We need to set this as it is used to know the password was initially crypted
	}
	else $mounir_main_db_pass = dol_decode($mounir_main_db_encrypted_pass);
}
}