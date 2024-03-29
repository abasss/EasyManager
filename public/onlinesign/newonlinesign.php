<?php

define("NOLOGIN", 1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK", 1);	// We accept to go on this page from external web site.

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// TODO This should be useless. Because entity must be retreive from object ref and not from url.
$entity=(! empty($_GET['entity']) ? (int) $_GET['entity'] : (! empty($_POST['entity']) ? (int) $_POST['entity'] : 1));
if (is_numeric($entity)) define("DOLENTITY", $entity);

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Security check
// No check on module enabled. Done later according to $validpaymentmethod

$langs->loadLangs(array("main", "other", "dict", "bills", "companies", "errors", "paybox"));

$action=GETPOST('action', 'alpha');

// Input are:
// type ('invoice','order','contractline'),
// id (object id),
// amount (required if id is empty),
// tag (a free text, required if type is empty)
// currency (iso code)

$suffix=GETPOST("suffix", 'alpha');
$source=GETPOST("source", 'alpha');
$ref=$REF=GETPOST("ref", 'alpha');

if (empty($source)) $source='proposal';

if (! $action)
{
    if ($source && ! $ref)
    {
    	print $langs->trans('ErrorBadParameters')." - ref missing";
    	exit;
    }
}


$paymentmethod='';
$validpaymentmethod=array();




// Define $urlwithroot
//$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT,'/').'$/i','',trim($mounir_main_url_root));
//$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;		// This is to use external domain name found into config file
$urlwithroot=DOL_MAIN_URL_ROOT;						// This is to use same domain name than current. For Paypal payment, we can use internal URL like localhost.


// Complete urls for post treatment
$SECUREKEY=GETPOST("securekey");	        // Secure key

if (! empty($source))
{
    $urlok.='source='.urlencode($source).'&';
    $urlko.='source='.urlencode($source).'&';
}
if (! empty($REF))
{
    $urlok.='ref='.urlencode($REF).'&';
    $urlko.='ref='.urlencode($REF).'&';
}
if (! empty($SECUREKEY))
{
    $urlok.='securekey='.urlencode($SECUREKEY).'&';
    $urlko.='securekey='.urlencode($SECUREKEY).'&';
}
if (! empty($entity))
{
	$urlok.='entity='.urlencode($entity).'&';
	$urlko.='entity='.urlencode($entity).'&';
}
$urlok=preg_replace('/&$/', '', $urlok);  // Remove last &
$urlko=preg_replace('/&$/', '', $urlko);  // Remove last &

$creditor = $mysoc->name;


/*
 * Actions
 */


if ($action == 'dosign')
{
    // TODO
}


/*
 * View
 */

$head='';
if (! empty($conf->global->MAIN_SIGN_CSS_URL)) $head='<link rel="stylesheet" type="text/css" href="'.$conf->global->MAIN_SIGN_CSS_URL.'?lang='.$langs->defaultlang.'">'."\n";

$conf->dol_hide_topmenu=1;
$conf->dol_hide_leftmenu=1;

llxHeader($head, $langs->trans("OnlineSignature"), '', '', 0, 0, '', '', '', 'onlinepaymentbody');

// Check link validity
if (! empty($source) && in_array($ref, array('member_ref', 'contractline_ref', 'invoice_ref', 'order_ref', '')))
{
    $langs->load("errors");
    dol_print_error_email('BADREFINONLINESIGNFORM', $langs->trans("ErrorBadLinkSourceSetButBadValueForRef", $source, $ref));
    // End of page
    llxFooter();
    $db->close();
    exit;
}

print '<span id="dolpaymentspan"></span>'."\n";
print '<div class="center">'."\n";
print '<form id="dolpaymentform" class="center" name="paymentform" action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
print '<input type="hidden" name="action" value="dosign">'."\n";
print '<input type="hidden" name="tag" value="'.GETPOST("tag", 'alpha').'">'."\n";
print '<input type="hidden" name="suffix" value="'.GETPOST("suffix", 'alpha').'">'."\n";
print '<input type="hidden" name="securekey" value="'.$SECUREKEY.'">'."\n";
print '<input type="hidden" name="entity" value="'.$entity.'" />';
print "\n";
print '<!-- Form to sign -->'."\n";

print '<table id="dolpaymenttable" summary="Payment form" class="center">'."\n";

// Show logo (search order: logo defined by ONLINE_SIGN_LOGO_suffix, then ONLINE_SIGN_LOGO_, then small company logo, large company logo, theme logo, common logo)
$width=0;
// Define logo and logosmall
$logosmall=$mysoc->logo_small;
$logo=$mysoc->logo;
$paramlogo='ONLINE_SIGN_LOGO_'.$suffix;
if (! empty($conf->global->$paramlogo)) $logosmall=$conf->global->$paramlogo;
elseif (! empty($conf->global->ONLINE_SIGN_LOGO)) $logosmall=$conf->global->ONLINE_SIGN_LOGO;
//print '<!-- Show logo (logosmall='.$logosmall.' logo='.$logo.') -->'."\n";
// Define urllogo
$urllogo='';
if (! empty($logosmall) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$logosmall))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;entity='.$conf->entity.'&amp;file='.urlencode('logos/thumbs/'.$logosmall);
}
elseif (! empty($logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$logo))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;entity='.$conf->entity.'&amp;file='.urlencode('logos/'.$logo);
	$width=96;
}
// Output html code for logo
if ($urllogo)
{
	print '<tr>';
	print '<td align="center"><img id="dolpaymentlogo" title="'.$title.'" src="'.$urllogo.'"';
	if ($width) print ' width="'.$width.'"';
	print '></td>';
	print '</tr>'."\n";
}

// Output introduction text
$text='';
if (! empty($conf->global->ONLINE_SIGN_NEWFORM_TEXT))
{
    $langs->load("members");
    if (preg_match('/^\((.*)\)$/', $conf->global->ONLINE_SIGN_NEWFORM_TEXT, $reg)) $text.=$langs->trans($reg[1])."<br>\n";
    else $text.=$conf->global->ONLINE_SIGN_NEWFORM_TEXT."<br>\n";
    $text='<tr><td align="center"><br>'.$text.'<br></td></tr>'."\n";
}
if (empty($text))
{
    $text.='<tr><td class="textpublicpayment"><br><strong>'.$langs->trans("WelcomeOnOnlineSignaturePage", $mysoc->name).'</strong></td></tr>'."\n";
    $text.='<tr><td class="textpublicpayment">'.$langs->trans("ThisScreenAllowsYouToSignDocFrom", $creditor).'<br><br></td></tr>'."\n";
}
print $text;

// Output payment summary form
print '<tr><td align="center">';
print '<table with="100%" id="tablepublicpayment">';
print '<tr class="liste_total"><td align="left" colspan="2">'.$langs->trans("ThisIsInformationOnDocumentToSign").' :</td></tr>'."\n";

$found=false;
$error=0;
$var=false;

// Payment on customer order
if ($source == 'proposal')
{
	$found=true;
	$langs->load("proposal");

	require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';

	$proposal=new Propal($db);
	$result=$proposal->fetch('', $ref);
	if ($result <= 0)
	{
		$mesg=$proposal->error;
		$error++;
	}
	else
	{
		$result=$proposal->fetch_thirdparty($proposal->socid);
	}

	// Creditor

	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Creditor");
    print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$creditor.'</b>';
    print '<input type="hidden" name="creditor" value="'.$creditor.'">';
    print '</td></tr>'."\n";

	// Debitor

	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("ThirdParty");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$proposal->thirdparty->name.'</b>';

	// Object

	$text='<b>'.$langs->trans("SignatureProposalRef", $proposal->ref).'</b>';
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Designation");
	print '</td><td class="CTableRow'.($var?'1':'2').'">'.$text;
	print '<input type="hidden" name="source" value="'.GETPOST("source", 'alpha').'">';
	print '<input type="hidden" name="ref" value="'.$proposal->ref.'">';
	print '</td></tr>'."\n";
}



if (! $found && ! $mesg) $mesg=$langs->trans("ErrorBadParameters");

if ($mesg) print '<tr><td align="center" colspan="2"><br><div class="warning">'.$mesg.'</div></td></tr>'."\n";

print '</table>'."\n";
print "\n";

if ($action != 'dosign')
{
    if ($found && ! $error)	// We are in a management option and no error
    {


    }
    else
    {
    	dol_print_error_email('ERRORNEWONLINESIGN');
    }
}
else
{
    // Print
}

print '</td></tr>'."\n";

print '</table>'."\n";
print '</form>'."\n";
print '</div>'."\n";
print '<br>';


htmlPrintOnlinePaymentFooter($mysoc, $langs);

llxFooter('', 'public');

$db->close();
