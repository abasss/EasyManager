<?php

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Load translation files required by the page
$langs->load("companies");

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');

// Security check
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype);

$object = new Product($db);
if ($id > 0 || ! empty($ref)) $object->fetch($id, $ref);

$permissionnote=$user->rights->produit->creer;	// Used by the include of actions_setnotes.inc.php


/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php';	// Must be include, not includ_once


/*
 *	View
 */

$form = new Form($db);

$helpurl='';
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT)) $helpurl='EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE)) $helpurl='EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';

$title = $langs->trans('ProductServiceCard');
$shortlabel = dol_trunc($object->label, 16);
if (GETPOST("type") == '0' || ($object->type == Product::TYPE_PRODUCT))
{
	$title = $langs->trans('Product')." ". $shortlabel ." - ".$langs->trans('Notes');
	$helpurl='EN:Module_Products|FR:Module_Produits|ES:M&oacute;dulo_Productos';
}
if (GETPOST("type") == '1' || ($object->type == Product::TYPE_SERVICE))
{
	$title = $langs->trans('Service')." ". $shortlabel ." - ".$langs->trans('Notes');
	$helpurl='EN:Module_Services_En|FR:Module_Services|ES:M&oacute;dulo_Servicios';
}

llxHeader('', $title, $help_url);

if ($id > 0 || ! empty($ref))
{
    /*
     * Affichage onglets
     */
    if (! empty($conf->notification->enabled)) $langs->load("mails");

    $head = product_prepare_head($object);
    $titre=$langs->trans("CardProduct".$object->type);
    $picto=($object->type==Product::TYPE_SERVICE?'service':'product');

    dol_fiche_head($head, 'note', $titre, -1, $picto);

	$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
    $object->next_prev_filter=" fk_product_type = ".$object->type;

    $shownav = 1;
    if ($user->societe_id && ! in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav=0;

	dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

    $cssclass='titlefield';
    //if ($action == 'editnote_public') $cssclass='titlefieldcreate';
    //if ($action == 'editnote_private') $cssclass='titlefieldcreate';

    //print '<div class="fichecenter">';

    print '<div class="underbanner clearboth"></div>';

    include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

    dol_fiche_end();
}

// End of page
llxFooter();
$db->close();
