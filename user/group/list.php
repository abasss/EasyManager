<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

if (! empty($conf->global->MAIN_USE_ADVANCED_PERMS))
{
	if (! $user->rights->user->group_advance->read && ! $user->admin)
		accessforbidden();
}

// Users/Groups management only in master entity if transverse mode
if (! empty($conf->multicompany->enabled) && $conf->entity > 1 && $conf->global->MULTICOMPANY_TRANSVERSE_MODE)
{
	accessforbidden();
}

// Load translation files required by page
$langs->load("users");

$sall=trim((GETPOST('search_all', 'alphanohtml')!='')?GETPOST('search_all', 'alphanohtml'):GETPOST('sall', 'alphanohtml'));
$search_group=GETPOST('search_group');
$optioncss = GETPOST('optioncss', 'alpha');

// Defini si peux lire/modifier utilisateurs et permisssions
$caneditperms=($user->admin || $user->rights->user->user->creer);
// Advanced permissions
if (! empty($conf->global->MAIN_USE_ADVANCED_PERMS))
{
	$caneditperms=($user->admin || $user->rights->user->group_advance->write);
}

// Load variable for pagination
$limit = GETPOST('limit', 'int')?GETPOST('limit', 'int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
if (empty($page) || $page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (! $sortfield) $sortfield="g.nom";
if (! $sortorder) $sortorder="ASC";

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
    'g.nom'=>"Group",
    'g.note'=>"Note"
);


/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend' && $massaction != 'confirm_createbills') { $massaction=''; }

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    // Selection of new fields
    include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

    // Purge search criteria
    if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') ||GETPOST('button_removefilter', 'alpha')) // All test are required to be compatible with all browsers
    {
        $search_label="";
        $search_date_creation="";
        $search_date_update="";
        $search_array_options=array();
    }
}



/*
 * View
 */

llxHeader();

$sql = "SELECT g.rowid, g.nom as name, g.note, g.entity, g.datec, COUNT(DISTINCT ugu.fk_user) as nb, COUNT(DISTINCT ugr.fk_id) as nbpermissions";
$sql.= " FROM ".MAIN_DB_PREFIX."usergroup as g";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ugu ON ugu.fk_usergroup = g.rowid";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup_rights as ugr ON ugr.fk_usergroup = g.rowid";
if (! empty($conf->multicompany->enabled) && $conf->entity == 1 && ($conf->global->MULTICOMPANY_TRANSVERSE_MODE || ($user->admin && ! $user->entity)))
{
	$sql.= " WHERE g.entity IS NOT NULL";
}
else
{
	$sql.= " WHERE g.entity IN (0,".$conf->entity.")";
}
if (! empty($search_group)) natural_search(array("g.nom", "g.note"), $search_group);
if ($sall) $sql.= natural_search(array("g.nom", "g.note"), $sall);
$sql.= " GROUP BY g.rowid, g.nom, g.note, g.entity, g.datec";
$sql.= $db->order($sortfield, $sortorder);

$resql = $db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);

    $nbtotalofrecords = $num;

    $i = 0;

    $param="&amp;search_group=".urlencode($search_group)."&amp;sall=".urlencode($sall);
    if ($optioncss != '') $param.='&amp;optioncss='.$optioncss;

    $text = $langs->trans("ListOfGroups");

    $newcardbutton='';
    if ($caneditperms)
    {
        $newcardbutton.= dolGetButtonTitle($langs->trans('NewGroup'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/user/group/card.php?action=create&leftmenu=');
    }

    print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
    print '<input type="hidden" name="page" value="'.$page.'">';
    print '<input type="hidden" name="mode" value="'.$mode.'">';
    print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

    print_barre_liste($text, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, "", $num, $nbtotalofrecords, 'title_generic', 0, $newcardbutton, '', $limit);

    if ($sall)
    {
        foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
        print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $sall) . join(', ', $fieldstosearchall).'</div>';
    }

    $moreforfilter='';

	//$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	//$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";

    print '<tr class="liste_titre">';
    print_liste_field_titre("Group", $_SERVER["PHP_SELF"], "g.nom", $param, "", "", $sortfield, $sortorder);
    //multicompany
    if(! empty($conf->multicompany->enabled) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1)
    {
    	print_liste_field_titre("Entity", $_SERVER["PHP_SELF"], "g.entity", $param, "", '', $sortfield, $sortorder, 'center ');
    }
    print_liste_field_titre("NbOfUsers", $_SERVER["PHP_SELF"], "nb", $param, "", '', $sortfield, $sortorder, 'center ');
    print_liste_field_titre("NbOfPermissions", $_SERVER["PHP_SELF"], "nbpermissions", $param, "", '', $sortfield, $sortorder, 'center ');
    print_liste_field_titre("DateCreationShort", $_SERVER["PHP_SELF"], "g.datec", $param, "", '', $sortfield, $sortorder, 'center ');
    print_liste_field_titre("", $_SERVER["PHP_SELF"]);
    print "</tr>\n";

    $grouptemp = new UserGroup($db);

    while ($i < $num)
    {
        $obj = $db->fetch_object($resql);

        $grouptemp->id = $obj->rowid;
        $grouptemp->name = $obj->name;
        $grouptemp->note = $obj->note;

        print '<tr class="oddeven">';
        print '<td>';
        print $grouptemp->getNomUrl(1);
        if (! $obj->entity)
        {
        	print img_picto($langs->trans("GlobalGroup"), 'redstar');
        }
        print "</td>";
        //multicompany
        if (! empty($conf->multicompany->enabled) && is_object($mc) && empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE) && $conf->entity == 1)
        {
            $mc->getInfo($obj->entity);
            print '<td class="center">'.$mc->label.'</td>';
        }
        print '<td class="center">'.$obj->nb.'</td>';
        print '<td class="center">'.$obj->nbpermissions.'</td>';
        print '<td class="center nowrap">'.dol_print_date($db->jdate($obj->datec), "dayhour").'</td>';
        print '<td></td>';
        print "</tr>\n";
        $i++;
    }
    print "</table>";

    print '</div>';
    print "</form>\n";

    $db->free($resql);
}
else
{
    dol_print_error($db);
}

// End of page
llxFooter();
$db->close();
