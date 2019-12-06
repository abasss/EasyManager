<?php

class AccountancySystem
{
    /**
     * @var DoliDB Database handler.
     */
    public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error='';

	/**
	 * @var int ID
	 */
	public $rowid;

	/**
     * @var int ID
     */
	public $fk_pcg_version;

	public $pcg_type;
	public $pcg_subtype;

    /**
     * @var string Accountancy System label
     */
    public $label;

	public $account_number;
	public $account_parent;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
    public function __construct($db)
    {
		$this->db = $db;
    }


	/**
	 * Load record in memory
	 *
	 * @param 	int 	$rowid 				   Id
	 * @param 	string 	$ref             	   ref
	 * @return 	int                            <0 if KO, Id of record if OK and found
	 */
	public function fetch($rowid = 0, $ref = '')
	{
	    global $conf;

	    if ($rowid > 0 || $ref)
	    {
	        $sql  = "SELECT a.rowid, a.pcg_version, a.label, a.active";
	        $sql .= " FROM " . MAIN_DB_PREFIX . "accounting_system as a";
	        $sql .= " WHERE";
	        if ($rowid) {
	            $sql .= " a.rowid = '" . $rowid . "'";
	        } elseif ($ref) {
	            $sql .= " a.pcg_version = '" . $this->db->escape($ref) . "'";
	        }

	        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
	        $result = $this->db->query($sql);
	        if ($result) {
	            $obj = $this->db->fetch_object($result);

	            if ($obj) {
	                $this->id = $obj->rowid;
	                $this->rowid = $obj->rowid;
	                $this->pcg_version = $obj->pcg_version;
	                $this->ref = $obj->pcg_version;
	                $this->label = $obj->label;
	                $this->active = $obj->active;

	                return $this->id;
	            } else {
	                return 0;
	            }
	        } else {
	            $this->error = "Error " . $this->db->lasterror();
	            $this->errors[] = "Error " . $this->db->lasterror();
	        }
	    }
	    return - 1;
	}


	/**
	 * Insert accountancy system name into database
	 *
	 * @param User $user making insert
	 * @return int if KO, Id of line if OK
	 */
    public function create($user)
    {
		$now = dol_now();

		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "accounting_system";
		$sql .= " (date_creation, fk_user_author, numero, label)";
		$sql .= " VALUES ('" . $this->db->idate($now) . "'," . $user->id . ",'" . $this->db->escape($this->numero) . "','" . $this->db->escape($this->label) . "')";

		dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$id = $this->db->last_insert_id(MAIN_DB_PREFIX . "accounting_system");

			if ($id > 0) {
				$this->rowid = $id;
				$result = $this->rowid;
			} else {
				$result = - 2;
				$this->error = "AccountancySystem::Create Error $result";
				dol_syslog($this->error, LOG_ERR);
			}
		} else {
			$result = - 1;
			$this->error = "AccountancySystem::Create Error $result";
			dol_syslog($this->error, LOG_ERR);
		}

        return $result;
    }
}
