<?
class RulesView {

	public static function render_edit_sections($Sections) {
?>
		<div class="header">
			<h2>Site Rules</h2>
		</div>

		<p>
			The rules are listed in order of their position.
			Sections in a language other than English should use the same Section Name as a page in English.
		</p>

		<h3>Existing Rules Sections</h2>

		<div class="table">
			<div class="colhead">
				<div>Position</div>
				<div>Section Name</div>
				<div>Slug</div>
				<div>Language</div>
				<div>Has Filter</div>
				<div>Has <abbr title="Table of Contents">TOC<abbr></div>
				<div>Description</div>
				<div>Submit</div>
				<div>Edit Rules</div>
			</div>
<?
		$Row = 'b';
		foreach ($Sections as $Section) {
			$Row = ($Row === 'a' ? 'b' : 'a');
			$PositionDisabled = ($Section['Language'] === 'English' ? '' : 'disabled="disabled"');
			$SlugDisabled = ($Section['Language'] === 'English' ? '' : 'disabled="disabled"');
?>
			<form class="manage_form" name="rules_sections" action="" method="post">
				<div>
					<input type="text" size="1" name="position" value="<?=$Section['Position']?>" <?=$PositionDisabled?>/>
				</div>
				<div>
					<input type="text" size="20" name="section_name" value="<?=display_str($Section['SectionName'])?>" />
				</div>
				<div>
					<input type="text" size="10" name="slug" value="<?=display_str($Section['Slug'])?>" <?=$SlugDisabled?>/>
				</div>
				<div>
					<input type="text" size="16" name="language" value="<?=display_str($Section['Language'])?>" />
				</div>
				<div>
					<input type="checkbox" name="has_filter" value="1" <?=($Section['HasFilter'] ? 'checked="checked"' : '')?> />
				</div>
				<div>
					<input type="checkbox" name="has_toc" value="1" <?=($Section['HasTableOfContents'] ? 'checked="checked"' : '')?> />
				</div>
				<div>
					<input type="text" size="50" name="description" value="<?=display_str($Section['Description'])?>" />
				</div>
				<div>
					<input type="hidden" name="id" value="<?=$Section['RulesSectionID']?>" />
					<input type="submit" name="submit" value="Edit" />
					<input type="submit" name="submit" value="Delete" />
					<input type="hidden" name="action" value="rules_alter" />
					<input type="hidden" name="auth" value="<?=G::$LoggedUser['AuthKey']?>" />
				</div>
				<div>
					<a href="tools.php?action=rules&do=edit_rules&section_id=<?=$Section['RulesSectionID']?>">Edit Rules</a>
				</div>
			</form>
		<? } ?>
		</div>

		<h3>Add a new Rules Section</h3>
		<form class="add_form" name="rules_sections" action="" method="post">
			<table>
				<tr class="colhead">
					<td>Position</td>
					<td>Section Name</td>
					<td>Slug</td>
					<td>Language</td>
					<td>Has Filter</td>
					<td>Has <abbr title="Table of Contents">TOC</abbr></td>
					<td>Description</td>
					<td>Submit</td>
				</tr>
				<tr class="rowa">
					<td>
						<input type="text" size="1" name="position" />
					</td>
					<td>
						<input type="text" size="20" name="section_name" />
						<select style="display: none;">
		<? foreach ($Sections as $Section) { ?>
							<option value="<?=display_str($Section['SectionName'])?>"><?=display_str($Section['SectionName'])?></option>
		<? } ?>
						</select>
					</td>
					<td>
						<input type="text" size="10" name="slug" />
					</td>
					<td>
						<input type="text" size="16" name="language" />
					</td>
					<td>
						<input type="checkbox" value="1" name="has_filter" />
					</td>
					<td>
						<input type="checkbox" value="1" name="has_toc" />
					</td>
					<td>
						<input type="text" size="50" name="description" />
					</td>
					<td>
						<input type="submit" name="submit" value="Create" />
						<input type="hidden" name="action" value="rules_alter" />
						<input type="hidden" name="auth" value="<?=G::$LoggedUser['AuthKey']?>" />
					</td>
				</tr>
			</table>
		</form>
<?
	}

	public static function render_edit_rules($Section, $Rules) {
?>
		<a href="tools.php?action=rules&do=edit_sections">&larr; Back to Rules Sections</a>

		<div class="header">
			<h2><?=display_str($Section['SectionName'])?> (<?=display_str($Section['Language'])?>)</h2>
		</div>

		<h3>Add a new rule</h3>
		<form class="add_form" name="rules" action="" method="post">
			<table>
				<tr class="colhead">
					<td>Rule Number</td>
					<td>Is Heading</td>
					<td>Description</td>
					<td>Submit</td>
				</tr>
				<tr class="rowa">
					<td>
						<input type="text" size="1" name="rule_number" />
					</td>
					<td>
						<input type="checkbox" value="1" name="heading" />
					</td>
					<td>
						<textarea cols="150" name="description"></textarea>
					</td>
					<td>
						<input type="submit" name="submit" value="Create" />
						<input type="hidden" name="action" value="rules_alter" />
						<input type="hidden" name="section_id" value="<?=$Section['RulesSectionID']?>" />
						<input type="hidden" name="auth" value="<?=G::$LoggedUser['AuthKey']?>" />
					</td>
				</tr>
			</table>
		</form>

		<p><button onclick="window.location='tools.php?action=rules_alter&do=renumber&section_id=<?=$Section['RulesSectionID']?>&auth=<?=G::$LoggedUser['AuthKey']?>'">Automatically Re-number Rules</button></p>

		<div class="table">
			<div class="colhead">
				<div>Rule Number</div>
				<div>Is Heading</div>
				<div>Description</div>
				<div>Submit</div>
			</div>
		<?  foreach ($Rules as $Rule) { ?>
			<form class="manage_form" name="rules" action="" method="post">
				<div style="vertical-align: middle;">
					<input type="text" size="1" name="rule_number" value="<?=$Rule['RuleNumber']?>" />
				</div>
				<div style="text-align: center; vertical-align: middle;">
					<input type="checkbox" name="heading" value="1" <?=($Rule['Heading'] == 1 ? 'checked="checked"' : '')?> />
				</div>
				<div>
					<textarea type="text" cols="150" name="description"><?=$Rule['Description']?></textarea>
				</div>
				<div style="vertical-align: middle;">
					<input type="hidden" name="id" value="<?=$Rule['RuleID']?>" />
					<input type="submit" name="submit" value="Edit" />
					<input type="submit" name="submit" value="Delete" />
					<input type="hidden" name="action" value="rules_alter" />
					<input type="hidden" name="section_id" value="<?=$Section['RulesSectionID']?>" />
					<input type="hidden" name="auth" value="<?=G::$LoggedUser['AuthKey']?>" />
				</div>
			</form>
		<? } ?>
		</div>

<?
	}

	public static function render_other_sections($GroupedSections) {
		$RowClass = 'rowb';
?>
		<!-- Other Sections -->
		<h3 id="jump">Other Sections</h3>
		<div class="box pad rule_table" style="padding: 10px 10px 10px 20px;">
			<table width="100%">
				<tr class="colhead">
					<td style="width: 150px;">Category</td>
					<td style="width: 100px;">Languages</td>
					<td style="width: 400px;">Additional Information</td>
				</tr>
		<? foreach ($GroupedSections as $SectionName => $Sections) { ?>
				<tr class="<?=$RowClass?>">
					<td class="nobr">
			<? foreach ($Sections as $RulesSectionID => $Section) {
						if ($Section['Language'] != 'English') {
							continue;
						} ?>
						<a href="rules.php?p=<?=$Section['Slug']?>"><?=display_str($Section['SectionName'])?></a>
		 	<? } ?>
					</td>
					<td class="nobr">
			<? foreach ($Sections as $RulesSectionID => $Section) { ?>
						<a href="rules.php?p=<?=$Section['Slug']?>&language=<?=$Section['Language']?>"><?=display_str($Section['Language'])?></a>
		 	<? } ?>
					</td>
					<td class="nobr">
			<? foreach ($Sections as $RulesSectionID => $Section) {
						if ($Section['Language'] != 'English') {
							continue;
						} ?>
						<?=display_str($Section['Description'])?>
					</td>
		 	<? } ?>
				</tr>
<?  	$RowClass = ($RowClass == 'rowa' ? 'rowb' : 'rowa');
		} ?>
			</table>
		</div>
		<!-- END Other Sections -->
<?
	}

	public static function render_filter() {
?>
		<form class="search_form" name="rules" onsubmit="return false" action="">
			<input type="text" id="search_string" value="Filter (empty to reset)" />
			<span>Example: The search term <strong>FLAC</strong> returns all rules containing <strong>FLAC</strong>. The search term <strong>FLAC+trump</strong> returns all rules containing both <strong>FLAC</strong> and <strong>trump</strong>.</span>
		</form>
<?
	}

	public static function render_table_of_contents($Rules) {
		echo '<ol id="table_of_contents" class="before_rules box">';
		foreach ($Rules as $Rule) {
			if (!$Rule['Heading']) {
				continue;
			}

			$Rule['Description'] = display_str($Rule['Description']);
			$Depth = substr_count($Rule['RuleNumber'], '.');
			if ((int) $Rule['RuleNumber'] === 0) {
				echo '<li class="rule_depth_'.$Depth.'"><a href="#r'.$Rule['RuleNumber'].'">'.$Rule['Description'].'</li>';
			} else {
				echo '<li class="rule_depth_'.$Depth.'"><a href="#r'.$Rule['RuleNumber'].'"><span>'.$Rule['RuleNumber'].'</span> '.$Rule['Description'].'</a></li>';
			}
		}
		echo '</ol>';
?>
			
<?
	}

	public static function render_rules($Rules) {
		$Text = new Text();	
		echo '<ol id="actual_rules" class="box">';
		foreach ($Rules as $Rule) {
			$Depth = Rules::calculate_rule_depth($Rule['RuleNumber']);
			if (substr($Rule['RuleNumber'], 0, 1) === '0') { // Introduction, don't include in filter
				echo '<li id="r'.$Rule['RuleNumber'].'" class="before_rules rule_depth_'.$Depth.'">';
			} else {
				echo '<li id="r'.$Rule['RuleNumber'].'" class="rule_depth_'.$Depth.'">';
			}

			// The line will have an anchor on the rule number and the bbcode parsed rule description
			$LineContent = '';
			if (substr($Rule['RuleNumber'], 0, 1) !== '0') {
				$LineContent .= '<a href="#r'.$Rule['RuleNumber'].'">'.$Rule['RuleNumber'].'</a> ';
			}
			$LineContent .= $Text->full_format(display_str($Rule['Description']));

			if ($Rule['Heading']) {
				$Depth = substr_count($Rule['RuleNumber'], '.');
				if ($Depth === 0) {
					echo "<h4>$LineContent</h4>";
				} elseif ($Depth === 1) {
					echo "<h5>$LineContent</h5>";
				} else {
					echo "<h6>$LineContent</h6>";
				}
			} else {
				echo "<p>$LineContent</p>";
			}
			echo '</li>';

			// Special headings include extra code
			if ($Rule['Description'] == 'Required Ratio Table') {
				RulesView::render_required_ratio_table();
			} elseif( $Rule['Description'] == 'Allowed Clients') {
				RulesView::render_allowed_clients($Rule['RuleNumber']);
			} elseif( $Rule['Description'] == '[b]Trumping Overview[/b]') {
				RulesView::render_trump_overview();
			}
		}
		echo '</ol>';
	}
	
	public static function render_required_ratio_table() {
?>
		<li>
			<table class="ratio_table">
				<tr class="colhead">
					<td class="tooltip" title="These units are actually in base 2, not base 10. For example, there are 1,024 MB in 1 GB.">Amount Downloaded</span></td>
					<td>Required Ratio (0% seeded)</td>
					<td>Required Ratio (100% seeded)</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] < 5 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>0&ndash;5 GB</td>
					<td>0.00</td>
					<td>0.00</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 5 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 10 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>5&ndash;10 GB</td>
					<td>0.15</td>
					<td>0.00</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 10 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 20 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>10&ndash;20 GB</td>
					<td>0.20</td>
					<td>0.00</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 20 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 30 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>20&ndash;30 GB</td>
					<td>0.30</td>
					<td>0.05</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 30 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 40 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>30&ndash;40 GB</td>
					<td>0.40</td>
					<td>0.10</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 40 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 50 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>40&ndash;50 GB</td>

					<td>0.50</td>
					<td>0.20</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 50 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 60 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>50&ndash;60 GB</td>
					<td>0.60</td>
					<td>0.30</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 60 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 80 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>60&ndash;80 GB</td>
					<td>0.60</td>
					<td>0.40</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 80 * 1024 * 1024 * 1024 && G::$LoggedUser['BytesDownloaded'] < 100 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>80&ndash;100 GB</td>
					<td>0.60</td>
					<td>0.50</td>
				</tr>
				<tr class="row<?=(G::$LoggedUser['BytesDownloaded'] >= 100 * 1024 * 1024 * 1024) ? 'a' : 'b' ?>">
					<td>100+ GB</td>
					<td>0.60</td>
					<td>0.60</td>
				</tr>
			</table>
		</li>
<?
	}

	public static function render_allowed_clients($HeadingRuleNumber) {
		if (!$WhitelistedClients = G::$Cache->get_value('whitelisted_clients')) {
			G::$DB->query('
				SELECT vstring
				FROM xbt_client_whitelist
				WHERE vstring NOT LIKE \'//%\'
				ORDER BY vstring ASC');
			$WhitelistedClients = G::$DB->to_array(false, MYSQLI_NUM, false);
			G::$Cache->cache_value('whitelisted_clients', $WhitelistedClients, 604800);
		}

		$GroupedClients = array();
		foreach ($WhitelistedClients as $Client) {
			list($FullClientName) = $Client;

			// Group clients and make a list of the acceptable versions
			// by getting the position of the first number or '(' character
			// and splitting the string at that position into $ClientName $Version
			preg_match('/^\D*(?=\d)/', $FullClientName, $Match);
			$Candidates = array(strlen($Match[0]), strpos($FullClientName, '('));
			$VersionPosition = min(array_filter($Candidates, function($a){ return $a > 0; }));
			$ClientName = trim(substr($FullClientName, 0, $VersionPosition));
			$Version = substr($FullClientName, $VersionPosition);
			$GroupedClients[$ClientName][] = $Version;
		}

		$i = 1;
		foreach ($GroupedClients as $ClientName => $Versions) {
			$ThisRuleNumber = "$HeadingRuleNumber.$i";
			$Depth = Rules::calculate_rule_depth($ThisRuleNumber);
			echo '<li id="r'.$ThisRuleNumber.'" class="rule_depth_'.$Depth.'">';
			echo '<p><a href="#'.$ThisRuleNumber.'">'.$ThisRuleNumber.'</a> '.display_str($ClientName).'</p>';
			echo '<ul>';
			foreach ($Versions as $Version) {
				echo '<li>'.display_str($Version).'</li>';
			}
			echo '</ul>';
			echo '</li>';
			$i++;
		}
	}

	public static function render_trump_overview() {
		echo '<li><p style="text-align: center;">';
		echo '<img alt="Trumping Overview" src="http://192.168.1.109/static/common/trumpchart.png">';
		echo '<figcaption style="text-align:center; font-style: italic;">This chart is an overview of how the dupe and trump rules work.</figcaption>';
		echo '</p></li>';
	}
}
