<?php

include_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';


/**
 *	Class to manage customers or prospects
 */
class Client extends Societe
{
    public $next_prev_filter="te.client in (1,2,3)";	// Used to add a filter in Form::showrefnav method

    public $cacheprospectstatus=array();


	/**
     *  Constructor
     *
     *  @param	DoliDB	$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->client = 3;
        $this->fournisseur = 0;
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *  Load indicators into this->nb for board
     *
     *  @return     int         <0 if KO, >0 if OK
     */
    public function load_state_board()
    {
        // phpcs:enable
        global $user;

        $this->nb=array("customers" => 0,"prospects" => 0);
        $clause = "WHERE";

        $sql = "SELECT count(s.rowid) as nb, s.client";
        $sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
        if (!$user->rights->societe->client->voir && !$user->societe_id)
        {
        	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON s.rowid = sc.fk_soc";
        	$sql.= " WHERE sc.fk_user = " .$user->id;
        	$clause = "AND";
        }
        $sql.= " ".$clause." s.client IN (1,2,3)";
        $sql.= ' AND s.entity IN ('.getEntity($this->element).')';
        $sql.= " GROUP BY s.client";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            while ($obj=$this->db->fetch_object($resql))
            {
                if ($obj->client == 1 || $obj->client == 3) $this->nb["customers"]+=$obj->nb;
                if ($obj->client == 2 || $obj->client == 3) $this->nb["prospects"]+=$obj->nb;
            }
            $this->db->free($resql);
            return 1;
        }
        else
        {
            dol_print_error($this->db);
            $this->error=$this->db->lasterror();
            return -1;
        }
    }

	/**
	 *  Load array of prospect status
	 *
	 *  @param	int		$active     1=Active only, 0=Not active only, -1=All
	 *  @return int					<0 if KO, >0 if OK
	 */
    public function loadCacheOfProspStatus($active = 1)
    {
    	global $langs;

   		$sql="SELECT id, code, libelle as label FROM ".MAIN_DB_PREFIX."c_stcomm";
   		if ($active >= 0) $sql.=" WHERE active = ".$active;
		$resql=$this->db->query($sql);
		$num=$this->db->num_rows($resql);
		$i=0;
		while ($i < $num) {
			$obj=$this->db->fetch_object($resql);
			$this->cacheprospectstatus[$obj->id]=array('id'=>$obj->id, 'code'=>$obj->code, 'label'=> ($langs->trans("ST_".strtoupper($obj->code))=="ST_".strtoupper($obj->code))?$obj->label:$langs->trans("ST_".strtoupper($obj->code)));
			$i++;
		}
		return 1;
    }
}
