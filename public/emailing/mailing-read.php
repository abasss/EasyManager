<?php

if (! defined('NOLOGIN'))        define("NOLOGIN", 1);			// This means this output page does not require to be logged.
if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN', '1');
if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');		// Do not check anti CSRF attack test
if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');	// Do not check anti POST attack test
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');	// If there is no need to load and show top and left menu

/**
 * Header empty
 *
 * @return	void
 */
function llxHeader()
{
}
/**
 * Footer empty
 *
 * @return	void
 */
function llxFooter()
{
}


require '../../main.inc.php';

$tag=GETPOST('tag');
$securitykey=GETPOST('securitykey');


/*
 * Actions
 */

dol_syslog("public/emailing/mailing-read.php : tag=".$tag." securitykey=".$securitykey, LOG_DEBUG);

if ($securitykey != $conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY)
{
	print 'Bad security key value.';
	exit;
}

if (! empty($tag))
{
	$statut='2';
	$sql = "UPDATE ".MAIN_DB_PREFIX."mailing_cibles SET statut=".$statut." WHERE tag='".$db->escape($tag)."'";
	dol_syslog("public/emailing/mailing-read.php : Mail read : ".$sql, LOG_DEBUG);

	$resql=$db->query($sql);

	//Update status communication of thirdparty prospect
	$sql = "UPDATE ".MAIN_DB_PREFIX."societe SET fk_stcomm=3 WHERE fk_stcomm != -1 AND rowid IN (SELECT source_id FROM ".MAIN_DB_PREFIX."mailing_cibles WHERE tag='".$db->escape($tag)."' AND source_type='thirdparty' AND source_id is not null)";
	dol_syslog("public/emailing/mailing-read.php : Mail read thirdparty : ".$sql, LOG_DEBUG);

	$resql=$db->query($sql);

    //Update status communication of contact prospect
	$sql = "UPDATE ".MAIN_DB_PREFIX."societe SET fk_stcomm=3 WHERE fk_stcomm != -1 AND rowid IN (SELECT sc.fk_soc FROM ".MAIN_DB_PREFIX."socpeople AS sc INNER JOIN ".MAIN_DB_PREFIX."mailing_cibles AS mc ON mc.tag = '".$db->escape($tag)."' AND mc.source_type = 'contact' AND mc.source_id = sc.rowid)";
	dol_syslog("public/emailing/mailing-read.php : Mail read contact : ".$sql, LOG_DEBUG);

	$resql=$db->query($sql);
}

$db->close();
