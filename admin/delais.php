<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// Load translation files required by the page
$langs->load("admin");

if (! $user->admin) accessforbidden();

$action=GETPOST('action', 'alpha');

$modules=array(
		'facture' => array(
				array(
						'code' => 'MAIN_DELAY_CUSTOMER_BILLS_UNPAYED',
						'img' => 'bill'
				)
		),
		'service' => array(
				array(
						'code' => 'MAIN_DELAY_NOT_ACTIVATED_SERVICES',
						'img' => 'service'
				),
				array(
						'code' => 'MAIN_DELAY_RUNNING_SERVICES',
						'img' => 'service'
				)
		),
		
	
);

$labelmeteo = array(0=>$langs->trans("No"), 1=>$langs->trans("Yes"), 2=>$langs->trans("OnMobileOnly"));


/*
 * Actions
 */

if ($action == 'update')
{
	foreach($modules as $module => $delays)
	{
		if (! empty($conf->$module->enabled))
    	{
    		foreach($delays as $delay)
    		{
    			if (GETPOST($delay['code']) != '')
    			{
    				mounir_set_const($db, $delay['code'], GETPOST($delay['code']), 'chaine', 0, '', $conf->entity);
    			}
    		}
    	}
	}

	mounir_set_const($db, "MAIN_DISABLE_METEO", $_POST["MAIN_DISABLE_METEO"], 'chaine', 0, '', $conf->entity);
	mounir_set_const($db, "MAIN_USE_METEO_WITH_PERCENTAGE", GETPOST("MAIN_USE_METEO_WITH_PERCENTAGE"), 'chaine', 0, '', $conf->entity);

	// For update value with percentage
	$plus='';
	if(!empty($conf->global->MAIN_USE_METEO_WITH_PERCENTAGE)) $plus = '_PERCENTAGE';
	// Update values
	for($i=0; $i<4; $i++) {
    	if(isset($_POST['MAIN_METEO'.$plus.'_LEVEL'.$i])) mounir_set_const($db, 'MAIN_METEO'.$plus.'_LEVEL'.$i, GETPOST('MAIN_METEO'.$plus.'_LEVEL'.$i, 'int'), 'chaine', 0, '', $conf->entity);
    }
}


/*
 * View
 */

$form = new Form($db);

llxHeader();

print load_fiche_titre($langs->trans("DelaysOfToleranceBeforeWarning"), '', 'title_setup');

print $langs->transnoentities("DelaysOfToleranceDesc", img_warning());
print " ".$langs->trans("OnlyActiveElementsAreShown", DOL_URL_ROOT.'/admin/modules.php')."<br>\n";
print "<br>\n";

$countrynotdefined='<font class="error">'.$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')</font>';

if ($action == 'edit')
{
    print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" name="form_index">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="update">';

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("DelaysOfToleranceBeforeWarning").'</td><td class="center" width="120px">'.$langs->trans("Value").'</td></tr>';

    foreach($modules as $module => $delays)
    {
    	if (! empty($conf->$module->enabled))
    	{
    		foreach($delays as $delay)
    		{

				$value=(! empty($conf->global->{$delay['code']})?$conf->global->{$delay['code']}:0);
    			print '<tr class="oddeven">';
    			print '<td width="20px">'.img_object('', $delay['img']).'</td>';
    			print '<td>'.$langs->trans('Delays_'.$delay['code']).'</td><td>';
    			print '<input class="right maxwidth75" type="number" name="'.$delay['code'].'" value="'.$value.'"> '.$langs->trans("days").'</td></tr>';
    		}
    	}
    }

    print '</table>';

    print '<br>';

	
}
else
{
    /*
     * Affichage des parametres
     */

	print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("DelaysOfToleranceBeforeWarning").'</td><td class="center" width="120px">'.$langs->trans("Value").'</td></tr>';

    foreach($modules as $module => $delays)
    {
    	if (! empty($conf->$module->enabled))
    	{
    		foreach($delays as $delay)
    		{

				$value=(! empty($conf->global->{$delay['code']})?$conf->global->{$delay['code']}:0);
    			print '<tr class="oddeven">';
    			print '<td width="20px">'.img_object('', $delay['img']).'</td>';
    			print '<td>'.$langs->trans('Delays_'.$delay['code']).'</td>';
    			print '<td class="right">'.$value.' '.$langs->trans("days").'</td></tr>';
    		}
    	}
    }

    print '</table>';

	print '<br>';

	print '</table>';
}

print '<br>';



if($action == 'edit') {

	print '<br><div class="center"><input type="submit" class="button" value="'.$langs->trans("Save").'"></div>';
	print '<br></form>';
} else {

	print '<br><div class="tabsAction">';
	print '<a class="butAction" href="delais.php?action=edit">'.$langs->trans("Modify").'</a></div>';
}

// End of page
llxFooter();
$db->close();
