<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville         <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur          <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin                <regis.houssin@inodbox.com>
 * Copyright (C) 2008      Raphael Bertrand (Resultic)  <raphael.bertrand@resultic.fr>
 * Copyright (C) 2013      Juanjo Menent				<jmenent@2byte.es>
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
 *	\file       htdocs/core/modules/fichinter/mod_arctic.php
 *	\ingroup    fiche intervention
 *	\brief      File with Arctic numbering module for interventions
 */
require_once DOL_DOCUMENT_ROOT .'/core/modules/fichinter/modules_fichinter.php';

/**
 *	Class to manage numbering of intervention cards with rule Artic.
 */
class mod_arctic extends ModeleNumRefFicheinter
{
    /**
     * ERP version of the loaded document
     * @var string
     */
	public $version = 'mounir';		// 'development', 'experimental', 'mounir'

	/**
	 * @var string Error message
	 */
	public $error = '';

	/**
	 * @var string Nom du modele
	 * @deprecated
	 * @see name
	 */
	public $nom='arctic';

	/**
	 * @var string model name
	 */
	public $name='arctic';


	/**
     *  Renvoi la description du modele de numerotation
     *
     *  @return     string      Texte descripif
     */
    public function info()
    {
    	global $conf, $langs;

		$langs->load("bills");

		$form = new Form($this->db);

		$texte = $langs->trans('GenericNumRefModelDesc')."<br>\n";
		$texte.= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte.= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte.= '<input type="hidden" name="action" value="updateMask">';
		$texte.= '<input type="hidden" name="maskconst" value="FICHINTER_ARTIC_MASK">';
		$texte.= '<table class="nobordernopadding" width="100%">';

		$tooltip=$langs->trans("GenericMaskCodes", $langs->transnoentities("InterventionCard"), $langs->transnoentities("InterventionCard"));
		$tooltip.=$langs->trans("GenericMaskCodes2");
		$tooltip.=$langs->trans("GenericMaskCodes3");
		$tooltip.=$langs->trans("GenericMaskCodes4a", $langs->transnoentities("InterventionCard"), $langs->transnoentities("InterventionCard"));
		$tooltip.=$langs->trans("GenericMaskCodes5");

		// Parametrage du prefix
		$texte.= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte.= '<td class="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskvalue" value="'.$conf->global->FICHINTER_ARTIC_MASK.'">', $tooltip, 1, 1).'</td>';

		$texte.= '<td class="left" rowspan="2">&nbsp; <input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';

		$texte.= '</tr>';

		$texte.= '</table>';
		$texte.= '</form>';

		return $texte;
    }

    /**
     * Renvoi un exemple de numerotation
     *
     * @return     string      Example
     */
    public function getExample()
    {
     	global $conf,$langs,$mysoc;

    	$old_code_client=$mysoc->code_client;
    	$mysoc->code_client='CCCCCCCCCC';
     	$numExample = $this->getNextValue($mysoc, '');
		$mysoc->code_client=$old_code_client;

		if (! $numExample)
		{
			$numExample = $langs->trans('NotConfigured');
		}
		return $numExample;
    }

	/**
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
    public function getNextValue($objsoc = 0, $object = '')
    {
		global $db,$conf;

		require_once DOL_DOCUMENT_ROOT .'/core/lib/functions2.lib.php';

		// On défini critere recherche compteur
		$mask=$conf->global->FICHINTER_ARTIC_MASK;

		if (! $mask)
		{
			$this->error='NotConfigured';
			return 0;
		}

		$numFinal=get_next_value($db, $mask, 'fichinter', 'ref', '', $objsoc, $object->datec);

		return  $numFinal;
    }


	/**
	 *  Return next free value
	 *
     *  @param	Societe		$objsoc     Object third party
	 *  @param	Object		$objforref	Object for number to search
     *  @return string      			Next free value
     */
    public function getNumRef($objsoc, $objforref)
    {
        return $this->getNextValue($objsoc, $objforref);
    }
}
