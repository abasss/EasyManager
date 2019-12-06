<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies'));

$search_project_user = GETPOST('search_project_user', 'int');
$mine = GETPOST('mode', 'aZ09')=='mine' ? 1 : 0;
if ($search_project_user == $user->id) $mine = 1;

// Security check
$socid=0;
//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
if (!$user->rights->projet->lire) accessforbidden();

$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');

$max=3;


/*
 * View
 */

$companystatic=new Societe($db);
$projectstatic=new Project($db);
$form=new Form($db);
$formfile=new FormFile($db);

$projectset = ($mine?$mine:(empty($user->rights->projet->all->lire)?0:2));
$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user, $projectset, 1);
//var_dump($projectsListId);

llxHeader("", $langs->trans("Projects"), "EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos");

$title=$langs->trans("ProjectsArea");
//if ($mine) $title=$langs->trans("MyProjectsArea");


// Title for combo list see all projects
$titleall=$langs->trans("AllAllowedProjects");
if (! empty($user->rights->projet->all->lire) && ! $socid) $titleall=$langs->trans("AllProjects");
else $titleall=$langs->trans("AllAllowedProjects").'<br><br>';

$morehtml='';
//abasssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss
// $morehtml.='<form name="projectform" method="POST">';
// $morehtml.='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
// $morehtml.='<SELECT name="search_project_user">';
// $morehtml.='<option name="all" value="0"'.($mine?'':' selected').'>'.$titleall.'</option>';
// $morehtml.='<option name="mine" value="'.$user->id.'"'.(($search_project_user == $user->id)?' selected':'').'>'.$langs->trans("ProjectsImContactFor").'</option>';
// $morehtml.='</SELECT>';
// $morehtml.='<input type="submit" class="button" name="refresh" value="'.$langs->trans("Refresh").'">';
// $morehtml.='</form>';
//abasssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss
print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', '', '', '', 0, -1, 'title_project.png', 0, $morehtml);

// Show description of content
print '<div class="opacitymedium">';
if ($mine) print $langs->trans("MyProjectsDesc").'<br><br>';
else
{
	if (! empty($user->rights->projet->all->lire) && ! $socid) print $langs->trans("ProjectsDesc").'<br><br>';
	else print $langs->trans("ProjectsPublicDesc").'<br><br>';
}
print '</div>';

// Get list of ponderated percent for each status
$listofoppstatus=array(); $listofopplabel=array(); $listofoppcode=array();
$sql = "SELECT cls.rowid, cls.code, cls.percent, cls.label";
$sql.= " FROM ".MAIN_DB_PREFIX."c_lead_status as cls";
$sql.= " WHERE active=1";
$resql = $db->query($sql);
if ( $resql )
{
	$num = $db->num_rows($resql);
	$i = 0;

	while ($i < $num)
	{
		$objp = $db->fetch_object($resql);
		$listofoppstatus[$objp->rowid]=$objp->percent;
		$listofopplabel[$objp->rowid]=$objp->label;
		$listofoppcode[$objp->rowid]=$objp->code;
		$i++;
	}
}
else dol_print_error($db);



print '<div class="fichecenter"><div class="fichethirdleft">';


if (! empty($conf->global->MAIN_SEARCH_FORM_ON_HOME_AREAS))     // This is useless due to the global search combo
{
    // Search project
    if (! empty($conf->projet->enabled) && $user->rights->projet->lire)
    {
    	$listofsearchfields['search_project']=array('text'=>'Project');
    }

    if (count($listofsearchfields))
    {
    	print '<form method="post" action="'.DOL_URL_ROOT.'/core/search.php">';
    	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    	print '<table class="noborder nohover centpercent">';
    	$i=0;
    	foreach($listofsearchfields as $key => $value)
    	{
    		if ($i == 0) print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("Search").'</td></tr>';
    		print '<tr '.$bc[false].'>';
    		print '<td class="nowrap"><label for="'.$key.'">'.$langs->trans($value["text"]).'</label></td><td><input type="text" class="flat inputsearch" name="'.$key.'" id="'.$key.'" size="18"></td>';
    		if ($i == 0) print '<td rowspan="'.count($listofsearchfields).'"><input type="submit" value="'.$langs->trans("Search").'" class="button"></td>';
    		print '</tr>';
    		$i++;
    	}
    	print '</table>';
    	print '</form>';
    	print '<br>';
    }
}


/*
 * Statistics
 */
//include DOL_DOCUMENT_ROOT.'/projet/graph_opportunities.inc.php';

// End of page
llxFooter();
$db->close();
