<?php

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');

require '../main.inc.php';

$country=GETPOST('country', 'alpha');


/*
 * View
 */

// Ajout directives pour resoudre bug IE
//header('Cache-Control: Public, must-revalidate');
//header('Pragma: public');

//top_htmlhead("", "", 1);  // Replaced with top_httphead. An ajax page does not need html header.
top_httphead();

print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

dol_syslog(join(',', $_POST));

// Generate list of countries
if (! empty($country))
{
	global $langs;
	$langs->load("dict");

	$sql = "SELECT rowid, code, label, active";
	$sql.= " FROM ".MAIN_DB_PREFIX."c_country";
	$sql.= " WHERE active = 1 AND label LIKE '%" . $db->escape(utf8_decode($country)) . "%'";
	$sql.= " ORDER BY label ASC";

	$resql=$db->query($sql);
	if ($resql)
	{
		print '<ul>';
		while($country = $db->fetch_object($resql))
		{
			print '<li>';
			// Si traduction existe, on l'utilise, sinon on prend le libellé par défaut
			print ($country->code && $langs->trans("Country".$country->code)!="Country".$country->code?$langs->trans("Country".$country->code):($country->label!='-'?$country->label:'&nbsp;'));
			print '<span class="informal" style="display:none">'.$country->rowid.'-idcache</span>';
			print '</li>';
		}
		print '</ul>';
	}
}
