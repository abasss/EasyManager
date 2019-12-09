<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');
if (empty($_GET['keysearch']) && ! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');

require '../../main.inc.php';

$htmlname=GETPOST('htmlname', 'alpha');
$socid=GETPOST('socid', 'int');
$action=GETPOST('action', 'alpha');
$id=GETPOST('id', 'int');
$discard_closed =GETPOST('discardclosed', 'int');


/*
 * View
 */

dol_syslog(join(',', $_GET));

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

// Load translation files required by the page
$langs->load("main");

top_httphead();

if (empty($htmlname)) return;

$match = preg_grep('/('.$htmlname.'[0-9]+)/', array_keys($_GET));
sort($match);
$idprod = (! empty($match[0]) ? $match[0] : '');

if (! GETPOST($htmlname) && ! GETPOST($idprod)) return;

// When used from jQuery, the search term is added as GET param "term".
$searchkey=((!empty($idprod) && GETPOST($idprod))?GETPOST($idprod):(GETPOST($htmlname)?GETPOST($htmlname):''));

$form = new FormProjets($db);
$arrayresult=$form->select_projects_list($socid, '', $htmlname, 0, 0, 1, $discard_closed, 0, 0, 1, $searchkey);

$db->close();

print json_encode($arrayresult);
