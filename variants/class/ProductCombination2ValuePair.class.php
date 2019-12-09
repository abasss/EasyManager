<?php


class ProductCombination2ValuePair
{
	/**
	 * Database handler
	 * @var DoliDB
	 */
	private $db;

	/**
	 * Combination 2 value pair id
	 * @var int
	 */
	public $id;

	/**
	 * Product combination id
	 * @var int
	 */
	public $fk_prod_combination;

	/**
	 * Product attribute id
	 * @var int
	 */
	public $fk_prod_attr;

	/**
	 * Product attribute value id
	 * @var int
	 */
	public $fk_prod_attr_val;

    /**
     * Constructor
     *
     * @param   DoliDB $db     Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

	/**
	 * Translates this class to a human-readable string
	 *
	 * @return string
	 */
	public function __toString()
	{
		require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
		require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';

		$prodattr = new ProductAttribute($this->db);
		$prodattrval = new ProductAttributeValue($this->db);

		$prodattr->fetch($this->fk_prod_attr);
		$prodattrval->fetch($this->fk_prod_attr_val);

		return $prodattr->label.': '.$prodattrval->value;
	}

	/**
	 * Creates a product combination 2 value pair
	 * @return int <0 KO, >0 OK
	 */
	public function create()
	{
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_attribute_combination2val
		(fk_prod_combination, fk_prod_attr, fk_prod_attr_val)
		VALUES(".(int) $this->fk_prod_combination.", ".(int) $this->fk_prod_attr.", ".(int) $this->fk_prod_attr_val.")";

		$query = $this->db->query($sql);

		if ($query) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'product_attribute_combination2val');

			return 1;
		}

		return -1;
	}

	/**
	 * Retrieves a product combination 2 value pair from its rowid
	 *
	 * @param int $fk_combination Fk combination to search
	 * @return int|ProductCombination2ValuePair[] -1 if KO
	 */
	public function fetchByFkCombination($fk_combination)
	{
		$sql = "SELECT
        c.rowid,
        c2v.fk_prod_attr_val,
        c2v.fk_prod_attr,
        c2v.fk_prod_combination
        FROM ".MAIN_DB_PREFIX."product_attribute c LEFT JOIN ".MAIN_DB_PREFIX."product_attribute_combination2val c2v ON c.rowid = c2v.fk_prod_attr
        WHERE c2v.fk_prod_combination = ".(int) $fk_combination;

		$sql .= $this->db->order('c.rang', 'asc');

		$query = $this->db->query($sql);

		if (!$query) {
			return -1;
		}

		$return = array();

		while ($result = $this->db->fetch_object($query)) {
			$tmp = new ProductCombination2ValuePair($this->db);
			$tmp->fk_prod_attr_val = $result->fk_prod_attr_val;
			$tmp->fk_prod_attr = $result->fk_prod_attr;
			$tmp->fk_prod_combination = $result->fk_prod_combination;
			$tmp->id = $result->rowid;

			$return[] = $tmp;
		}

		return $return;
	}

	/**
	 * Deletes a product combination 2 value pair
	 *
	 * @param int $fk_combination Rowid of the combination
	 * @return int >0 OK <0 KO
	 */
	public function deleteByFkCombination($fk_combination)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."product_attribute_combination2val WHERE fk_prod_combination = ".(int) $fk_combination;

		if ($this->db->query($sql)) {
			return 1;
		}

		return -1;
	}
}
