<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');

require '../../main.inc.php';

$htmlname=GETPOST('htmlname', 'alpha');
$filter=GETPOST('filter', 'alpha');
$outjson=(GETPOST('outjson', 'int') ? GETPOST('outjson', 'int') : 0);
$action=GETPOST('action', 'alpha');
$id=GETPOST('id', 'int');
$showtype=GETPOST('showtype', 'int');


/*
 * View
 */

//print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

dol_syslog(join(',', $_GET));
//print_r($_GET);

if (! empty($action) && $action == 'fetch' && ! empty($id))
{
	require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

	$outjson=array();

	$object = new Societe($db);
	$ret=$object->fetch($id);
	if ($ret > 0)
	{
		$outname=$object->name;
		$outlabel = '';
		$outdesc = '';
		$outtype = $object->type;

		$outjson = array('ref' => $outref,'name' => $outname,'desc' => $outdesc,'type' => $outtype);
	}

	echo json_encode($outjson);
}
else
{
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

	$langs->load("companies");

	top_httphead();

	if (empty($htmlname)) return;

	$match = preg_grep('/('.$htmlname.'[0-9]+)/', array_keys($_GET));
	sort($match);
	$id = (! empty($match[0]) ? $match[0] : '');

	// When used from jQuery, the search term is added as GET param "term".
	$searchkey=(($id && GETPOST($id, 'alpha'))?GETPOST($id, 'alpha'):(($htmlname && GETPOST($htmlname, 'alpha'))?GETPOST($htmlname, 'alpha'):''));

	if (! $searchkey) return;

	if (! is_object($form)) $form = new Form($db);
	$arrayresult=$form->select_thirdparty_list(0, $htmlname, $filter, 1, $showtype, 0, null, $searchkey, $outjson);

	$db->close();

	if ($outjson) print json_encode($arrayresult);
}
