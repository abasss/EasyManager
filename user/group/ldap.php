<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/ldap.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ldap.lib.php';

// Load translation files required by page
$langs->loadLangs(array('companies', 'ldap', 'users', 'admin'));

// Users/Groups management only in master entity if transverse mode
if (! empty($conf->multicompany->enabled) && $conf->entity > 1 && $conf->global->MULTICOMPANY_TRANSVERSE_MODE)
{
	accessforbidden();
}

$canreadperms=true;
if (! empty($conf->global->MAIN_USE_ADVANCED_PERMS))
{
	$canreadperms=($user->admin || $user->rights->user->group_advance->read);
}

$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

$socid=0;
if ($user->societe_id > 0) $socid = $user->societe_id;

$object = new Usergroup($db);
$object->fetch($id);
$object->getrights();


/*
 * Actions
 */

if ($action == 'mounir2ldap')
{
	$ldap=new Ldap();
	$result=$ldap->connect_bind();

	if ($result > 0)
	{
		$info=$object->_load_ldap_info();

		// Get a gid number for objectclass PosixGroup
		if (in_array('posixGroup', $info['objectclass'])) {
			$info['gidNumber'] = $ldap->getNextGroupGid('LDAP_KEY_GROUPS');
		}

		$dn=$object->_load_ldap_dn($info);
		$olddn=$dn;	// We can say that old dn = dn as we force synchro

		$result=$ldap->update($dn, $info, $user, $olddn);
	}

	if ($result >= 0)
	{
		setEventMessages($langs->trans("GroupSynchronized"), null, 'mesgs');
	}
	else
	{
		setEventMessages($ldap->error, $ldap->errors, 'errors');
	}
}


/*
 *	View
 */

$form = new Form($db);

llxHeader();

$head = group_prepare_head($object);

dol_fiche_head($head, 'ldap', $langs->trans("Group"), -1, 'group');

$linkback = '<a href="'.DOL_URL_ROOT.'/user/group/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

dol_banner_tab($object, 'id', $linkback, $user->rights->user->user->lire || $user->admin);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

print '<table class="border centpercent">';

// Name (already in dol_banner, we keep it to have the GlobalGroup picto, but we should move it in dol_banner)
if (! empty($conf->mutlicompany->enabled))
{
    print '<tr><td class="titlefield">'.$langs->trans("Name").'</td>';
    print '<td class="valeur">'.$object->name;
    if (!$object->entity)
    {
    	print img_picto($langs->trans("GlobalGroup"), 'redstar');
    }
    print "</td></tr>\n";
}

// Note
print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
print '<td class="valeur">'.dol_htmlentitiesbr($object->note).'</td>';
print "</tr>\n";

// LDAP DN
print '<tr><td>LDAP '.$langs->trans("LDAPGroupDn").'</td><td class="valeur">'.$conf->global->LDAP_GROUP_DN."</td></tr>\n";

// LDAP Cle
print '<tr><td>LDAP '.$langs->trans("LDAPNamingAttribute").'</td><td class="valeur">'.$conf->global->LDAP_KEY_GROUPS."</td></tr>\n";

// LDAP Server
print '<tr><td>LDAP '.$langs->trans("LDAPPrimaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPSecondaryServer").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_HOST_SLAVE."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPServerPort").'</td><td class="valeur">'.$conf->global->LDAP_SERVER_PORT."</td></tr>\n";

print "</table>\n";

print '</div>';

dol_fiche_end();


/*
 * Barre d'actions
 */

print '<div class="tabsAction">';

if ($conf->global->LDAP_SYNCHRO_ACTIVE == 'mounir2ldap')
{
	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=mounir2ldap">'.$langs->trans("ForceSynchronize").'</a>';
}

print "</div>\n";

if ($conf->global->LDAP_SYNCHRO_ACTIVE == 'mounir2ldap') print "<br>\n";



// Affichage attributs LDAP
print load_fiche_titre($langs->trans("LDAPInformationsForThisGroup"));

print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LDAPAttributes").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Lecture LDAP
$ldap=new Ldap();
$result=$ldap->connect_bind();
if ($result > 0)
{
	$info=$object->_load_ldap_info();
	$dn=$object->_load_ldap_dn($info, 1);
	$search = "(".$object->_load_ldap_dn($info, 2).")";
	$records = $ldap->getAttribute($dn, $search);

	//var_dump($records);

	// Affichage arbre
    if ((! is_numeric($records) || $records != 0) && (! isset($records['count']) || $records['count'] > 0))
	{
		if (! is_array($records))
		{
			print '<tr '.$bc[false].'><td colspan="2"><font class="error">'.$langs->trans("ErrorFailedToReadLDAP").'</font></td></tr>';
		}
		else
		{
			$result=show_ldap_content($records, 0, $records['count'], true);
		}
	}
	else
	{
		print '<tr '.$bc[false].'><td colspan="2">'.$langs->trans("LDAPRecordNotFound").' (dn='.$dn.' - search='.$search.')</td></tr>';
	}
	$ldap->unbind();
	$ldap->close();
}
else
{
	setEventMessages($ldap->error, $ldap->errors, 'errors');
}

print '</table>';

// End of page
llxFooter();
$db->close();
