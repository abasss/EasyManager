<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formaccounting.class.php';

// Load translation files required by the page
$langs->loadLangs(array("compta","admin","accountancy"));

// Security check
if (empty($user->rights->accounting->chartofaccount)) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');


$list_account_main = array (
    'ACCOUNTING_RESULT_PROFIT',
    'ACCOUNTING_RESULT_LOSS'
);

/*
 * Actions
 */

if ($action == 'update') {
    $error = 0;

    $defaultjournal = GETPOST('ACCOUNTING_CLOSURE_DEFAULT_JOURNAL', 'alpha');

    if (! empty($defaultjournal)) {
        if (! mounir_set_const($db, 'ACCOUNTING_CLOSURE_DEFAULT_JOURNAL', $defaultjournal, 'chaine', 0, '', $conf->entity)) {
            $error ++;
        }
    } else {
        $error ++;
    }

	foreach ($list_account_main as $constname) {
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
print load_fiche_titre($langs->trans('MenuClosureAccounts'), $linkback, 'title_accountancy');

print '<span class="opacitymedium">'.$langs->trans("DefaultClosureDesc").'</span><br>';
print '<br>';

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

// Define main accounts for closure
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

// Journal
print '<tr class="oddeven">';
print '<td width="50%">' . $langs->trans("ACCOUNTING_CLOSURE_DEFAULT_JOURNAL") . '</td>';
print '<td>';
$defaultjournal=$conf->global->ACCOUNTING_CLOSURE_DEFAULT_JOURNAL;
print $formaccounting->select_journal($defaultjournal, "ACCOUNTING_CLOSURE_DEFAULT_JOURNAL", 9, 1, 0, 0);
print '</td></tr>';

print "</table>\n";

print '<div class="center"><input type="submit" class="button" value="' . $langs->trans('Modify') . '" name="button"></div>';

print '</form>';

// End of page
llxFooter();
$db->close();
