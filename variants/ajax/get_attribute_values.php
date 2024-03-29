<?php


if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';

header('Content-Type: application/json');

$id = GETPOST('id');

if (!$id) {
print json_encode(array(
		'error' => 'ID not set'
	));
	exit();
}

$prodattr = new ProductAttribute($db);

if ($prodattr->fetch($id) < 0) {
print json_encode(array(
		'error' => 'Attribute not found'
	));
	exit();
}

$prodattrval = new ProductAttributeValue($db);

$res = $prodattrval->fetchAllByProductAttribute($id);

if ($res == -1) {
print json_encode(array(
		'error' => 'Internal error'
	));
	exit();
}

print json_encode($res);
