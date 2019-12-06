<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/fiscalyear.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/fiscalyear.class.php';

// Load translation files required by the page
$langs->loadLangs(array("admin","compta"));

// Security check
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accounting->fiscalyear)
	accessforbidden();

$id = GETPOST('id', 'int');

// View
$title = $langs->trans("Fiscalyear") . " - " . $langs->trans("Info");
$helpurl = "";
llxHeader("", $title, $helpurl);

if ($id) {
	$object = new Fiscalyear($db);
	$object->fetch($id);
	$object->info($id);

	$head = fiscalyear_prepare_head($object);

	dol_fiche_head($head, 'info', $langs->trans("Fiscalyear"), 0, 'cron');

	print '<table width="100%"><tr><td>';
	dol_print_object_info($object);
	print '</td></tr></table>';

	print '</div>';
}

// End of page
llxFooter();
$db->close();
