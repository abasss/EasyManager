<?php

use Luracast\Restler\RestException;

//require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
//require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';


/**
 * API class for contacts
 *
 * @access protected
 * @class  MounirApiAccess {@requires user,external}
 */
class Contacts extends MounirApi
{
	/**
	 *
	 * @var array   $FIELDS     Mandatory fields, checked when create and update object
	 */
	static $FIELDS = array(
		'lastname',
	);

	/**
	 * @var Contact $contact {@type Contact}
	 */
	public $contact;

	/**
	 * Constructor
	 */
    public function __construct()
	{
		global $db, $conf;
		$this->db = $db;

		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		$this->contact = new Contact($this->db);
	}

	/**
	 * Get properties of a contact object
	 *
	 * Return an array with contact informations
	 *
	 * @param 	int    $id                  ID of contact
	 * @param   int    $includecount        Count and return also number of elements the contact is used as a link for
	 * @return 	array|mixed data without useless information
	 *
	 * @throws 	RestException
	 */
    public function get($id, $includecount = 0)
	{
		if (!MounirApiAccess::$user->rights->societe->contact->lire)
		{
			throw new RestException(401, 'No permission to read contacts');
		}

		$result = $this->contact->fetch($id);

		if (!$result)
		{
			throw new RestException(404, 'Contact not found');
		}

		if (!MounirApi::_checkAccessToResource('contact', $this->contact->id, 'socpeople&societe'))
		{
			throw new RestException(401, 'Access not allowed for login ' . MounirApiAccess::$user->login);
		}

		if ($includecount)
		{
		    $this->contact->load_ref_elements();
		}

		return $this->_cleanObjectDatas($this->contact);
	}

	/**
	 * List contacts
	 *
	 * Get a list of contacts
	 *
	 * @param string	$sortfield	        Sort field
	 * @param string	$sortorder	        Sort order
	 * @param int		$limit		        Limit for list
	 * @param int		$page		        Page number
	 * @param string   	$thirdparty_ids	    Thirdparty ids to filter contacts of. {@example '1' or '1,2,3'} {@pattern /^[0-9,]*$/i}
	 * @param string    $sqlfilters         Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param int       $includecount       Count and return also number of elements the contact is used as a link for
	 * @return array                        Array of contact objects
     *
	 * @throws RestException
     */
    public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $thirdparty_ids = '', $sqlfilters = '', $includecount = 0)
    {
		global $db, $conf;

		$obj_ret = array();

		if (!MounirApiAccess::$user->rights->societe->contact->lire)
		{
		    throw new RestException(401, 'No permission to read contacts');
		}

        // case of external user, $thirdparty_ids param is ignored and replaced by user's socid
		$socids = MounirApiAccess::$user->socid ? MounirApiAccess::$user->socid : $thirdparty_ids;

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if (!MounirApiAccess::$user->rights->societe->client->voir && !$socids)
			$search_sale = MounirApiAccess::$user->id;

		$sql = "SELECT t.rowid";
		$sql.= " FROM " . MAIN_DB_PREFIX . "socpeople as t";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX . "socpeople_extrafields as te ON te.fk_object = t.rowid";
		if ((!MounirApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) {
			// We need this table joined to the select in order to filter by sale
			$sql.= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
		}
		$sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON t.fk_soc = s.rowid";
		$sql.= ' WHERE t.entity IN (' . getEntity('socpeople') . ')';
		if ($socids) $sql.= " AND t.fk_soc IN (" . $socids . ")";

		if ((!MounirApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0)
			$sql.= " AND t.fk_soc = sc.fk_soc";
		if ($search_sale > 0)
			$sql.= " AND s.rowid = sc.fk_soc";  // Join for the needed table to filter by sale
		// Insert sale filter
		if ($search_sale > 0)
		{
			$sql .= " AND sc.fk_user = " . $search_sale;
		}
	    // Add sql filters
        if ($sqlfilters)
        {
            if (! MounirApi::_checkFilters($sqlfilters))
            {
                throw new RestException(503, 'Error when validating parameter sqlfilters '.$sqlfilters);
            }
	        $regexstring='\(([^:\'\(\)]+:[^:\'\(\)]+:[^:\(\)]+)\)';
            $sql.=" AND (".preg_replace_callback('/'.$regexstring.'/', 'MounirApi::_forge_criteria_callback', $sqlfilters).")";
        }

		$sql.= $db->order($sortfield, $sortorder);

		if ($limit)
		{
			if ($page < 0)
			{
				$page = 0;
			}
			$offset = $limit * $page;

			$sql.= $db->plimit($limit + 1, $offset);
		}
		$result = $db->query($sql);
		if ($result)
		{
			$num = $db->num_rows($result);
			$min = min($num, ($limit <= 0 ? $num : $limit));
            $i = 0;
			while ($i < $min)
			{
				$obj = $db->fetch_object($result);
				$contact_static = new Contact($db);
				if ($contact_static->fetch($obj->rowid))
				{
		            if ($includecount)
		            {
		                $contact_static->load_ref_elements();
		            }
					$obj_ret[] = $this->_cleanObjectDatas($contact_static);
				}

				$i++;
			}
		}
		else {
			throw new RestException(503, 'Error when retrieve contacts : ' . $sql);
		}
		if (!count($obj_ret))
		{
			throw new RestException(404, 'Contacts not found');
		}
		return $obj_ret;
	}

	/**
	 * Create contact object
	 *
	 * @param   array   $request_data   Request datas
	 * @return  int     ID of contact
	 */
    public function post($request_data = null)
    {
		if (!MounirApiAccess::$user->rights->societe->contact->creer)
		{
			throw new RestException(401, 'No permission to create/update contacts');
		}
		// Check mandatory fields
		$result = $this->_validate($request_data);

		foreach ($request_data as $field => $value)
		{
			$this->contact->$field = $value;
		}
		if ($this->contact->create(MounirApiAccess::$user) < 0) {
		    throw new RestException(500, "Error creating contact", array_merge(array($this->contact->error), $this->contact->errors));
		}
		return $this->contact->id;
	}

	/**
	 * Update contact
	 *
	 * @param int   $id             Id of contact to update
	 * @param array $request_data   Datas
	 * @return int
	 */
    public function put($id, $request_data = null)
    {
		if (!MounirApiAccess::$user->rights->societe->contact->creer)
		{
			throw new RestException(401, 'No permission to create/update contacts');
		}

		$result = $this->contact->fetch($id);
		if (!$result)
		{
			throw new RestException(404, 'Contact not found');
		}

		if (!MounirApi::_checkAccessToResource('contact', $this->contact->id, 'socpeople&societe'))
		{
			throw new RestException(401, 'Access not allowed for login ' . MounirApiAccess::$user->login);
		}

		foreach ($request_data as $field => $value)
		{
            if ($field == 'id') continue;
		    $this->contact->$field = $value;
		}

		if ($this->contact->update($id, MounirApiAccess::$user, 1, '', '', 'update'))
			return $this->get($id);

		return false;
	}

	/**
	 * Delete contact
	 *
	 * @param   int     $id Contact ID
	 * @return  integer
	 */
    public function delete($id)
    {
		if (!MounirApiAccess::$user->rights->societe->contact->supprimer)
		{
			throw new RestException(401, 'No permission to delete contacts');
		}
		$result = $this->contact->fetch($id);
		if (!$result)
		{
			throw new RestException(404, 'Contact not found');
		}

		if (!MounirApi::_checkAccessToResource('contact', $this->contact->id, 'socpeople&societe'))
		{
			throw new RestException(401, 'Access not allowed for login ' . MounirApiAccess::$user->login);
		}

		return $this->contact->delete($id);
	}

	/**
	 * Create an user account object from contact (external user)
	 *
	 * @param   int   	$id   Id of contact
	 * @param   array   $request_data   Request datas
	 * @return  int     ID of user
	 *
	 * @url	POST {id}/createUser
	 */
    public function createUser($id, $request_data = null)
    {
	    //if (!MounirApiAccess::$user->rights->user->user->creer) {
	    //throw new RestException(401);
	    //}

	    if (!isset($request_data["login"]))
    		throw new RestException(400, "login field missing");
	    if (!isset($request_data["password"]))
	    	throw new RestException(400, "password field missing");

	    if (!MounirApiAccess::$user->rights->societe->contact->lire) {
	        throw new RestException(401, 'No permission to read contacts');
	    }
	    if (!MounirApiAccess::$user->rights->user->user->creer) {
	        throw new RestException(401, 'No permission to create user');
	    }

	    $contact = new Contact($this->db);
	    $contact->fetch($id);
	    if ($contact->id <= 0) {
	        throw new RestException(404, 'Contact not found');
	    }

	    if (!MounirApi::_checkAccessToResource('contact', $contact->id, 'socpeople&societe')) {
	        throw new RestException(401, 'Access not allowed for login ' . MounirApiAccess::$user->login);
	    }

	    // Check mandatory fields
	    $login = $request_data["login"];
	    $password = $request_data["password"];
	    $useraccount = new User($this->db);
	    $result = $useraccount->create_from_contact($contact, $login, $password);
	    if ($result <= 0) {
	        throw new RestException(500, "User not created");
	    }
	    // password parameter not used in create_from_contact
	    $useraccount->setPassword($useraccount, $password);

	    return $result;
	}

    /**
     * Get categories for a contact
     *
     * @param int		$id         ID of contact
     * @param string	$sortfield	Sort field
     * @param string	$sortorder	Sort order
     * @param int		$limit		Limit for list
     * @param int		$page		Page number
     *
     * @return mixed
     *
     * @url GET {id}/categories
     */
    public function getCategories($id, $sortfield = "s.rowid", $sortorder = 'ASC', $limit = 0, $page = 0)
	{
		if (! MounirApiAccess::$user->rights->categorie->lire) {
			throw new RestException(401);
		}

		$categories = new Categorie($this->db);

		$result = $categories->getListForItem($id, 'contact', $sortfield, $sortorder, $limit, $page);

		if (empty($result)) {
			throw new RestException(404, 'No category found');
		}

		if ($result < 0) {
			throw new RestException(503, 'Error when retrieve category list : '.$categories->error);
		}

		return $result;
    }


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
    /**
     * Clean sensible object datas
     *
     * @param   Object  $object     Object to clean
     * @return  array               Array of cleaned object properties
     */
    protected function _cleanObjectDatas($object)
    {
        // phpcs:enable
        $object = parent::_cleanObjectDatas($object);

        unset($object->total_ht);
        unset($object->total_tva);
        unset($object->total_localtax1);
        unset($object->total_localtax2);
        unset($object->total_ttc);

        unset($object->note);
        unset($object->lines);
        unset($object->thirdparty);

        return $object;
    }

    /**
     * Validate fields before create or update object
     *
     * @param   array|null     $data   Data to validate
     * @return  array
     * @throws  RestException
     */
    private function _validate($data)
    {
        $contact = array();
        foreach (Contacts::$FIELDS as $field) {
            if (!isset($data[$field])) {
                throw new RestException(400, "$field field missing");
            }
            $contact[$field] = $data[$field];
        }

        return $contact;
    }
}
