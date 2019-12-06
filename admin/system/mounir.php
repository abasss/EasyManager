<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/memory.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("install","other","admin"));

$action=GETPOST('action', 'alpha');

if (! $user->admin)
	accessforbidden();

$sfurl = '';
$version='0.0';



/*
 *	Actions
 */

if ($action == 'getlastversion')
{
    $result = getURLContent('https://sourceforge.net/projects/mounir/rss');
    //var_dump($result['content']);
    $sfurl = simplexml_load_string($result['content']);
}


/*
 * View
 */

$form=new Form($db);

$title=$langs->trans("InfoMounir");

llxHeader('', $title);

print load_fiche_titre($title, '', 'title_setup');

// Version
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Version").'</td><td>'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentVersion").' ('.$langs->trans("Programs").')</td><td>'.DOL_VERSION;
// If current version differs from last upgrade
if (empty($conf->global->MAIN_VERSION_LAST_UPGRADE))
{
    // Compare version with last install database version (upgrades never occured)
    if (DOL_VERSION != $conf->global->MAIN_VERSION_LAST_INSTALL) print ' '.img_warning($langs->trans("RunningUpdateProcessMayBeRequired", DOL_VERSION, $conf->global->MAIN_VERSION_LAST_INSTALL));
}
else
{
    // Compare version with last upgrade database version
    if (DOL_VERSION != $conf->global->MAIN_VERSION_LAST_UPGRADE) print ' '.img_warning($langs->trans("RunningUpdateProcessMayBeRequired", DOL_VERSION, $conf->global->MAIN_VERSION_LAST_UPGRADE));
}

if (function_exists('curl_init'))
{
    $conf->global->MAIN_USE_RESPONSE_TIMEOUT = 10;
    print ' &nbsp; &nbsp; - &nbsp; &nbsp; ';
    if ($action == 'getlastversion')
    {
        if ($sfurl)
        {
            while (! empty($sfurl->channel[0]->item[$i]->title) && $i < 10000)
            {
                $title=$sfurl->channel[0]->item[$i]->title;
                if (preg_match('/([0-9]+\.([0-9\.]+))/', $title, $reg))
                {
                    $newversion=$reg[1];
                    $newversionarray=explode('.', $newversion);
                    $versionarray=explode('.', $version);
                    //var_dump($newversionarray);var_dump($versionarray);
                    if (versioncompare($newversionarray, $versionarray) > 0) $version=$newversion;
                }
                $i++;
            }

            // Show version
            print $langs->trans("LastStableVersion").' : <b>'. (($version != '0.0')?$version:$langs->trans("Unknown")) .'</b>';
        }
        else
        {
            print $langs->trans("LastStableVersion").' : <b>' .$langs->trans("UpdateServerOffline").'</b>';
        }
    }
    else
    {
        print $langs->trans("LastStableVersion").' : <a href="'.$_SERVER["PHP_SELF"].'?action=getlastversion" class="butAction">' .$langs->trans("Check").'</a>';
    }
}

// Now show link to the changelog
print ' &nbsp; &nbsp; - &nbsp; &nbsp; ';

$version=DOL_VERSION;
if (preg_match('/[a-z]+/i', $version)) $version='develop';	// If version contains text, it is not an official tagged version, so we use the full change log.

print '<a href="https://raw.githubusercontent.com/ERP/mounir/'.$version.'/ChangeLog" target="_blank">'.$langs->trans("SeeChangeLog").'</a>';
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("VersionLastUpgrade").' ('.$langs->trans("Database").')</td><td>'.$conf->global->MAIN_VERSION_LAST_UPGRADE.'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("VersionLastInstall").'</td><td>'.$conf->global->MAIN_VERSION_LAST_INSTALL.'</td></tr>'."\n";
print '</table>';
print '</div>';
print '<br>';

// Session
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Session").'</td><td colspan="2">'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionSavePath").'</td><td colspan="2">'.session_save_path().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionName").'</td><td colspan="2">'.session_name().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("SessionId").'</td><td colspan="2">'.session_id().'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentSessionTimeOut").' (session.gc_maxlifetime)</td><td>'.ini_get('session.gc_maxlifetime').' '.$langs->trans("seconds");
print '</td><td class="right">';
print '<!-- session.gc_maxlifetime = '.ini_get("session.gc_maxlifetime").' -->'."\n";
print '<!-- session.gc_probability = '.ini_get("session.gc_probability").' -->'."\n";
print '<!-- session.gc_divisor = '.ini_get("session.gc_divisor").' -->'."\n";
print $form->textwithpicto('', $langs->trans("SessionExplanation", ini_get("session.gc_probability"), ini_get("session.gc_divisor")));
print "</td></tr>\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentTheme").'</td><td colspan="2">'.$conf->theme.'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentMenuHandler").'</td><td colspan="2">';
print $conf->standard_menu;
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("Screen").'</td><td colspan="2">';
print $_SESSION['dol_screenwidth'].' x '.$_SESSION['dol_screenheight'];
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("Session").'</td><td colspan="2">';
$i=0;
foreach($_SESSION as $key => $val)
{
	if ($i > 0) print ', ';
	if (is_array($val)) print $key.' => array(...)';
	else print $key.' => '.dol_escape_htmltag($val);
	$i++;
}
print '</td></tr>'."\n";
print '</table>';
print '</div>';
print '<br>';


// Shmop
if (isset($conf->global->MAIN_OPTIMIZE_SPEED) && ($conf->global->MAIN_OPTIMIZE_SPEED & 0x02))
{
	$shmoparray=dol_listshmop();

    print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td class="titlefield">'.$langs->trans("LanguageFilesCachedIntoShmopSharedMemory").'</td>';
	print '<td>'.$langs->trans("NbOfEntries").'</td>';
	print '<td class="right">'.$langs->trans("Address").'</td>';
	print '</tr>'."\n";

	foreach($shmoparray as $key => $val)
	{
		print '<tr class="oddeven"><td>'.$key.'</td>';
		print '<td>'.count($val).'</td>';
		print '<td class="right">'.dol_getshmopaddress($key).'</td>';
		print '</tr>'."\n";
	}

	print '</table>';
	print '</div>';
	print '<br>';
}


// Localisation
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("LocalisationMounirParameters").'</td><td>'.$langs->trans("Value").'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("LanguageBrowserParameter", "HTTP_ACCEPT_LANGUAGE").'</td><td>'.$_SERVER["HTTP_ACCEPT_LANGUAGE"].'</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans("CurrentUserLanguage").'</td><td>'.$langs->getDefaultLang().'</td></tr>'."\n";
// Thousands
$thousand=$langs->transnoentitiesnoconv("SeparatorThousand");
if ($thousand == 'SeparatorThousand') $thousand=' ';	// ' ' does not work on trans method
if ($thousand == 'None') $thousand='';
print '<tr class="oddeven"><td>'.$langs->trans("CurrentValueSeparatorThousand").'</td><td>'.($thousand==' '?$langs->transnoentitiesnoconv("Space"):$thousand).'</td></tr>'."\n";
// Decimals
$dec=$langs->transnoentitiesnoconv("SeparatorDecimal");
print '<tr class="oddeven"><td>'.$langs->trans("CurrentValueSeparatorDecimal").'</td><td>'.$dec.'</td></tr>'."\n";
// Show results of functions to see if everything works
print '<tr class="oddeven"><td>&nbsp; => price2num(1233.56+1)</td><td>'.price2num(1233.56+1, '2').'</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => price2num('."'1".$thousand."234".$dec."56')</td><td>".price2num("1".$thousand."234".$dec."56", '2')."</td></tr>\n";
if (($thousand != ',' && $thousand != '.') || ($thousand != ' '))
{
	print '<tr class="oddeven"><td>&nbsp; => price2num('."'1 234.56')</td><td>".price2num("1 234.56", '2')."</td>";
	print "</tr>\n";
}
print '<tr class="oddeven"><td>&nbsp; => price(1234.56)</td><td>'.price(1234.56).'</td></tr>'."\n";
// Timezone
$txt =$langs->trans("OSTZ").' (variable system TZ): '.(! empty($_ENV["TZ"])?$_ENV["TZ"]:$langs->trans("NotDefined")).'<br>'."\n";
$txt.=$langs->trans("PHPTZ").' (php.ini date.timezone): '.(ini_get("date.timezone")?ini_get("date.timezone"):$langs->trans("NotDefined")).''."<br>\n"; // date.timezone must be in valued defined in http://fr3.php.net/manual/en/timezones.europe.php
$txt.=$langs->trans("ERP constant MAIN_SERVER_TZ").': '.(empty($conf->global->MAIN_SERVER_TZ)?$langs->trans("NotDefined"):$conf->global->MAIN_SERVER_TZ);
print '<tr class="oddeven"><td>'.$langs->trans("CurrentTimeZone").'</td><td>';	// Timezone server PHP
$a=getServerTimeZoneInt('now');
$b=getServerTimeZoneInt('winter');
$c=getServerTimeZoneInt('summer');
$daylight=(is_numeric($c) && is_numeric($b))?round($c-$b):'unknown';
//print $a." ".$b." ".$c." ".$daylight;
$val=($a>=0?'+':'').$a;
$val.=' ('.($a=='unknown'?'unknown':($a>=0?'+':'').($a*3600)).')';
$val.=' &nbsp; &nbsp; &nbsp; '.getServerTimeZoneString();
$val.=' &nbsp; &nbsp; &nbsp; '.$langs->trans("DaylingSavingTime").': '.($daylight==='unknown'?'unknown':($a==$c?yn($daylight):yn(0).($daylight?'  &nbsp; &nbsp; ('.$langs->trans('YesInSummer').')':'')));
print $form->textwithtooltip($val, $txt, 2, 1, img_info(''));
print '</td></tr>'."\n";	// value defined in http://fr3.php.net/manual/en/timezones.europe.php
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("CurrentHour").'</td><td>'.dol_print_date(dol_now(), 'dayhour', 'tzserver').'</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => dol_print_date(0,"dayhourtext")</td><td>'.dol_print_date(0, "dayhourtext").'</td>';
print '<tr class="oddeven"><td>&nbsp; => dol_get_first_day(1970,1,false)</td><td>'.dol_get_first_day(1970, 1, false).' &nbsp; &nbsp; (=> dol_print_date() or idate() of this value = '.dol_print_date(dol_get_first_day(1970, 1, false), 'dayhour').')</td>';
print '<tr class="oddeven"><td>&nbsp; => dol_get_first_day(1970,1,true)</td><td>'.dol_get_first_day(1970, 1, true).' &nbsp; &nbsp; (=> dol_print_date() or idate() of this value = '.dol_print_date(dol_get_first_day(1970, 1, true), 'dayhour').')</td>';
// Database timezone
if ($conf->db->type == 'mysql' || $conf->db->type == 'mysqli')
{
	print '<tr class="oddeven"><td>'.$langs->trans("MySQLTimeZone").' (database)</td><td>';	// Timezone server base
	$sql="SHOW VARIABLES where variable_name = 'system_time_zone'";
	$resql = $db->query($sql);
	if ($resql)
	{
		$obj = $db->fetch_object($resql);
		print $form->textwithtooltip($obj->Value, $langs->trans('TZHasNoEffect'), 2, 1, img_info(''));
	}
	print '</td></tr>'."\n";
}
// Client
$tz=(int) $_SESSION['dol_tz'] + (int) $_SESSION['dol_dst'];
print '<tr class="oddeven"><td>'.$langs->trans("ClientTZ").'</td><td>'.($tz?($tz>=0?'+':'').$tz:'').' ('.($tz>=0?'+':'').($tz*60*60).')';
print ' &nbsp; &nbsp; &nbsp; '.$_SESSION['dol_tz_string'];
print ' &nbsp; &nbsp; &nbsp; '.$langs->trans("DaylingSavingTime").': ';
if ($_SESSION['dol_dst']>0) print yn(1);
else print yn(0);
if (! empty($_SESSION['dol_dst_first'])) print ' &nbsp; &nbsp; ('.dol_print_date(dol_stringtotime($_SESSION['dol_dst_first']), 'dayhour', 'gmt').' - '.dol_print_date(dol_stringtotime($_SESSION['dol_dst_second']), 'dayhour', 'gmt').')';
print '</td></tr>'."\n";
print '</td></tr>'."\n";
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("ClientHour").'</td><td>'.dol_print_date(dol_now(), 'dayhour', 'tzuser').'</td></tr>'."\n";

$filesystemencoding=ini_get("unicode.filesystem_encoding");	// Disponible avec PHP 6.0
print '<tr class="oddeven"><td>'.$langs->trans("File encoding").' (php.ini unicode.filesystem_encoding)</td><td>'.$filesystemencoding.'</td></tr>'."\n";

$tmp=ini_get("unicode.filesystem_encoding");						// Disponible avec PHP 6.0
if (empty($tmp) && ! empty($_SERVER["WINDIR"])) $tmp='iso-8859-1';	// By default for windows
if (empty($tmp)) $tmp='utf-8';										// By default for other
if (! empty($conf->global->MAIN_FILESYSTEM_ENCODING)) $tmp=$conf->global->MAIN_FILESYSTEM_ENCODING;
print '<tr class="oddeven"><td>&nbsp; => '.$langs->trans("File encoding").'</td><td>'.$tmp.'</td></tr>'."\n";	// date.timezone must be in valued defined in http://fr3.php.net/manual/en/timezones.europe.php

print '</table>';
print '</div>';
print '<br>';



// Parameters in conf.php file (when a parameter start with ?, it is shown only if defined)
$configfileparameters=array(
		'mounir_main_url_root' => $langs->trans("URLRoot"),
		'?mounir_main_url_root_alt' => $langs->trans("URLRoot").' (alt)',
		'mounir_main_document_root'=> $langs->trans("DocumentRootServer"),
		'?mounir_main_document_root_alt' => $langs->trans("DocumentRootServer").' (alt)',
		'mounir_main_data_root' => $langs->trans("DataRootServer"),
        'mounir_main_instance_unique_id' => $langs->trans("InstanceUniqueID"),
        'separator1' => '',
		'mounir_main_db_host' => $langs->trans("DatabaseServer"),
		'mounir_main_db_port' => $langs->trans("DatabasePort"),
		'mounir_main_db_name' => $langs->trans("DatabaseName"),
		'mounir_main_db_type' => $langs->trans("DriverType"),
		'mounir_main_db_user' => $langs->trans("DatabaseUser"),
		'mounir_main_db_pass' => $langs->trans("DatabasePassword"),
		'mounir_main_db_character_set' => $langs->trans("DBStoringCharset"),
		'mounir_main_db_collation' => $langs->trans("DBSortingCollation"),
		'?mounir_main_db_prefix' => $langs->trans("Prefix"),
		'separator2' => '',
		'mounir_main_authentication' => $langs->trans("AuthenticationMode"),
        '?multicompany_transverse_mode'=>  $langs->trans("MultiCompanyMode"),
		'separator'=> '',
		'?mounir_main_auth_ldap_login_attribute' => 'mounir_main_auth_ldap_login_attribute',
		'?mounir_main_auth_ldap_host' => 'mounir_main_auth_ldap_host',
		'?mounir_main_auth_ldap_port' => 'mounir_main_auth_ldap_port',
		'?mounir_main_auth_ldap_version' => 'mounir_main_auth_ldap_version',
		'?mounir_main_auth_ldap_dn' => 'mounir_main_auth_ldap_dn',
		'?mounir_main_auth_ldap_admin_login' => 'mounir_main_auth_ldap_admin_login',
		'?mounir_main_auth_ldap_admin_pass' => 'mounir_main_auth_ldap_admin_pass',
		'?mounir_main_auth_ldap_debug' => 'mounir_main_auth_ldap_debug',
		'separator3' => '',
		'?mounir_lib_ADODB_PATH' => 'mounir_lib_ADODB_PATH',
		'?mounir_lib_FPDF_PATH' => 'mounir_lib_FPDF_PATH',
		'?mounir_lib_TCPDF_PATH' => 'mounir_lib_TCPDF_PATH',
		'?mounir_lib_FPDI_PATH' => 'mounir_lib_FPDI_PATH',
		'?mounir_lib_TCPDI_PATH' => 'mounir_lib_TCPDI_PATH',
		'?mounir_lib_NUSOAP_PATH' => 'mounir_lib_NUSOAP_PATH',
		'?mounir_lib_PHPEXCEL_PATH' => 'mounir_lib_PHPEXCEL_PATH',
		'?mounir_lib_GEOIP_PATH' => 'mounir_lib_GEOIP_PATH',
		'?mounir_lib_ODTPHP_PATH' => 'mounir_lib_ODTPHP_PATH',
		'?mounir_lib_ODTPHP_PATHTOPCLZIP' => 'mounir_lib_ODTPHP_PATHTOPCLZIP',
		'?mounir_js_CKEDITOR' => 'mounir_js_CKEDITOR',
		'?mounir_js_JQUERY' => 'mounir_js_JQUERY',
		'?mounir_js_JQUERY_UI' => 'mounir_js_JQUERY_UI',
		'?mounir_js_JQUERY_FLOT' => 'mounir_js_JQUERY_FLOT',
		'?mounir_font_DOL_DEFAULT_TTF' => 'mounir_font_DOL_DEFAULT_TTF',
		'?mounir_font_DOL_DEFAULT_TTF_BOLD' => 'mounir_font_DOL_DEFAULT_TTF_BOLD',
		'separator4' => '',
		'mounir_main_prod' => 'Production mode (Hide all error messages)',
		'mounir_main_restrict_os_commands' => 'Restrict CLI commands for backups',
		'mounir_main_restrict_ip' => 'Restrict access to some IPs only',
		'?mounir_mailing_limit_sendbyweb' => 'Limit nb of email sent by page',
		'?mounir_mailing_limit_sendbycli' => 'Limit nb of email sent by cli',
		'?mounir_strict_mode' => 'Strict mode is on/off',
		'?mounir_nocsrfcheck' => 'Disable CSRF security checks'
);

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td class="titlefield">'.$langs->trans("Parameters").' ';
print $langs->trans("ConfigurationFile").' ('.$conffiletoshowshort.')';
print '</td>';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>'."\n";

foreach($configfileparameters as $key => $value)
{
	$ignore=0;

	if (empty($ignore))
	{
		$newkey = preg_replace('/^\?/', '', $key);

		if (preg_match('/^\?/', $key) && empty(${$newkey}))
		{
		    if ($newkey != 'multicompany_transverse_mode' || empty($conf->multicompany->enabled))
                continue;    // We discard parameters starting with ?
		}
		if (strpos($newkey, 'separator') !== false && $lastkeyshown == 'separator') continue;

		print '<tr class="oddeven">';
		if (strpos($newkey, 'separator') !== false)
		{
			print '<td colspan="3">&nbsp;</td>';
		}
		else
		{
			// Label
			print "<td>".$value.'</td>';
			// Key
			print '<td>'.$newkey.'</td>';
			// Value
			print "<td>";
			if ($newkey == 'mounir_main_db_pass') print preg_replace('/./i', '*', ${$newkey});
			elseif ($newkey == 'mounir_main_url_root' && preg_match('/__auto__/', ${$newkey})) print ${$newkey}.' => '.constant('DOL_MAIN_URL_ROOT');
			elseif ($newkey == 'mounir_main_document_root_alt')
			{
				$tmparray=explode(',', ${$newkey});
				$i=0;
				foreach($tmparray as $value2)
				{
					if ($i > 0) print ', ';
					print $value2;
					if (! is_readable($value2))
					{
						$langs->load("errors");
						print ' '.img_warning($langs->trans("ErrorCantReadDir", $value2));
					}
					++$i;
				}
			}
			elseif ($newkey == 'mounir_main_instance_unique_id')
			{
			    //print $conf->file->instance_unique_id;
			    global $mounir_main_cookie_cryptkey;
			    $valuetoshow = ${$newkey} ? ${$newkey} : $mounir_main_cookie_cryptkey;
			    print $valuetoshow;
			    if (empty($valuetoshow)) {
			        print img_warning("EditConfigFileToAddEntry", 'mounir_main_instance_unique_id');
			    }
			}
			else
			{
			    print ${$newkey};
			}
			if ($newkey == 'mounir_main_url_root' && ${$newkey} != DOL_MAIN_URL_ROOT) print ' (currently overwritten by autodetected value: '.DOL_MAIN_URL_ROOT.')';
			print "</td>";
		}
		print "</tr>\n";
		$lastkeyshown=$newkey;
	}
}
print '</table>';
print '</div>';
print '<br>';



// Parameters in database
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<td class="titlefield">'.$langs->trans("Parameters").' '.$langs->trans("Database").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
if (empty($conf->multicompany->enabled) || !$user->entity) print '<td class="center width="80px"">'.$langs->trans("Entity").'</td>';	// If superadmin or multicompany disabled
print "</tr>\n";

$sql = "SELECT";
$sql.= " rowid";
$sql.= ", ".$db->decrypt('name')." as name";
$sql.= ", ".$db->decrypt('value')." as value";
$sql.= ", type";
$sql.= ", note";
$sql.= ", entity";
$sql.= " FROM ".MAIN_DB_PREFIX."const";
if (empty($conf->multicompany->enabled))
{
	// If no multicompany mode, admins can see global and their constantes
	$sql.= " WHERE entity IN (0,".$conf->entity.")";
}
else
{
	// If multicompany mode, superadmin (user->entity=0) can see everything, admin are limited to their entities.
	if ($user->entity) $sql.= " WHERE entity IN (".$user->entity.",".$conf->entity.")";
}
$sql.= " ORDER BY entity, name ASC";
$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	$i = 0;

	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);

		print '<tr class="oddeven">';
		print '<td class="tdoverflowmax300">'.$obj->name.'</td>'."\n";
		print '<td class="tdoverflowmax300">'.dol_escape_htmltag($obj->value).'</td>'."\n";
		if (empty($conf->multicompany->enabled) || !$user->entity) print '<td class="center" width="80px">'.$obj->entity.'</td>'."\n";	// If superadmin or multicompany disabled
		print "</tr>\n";

		$i++;
	}
}

print '</table>';
print '</div>';

// End of page
llxFooter();
$db->close();
