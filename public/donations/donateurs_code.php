<?php

define("NOLOGIN", 1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.

// C'est un wrapper, donc header vierge
/**
 * Header function
 *
 * @return	void
 */
function llxHeaderVierge()
{
    print '<html><title>Export agenda cal</title><body>';
}
/**
 * Header function
 *
 * @return	void
 */
function llxFooterVierge()
{
    print '</body></html>';
}

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT .'/don/class/don.class.php';

// Security check
if (empty($conf->don->enabled)) accessforbidden('', 0, 0, 1);


$langs->load("donations");


/*
 * View
 */

llxHeaderVierge();

$sql = "SELECT d.datedon as datedon, d.lastname, d.firstname, d.amount, d.public, d.societe";
$sql.= " FROM ".MAIN_DB_PREFIX."don as d";
$sql.= " WHERE d.fk_statut in (2, 3) ORDER BY d.datedon DESC";

$resql=$db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
	if ($num)
	{

		print "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"4\">";

		print '<tr>';
		print "<td>".$langs->trans("Name")." / ".$langs->trans("Company")."</td>";
		print "<td>Date</td>";
		print '<td class="right">'.$langs->trans("Amount").'</td>';
		print "</tr>\n";

		while ($i < $num)
		{
			$objp = $db->fetch_object($resql);

			print '<tr class="oddeven">';
			if ($objp->public)
			{
				print "<td>".dolGetFirstLastname($objp->firstname, $objp->lastname)." ".$objp->societe."</td>\n";
			}
			else
			{
				print "<td>Anonyme Anonyme</td>\n";
			}
			print "<td>".dol_print_date($db->jdate($objp->datedon))."</td>\n";
			print '<td class="right">'.number_format($objp->amount, 2, '.', ' ').' '.$langs->trans("Currency".$conf->currency).'</td>';
			print "</tr>";
			$i++;
		}
		print "</table>";
	}
	else
	{
		print "Aucun don publique";
	}
}
else
{
	dol_print_error($db);
}

$db->close();

llxFooterVierge();
