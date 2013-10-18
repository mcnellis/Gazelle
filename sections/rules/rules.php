<?
$SectionSlug = $_GET['p'];
if (empty($SectionSlug)) {
	$RulesSectionID = Rules::get_default_section_id();
	$Section = Rules::get_section($RulesSectionID);
	header('Location: rules.php?p='.$Section['Slug']);
	die();
}
$Language = (empty($_GET['language']) ? 'English' : ucwords(strtolower($_GET['language'])));

// Fetch data
$Section = Rules::get_section_by_slug_and_language($SectionSlug, $Language);
$Rules = Rules::get_rules($Section['RulesSectionID']);

// Set page title and show header
$PageTitle = display_str($Section['SectionName']).' :: Rules';
if ($Section['Language'] != 'English') {
	$PageTitle = display_str($Section['Language']).' '.$PageTitle;
}
View::show_header($PageTitle, 'rules');
?>
<div class="thin">
	<div class="header">
		<h2><?=$Section['SectionName']?><?=($Section['Language'] == 'English' ? '' : ' ('.display_str($Section['Language']).')')?></h2>
	</div>
<?
// Render filter
if ($Section['HasFilter']) {
	RulesView::render_filter();
}

// Render table of contents
if ($Section['HasTableOfContents']) {
	RulesView::render_table_of_contents($Rules);
}

// Render rules
RulesView::render_rules($Rules);

// Render other sections navigation
$Sections = Rules::get_sections();
$GroupedSections = Rules::group_sections_by_name($Sections);
RulesView::render_other_sections($GroupedSections);

?>
</div>
<?

View::show_footer();
?>
