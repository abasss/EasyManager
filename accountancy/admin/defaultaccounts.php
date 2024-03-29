<?php

require '../../main.inc.php';

// Class
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formaccounting.class.php';

// Load translation files required by the page
$langs->loadLangs(array("compta","bills","admin","accountancy","salaries","loan"));

// Security check
if (empty($user->rights->accounting->chartofaccount))
{
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');


$list_account_main = array (
    'ACCOUNTING_ACCOUNT_CUSTOMER',
    'ACCOUNTING_ACCOUNT_SUPPLIER',
    'SALARIES_ACCOUNTING_ACCOUNT_PAYMENT',
);

$list_account = array (
    'ACCOUNTING_PRODUCT_BUY_ACCOUNT',
    'ACCOUNTING_PRODUCT_SOLD_ACCOUNT',
    'ACCOUNTING_PRODUCT_SOLD_INTRA_ACCOUNT',
    'ACCOUNTING_PRODUCT_SOLD_EXPORT_ACCOUNT',
    'ACCOUNTING_SERVICE_BUY_ACCOUNT',
    'ACCOUNTING_SERVICE_SOLD_ACCOUNT',
    'ACCOUNTING_VAT_BUY_ACCOUNT',
    'ACCOUNTING_VAT_SOLD_ACCOUNT',
    'ACCOUNTING_VAT_PAY_ACCOUNT',
    'ACCOUNTING_ACCOUNT_SUSPENSE',
    'ACCOUNTING_ACCOUNT_TRANSFER_CASH',
    'DONATION_ACCOUNTINGACCOUNT',
    'ADHERENT_SUBSCRIPTION_ACCOUNTINGACCOUNT',
    'LOAN_ACCOUNTING_ACCOUNT_CAPITAL',
    'LOAN_ACCOUNTING_ACCOUNT_INTEREST',
    'LOAN_ACCOUNTING_ACCOUNT_INSURANCE'
);


/*
 * Actions
 */

$accounting_mode = empty($conf->global->ACCOUNTING_MODE) ? 'RECETTES-DEPENSES' : $conf->global->ACCOUNTING_MODE;

if (GETPOST('change_chart', 'alpha'))
{
    $chartofaccounts = GETPOST('chartofaccounts', 'int');

    if (! empty($chartofaccounts)) {

        if (! mounir_set_const($db, 'CHARTOFACCOUNTS', $chartofaccounts, 'chaine', 0, '', $conf->entity)) {
            $error ++;
        }
    } else {
        $error ++;
    }
}

if ($action == 'update') {
	$error = 0;

	foreach ($list_account_main as $constname) {
		$constvalue = GETPOST($constname, 'alpha');

		if (! mounir_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
			$error ++;
		}
	}

	foreach ($list_account as $constname) {
	    $constvalue = GETPOST($constname, 'alpha');

	    if (! mounir_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
	        $error ++;
	    }
	}

	if (! $error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}


/*
 * View
 */

$form = new Form($db);
$formaccounting = new FormAccounting($db);

llxHeader();

$linkback = '';
print load_fiche_titre($langs->trans('MenuDefaultAccounts'), $linkback, 'title_accountancy');

print '<span class="opacitymedium">'.$langs->trans("DefaultBindingDesc").'</span><br>';
print '<br>';

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';


// Define main accounts for thirdparty

print '<table class="noborder" width="100%">';

foreach ($list_account_main as $key) {

    print '<tr class="oddeven value">';
    // Param
    $label = $langs->trans($key);
    $keydesc=$key.'_Desc';

    $htmltext = $langs->trans($keydesc);
    print '<td class="fieldrequired" width="50%">';
    print $form->textwithpicto($label, $htmltext);
    print '</td>';
    // Value
    print '<td>';  // Do not force class=right, or it align also the content of the select box
    print $formaccounting->select_account($conf->global->$key, $key, 1, '', 1, 1);
    print '</td>';
    print '</tr>';
}


print "</table>\n";


print '<br>';

// Define default accounts

print '<table class="noborder" width="100%">';

foreach ($list_account as $key) {

	print '<tr class="oddeven value">';
	// Param
	$label = $langs->trans($key);
	print '<td width="50%">' . $label . '</td>';
	// Value
	print '<td>';  // Do not force class=right, or it align also the content of the select box
	print $formaccounting->select_account($conf->global->$key, $key, 1, '', 1, 1);
	print '</td>';
	print '</tr>';
}


print "</table>\n";


print '<div class="center"><input type="submit" class="button" value="' . $langs->trans('Modify') . '" name="button"></div>';

print '</form>';

// End of page
llxFooter();
$db->close();
