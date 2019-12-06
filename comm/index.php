<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
if (! empty($conf->contrat->enabled)) require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
if (! empty($conf->propal->enabled))  require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
if (! empty($conf->supplier_proposal->enabled))  require_once DOL_DOCUMENT_ROOT.'/supplier_proposal/class/supplier_proposal.class.php';
if (! empty($conf->commande->enabled))  require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (! empty($conf->fournisseur->enabled)) require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';

if (! $user->rights->societe->lire) accessforbidden();

// Load translation files required by the page
$langs->loadLangs(array("commercial", "propal"));

$action=GETPOST('action', 'alpha');
$bid=GETPOST('bid', 'int');

// Securite acces client
$socid=GETPOST('socid', 'int');
if (isset($user->societe_id) && $user->societe_id > 0)
{
	$action = '';
	$socid = $user->societe_id;
}

$max=3;
$now=dol_now();

/*
 * Actions
 */


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$companystatic=new Societe($db);
if (! empty($conf->propal->enabled)) $propalstatic=new Propal($db);
if (! empty($conf->supplier_proposal->enabled)) $supplierproposalstatic=new SupplierProposal($db);
if (! empty($conf->commande->enabled)) $orderstatic=new Commande($db);
if (! empty($conf->fournisseur->enabled)) $supplierorderstatic=new CommandeFournisseur($db);

llxHeader("", $langs->trans("CommercialArea"));

print load_fiche_titre($langs->trans("CommercialArea"), '', 'title_commercial.png');

print '<div class="fichecenter"><div class="fichethirdleft">';

if (! empty($conf->global->MAIN_SEARCH_FORM_ON_HOME_AREAS))     // This is useless due to the global search combo
{
    // Search proposal
    if (! empty($conf->propal->enabled) && $user->rights->propal->lire)
    {
    	$listofsearchfields['search_proposal']=array('text'=>'Proposal');
    }
    // Search customer order
    if (! empty($conf->commande->enabled) && $user->rights->commande->lire)
    {
    	$listofsearchfields['search_customer_order']=array('text'=>'CustomerOrder');
    }
    // Search supplier proposal
    if (! empty($conf->supplier_proposal->enabled) && $user->rights->supplier_proposal->lire)
    {
        $listofsearchfields['search_supplier_proposal']=array('text'=>'SupplierProposalShort');
    }
    // Search supplier order
    if (! empty($conf->fournisseur->enabled) && $user->rights->fournisseur->commande->lire)
    {
    	$listofsearchfields['search_supplier_order']=array('text'=>'SupplierOrder');
    }
    // Search intervention
    if (! empty($conf->ficheinter->enabled) && $user->rights->ficheinter->lire)
    {
    	$listofsearchfields['search_intervention']=array('text'=>'Intervention');
    }
    // Search contract
    if (! empty($conf->contrat->enabled) && $user->rights->contrat->lire)
    {
        $listofsearchfields['search_contract']=array('text'=>'Contract');
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
    		if ($i == 0) print '<td class="noborderbottom" rowspan="'.count($listofsearchfields).'"><input type="submit" value="'.$langs->trans("Search").'" class="button "></td>';
    		print '</tr>';
    		$i++;
    	}
    	print '</table>';
    	print '</form>';
    	print '<br>';
    }
}


/*
 * Draft proposals
 */
// End of page
llxFooter();
$db->close();
