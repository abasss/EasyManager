<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

$langs->loadLangs(array("companies", "projects"));

// Security check
$socid = GETPOST('socid', 'int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe', $socid, '&societe');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('projectthirdparty'));


/*
 *	Actions
 */

$parameters=array('id'=>$socid);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');



/*
 *	View
 */

$contactstatic = new Contact($db);

$form = new Form($db);

if ($socid)
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

	$langs->load("companies");


	$object = new Societe($db);
	$result = $object->fetch($socid);

	$title=$langs->trans("Projects");
	if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->name." - ".$title;
	llxHeader('', $title);

	if (! empty($conf->notification->enabled)) $langs->load("mails");
	$head = societe_prepare_head($object);

	dol_fiche_head($head, 'project', $langs->trans("ThirdParty"), -1, 'company');

    $linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

    dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom');

    print '<div class="fichecenter">';

    print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">';

    if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
    {
        print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$object->prefix_comm.'</td></tr>';
    }

	if ($object->client)
	{
		print '<tr><td class="titlefield">';
		print $langs->trans('CustomerCode').'</td><td colspan="3">';
		print $object->code_client;
		if ($object->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
		print '</td></tr>';
	}

	if ($object->fournisseur)
	{
		print '<tr><td class="titlefield">';
		print $langs->trans('SupplierCode').'</td><td colspan="3">';
		print $object->code_fournisseur;
		if ($object->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
		print '</td></tr>';
	}

	print '</table>';

	print '</div>';

	dol_fiche_end();


   	$addbutton = '<a class="butActionNew" href="'.DOL_URL_ROOT.'/projet/card.php?action=create&socid='.$object->id.'&amp;backtopage='.urlencode($backtopage).'"><span class="text-plus-circle">'.$langs->trans("AddProject").'</span><span class="fa fa-plus-circle valignmiddle"></span></a>';

    print '<br>';


	// Projects list
	$result=show_projects($conf, $langs, $db, $object, $_SERVER["PHP_SELF"].'?socid='.$object->id, 1, $addbutton);
}

// End of page
llxFooter();
$db->close();
