<?php
/* Copyright (C) 2006-2011 Laurent Destailleur <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *      \file       htdocs/core/modules/security/generate/modGeneratePassStandard.class.php
 *      \ingroup    core
 *		\brief      File to manage password generation according to standard rule
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/security/generate/modules_genpassword.php';


/**
 *	    \class      modGeneratePassStandard
 *		\brief      Class to generate a password according to a mounir standard rule (8 random chars)
 */
class modGeneratePassStandard extends ModeleGenPassword
{
	/**
	 * @var int ID
	 */
	public $id;

	public $length;

	/**
     * @var DoliDB Database handler.
     */
    public $db;

	public $conf;
	public $lang;
	public $user;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db			Database handler
	 *	@param		Conf		$conf		Handler de conf
	 *	@param		Translate	$langs		Handler de langue
	 *	@param		User		$user		Handler du user connecte
	 */
	public function __construct($db, $conf, $langs, $user)
	{
		$this->id = "standard";
		$this->length = 8;

		$this->db=$db;
		$this->conf=$conf;
		$this->langs=$langs;
		$this->user=$user;
	}

	/**
	 *		Return description of module
	 *
 	 *      @return     string      Description of module
	 */
	public function getDescription()
	{
		global $langs;
		return $langs->trans("PasswordGenerationStandard");
	}

	/**
	 * 		Return an example of password generated by this module
	 *
 	 *      @return     string      Example of password
	 */
	public function getExample()
	{
		return $this->getNewGeneratedPassword();
	}

	/**
	 * 		Build new password
	 *
 	 *      @return     string      Return a new generated password
	 */
	public function getNewGeneratedPassword()
	{
		// start with a blank password
		$password = "";

		// define possible characters
		$possible = "0123456789bcdfghjkmnpqrstvwxyz";

		// set up a counter
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $this->length)
		{

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, dol_strlen($possible)-1), 1);

			// we don't want this character if it's already in the password
			if (!strstr($password, $char))
			{
				$password .= $char;
				$i++;
			}
		}

		// done!
		return $password;
	}

    /**
     *  Validate a password
     *
     *  @param      string  $password   Password to check
     *  @return     int                 0 if KO, >0 if OK
     */
    public function validatePassword($password)
    {
        if (dol_strlen($password) < $this->length) return 0;
        return 1;
    }
}
