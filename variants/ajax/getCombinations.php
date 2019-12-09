<?php


if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';

header('Content-Type: application/json');

$id = GETPOST('id', 'int');

if (!$id) {
print json_encode(array(
		'error' => 'ID not set'
	));
	exit();
}

$product = new Product($db);

if ($product->fetch($id) < 0) {
print json_encode(array(
		'error' => 'Product not found'
	));
}

$prodcomb = new ProductCombination($db);

echo json_encode($prodcomb->getUniqueAttributesAndValuesByFkProductParent($product->id));
