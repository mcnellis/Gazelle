<?
if (!check_perms('admin_manage_rules')) {
	error(403);
}

if ($_GET['do'] == 'edit_sections') {
	View::show_header('Rules Sections');
	$Sections = Rules::get_sections();
	RulesView::render_edit_sections($Sections);
} elseif ($_GET['do'] == 'edit_rules') {
	assert_numbers($_GET, array('section_id'), 'You must specify a section id');
	$Section = Rules::get_section($_GET['section_id']);
	$Rules = Rules::get_rules($_GET['section_id']);
	View::show_header($Section['SectionName'].' :: Site Rules');
	RulesView::render_edit_rules($Section, $Rules);
}

View::show_footer();
