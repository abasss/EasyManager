<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

// Load translation files required by page
$langs->load("users");

// Security check
$id = GETPOST('id', 'int');
$object = new User($db);
if ($id > 0 || ! empty($ref))
{
	$result = $object->fetch($id, $ref, '', 1);
	$object->getrights();
}

// Security check
$socid=0;
if ($user->societe_id > 0) $socid = $user->societe_id;
$feature2 = (($socid && $user->rights->user->self->creer)?'':'user');

$result = restrictedArea($user, 'user', $id, 'user&user', $feature2);

// If user is not user that read and no permission to read other users, we stop
if (($object->id != $user->id) && (! $user->rights->user->user->lire))
  accessforbidden();



/*
 * View
 */

$form = new Form($db);

llxHeader();

$head = user_prepare_head($object);

$title = $langs->trans("User");
dol_fiche_head($head, 'info', $title, -1, 'user');


$linkback = '';

if ($user->rights->user->user->lire || $user->admin) {
	$linkback = '<a href="'.DOL_URL_ROOT.'/user/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
}

dol_banner_tab($object, 'id', $linkback, $user->rights->user->user->lire || $user->admin);


$object->info($id); // This overwrite ->ref with login instead of id


print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

print '<br>';

dol_print_object_info($object);

print '</div>';


dol_fiche_end();

// End of page
llxFooter();
$db->close();
