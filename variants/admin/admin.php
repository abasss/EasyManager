<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';

$langs->loadLangs(array("admin", "products"));

// Security check
if (! $user->admin || (empty($conf->product->enabled) && empty($conf->service->enabled)))
	accessforbidden();

if ($_POST) {

	$value = GETPOST('PRODUIT_ATTRIBUTES_HIDECHILD');

	if (mounir_set_const($db, 'PRODUIT_ATTRIBUTES_HIDECHILD', $value, 'chaine', 0, '', $conf->entity)) {
		setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
	} else {
		setEventMessages($langs->trans('CoreErrorMessage'), null, 'errors');
	}

       if (mounir_set_const($db, 'PRODUIT_ATTRIBUTES_SEPARATOR', GETPOST('PRODUIT_ATTRIBUTES_SEPARATOR'), 'chaine', 0, '', $conf->entity)) {
               setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
       } else {
               setEventMessages($langs->trans('CoreErrorMessage'), null, 'errors');
       }
}

$title = $langs->trans('ModuleSetup').' '.$langs->trans('ProductAttributes');
llxHeader('', $title);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

dol_fiche_head(array(), 'general', $tab, 0, 'product');

print '<form method="post">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<th>'.$langs->trans("Parameters").'</td>'."\n";
print '<th class="right" width="60">'.$langs->trans("Value").'</td>'."\n";
print '<th width="80">&nbsp;</td></tr>'."\n";
print '<tr class="oddeven"><td>'.$langs->trans('HideProductCombinations').'</td><td>';
print $form->selectyesno("PRODUIT_ATTRIBUTES_HIDECHILD", $conf->global->PRODUIT_ATTRIBUTES_HIDECHILD, 1).'</td></tr>';
print '<tr class="oddeven"><td>'.$langs->trans('CombinationsSeparator').'</td>';
if (isset($conf->global->PRODUIT_ATTRIBUTES_SEPARATOR)) {
    $separator = $conf->global->PRODUIT_ATTRIBUTES_SEPARATOR;
} else {
    $separator = "_";
}
print '<td class="right"><input size="3" type="text" class="flat" name="PRODUIT_ATTRIBUTES_SEPARATOR" value="'.$separator.'"></td></tr>';
print '</table>';
print '<br><div class="center"><input type="submit" value="'.$langs->trans('Save').'" class="button"></div>';
print '</form>';

// End of page
llxFooter();
$db->close();
