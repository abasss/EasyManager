<?php

require "../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/task/modules_task.php';

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies'));

$id=GETPOST('id', 'int');
$idcomment=GETPOST('idcomment', 'int');
$ref=GETPOST("ref", 'alpha', 1);          // task ref
$objectref=GETPOST("taskref", 'alpha');    // task ref
$action=GETPOST('action', 'alpha');
$confirm=GETPOST('confirm', 'alpha');
$withproject=GETPOST('withproject', 'int');
$project_ref=GETPOST('project_ref', 'alpha');
$planned_workload=((GETPOST('planned_workloadhour', 'int')!='' || GETPOST('planned_workloadmin', 'int')!='') ? (GETPOST('planned_workloadhour', 'int')>0?GETPOST('planned_workloadhour', 'int')*3600:0) + (GETPOST('planned_workloadmin', 'int')>0?GETPOST('planned_workloadmin', 'int')*60:0) : '');

// Security check
$socid=0;
//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
if (! $user->rights->projet->lire) accessforbidden();

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('projecttaskcommentcard','globalcard'));

$object = new Task($db);
$extrafields = new ExtraFields($db);
$projectstatic = new Project($db);

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);

// include comment actions
include DOL_DOCUMENT_ROOT . '/core/actions_comments.inc.php';

// Retreive First Task ID of Project if withprojet is on to allow project prev next to work
if (! empty($project_ref) && ! empty($withproject))
{
	if ($projectstatic->fetch('', $project_ref) > 0)
	{
		$objectsarray=$object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($objectsarray) > 0)
		{
			$id=$objectsarray[0]->id;
		}
		else
		{
			header("Location: ".DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.(empty($mode)?'':'&mode='.$mode));
		}
	}
}

/*
 * View
*/


llxHeader('', $langs->trans("CommentPage"));

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);

if ($id > 0 || ! empty($ref))
{
	if ($object->fetch($id, $ref) > 0)
	{
		$result=$object->fetch_optionals();

		$result=$object->fetchComments();
		if ($result<0){
			setEventMessages($object->error, $object->errors, 'errors');
		}

		$result=$projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
		if(! empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($projectstatic, 'fetchComments') && empty($projectstatic->comments)) $projectstatic->fetchComments();

		$object->project = clone $projectstatic;

		$userWrite  = $projectstatic->restrictedProjectArea($user, 'write');

		if (! empty($withproject))
		{
			// Tabs for project
			$tab='tasks';
			$head=project_prepare_head($projectstatic);
			dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($projectstatic->public?'projectpub':'project'));

			$param=($mode=='mine'?'&mode=mine':'');

			// Project card

            $linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

            $morehtmlref='<div class="refidno">';
            // Title
            $morehtmlref.=$projectstatic->title;
            // Thirdparty
            if ($projectstatic->thirdparty->id > 0)
            {
                $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $projectstatic->thirdparty->getNomUrl(1, 'project');
            }
            $morehtmlref.='</div>';

            // Define a complementary filter for search of next/prev ref.
            if (! $user->rights->projet->all->lire)
            {
                $objectsListId = $projectstatic->getProjectsAuthorizedForUser($user, 0, 0);
                $projectstatic->next_prev_filter=" rowid in (".(count($objectsListId)?join(',', array_keys($objectsListId)):'0').")";
            }

            dol_banner_tab($projectstatic, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

            print '<div class="fichecenter">';
            print '<div class="fichehalfleft">';
            print '<div class="underbanner clearboth"></div>';

            print '<table class="border" width="100%">';

            // Visibility
            print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
            if ($projectstatic->public) print $langs->trans('SharedProject');
            else print $langs->trans('PrivateProject');
            print '</td></tr>';

            // Date start - end
            print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
            $start = dol_print_date($projectstatic->date_start, 'day');
            print ($start?$start:'?');
            $end = dol_print_date($projectstatic->date_end, 'day');
            print ' - ';
            print ($end?$end:'?');
            if ($projectstatic->hasDelay()) print img_warning("Late");
            print '</td></tr>';

            // Budget
            print '<tr><td>'.$langs->trans("Budget").'</td><td>';
            if (strcmp($projectstatic->budget_amount, '')) print price($projectstatic->budget_amount, '', $langs, 1, 0, 0, $conf->currency);
            print '</td></tr>';

            // Other attributes
            $cols = 2;
            //include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

            print '</table>';

            print '</div>';
            print '<div class="fichehalfright">';
            print '<div class="ficheaddleft">';
            print '<div class="underbanner clearboth"></div>';

            print '<table class="border" width="100%">';

            // Description
            print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
            print nl2br($projectstatic->description);
            print '</td></tr>';

            // Categories
            if($conf->categorie->enabled) {
                print '<tr><td class="valignmiddle">'.$langs->trans("Categories").'</td><td>';
                print $form->showCategories($projectstatic->id, 'project', 1);
                print "</td></tr>";
            }

            print '</table>';

            print '</div>';
            print '</div>';
            print '</div>';

            print '<div class="clearboth"></div>';

			dol_fiche_end();

			print '<br>';
		}

		$head=task_prepare_head($object);

		/*
		 * Fiche tache en mode visu
		 */
		$param=($withproject?'&withproject=1':'');
		$linkback=$withproject?'<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'&restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>':'';

		dol_fiche_head($head, 'task_comment', $langs->trans("Task"), -1, 'projecttask');

		if ($action == 'delete')
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$_GET["id"].'&withproject='.$withproject, $langs->trans("DeleteATask"), $langs->trans("ConfirmDeleteATask"), "confirm_delete");
		}

		if (! GETPOST('withproject') || empty($projectstatic->id))
		{
		    $projectsListId = $projectstatic->getProjectsAuthorizedForUser($user, 0, 1);
		    $object->next_prev_filter=" fk_projet in (".$projectsListId.")";
		}
		else $object->next_prev_filter=" fk_projet = ".$projectstatic->id;

		$morehtmlref='';

		// Project
		if (empty($withproject))
		{
		    $morehtmlref.='<div class="refidno">';
		    $morehtmlref.=$langs->trans("Project").': ';
		    $morehtmlref.=$projectstatic->getNomUrl(1);
		    $morehtmlref.='<br>';

		    // Third party
		    $morehtmlref.=$langs->trans("ThirdParty").': ';
		    if (!empty($projectstatic->thirdparty)) {
                   $morehtmlref.=$projectstatic->thirdparty->getNomUrl(1);
		    }
		    $morehtmlref.='</div>';
		}

		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

		print '<div class="fichecenter">';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Nb comments
		print '<td class="titlefield">'.$langs->trans("NbComments").'</td><td>';
		print $object->getNbComments();
		print '</td></tr>';

		// Other attributes
		$cols = 3;
		$parameters=array('socid'=>$socid);
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

		print '</table>';

		print '</div>';

		dol_fiche_end();


		// Include comment tpl view
		include DOL_DOCUMENT_ROOT . '/core/tpl/bloc_comment.tpl.php';
	}
}

// End of page
llxFooter();
$db->close();
