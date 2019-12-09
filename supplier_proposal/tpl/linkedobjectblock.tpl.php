<?php

// Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

?>

<!-- BEGIN PHP TEMPLATE LINKEDOBJECTBOCK-->

<?php

global $user;

$langs = $GLOBALS['langs'];
$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];

$total=0; $ilink=0;
foreach($linkedObjectBlock as $key => $objectlink)
{
    $ilink++;

    $trclass='oddeven';
    if ($ilink == count($linkedObjectBlock) && empty($noMoreLinkedObjectBlockAfter) && count($linkedObjectBlock) <= 1) $trclass.=' liste_sub_total';
?>
    <tr class="<?php echo $trclass; ?>">
    	<td><?php echo $langs->trans("SupplierProposal"); ?></td>
    	<td><a href="<?php echo DOL_URL_ROOT.'/supplier_proposal/card.php?id='.$objectlink->id ?>"><?php echo img_object($langs->trans("ShowSupplierProposal"), "supplier_proposal").' '.$objectlink->ref; ?></a></td>
    	<td></td>
    	<td class="center"><?php echo dol_print_date($objectlink->datec, 'day'); ?></td>
    	<td class="right"><?php
    		if ($user->rights->supplier_proposal->lire) {
    			$total = $total + $objectlink->total_ht;
    			echo price($objectlink->total_ht);
    		} ?></td>
    	<td class="right"><?php echo $objectlink->getLibStatut(3); ?></td>
    	<td class="right"><a href="<?php echo $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=dellink&dellinkid='.$key; ?>"><?php echo img_picto($langs->transnoentitiesnoconv("RemoveLink"), 'unlink'); ?></a></td>
    </tr>
<?php
}
if (count($linkedObjectBlock) > 1)
{
    ?>
    <tr class="liste_total <?php echo (empty($noMoreLinkedObjectBlockAfter)?'liste_sub_total':''); ?>">
        <td><?php echo $langs->trans("Total"); ?></td>
        <td></td>
    	<td class="center"></td>
    	<td class="center"></td>
    	<td class="right"><?php echo price($total); ?></td>
    	<td class="right"></td>
    	<td class="right"></td>
    </tr>
    <?php
}
?>

<!-- END PHP TEMPLATE -->
