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


print '<br>';
print '<br>';
print '<br>';




if (empty($reshook))
{
	
	print "<br>";

	// Show logo
	print '<div class="center"><div class="logo_setup"></div></div>';
}

// End of page
llxFooter();
$db->close();
