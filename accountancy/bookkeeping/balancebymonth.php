<?php

require '../../main.inc.php';

require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("bills","compta","accountancy","other"));

// Filter
$year = GETPOST("year", 'int');
if ($year == 0) {
	$year_current = strftime("%Y", time());
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}


/*
 * View
 */

llxHeader('', $langs->trans("Bookkeeping"));

$textprevyear = '<a href="' . $_SERVER["PHP_SELF"] . '?year=' . ($year_current - 1) . '">' . img_previous() . '</a>';
$textnextyear = '&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?year=' . ($year_current + 1) . '">' . img_next() . '</a>';

print load_fiche_titre($langs->trans("AccountBalanceByMonth") . ' ' . $textprevyear . ' ' . $langs->trans("Year") . ' ' . $year_start . ' ' . $textnextyear);

$sql = "SELECT count(*) FROM " . MAIN_DB_PREFIX . "facturedet as fd";
$sql .= " , " . MAIN_DB_PREFIX . "facture as f";
$sql .= " WHERE fd.fk_code_ventilation = 0";
$sql .= " AND f.rowid = fd.fk_facture AND f.fk_statut = 1;";

dol_syslog('accountancy/bookkeeping/balancebymonth.php:: $sql=' . $sql);
$result = $db->query($sql);
if ($result) {
	$row = $db->fetch_row($result);
	$nbfac = $row[0];

	$db->free($result);
}

$y = $year_current;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width=150>' . $langs->trans("Label") . '</td>';
for($i = 1; $i <= 12; $i++)
{
	print '<td class="right">' . $langs->trans("MonthShort".sprintf("%02s", $i)) . '</td>';
}
print '<td class="center"><strong>'.$langs->trans("Total").'</strong></td>';
print '</tr>';

$sql = "SELECT bk.numero_compte AS 'compte',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=1,bk.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=2,bk.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=3,bk.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=4,bk.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=5,bk.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=6,bk.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=7,bk.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=8,bk.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=9,bk.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=10,bk.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=11,bk.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(bk.doc_date)=12,bk.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(bk.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "accounting_bookkeeping as bk";
$sql .= " WHERE bk.doc_date >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND bk.doc_date <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " GROUP BY bk.numero_compte";

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ( $i < $num ) {

		$row = $db->fetch_row($resql);

		print '<tr class="oddeven"><td width="14%">' . length_accountg($row[0]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[1]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[2]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[3]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[4]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[5]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[6]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[7]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[8]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[9]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[10]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[11]) . '</td>';
		print '<td class="right" width="6.5%">' . price($row[12]) . '</td>';
		print '<td class="right" width="8%"><strong>' . price($row[13]) . '</strong></td>';
		print '</tr>';

		$i ++;
	}
	$db->free($resql);
} else {
	print $db->lasterror();
}
print "</table>\n";

// End of page
llxFooter();
$db->close();
