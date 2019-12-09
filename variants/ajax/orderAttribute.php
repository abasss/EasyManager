<?php



if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Disable token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN', '1');

require '../../main.inc.php';


/*
 * View
 */

top_httphead();

// Registering the location of boxes
if (isset($_POST['roworder'])) {
	$roworder=GETPOST('roworder', 'alpha', 2);

	dol_syslog("AjaxOrderAttribute roworder=".$roworder, LOG_DEBUG);

	$rowordertab = explode(',', $roworder);

	foreach ($rowordertab as $value) {
		if (!empty($value)) {
			$newrowordertab[] = $value;
		}
	}

	require DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';

	ProductAttribute::bulkUpdateOrder($db, $newrowordertab);
}
