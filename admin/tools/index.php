<?php

require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("companies","admin"));

if (! $user->admin)
	accessforbidden();


/*
 * View
 */

$form = new Form($db);

$title=$langs->trans("AdminTools");
//if (GETPOST('leftmenu',"aZ09") == 'admintools') $title=$langs->trans("ModulesSystemTools");

llxHeader('', $title);

print load_fiche_titre($title, '', 'title_setup');
// End of page
llxFooter();
$db->close();
