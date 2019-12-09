<?php


require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';

$ref = GETPOST('ref', 'alpha');
$label = GETPOST('label', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

if ($_POST) {
	if (empty($ref) || empty($label)) {
		setEventMessages($langs->trans('ErrorFieldsRequired'), null, 'errors');
	} else {

		$prodattr = new ProductAttribute($db);
		$prodattr->label = $label;
		$prodattr->ref = $ref;

		$resid = $prodattr->create($user);
		if ($resid > 0) {
			setEventMessages($langs->trans('RecordSaved'), null, 'mesgs');
			if ($backtopage)
			{
				header('Location: '.$backtopage);
			}
			else
			{
				header('Location: '.DOL_URL_ROOT.'/variants/card.php?id='.$resid.'&backtopage='.urlencode($backtopage));
			}
			exit;
		} else {
			setEventMessages($langs->trans('ErrorRecordAlreadyExists'), $prodattr->errors, 'errors');
		}
	}
}

$langs->load('products');


/*
 * View
 */

$title = $langs->trans('NewProductAttribute');

llxHeader('', $title);

print load_fiche_titre($title);

dol_fiche_head();

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="add">';
print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

?>

	<table class="border centpercent">
		<tr>
			<td class="titlefield fieldrequired"><label for="ref"><?php echo $langs->trans('Ref') ?></label></td>
			<td><input type="text" id="ref" name="ref" value="<?php echo $ref ?>"></td>
			<td><?php echo $langs->trans("VariantRefExample"); ?>
		</tr>
		<tr>
			<td class="fieldrequired"><label for="label"><?php echo $langs->trans('Label') ?></label></td>
			<td><input type="text" id="label" name="label" value="<?php echo $label ?>"></td>
			<td><?php echo $langs->trans("VariantLabelExample"); ?>
		</tr>

	</table>

<?php
dol_fiche_end();

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Create").'"></div>';

print '</form>';

// End of page
llxFooter();
$db->close();
