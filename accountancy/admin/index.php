<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("compta","bills","admin","accountancy"));

// Security access
if (empty($user->rights->accounting->chartofaccount))
{
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

// Parameters ACCOUNTING_* and others
$list = array (
    'ACCOUNTING_LENGTH_GACCOUNT',
    'ACCOUNTING_LENGTH_AACCOUNT' ,
//    'ACCOUNTING_LENGTH_DESCRIPTION',         // adjust size displayed for lines description for dol_trunc
//    'ACCOUNTING_LENGTH_DESCRIPTION_ACCOUNT', // adjust size displayed for select account description for dol_trunc
);



/*
 * Actions
 */

$accounting_mode = empty($conf->global->ACCOUNTING_MODE) ? 'RECETTES-DEPENSES' : $conf->global->ACCOUNTING_MODE;

if ($action == 'update') {
	$error = 0;

	if (! $error)
	{
	    foreach ($list as $constname)
	    {
	        $constvalue = GETPOST($constname, 'alpha');

	        if (! mounir_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
	            $error++;
	        }
	    }
	    if ($error) {
	    	setEventMessages($langs->trans("Error"), null, 'errors');
	    }
	}

    if (! $error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }
}

// TO DO Mutualize code for yes/no constants
if ($action == 'setlistsorttodo') {
    $setlistsorttodo = GETPOST('value', 'int');
    $res = mounir_set_const($db, "ACCOUNTING_LIST_SORT_VENTILATION_TODO", $setlistsorttodo, 'yesno', 0, '', $conf->entity);
    if (! $res > 0)
        $error ++;

        if (! $error) {
            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("Error"), null, 'mesgs');
        }
}

if ($action == 'setlistsortdone') {
    $setlistsortdone = GETPOST('value', 'int');
    $res = mounir_set_const($db, "ACCOUNTING_LIST_SORT_VENTILATION_DONE", $setlistsortdone, 'yesno', 0, '', $conf->entity);
    if (! $res > 0)
        $error ++;
        if (! $error) {
            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("Error"), null, 'mesgs');
        }
}

if ($action == 'setmanagezero') {
    $setmanagezero = GETPOST('value', 'int');
    $res = mounir_set_const($db, "ACCOUNTING_MANAGE_ZERO", $setmanagezero, 'yesno', 0, '', $conf->entity);
    if (! $res > 0)
        $error ++;
        if (! $error) {
            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("Error"), null, 'mesgs');
        }
}

if ($action == 'setdisabledirectinput') {
	$setdisabledirectinput = GETPOST('value', 'int');
	$res = mounir_set_const($db, "BANK_DISABLE_DIRECT_INPUT", $setdisabledirectinput, 'yesno', 0, '', $conf->entity);
	if (! $res > 0)
		$error ++;
		if (! $error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("Error"), null, 'mesgs');
		}
}

if ($action == 'setenabledraftexport') {
	$setenabledraftexport = GETPOST('value', 'int');
	$res = mounir_set_const($db, "ACCOUNTING_ENABLE_EXPORT_DRAFT_JOURNAL", $setenabledraftexport, 'yesno', 0, '', $conf->entity);
	if (! $res > 0)
		$error ++;
		if (! $error) {
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		} else {
			setEventMessages($langs->trans("Error"), null, 'mesgs');
		}
}

if ($action == 'setenablesubsidiarylist') {
    $setenablesubsidiarylist = GETPOST('value', 'int');
    $res = mounir_set_const($db, "ACCOUNTANCY_COMBO_FOR_AUX", $setenablesubsidiarylist, 'yesno', 0, '', $conf->entity);
    if (! $res > 0)
        $error ++;
    if (! $error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'mesgs');
    }
}

/*
 * View
 */

llxHeader();

$form = new Form($db);

//$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans('ConfigAccountingExpert'), $linkback, 'title_setup');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

// Default mode for calculating turnover (parameter ACCOUNTING_MODE)
/*
print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans('OptionMode') . '</td><td>' . $langs->trans('Description') . '</td>';
print "</tr>\n";
print '<tr ' . $bc[false] . '><td width="200"><input type="radio" name="accounting_mode" value="RECETTES-DEPENSES"' . ($accounting_mode != 'CREANCES-DETTES' ? ' checked' : '') . '> ' . $langs->trans('OptionModeTrue') . '</td>';
print '<td colspan="2">' . nl2br($langs->trans('OptionModeTrueDesc'));
// Write info on way to count VAT
// if (! empty($conf->global->MAIN_MODULE_COMPTABILITE))
// {
// // print "<br>\n";
// // print nl2br($langs->trans('OptionModeTrueInfoModuleComptabilite'));
// }
// else
// {
// // print "<br>\n";
// // print nl2br($langs->trans('OptionModeTrueInfoExpert'));
// }
print "</td></tr>\n";
print '<tr ' . $bc[true] . '><td width="200"><input type="radio" name="accounting_mode" value="CREANCES-DETTES"' . ($accounting_mode == 'CREANCES-DETTES' ? ' checked' : '') . '> ' . $langs->trans('OptionModeVirtual') . '</td>';
print '<td colspan="2">' . nl2br($langs->trans('OptionModeVirtualDesc')) . "</td></tr>\n";

print "</table>\n";


print '<br>';
*/

// Others params

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="2">' . $langs->trans('OtherOptions') . '</td>';
print "</tr>\n";

if (! empty($user->admin))
{
    // TO DO Mutualize code for yes/no constants
    print '<tr class="oddeven">';
    print '<td>' . $langs->trans("ACCOUNTING_LIST_SORT_VENTILATION_TODO") . '</td>';
    if (! empty($conf->global->ACCOUNTING_LIST_SORT_VENTILATION_TODO)) {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setlistsorttodo&value=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setlistsorttodo&value=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td>' . $langs->trans("ACCOUNTING_LIST_SORT_VENTILATION_DONE") . '</td>';
    if (! empty($conf->global->ACCOUNTING_LIST_SORT_VENTILATION_DONE)) {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setlistsortdone&value=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setlistsortdone&value=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }
    print '</tr>';

	print '<tr class="oddeven">';
	print '<td>' . $langs->trans("ACCOUNTING_ENABLE_EXPORT_DRAFT_JOURNAL") . '</td>';
	if (! empty($conf->global->ACCOUNTING_ENABLE_EXPORT_DRAFT_JOURNAL)) {
		print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setenabledraftexport&value=0">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a></td>';
	} else {
		print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setenabledraftexport&value=1">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a></td>';
	}
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td>' . $langs->trans("BANK_DISABLE_DIRECT_INPUT") . '</td>';
	if (! empty($conf->global->BANK_DISABLE_DIRECT_INPUT)) {
		print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setdisabledirectinput&value=0">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a></td>';
	} else {
		print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setdisabledirectinput&value=1">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a></td>';
	}
	print '</tr>';

    print '<tr class="oddeven">';
    print '<td>' . $langs->trans("ACCOUNTANCY_COMBO_FOR_AUX") . '</td>';
    if (! empty($conf->global->ACCOUNTANCY_COMBO_FOR_AUX)) {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setenablesubsidiarylist&value=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setenablesubsidiarylist&value=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }
    print '</tr>';

    print '<tr class="oddeven">';
    print '<td>' . $langs->trans("ACCOUNTING_MANAGE_ZERO") . '</td>';
    if (! empty($conf->global->ACCOUNTING_MANAGE_ZERO)) {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setmanagezero&value=0">';
        print img_picto($langs->trans("Activated"), 'switch_on');
        print '</a></td>';
    } else {
        print '<td class="right"><a class="reposition" href="' . $_SERVER['PHP_SELF'] . '?action=setmanagezero&value=1">';
        print img_picto($langs->trans("Disabled"), 'switch_off');
        print '</a></td>';
    }
    print '</tr>';
}


// Param a user $user->rights->accounting->chartofaccount can access
foreach ($list as $key)
{
    print '<tr class="oddeven value">';

    if (! empty($conf->global->ACCOUNTING_MANAGE_ZERO) && ($key == 'ACCOUNTING_LENGTH_GACCOUNT' || $key == 'ACCOUNTING_LENGTH_AACCOUNT')) continue;

    // Param
    $label = $langs->trans($key);
    print '<td>'.$label.'</td>';
    // Value
    print '<td class="right">';
    print '<input type="text" class="maxwidth100" id="' . $key . '" name="' . $key . '" value="' . $conf->global->$key . '">';
    print '</td>';

    print '</tr>';
}


print '</table>';

print '<div class="center"><input type="submit" class="button" value="' . $langs->trans('Modify') . '" name="button"></div>';

print '<br>';
print '<br>';

print '<br>';
print '</form>';

// End of page
llxFooter();
$db->close();
