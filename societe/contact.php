<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
if (! empty($conf->adherent->enabled)) require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';

$langs->loadLangs(array("companies","commercial","bills","banks","users"));
if (! empty($conf->categorie->enabled)) $langs->load("categories");
if (! empty($conf->incoterm->enabled)) $langs->load("incoterm");
if (! empty($conf->notification->enabled)) $langs->load("mails");

$mesg=''; $error=0; $errors=array();

$action		= (GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view');
$cancel     = GETPOST('cancel', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$confirm	= GETPOST('confirm');
$socid		= GETPOST('socid', 'int')?GETPOST('socid', 'int'):GETPOST('id', 'int');
if ($user->societe_id) $socid=$user->societe_id;
if (empty($socid) && $action == 'view') $action='create';

$object = new Societe($db);
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('thirdpartycontact','globalcard'));

if ($action == 'view' && $object->fetch($socid)<=0)
{
	$langs->load("errors");
	print($langs->trans('ErrorRecordNotFound'));
	exit;
}

// Get object canvas (By default, this is not defined, so standard usage of mounir)
$object->getCanvas($socid);
$canvas = $object->canvas?$object->canvas:GETPOST("canvas");
$objcanvas=null;
if (! empty($canvas))
{
    require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
    $objcanvas = new Canvas($db, $action);
    $objcanvas->getCanvas('thirdparty', 'card', $canvas);
}

// Security check
$result = restrictedArea($user, 'societe', $socid, '&societe', '', 'fk_soc', 'rowid', $objcanvas);




/*
 * Actions
 */

$parameters=array('id'=>$socid, 'objcanvas'=>$objcanvas);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if ($cancel)
    {
        $action='';
        if (! empty($backtopage))
        {
            header("Location: ".$backtopage);
            exit;
        }
    }

    // Selection of new fields
    include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';
}


/*
 *  View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formadmin = new FormAdmin($db);
$formcompany = new FormCompany($db);

if ($socid > 0 && empty($object->id))
{
    $result=$object->fetch($socid);
	if ($result <= 0) dol_print_error('', $object->error);
}

$title=$langs->trans("ThirdParty");
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->name." - ".$langs->trans('Card');
$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $title, $help_url);

$countrynotdefined=$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';


if (!empty($object->id)) $res=$object->fetch_optionals($object->id, $extralabels);
//if ($res < 0) { dol_print_error($db); exit; }


$head = societe_prepare_head($object);

dol_fiche_head($head, 'contact', $langs->trans("ThirdParty"), 0, 'company');

$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom', '', '', 0, '', '', 'arearefnobottom');

dol_fiche_end();

print '<br>';

if ($action != 'presend')
{
	// Contacts list
	if (empty($conf->global->SOCIETE_DISABLE_CONTACTS))
	{
		$result=show_contacts($conf, $langs, $db, $object, $_SERVER["PHP_SELF"].'?socid='.$object->id);
	}
}

// End of page
llxFooter();
$db->close();
