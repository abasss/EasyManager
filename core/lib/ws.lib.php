<?php

function check_authentication($authentication, &$error, &$errorcode, &$errorlabel)
{
    global $db,$conf,$langs;
    global $mounir_main_authentication,$mounir_auto_user;

    $fuser=new User($db);

    if (! $error && ($authentication['mounirkey'] != $conf->global->WEBSERVICES_KEY))
    {
        $error++;
        $errorcode='BAD_VALUE_FOR_SECURITY_KEY'; $errorlabel='Value provided into mounirkey entry field does not match security key defined in Webservice module setup';
    }

    if (! $error && ! empty($authentication['entity']) && ! is_numeric($authentication['entity']))
    {
        $error++;
        $errorcode='BAD_PARAMETERS'; $errorlabel="The entity parameter must be empty (or filled with numeric id of instance if multicompany module is used).";
    }

    if (! $error)
    {
        $result=$fuser->fetch('', $authentication['login'], '', 0);
        if ($result < 0)
        {
            $error++;
            $errorcode='ERROR_FETCH_USER'; $errorlabel='A technical error occurred during fetch of user';
        }
        elseif ($result == 0)
        {
            $error++;
            $errorcode='BAD_CREDENTIALS'; $errorlabel='Bad value for login or password';
        }

		if (! $error && $fuser->statut == 0)
		{
			$error++;
			$errorcode='ERROR_USER_DISABLED'; $errorlabel='This user has been locked or disabled';
		}

    	// Validation of login
		if (! $error)
		{
			$fuser->getrights();	// Load permission of user

        	// Authentication mode
        	if (empty($mounir_main_authentication)) $mounir_main_authentication='http,mounir';
        	// Authentication mode: forceuser
        	if ($mounir_main_authentication == 'forceuser' && empty($mounir_auto_user)) $mounir_auto_user='auto';
        	// Set authmode
        	$authmode=explode(',', $mounir_main_authentication);

            include_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
        	$login = checkLoginPassEntity($authentication['login'], $authentication['password'], $authentication['entity'], $authmode);
			if (empty($login))
			{
			    $error++;
                $errorcode='BAD_CREDENTIALS'; $errorlabel='Bad value for login or password';
			}
		}
    }

    return $fuser;
}
