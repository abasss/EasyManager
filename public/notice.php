<?php

define('NOCSRFCHECK', 1);
define('NOLOGIN', 1);

require '../main.inc.php';


/**
 * View
 */

if (! GETPOST('transkey', 'alphanohtml') && ! GETPOST('transphrase', 'alphanohtml'))
{
    print 'Sorry, it seems your internet connexion is off.<br>';
    print 'You need to be connected to network to use this software.<br>';
}
else
{
    $langs->load("error");
    $langs->load("other");

    if (GETPOST('transphrase', 'alphanohtml')) print GETPOST('transphrase', 'alphanohtml');
    if (GETPOST('transkey', 'alphanohtml')) print $langs->trans(GETPOST('transkey', 'alphanohtml'));
}
