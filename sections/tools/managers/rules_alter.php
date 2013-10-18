<?
if (!check_perms('admin_manage_rules')) {
	error(403);
}

authorize();

switch ($_REQUEST['do']) {
	case 'edit_sections':
		if ($_POST['submit'] == 'Delete') { // Delete
			assert_numbers($_POST, array('id'), 'id must be numeric');
			Rules::delete_section($_POST['id']);
		} elseif($_POST['submit'] == 'Edit') { // Edit
			Rules::edit_section($_POST);
		} elseif($_POST['submit'] == 'Create') { // Create
			Rules::create_section($_POST);
		}

		// Go back
		header('Location: tools.php?action=rules&do=edit_sections');
		die();
	break;

	case 'edit_rules':
		if ($_POST['submit'] == 'Delete') { // Delete
			assert_numbers($_POST, array('id'), 'id must be numeric');
			Rules::delete_rule($_POST['id']);
		} elseif ($_POST['submit'] == 'Edit') {
			Rules::edit_rule($_POST);
		} elseif($_POST['submit'] == 'Create') { // Create
			Rules::create_rule($_POST);
		}

		// Go back
		header('Location: tools.php?action=rules&do=edit_rules&section_id='.$_GET['section_id']);
		die();
	break;

	default:
}
