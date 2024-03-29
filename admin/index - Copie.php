<?php

require '../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array('admin', 'companies'));

if (!$user->admin) accessforbidden();

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('homesetup'));


/*
 * View
 */

$form = new Form($db);

$wikihelp='EN:First_setup|FR:Premiers_paramétrages|ES:Primeras_configuraciones';
llxHeader('', $langs->trans("Setup"), $wikihelp);


print load_fiche_titre($langs->trans("SetupArea"), '', 'title_setup.png');


if (! empty($conf->global->MAIN_MOTD_SETUPPAGE))
{
    $conf->global->MAIN_MOTD_SETUPPAGE=preg_replace('/<br(\s[\sa-zA-Z_="]*)?\/?>/i', '<br>', $conf->global->MAIN_MOTD_SETUPPAGE);
    if (! empty($conf->global->MAIN_MOTD_SETUPPAGE))
    {
    	$i=0;
    	while (preg_match('/__\(([a-zA-Z|@]+)\)__/i', $conf->global->MAIN_MOTD_SETUPPAGE, $reg) && $i < 100)
    	{
    		$tmp=explode('|', $reg[1]);
    		if (! empty($tmp[1])) $langs->load($tmp[1]);
    		$conf->global->MAIN_MOTD_SETUPPAGE=preg_replace('/__\('.preg_quote($reg[1]).'\)__/i', $langs->trans($tmp[0]), $conf->global->MAIN_MOTD_SETUPPAGE);
    		$i++;
    	}

    	print "\n<!-- Start of welcome text for setup page -->\n";
        print '<table width="100%" class="notopnoleftnoright"><tr><td>';
        print dol_htmlentitiesbr($conf->global->MAIN_MOTD_SETUPPAGE);
        print '</td></tr></table><br>';
        print "\n<!-- End of welcome text for setup page -->\n";
    }
}

print $langs->trans("SetupDescription1");
print $langs->trans("AreaForAdminOnly").' ';
print $langs->trans("SetupDescription2", $langs->transnoentities("MenuCompanySetup"), $langs->transnoentities("Modules"))."<br><br>";

print '<br>';

// Show info setup company
if (empty($conf->global->MAIN_INFO_SOCIETE_NOM) || empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY)) $setupcompanynotcomplete=1;
print img_picto('', 'puce').' '.$langs->trans("SetupDescription3", DOL_URL_ROOT.'/admin/company.php?mainmenu=home'.(empty($setupcompanynotcomplete)?'':'&action=edit'), $langs->transnoentities("Setup"), $langs->transnoentities("MenuCompanySetup"));
if (! empty($setupcompanynotcomplete))
{
	$langs->load("errors");
	$warnpicto=img_warning($langs->trans("WarningMandatorySetupNotComplete"), 'style="padding-right: 6px;"');
	print '<br><div class="warning"><a href="'.DOL_URL_ROOT.'/admin/company.php?mainmenu=home'.(empty($setupcompanynotcomplete)?'':'&action=edit').'">'.$warnpicto.$langs->trans("WarningMandatorySetupNotComplete").'</a></div>';
}
print '<br>';
print '<br>';
print '<br>';

// Show info setup module
print img_picto('', 'puce').' '.$langs->trans("SetupDescription4", DOL_URL_ROOT.'/admin/modules.php?mainmenu=home', $langs->transnoentities("Setup"), $langs->transnoentities("Modules"));

/*
$nbofactivatedmodules=count($conf->modules);
$moreinfo=$langs->trans("TotalNumberOfActivatedModules",($nbofactivatedmodules-1), count($modules));
if ($nbofactivatedmodules <= 1) $moreinfo .= ' '.img_warning($langs->trans("YouMustEnableOneModule"));
print '<br>'.$moreinfo;
*/

if (count($conf->modules) <= (empty($conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING)?1:$conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING))	// If only user module enabled
{
	$langs->load("errors");
	$warnpicto=img_warning($langs->trans("WarningEnableYourModulesApplications"), 'style="padding-right: 6px;"');
	print '<br><div class="warning"><a href="'.DOL_URL_ROOT.'/admin/modules.php?mainmenu=home">'.$warnpicto.$langs->trans("WarningEnableYourModulesApplications").'</a></div>';
}
print '<br>';
print '<br>';
print '<br>';
print '<br>';

// Add hook to add information
$parameters=array();
$reshook=$hookmanager->executeHooks('addHomeSetup', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
print $hookmanager->resPrint;
if (empty($reshook))
{
	// Show into other
    print '<span class="opacitymedium">'.$langs->trans("SetupDescription5")."</span><br>";
	print "<br>";

	// Show logo
	print '<div class="center"><div class="logo_setup"></div></div>';
}

// End of page
llxFooter();
$db->close();
