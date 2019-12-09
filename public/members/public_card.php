<?php

if (! defined('NOLOGIN'))		define("NOLOGIN", 1);		// This means this output page does not require to be logged.
if (! defined('NOCSRFCHECK'))	define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.
if (! defined('NOIPCHECK'))		define('NOIPCHECK', '1');	// Do not check IP defined into conf $mounir_main_restrict_ip

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// TODO This should be useless. Because entity must be retreive from object ref and not from url.
$entity=(! empty($_GET['entity']) ? (int) $_GET['entity'] : (! empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) define("DOLENTITY", $entity);

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Security check
if (empty($conf->adherent->enabled)) accessforbidden('', 0, 0, 1);


$langs->loadLangs(array("main", "members", "companies", "other"));

$id=GETPOST('id', 'int');
$object = new Adherent($db);
$extrafields = new ExtraFields($db);



/*
 * Actions
 */

// None



/*
 * View
 */

$morehead='';
if (! empty($conf->global->MEMBER_PUBLIC_CSS)) $morehead='<link rel="stylesheet" type="text/css" href="'.$conf->global->MEMBER_PUBLIC_CSS.'">';
else $morehead='<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/theme/eldy/style.css.php'.'">';

llxHeaderVierge($langs->trans("MemberCard"), $morehead);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('adherent');
if ($id > 0)
{
	$res=$object->fetch($id);
	if ($res < 0) { dol_print_error($db, $object->error); exit; }
	$res=$object->fetch_optionals();

	print load_fiche_titre($langs->trans("MemberCard"), '', '');

	if (empty($object->public))
	{
		 print $langs->trans("ErrorThisMemberIsNotPublic");
	}
	else
	{
		print '<table class="public_border" cellspacing="0" width="100%" cellpadding="3">';

		print '<tr><td width="15%">'.$langs->trans("Type").'</td><td class="valeur">'.$object->type."</td></tr>\n";
		print '<tr><td>'.$langs->trans("Person").'</td><td class="valeur">'.$object->morphy.'</td></tr>';
		print '<tr><td>'.$langs->trans("Firstname").'</td><td class="valeur" width="35%">'.$object->firstname.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Lastname").'</td><td class="valeur">'.$object->lastname.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Gender").'</td><td class="valeur">'.$object->gender.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Company").'</td><td class="valeur">'.$object->societe.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Address").'</td><td class="valeur">'.nl2br($object->address).'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td><td class="valeur">'.$object->zip.' '.$object->town.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Country").'</td><td class="valeur">'.$object->country.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("EMail").'</td><td class="valeur">'.$object->email.'&nbsp;</td></tr>';
		print '<tr><td>'.$langs->trans("Birthday").'</td><td class="valeur">'.dol_print_date($object->birth, 'day').'</td></tr>';

		if (isset($object->photo) && $object->photo !='')
		{
			$form = new Form($db);
			print '<tr><td>URL Photo</td><td class="valeur">';
			print $form->showphoto('memberphoto', $object, 64);
			print '</td></tr>'."\n";
		}
		//  foreach($extrafields->attributes[$object->table_element]['label'] as $key=>$value){
		//    print "<tr><td>$value</td><td>".$object->array_options["options_$key"]."&nbsp;</td></tr>\n";
		//  }

		print '<tr><td class="tdtop">'.$langs->trans("Comments").'</td><td>'.nl2br($object->note_public).'</td></tr>';

		print '</table>';
	}
}


llxFooterVierge();

$db->close();



/**
 * Show header for card member
 *
 * @param 	string		$title		Title
 * @param 	string		$head		More info into header
 * @return	void
 */
function llxHeaderVierge($title, $head = "")
{
	global $user, $conf, $langs;

	header("Content-type: text/html; charset=".$conf->file->character_set_client);
	print "<html>\n";
	print "<head>\n";
	print "<title>".$title."</title>\n";
	if ($head) print $head."\n";
	print "</head>\n";
	print '<body class="public_body">'."\n";
}

/**
* Show footer for card member
*
* @return	void
*/
function llxFooterVierge()
{
    printCommonFooter('public');

	print "</body>\n";
	print "</html>\n";
}
