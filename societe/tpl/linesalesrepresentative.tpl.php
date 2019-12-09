<?php

if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

// Sale representative
print '<tr><td class="titlefield">';
print $langs->trans('SalesRepresentatives');
print '</td>';
print '<td>';

$listsalesrepresentatives=$object->getSalesRepresentatives($user);
$nbofsalesrepresentative=count($listsalesrepresentatives);
if ($nbofsalesrepresentative > 0)
{
	$userstatic=new User($db);
	foreach($listsalesrepresentatives as $val)
	{
		$userstatic->id=$val['id'];
		$userstatic->login=$val['login'];
		$userstatic->lastname=$val['lastname'];
		$userstatic->firstname=$val['firstname'];
		$userstatic->statut=$val['statut'];
		$userstatic->photo=$val['photo'];
		$userstatic->email=$val['email'];
		$userstatic->entity=$val['entity'];
		print $userstatic->getNomUrl(-1);
		print ' ';
	}
}
else print '<span class="opacitymedium">'.$langs->trans("NoSalesRepresentativeAffected").'</span>';
print '</td></tr>';
