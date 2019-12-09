<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');

require '../main.inc.php';


/*
 * View
 */

// Ajout directives pour resoudre bug IE
//header('Cache-Control: Public, must-revalidate');
//header('Pragma: public');

//top_htmlhead("", "", 1);  // Replaced with top_httphead. An ajax page does not need html header.
top_httphead();

//print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

dol_syslog(join(',', $_GET));


// Generation liste des societes
if (GETPOST('newcompany') || GETPOST('socid', 'int') || GETPOST('id_fourn'))
{
	$return_arr = array();

	// Define filter on text typed
	$socid = $_GET['newcompany']?$_GET['newcompany']:'';
	if (! $socid) $socid = $_GET['socid']?$_GET['socid']:'';
	if (! $socid) $socid = $_GET['id_fourn']?$_GET['id_fourn']:'';

	$sql = "SELECT rowid, nom";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
	$sql.= " WHERE s.entity IN (".getEntity('societe').")";
	if ($socid)
	{
        $sql.=" AND (";
        // Add criteria on name/code
        if (! empty($conf->global->COMPANY_DONOTSEARCH_ANYWHERE))   // Can use index
        {
            $sql.="nom LIKE '" . $db->escape($socid) . "%'";
            $sql.=" OR code_client LIKE '" . $db->escape($socid) . "%'";
            $sql.=" OR code_fournisseur LIKE '" . $db->escape($socid) . "%'";
        }
        else
        {
    		$sql.="nom LIKE '%" . $db->escape($socid) . "%'";
    		$sql.=" OR code_client LIKE '%" . $db->escape($socid) . "%'";
    		$sql.=" OR code_fournisseur LIKE '%" . $db->escape($socid) . "%'";
        }
		if (! empty($conf->global->SOCIETE_ALLOW_SEARCH_ON_ROWID)) $sql.=" OR rowid = '" . $db->escape($socid) . "'";
		$sql.=")";
	}
	//if (GETPOST("filter")) $sql.= " AND (".GETPOST("filter", "alpha").")"; // Add other filters
	$sql.= " ORDER BY nom ASC";

	//dol_syslog("ajaxcompanies", LOG_DEBUG);
	$resql=$db->query($sql);
	if ($resql)
	{
		while ($row = $db->fetch_array($resql))
		{
		    $label=$row['nom'];
		    if ($socid) $label=preg_replace('/('.preg_quote($socid, '/').')/i', '<strong>$1</strong>', $label, 1);
			$row_array['label'] = $label;
			$row_array['value'] = $row['nom'];
	        $row_array['key'] = $row['rowid'];

	        array_push($return_arr, $row_array);
	    }

	    echo json_encode($return_arr);
	}
	else
	{
	    echo json_encode(array('nom'=>'Error','label'=>'Error','key'=>'Error','value'=>'Error'));
	}
}
else
{
    echo json_encode(array('nom'=>'ErrorBadParameter','label'=>'ErrorBadParameter','key'=>'ErrorBadParameter','value'=>'ErrorBadParameter'));
}
