<?php

define('NOCSRFCHECK', 1);	// This is main home and login page. We must be able to go on it from another web site.

require 'main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

// If not defined, we select menu "home"
$_GET['mainmenu']=GETPOST('mainmenu', 'aZ09')?GETPOST('mainmenu', 'aZ09'):'home';
$action=GETPOST('action', 'aZ09');

$hookmanager->initHooks(array('index'));


/*
 * Actions
 */

// Check if company name is defined (first install)
if (!isset($conf->global->MAIN_INFO_SOCIETE_NOM) || empty($conf->global->MAIN_INFO_SOCIETE_NOM))
{
    header("Location: ".DOL_URL_ROOT."/admin/index.php?mainmenu=home&leftmenu=setup&mesg=setupnotcomplete");
    exit;
}
if (count($conf->modules) <= (empty($conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING)?1:$conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING))	// If only user module enabled
{
    header("Location: ".DOL_URL_ROOT."/admin/index.php?mainmenu=home&leftmenu=setup&mesg=setupnotcomplete");
    exit;
}
if (GETPOST('addbox'))	// Add box (when submit is done from a form when ajax disabled)
{
	require_once DOL_DOCUMENT_ROOT.'/core/class/infobox.class.php';
	$zone=GETPOST('areacode', 'aZ09');
	$userid=GETPOST('userid', 'int');
	$boxorder=GETPOST('boxorder', 'aZ09');
	$boxorder.=GETPOST('boxcombo', 'aZ09');

	$result=InfoBox::saveboxorder($db, $zone, $boxorder, $userid);
	if ($result > 0) setEventMessages($langs->trans("BoxAdded"), null);
}


/*
 * View
 */

if (! is_object($form)) $form=new Form($db);

// Title
$title=$langs->trans("HomeArea").' - ERP '.DOL_VERSION;
if (! empty($conf->global->MAIN_APPLICATION_TITLE)) $title=$langs->trans("HomeArea").' - '.$conf->global->MAIN_APPLICATION_TITLE;

llxHeader('', $title);


$resultboxes=FormOther::getBoxesArea($user, "0");    // Load $resultboxes (selectboxlist + boxactivated + boxlista + boxlistb)


print load_fiche_titre($langs->trans("HomeArea"), $resultboxes['selectboxlist'], 'title_home');

if (! empty($conf->global->MAIN_MOTD))
{
    $conf->global->MAIN_MOTD=preg_replace('/<br(\s[\sa-zA-Z_="]*)?\/?>/i', '<br>', $conf->global->MAIN_MOTD);
    if (! empty($conf->global->MAIN_MOTD))
    {
        $substitutionarray=getCommonSubstitutionArray($langs);
        complete_substitutions_array($substitutionarray, $langs);
        $texttoshow = make_substitutions($conf->global->MAIN_MOTD, $substitutionarray, $langs);

        print "\n<!-- Start of welcome text -->\n";
        print '<table width="100%" class="notopnoleftnoright"><tr><td>';
        print dol_htmlentitiesbr($texttoshow);
        print '</td></tr></table><br>';
        print "\n<!-- End of welcome text -->\n";
    }
}


print '<div class="fichecenter"><div class="fichethirdleft">';


/*
 * Dashboard ERP states (statistics)
 * Hidden for external users
 */
$boxstat='';

// Load translation files required by page
$langs->loadLangs(array('commercial', 'bills', 'orders', 'contracts'));

if (empty($user->societe_id))
{
    $boxstat.='<div class="box">';
    $boxstat.='<table summary="'.dol_escape_htmltag($langs->trans("MounirStateBoard")).'" class="noborder boxtable boxtablenobottom nohover" width="100%">';
    $boxstat.='<tr class="liste_titre">';
    $boxstat.='<th class="liste_titre">';
    $boxstat.='<div class="inline-block valignmiddle">'.$langs->trans("MounirStateBoard").'</div>';
    $boxstat.='</th>';
    $boxstat.='</tr>';
    $boxstat.='<tr class="nobottom nohover"><td class="tdboxstats nohover flexcontainer">';

    $object=new stdClass();
    $parameters=array();
    $action='';
    $reshook=$hookmanager->executeHooks('addStatisticLine', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
    $boxstat.=$hookmanager->resPrint;

    if (empty($reshook))
    {
	    // Condition to be checked for each display line dashboard
	    $conditions=array(
	    $user->rights->user->user->lire,
	    ! empty($conf->societe->enabled) && $user->rights->societe->lire && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS_STATS),
	    ! empty($conf->societe->enabled) && $user->rights->societe->lire && empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_PROSPECTS_STATS),
	    ! empty($conf->fournisseur->enabled) && $user->rights->fournisseur->lire && empty($conf->global->SOCIETE_DISABLE_SUPPLIERS_STATS),
	    ! empty($conf->societe->enabled) && $user->rights->societe->contact->lire,
	    ! empty($conf->adherent->enabled) && $user->rights->adherent->lire,
	    ! empty($conf->product->enabled) && $user->rights->produit->lire,
	    ! empty($conf->service->enabled) && $user->rights->service->lire,
	    ! empty($conf->propal->enabled) && $user->rights->propale->lire,
	    ! empty($conf->commande->enabled) && $user->rights->commande->lire,
	    ! empty($conf->facture->enabled) && $user->rights->facture->lire,
	    ! empty($conf->contrat->enabled) && $user->rights->contrat->lire,
	    ! empty($conf->ficheinter->enabled) && $user->rights->ficheinter->lire,
		! empty($conf->supplier_order->enabled) && $user->rights->fournisseur->commande->lire && empty($conf->global->SOCIETE_DISABLE_SUPPLIERS_ORDERS_STATS),
		! empty($conf->supplier_invoice->enabled) && $user->rights->fournisseur->facture->lire && empty($conf->global->SOCIETE_DISABLE_SUPPLIERS_INVOICES_STATS),
		! empty($conf->supplier_proposal->enabled) && $user->rights->supplier_proposal->lire && empty($conf->global->SOCIETE_DISABLE_SUPPLIERS_PROPOSAL_STATS),
	    ! empty($conf->projet->enabled) && $user->rights->projet->lire,
	    ! empty($conf->expensereport->enabled) && $user->rights->expensereport->lire,
        ! empty($conf->holiday->enabled) && $user->rights->holiday->read,
		! empty($conf->don->enabled) && $user->rights->don->lire
	    );
	    // Class file containing the method load_state_board for each line
	    $includes=array(
	        DOL_DOCUMENT_ROOT."/user/class/user.class.php",
	        DOL_DOCUMENT_ROOT."/societe/class/client.class.php",
	        DOL_DOCUMENT_ROOT."/societe/class/client.class.php",
    	    DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.class.php",
    	    DOL_DOCUMENT_ROOT."/contact/class/contact.class.php",
    	    DOL_DOCUMENT_ROOT."/adherents/class/adherent.class.php",
    	    DOL_DOCUMENT_ROOT."/product/class/product.class.php",
    	    DOL_DOCUMENT_ROOT."/product/class/product.class.php",
    	    DOL_DOCUMENT_ROOT."/comm/propal/class/propal.class.php",
    	    DOL_DOCUMENT_ROOT."/commande/class/commande.class.php",
    	    DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php",
    	    DOL_DOCUMENT_ROOT."/contrat/class/contrat.class.php",
    	    DOL_DOCUMENT_ROOT."/fichinter/class/fichinter.class.php",
    	    DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.commande.class.php",
    	    DOL_DOCUMENT_ROOT."/fourn/class/fournisseur.facture.class.php",
    	    DOL_DOCUMENT_ROOT."/supplier_proposal/class/supplier_proposal.class.php",
            DOL_DOCUMENT_ROOT."/projet/class/project.class.php",
	        DOL_DOCUMENT_ROOT."/expensereport/class/expensereport.class.php",
            DOL_DOCUMENT_ROOT."/holiday/class/holiday.class.php",
			DOL_DOCUMENT_ROOT."/don/class/don.class.php"
	    );
	    // Name class containing the method load_state_board for each line
	    $classes=array('User',
	                   'Client',
	                   'Client',
	                   'Fournisseur',
	                   'Contact',
	                   'Adherent',
	                   'Product',
	                   'Product',
	                   'Propal',
	                   'Commande',
	                   'Facture',
	                   'Contrat',
	                   'Fichinter',
	                   'CommandeFournisseur',
	                   'FactureFournisseur',
            	       'SupplierProposal',
	                   'Project',
	                   'ExpenseReport',
                       'Holiday',
					   'Don'
	    );
	    // Cle array returned by the method load_state_board for each line
	    $keys=array('users',
	                'customers',
	                'suppliers',
	                'contacts',
	                'members',
	                'products',
	                 'services',
	                'proposals',
	                'orders',
	                'invoices',
	                'Contracts',
	                'fichinters',
	                'supplier_orders',
	                'supplier_invoices',
	                'askprice',
	                'projects',
	                'expensereports',
                    'holiday',
					'donations'
	    );
	    // Dashboard Icon lines
	    $icons=array('user',
	                 'company',
	                 'company',
	                 'company',
	                 'contact',
	                 'user',
	                 'product',
	                 'service',
	                 'propal',
	                 'order',
	                 'bill',
	                 'order',
	                 'order',
	                 'order',
	                 'bill',
	                 'propal',
	                 'projectpub',
					 'trip',
                     'holiday',
					 'generic'
	    );
	    // Translation keyword
	    $titres=array("Users",
	                  "ThirdPartyCustomersStats",
	                  "ThirdPartyProspectsStats",
	                  "Suppliers",
	                  "Contacts",
	                  "Members",
	                  "Products",
	                  "Services",
	                  "CommercialProposalsShort",
	                  "CustomersOrders",
	                  "BillsCustomers",
	                  "Contracts",
	                  "Interventions",
	                  "SuppliersOrders",
                      "SuppliersInvoices",
	                  "SupplierProposalShort",
	                  "Projects",
					  "ExpenseReports",
                      "Holidays",
					  "Donations"
	    );
	    // Dashboard Link lines
	    $links=array(
	        DOL_URL_ROOT.'/user/list.php',
    	    DOL_URL_ROOT.'/societe/list.php?type=c&mainmenu=companies',
    	    DOL_URL_ROOT.'/societe/list.php?type=p&mainmenu=companies',
    	    DOL_URL_ROOT.'/societe/list.php?type=f&mainmenu=companies',
    	    DOL_URL_ROOT.'/contact/list.php?mainmenu=companies',
    	    DOL_URL_ROOT.'/adherents/list.php?statut=1&mainmenu=members',
    	    DOL_URL_ROOT.'/product/list.php?type=0&mainmenu=products',
    	    DOL_URL_ROOT.'/product/list.php?type=1&mainmenu=products',
    	    DOL_URL_ROOT.'/comm/propal/list.php?mainmenu=commercial&leftmenu=propals',
    	    DOL_URL_ROOT.'/commande/list.php?mainmenu=commercial&leftmenu=orders',
    	    DOL_URL_ROOT.'/compta/facture/list.php?mainmenu=billing&leftmenu=customers_bills',
    	    DOL_URL_ROOT.'/contrat/list.php?mainmenu=commercial&leftmenu=contracts',
    	    DOL_URL_ROOT.'/fichinter/list.php?mainmenu=commercial&leftmenu=ficheinter',
    	    DOL_URL_ROOT.'/fourn/commande/list.php?mainmenu=commercial&leftmenu=orders_suppliers',
	        DOL_URL_ROOT.'/fourn/facture/list.php?mainmenu=billing&leftmenu=suppliers_bills',
	        DOL_URL_ROOT.'/supplier_proposal/list.php?mainmenu=commercial&leftmenu=',
	        DOL_URL_ROOT.'/projet/list.php?mainmenu=project',
    		DOL_URL_ROOT.'/expensereport/list.php?mainmenu=hrm&leftmenu=expensereport',
            DOL_URL_ROOT.'/holiday/list.php?mainmenu=hrm&leftmenu=holiday',
			DOL_URL_ROOT.'/don/list.php?leftmenu=donations'
	    );
	    // Translation lang files
	    $langfile=array("users",
	                    "companies",
	                    "prospects",
	                    "suppliers",
	                    "companies",
	                    "members",
	                    "products",
	                    "products",
	                    "propal",
	                    "orders",
            	        "bills",
						"contracts",
						"interventions",
	                    "bills",
	                    "bills",
	                    "supplier_proposal",
	                    "projects",
						"trips",
                        "holidays",
						"donations"
	    );


	    // Loop and displays each line of table
	    foreach ($keys as $key=>$val)
	    {  
		   $key++; // Abass
			if ($conditions[$key])
	        {
	            $classe=$classes[$key];
	            // Search in cache if load_state_board is already realized
	            if (! isset($boardloaded[$classe]) || ! is_object($boardloaded[$classe]))
	            {
	            	include_once $includes[$key];	// Loading a class cost around 1Mb

	                $board=new $classe($db);
	                $board->load_state_board($user);
	                $boardloaded[$classe]=$board;
	            }
	            else
	            {
	                $board=$boardloaded[$classe];
	            }


	            if (!empty($langfile[$key])) $langs->load($langfile[$key]);
	            $text=$langs->trans($titres[$key]);
	            //$sep=($conf->dol_use_jmobile?'<br>':' ');
//abasssssssssssssssssssssssssssssssss
// $boxstat.='<a href="'.$links[$key].'" class="boxstatsindicator thumbstat nobold nounderline">';
$boxstat.='<a  class="boxstatsindicator thumbstat nobold nounderline">';
	            $boxstat.='<div class="boxstats">';
	            $boxstat.='<span class="boxstatstext" title="'.dol_escape_htmltag($text).'">'.$text.'</span><br>';
	            $boxstat.='<span class="boxstatsindicator">'.img_object("", $icons[$key], 'class="inline-block"').' '.($board->nb[$val]?$board->nb[$val]:0).'</span>';
	            $boxstat.='</div>';
	            $boxstat.='</a>';
	        }
	    }
    
}
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';
    // $boxstat.='<a class="boxstatsindicator thumbstat nobold nounderline"><div class="boxstatsempty"></div></a>';

    $boxstat.='</td></tr>';
    $boxstat.='</table>';
    $boxstat.='</div>';
}
//print $boxstat;

print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


/*
 * ERP Working Board with weather
 */


//Array that contains all WorkboardResponse classes to process them
$dashboardlines=array();

// Do not include sections without management permission
require_once DOL_DOCUMENT_ROOT.'/core/class/workboardresponse.class.php';

//1 Number of actions to do (late)
if (! empty($conf->agenda->enabled) && $user->rights->agenda->myactions->read)
{
	include_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
	$board=new ActionComm($db);

	$dashboardlines[] = $board->load_board($user);
}

//2 Number of cheque to send
if (! empty($conf->banque->enabled) && $user->rights->banque->lire && ! $user->societe_id && empty($conf->global->BANK_DISABLE_CHECK_DEPOSIT))
{
	include_once DOL_DOCUMENT_ROOT.'/compta/paiement/cheque/class/remisecheque.class.php';
	$board=new RemiseCheque($db);
	$dashboardlines[] = $board->load_board($user);
}
//3 Number of tasks to do (late)
if (! empty($conf->projet->enabled) && empty($conf->global->PROJECT_HIDE_TASKS) && $user->rights->projet->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
	$board=new Task($db);
	$dashboardlines[] = $board->load_board($user);
}
//4 Number of customer orders a deal
if (! empty($conf->commande->enabled) && $user->rights->commande->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
	$board=new Commande($db);
	$dashboardlines[] = $board->load_board($user);
}



// Number of invoices customers (has paid)
if (! empty($conf->facture->enabled) && $user->rights->facture->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
	$board=new Facture($db);
	$dashboardlines[] = $board->load_board($user);
}

// Number of supplier invoices (has paid)
if (! empty($conf->supplier_invoice->enabled) && ! empty($user->rights->fournisseur->facture->lire))
{
	include_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
	$board=new FactureFournisseur($db);
	$dashboardlines[] = $board->load_board($user);
}



/*
// Number of suppliers orders a deal
if (! empty($conf->supplier_order->enabled) && $user->rights->fournisseur->commande->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
	$board=new CommandeFournisseur($db);
	$dashboardlines[] = $board->load_board($user);
}
//4 Number of commercial proposals opened (expired)
if (! empty($conf->supplier_proposal->enabled) && $user->rights->supplier_proposal->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/supplier_proposal/class/supplier_proposal.class.php';
	$board=new SupplierProposal($db);
	$dashboardlines[] = $board->load_board($user, "opened");
	// Number of commercial proposals CLOSED signed (billed)
	$dashboardlines[] = $board->load_board($user, "signed");
}



// Number of project opened
if (! empty($conf->projet->enabled) && $user->rights->projet->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	$board=new Project($db);
	$dashboardlines[] = $board->load_board($user);
}

// Number of commercial proposals opened (expired)
if (! empty($conf->propal->enabled) && $user->rights->propale->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
	$board=new Propal($db);
	$dashboardlines[] = $board->load_board($user, "opened");
	// Number of commercial proposals CLOSED signed (billed)
	$dashboardlines[] = $board->load_board($user, "signed");
}

// Number of services enabled (delayed)
if (! empty($conf->contrat->enabled) && $user->rights->contrat->lire)
{
	include_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
	$board=new Contrat($db);
	$dashboardlines[] = $board->load_board($user, "inactive");
	// Number of active services (expired)
	$dashboardlines[] = $board->load_board($user, "active");
}


// Number of transactions to conciliate
if (! empty($conf->banque->enabled) && $user->rights->banque->lire && ! $user->societe_id)
{
	include_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
	$board=new Account($db);
	$nb = $board::countAccountToReconcile();    // Get nb of account to reconciliate
	if ($nb > 0)
	{
		$dashboardlines[] = $board->load_board($user);
	}
}

// Number of foundation members
if (! empty($conf->adherent->enabled) && $user->rights->adherent->lire && ! $user->societe_id)
{
	include_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
	$board=new Adherent($db);
	$dashboardlines[] = $board->load_board($user);
}

// Number of expense reports to approve
if (! empty($conf->expensereport->enabled) && $user->rights->expensereport->approve)
{
	include_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport.class.php';
	$board=new ExpenseReport($db);
	$dashboardlines[] = $board->load_board($user, 'toapprove');
}

// Number of expense reports to pay
if (! empty($conf->expensereport->enabled) && $user->rights->expensereport->to_paid)
{
	include_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport.class.php';
	$board=new ExpenseReport($db);
	$dashboardlines[] = $board->load_board($user, 'topay');
}

// Number of holidays to approve
if (! empty($conf->holiday->enabled) && $user->rights->holiday->approve)
{
    include_once DOL_DOCUMENT_ROOT.'/holiday/class/holiday.class.php';
    $board=new Holiday($db);
    $dashboardlines[] = $board->load_board($user);
}
*/
$object=new stdClass();
$parameters=array();
$action='';
$reshook=$hookmanager->executeHooks('addOpenElementsDashboardLine', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook == 0) {
	$dashboardlines = array_merge($dashboardlines, $hookmanager->resArray);
}

// Calculate total nb of late
$totallate=$totaltodo=0;

//Remove any invalid response
//load_board can return an integer if failed or WorkboardResponse if OK
$valid_dashboardlines=array();
foreach($dashboardlines as $tmp)
{
	if ($tmp instanceof WorkboardResponse) $valid_dashboardlines[] = $tmp;
}

// We calculate $totallate. Must be defined before start of next loop because it is show in first fetch on next loop
foreach($valid_dashboardlines as $board)
{
    if ($board->nbtodolate > 0) {
    	if(!empty($conf->global->MAIN_USE_METEO_WITH_PERCENTAGE)) $totaltodo += $board->nbtodo;
	    $totallate += $board->nbtodolate;
    }
}
//var_dump($totallate, $totaltodo);
if(!empty($conf->global->MAIN_USE_METEO_WITH_PERCENTAGE) && !empty($totaltodo)) $totallate = round($totallate / $totaltodo * 100, 2);
//var_dump($totallate);
$boxwork='';
$boxwork.='<div class="box">';
$boxwork.='<table summary="'.dol_escape_htmltag($langs->trans("WorkingBoard")).'" class="noborder boxtable boxtablenobottom boxworkingboard" width="100%">'."\n";
$boxwork.='<tr class="liste_titre">';
$boxwork.='<th class="liste_titre"><div class="inline-block valignmiddle">'.$langs->trans("MounirWorkBoard").'</div>';

$boxwork.='</th>';
$boxwork.='</tr>'."\n";


// Show dashboard
$nbworkboardempty=0;
if (! empty($valid_dashboardlines))
{
    $boxwork.='<tr class="nobottom nohover"><td class="tdboxstats nohover flexcontainer centpercent"><div style="display: flex: flex-wrap: wrap">';

    foreach($valid_dashboardlines as $board)
    {
        if (empty($board->nbtodo)) $nbworkboardempty++;

        $textlate = $langs->trans("NActionsLate", $board->nbtodolate);
        $textlate.= ' ('.$langs->trans("Late").' = '.$langs->trans("DateReference").' > '.$langs->trans("DateToday").' '.(ceil($board->warning_delay) >= 0 ? '+' : '').ceil($board->warning_delay).' '.$langs->trans("days").')';

        $boxwork .='<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats130 boxstatsborder">';
        $boxwork .= '<div class="boxstatscontent">';
        $sep=($conf->dol_use_jmobile?'<br>':' ');
        $boxwork .= '<span class="boxstatstext" title="'.dol_escape_htmltag($board->label).'">'.$board->img.' '.$board->label.'</span><br>';
//abass
// $boxwork .= '<a class="valignmiddle dashboardlineindicator" href="'.$board->url.'"><span class="dashboardlineindicator'.(($board->nbtodo == 0)?' dashboardlineok':'').'">'.$board->nbtodo.'</span></a>';
        
$boxwork .= '<a class="valignmiddle dashboardlineindicator"><span class="dashboardlineindicator'.(($board->nbtodo == 0)?' dashboardlineok':'').'">'.$board->nbtodo.'</span></a>';
		
		if ($board->total > 0 && ! empty($conf->global->MAIN_WORKBOARD_SHOW_TOTAL_WO_TAX))
        {
            $boxwork .= '&nbsp;/&nbsp;<a class="valignmiddle dashboardlineindicator" href="'.$board->url.'"><span class="dashboardlineindicator'.(($board->nbtodo == 0)?' dashboardlineok':'').'">'.price($board->total)	.'</span></a>';
        }
        $boxwork .= '</div>';
        if ($board->nbtodolate > 0)
        {
            $boxwork .= '<div class="dashboardlinelatecoin nowrap">';
            $boxwork .= '<a title="'.dol_escape_htmltag($textlate).'" class="valignmiddle dashboardlineindicatorlate'.($board->nbtodolate>0?' dashboardlineko':' dashboardlineok').'" href="'.((!$board->url_late) ? $board->url : $board->url_late ).'">';
            //$boxwork .= img_picto($textlate, "warning_white", 'class="valigntextbottom"').'';
            $boxwork .= img_picto($textlate, "warning_white", 'class="inline-block hideonsmartphone valigntextbottom"').'';
            $boxwork .= '<span class="dashboardlineindicatorlate'.($board->nbtodolate>0?' dashboardlineko':' dashboardlineok').'">';
            $boxwork .= $board->nbtodolate;
            $boxwork .= '</span>';
            $boxwork .= '</a>';
            $boxwork .= '</div>';
        }
        $boxwork.='</div></div>';
        $boxwork .="\n";
    }

    $boxwork .='<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
    $boxwork .='<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
    $boxwork .='<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';
    $boxwork .='<div class="boxstatsindicator thumbstat150 nobold nounderline"><div class="boxstats150empty"></div></div>';

    $boxwork .='</div>';
    $boxwork .='</td></tr>';
}
else
{
    $boxwork.='<tr class="nohover">';
    $boxwork.='<td class="nohover valignmiddle opacitymedium">';
    $boxwork.=$langs->trans("NoOpenedElementToProcess");
    $boxwork.='</td>';
    $boxwork.='</tr>';
}

$boxwork.='</td></tr>';

$boxwork.='</table>';   // End table array of working board
$boxwork.='</div>';

print '</div></div></div><div class="clearboth"></div>';

print '<div class="fichecenter fichecenterbis">';


/*
 * Show boxes
 */

$boxlist.='<div class="twocolumns">';

$boxlist.='<div class="firstcolumn fichehalfleft boxhalfleft" id="boxhalfleft">';

$boxlist.=$boxwork;
$boxlist.=$resultboxes['boxlista'];

$boxlist.= '</div>';

$boxlist.= '<div class="secondcolumn fichehalfright boxhalfright" id="boxhalfright">';

$boxlist.=$boxstat;
$boxlist.=$resultboxes['boxlistb'];

$boxlist.= '</div>';
$boxlist.= "\n";

$boxlist.='</div>';


print $boxlist;

print '</div>';


/*
 * Show security warnings
 */

// Security warning repertoire install existe (si utilisateur admin)
if ($user->admin && empty($conf->global->MAIN_REMOVE_INSTALL_WARNING))
{
    $message='';

    // Check if install lock file is present
    $lockfile=DOL_DATA_ROOT.'/install.lock';
    if (! empty($lockfile) && ! file_exists($lockfile) && is_dir(DOL_DOCUMENT_ROOT."/install"))
    {
        $langs->load("errors");
        //if (! empty($message)) $message.='<br>';
        $message.=info_admin($langs->trans("WarningLockFileDoesNotExists", DOL_DATA_ROOT).' '.$langs->trans("WarningUntilDirRemoved", DOL_DOCUMENT_ROOT."/install"), 0, 0, '1', 'clearboth');
    }

    // Conf files must be in read only mode
    if (is_writable($conffile))
    {
        $langs->load("errors");
        //$langs->load("other");
        //if (! empty($message)) $message.='<br>';
        $message.=info_admin($langs->transnoentities("WarningConfFileMustBeReadOnly").' '.$langs->trans("WarningUntilDirRemoved", DOL_DOCUMENT_ROOT."/install"), 0, 0, '1', 'clearboth');
    }

    if ($message)
    {
        print $message;
        //$message.='<br>';
        //print info_admin($langs->trans("WarningUntilDirRemoved",DOL_DOCUMENT_ROOT."/install"));
    }
}

//print 'mem='.memory_get_usage().' - '.memory_get_peak_usage();

// End of page
llxFooter();
$db->close();