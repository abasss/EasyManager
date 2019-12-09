<?php

if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}


$object=$GLOBALS['object'];
?>

<!-- BEGIN PHP TEMPLATE VIEW.TPL -->
<?php
$head=product_prepare_head($object);
$titre=$langs->trans("CardProduct".$object->type);

dol_fiche_head($head, 'card', $titre, -1, 'service');

$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1&type='.$object->type.'">'.$langs->trans("BackToList").'</a>';
$object->next_prev_filter=" fk_product_type = ".$object->type;

$shownav = 1;
if ($user->societe_id && ! in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav=0;

dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');
?>

<?php dol_htmloutput_errors($object->error, $object->errors); ?>

<table class="border allwidth">

<tr>
<td width="15%"><?php echo $langs->trans("Ref"); ?></td>
<td colspan="2"><?php echo $object->ref; ?></td>
</tr>

<tr>
<td><?php echo $langs->trans("Label") ?></td>
<td><?php echo $object->label; ?></td>

<?php if ($object->photos) { ?>
<td valign="middle" align="center" width="30%" rowspan="<?php echo $object->nblignes; ?>">
<?php echo $object->photos; ?>
</td>
<?php } ?>

</tr>

<tr>
<td class="tdtop"><?php echo $langs->trans("Description"); ?></td>
<td colspan="2"><?php echo $object->description; ?></td>
</tr>

<tr><td><?php echo $langs->trans("Duration"); ?></td>
<td><?php echo $object->duration_value; ?>&nbsp;
<?php echo $object->duration_unit; ?>&nbsp;
</td></tr>

<tr>
<td class="tdtop"><?php echo $langs->trans("Note"); ?></td>
<td colspan="2"><?php echo $object->note; ?></td>
</tr>

</table>

<!-- END PHP TEMPLATE -->