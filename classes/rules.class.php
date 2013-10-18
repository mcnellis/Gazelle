<?
class Rules {
	/**
	 * Returns the RulesSectionID for the English section with Position 1
	 */
	public static function get_default_section_id() {
		$RulesSectionID = G::$Cache->get('default_rules_section_id');
		if (empty($RulesSectionID)) {
			G::$DB->query("
				SELECT RulesSectionID
				FROM rules_sections
				WHERE language = 'English'
				ORDER BY Position, SectionName ASC
				LIMIT 1
				");
			list($RulesSectionID) = G::$DB->next_record(MYSQLI_NUM);
			G::$Cache->cache_value('default_rules_section_id', $RulesSectionID, 0);
		}
		return $RulesSectionID;
	}

	/**
	 * Getter function for a single row from rules_sections
	 *
	 * @param int $RulesSectionID
	 * @return array with metadata about the section
	 */
	public static function get_section($RulesSectionID) {
		$RulesSection = G::$Cache->get('section_'.$RulesSectionID);
		if (empty($RulesSection)) {
			G::$DB->query("
				SELECT RulesSectionID, SectionName, Slug, Language, Position, Description, HasFilter, HasTableOfContents
				FROM rules_sections
				WHERE RulesSectionID = $RulesSectionID");
			$RulesSection = G::$DB->next_record(MYSQLI_ASSOC);
			G::$Cache->cache_value('section_'.$RulesSectionID, $RulesSection, 0);
		}
		return $RulesSection;
	}

	/**
	 * Getter function for all rows from rules_sections
	 *
	 * @return array of arrays with metadata about each section
	 */
	public static function get_sections() {
		$RulesSections = G::$Cache->get('rules_sections');
		if (empty($RulesSections)) {
			G::$DB->query('
				SELECT RulesSectionID, SectionName, Slug, Language, Position, Description, HasFilter, HasTableOfContents
				FROM rules_sections
				ORDER BY Position, SectionName ASC');
			$RulesSections = G::$DB->to_array(false, MYSQLI_ASSOC, false);
			G::$Cache->cache_value('rules_sections', $RulesSections, 0);
		}
		return $RulesSections;
	}

	/**
	 * Used to prepare the $GroupedSections argument for
	 * calling RulesView::render_other_sections($GroupedSections)
	 *
	 * @param array $RulesSections
	 * @return array of arrays with sections of all languages grouped
	 * together by section name
	 */
	public static function group_sections_by_name($RulesSections) {
		$GroupedSections = array();
		foreach ($RulesSections as $RulesSection) {
			$SectionName = $RulesSection['SectionName'];
			$RulesSectionID = $RulesSection['RulesSectionID'];
			$GroupedSections[$SectionName][$RulesSectionID] = $RulesSection;
		}
		return $GroupedSections;
	}

	/**
	 * Getter function to get the list of rules for a specific section
	 * ordered by rule number.
	 *
	 * @param array $RulesSectionID
	 * @return array of rules with all the data for each
	 */
	public static function get_rules($RulesSectionID) {
		$RulesSectionID = (int) $RulesSectionID;
		$CacheKey = 'section_'.$RulesSectionID.'_rules';
		$Rules = G::$Cache->get($CacheKey);
		if (empty($Rules)) {
			// ORDER BY solution from http://stackoverflow.com/a/8712381
			// The 4 in the REPEAT parameters means there are 4 sub-rules
			// supported. i.e. You may have a rule that is 1.1.1.1.1
			G::$DB->query("
				SELECT RuleID, RulesSectionID, RuleNumber, Heading, Description
				FROM rules
				WHERE RulesSectionID = $RulesSectionID
				ORDER BY
					INET_ATON(
						CONCAT(
							RuleNumber,
							REPEAT('.0', 4 - CHAR_LENGTH(RuleNumber) + CHAR_LENGTH(REPLACE(RuleNumber, '.', '')))
						)
					)
			");
			$Rules = G::$DB->to_array(false, MYSQLI_ASSOC);
			G::$Cache->cache_value($CacheKey, $RulesSections, 0);
		}
		return $Rules;
	}

	/**
	 * Getter function to get a section given the slug and language, used
	 * to lookup the section given the URL which contains the slug and language
	 *
	 * @param string $Slug
	 * @param string $Language
	 * @return array of data for the found Section, false if no section found
	 */
	public static function get_section_by_slug_and_language($Slug, $Language) {
		$Sections = self::get_sections();
		foreach ($Sections as $Section) {
			if ($Section['Slug'] == $Slug && $Section['Language'] == $Language) {
				return $Section;
			}
		}
		return false;
	}

	/**
	 * Helper function to calculate the depth of a given rule by counting
	 * the number of periods used in the rule
	 * e.g. Rule 1.1.1 has a depth of 2
	 *
	 * @param string $RuleNumber
	 * @return int representing the depth of the rule
	 */
	public static function calculate_rule_depth($RuleNumber) {
		return substr_count($RuleNumber, '.');
	}

	/**
	 * Helper function to renumber rules before a rule is created (Offset = 1),
	 * and after a rule is deleted (Offset = -1)
	 *
	 * @param int $RuleNumber
	 * @param string $RuleNumber
	 * @param int $Offset
	 * @return boolean True if the renumbering is successful
	 */
	public static function renumber($SectionID, $RuleNumber, $Offset) {
		// $Offset == 1 is renumber before create
		// $Offset == -1 is renumber after delete
		if ($Offset !== 1 && $Offset !== -1) {
			return false;
		}

		// See self::get_rules for notes about these INET_ATON clauses
		G::$DB->query("
			SELECT RuleID, RuleNumber, INET_ATON(
					CONCAT(
						RuleNumber,
						REPEAT('.0', 4 - CHAR_LENGTH(RuleNumber) + CHAR_LENGTH(REPLACE(RuleNumber, '.', '')))
					)
				) AS RuleNumberInt
			FROM rules
			WHERE RulesSectionID = $SectionID
			HAVING RuleNumberInt >=
					INET_ATON(
						CONCAT(
							'$RuleNumber',
							REPEAT('.0', 4 - CHAR_LENGTH('$RuleNumber') + CHAR_LENGTH(REPLACE('$RuleNumber', '.', '')))
						)
					)
			ORDER BY RuleNumberInt
			");
		$Rules = G::$DB->to_array(false, MYSQLI_ASSOC);
		$RuleDepth = self::calculate_rule_depth($RuleNumber);

		// Loop through the rules to recalculate rule numbers
		foreach ($Rules as $k => $Rule) {
			$ThisRuleDepth = self::calculate_rule_depth($Rule['RuleNumber']);

			// Stop if we get to a parent rule depth
			if ($ThisRuleDepth < $RuleDepth) {
				break;
			}

			// If we delete a rule that has subrules, don't do any renumbering.
			// If we tried to do renumbering we would end up with confusing behavior
			// by either deleting subrules or merging them into a different heading.
			if ($Offset === -1 && $k == 0 && $ThisRuleDepth != $RuleDepth) {
				return true;
			}

			$RuleParts = explode('.', $Rule['RuleNumber']);
			$RuleParts[$RuleDepth] += $Offset;
			$Rules[$k]['RuleNumber'] = implode('.', $RuleParts);
			$StopKey = $k;
		}

		// Don't allow the UPDATE loop to stop if we need to renumber all items
		if ($StopKey == count($Rules)-1) {
			$StopKey = -1;
		}

		// Update the records in reverse order to avoid
		// violating (RuleSectionID, RuleNumber) UNIQUE KEY
		if ($Offset === 1) {
			$Rules = array_reverse($Rules);
			$StopKey = ($StopKey != -1 ? (count($Rules) - $StopKey) : $StopKey);
		}

		foreach ($Rules as $k => $Rule) {
			G::$DB->query("UPDATE rules SET RuleNumber = $Rule[RuleNumber] WHERE RuleID = $Rule[RuleID]");
			if ($k == $StopKey) {
				break;
			}
		}
		return true;
	}

	/**
	 * Deletes a rule from the database and then renumbers the 
	 * remaining rules in that section
	 *
	 * @param int $RuleID
	 */
	public static function delete_rule($RuleID) {
		$RuleID = (int) $RuleID;
		G::$DB->query('SELECT RulesSectionID, RuleNumber FROM rules WHERE RuleID = '.$RuleID);
		list($SectionID, $RuleNumber) = G::$DB->next_record(MYSQLI_NUM);
		G::$DB->query('DELETE FROM rules WHERE RuleID='.$RuleID);
		self::renumber($SectionID, $RuleNumber, -1);
		G::$Cache->delete_value('section_'.$SectionID.'_rules');
	}

	/**
	 * Validates the form input and then creates the rule, renumbering the rules
	 * in that section if the new rule collides with a pre-existing rule number
	 *
	 * @param array $Fields of input data
	 */
	public static function create_rule($Fields) {
		$Val = new VALIDATE();
		$Val->SetFields('section_id', '1', 'number', 'The rules section id must be set', array('minlength'=>1));
		$Val->SetFields('rule_number', '1', 'string', 'The rule name must be set and has a max length of 16 characters', array('minlength'=>1,'maxlength'=>16));
		$Val->SetFields('description', '0', 'string', 'The description has a max length of 65,535 characters', array('maxlength'=>65535));
		$Err = $Val->ValidateForm($Fields); // Validate the form
		if ($Err) {
			error($Err);
		}

		$P = array();
		$P = db_array($Fields); // Sanitize the form

		// Set the heading attribute
		$P['heading'] = (empty($P['heading']) ? 0 : 1);

		// Check if this new rule addition collides with a pre-existing rule with the same rule number
		G::$DB->query("
			SELECT RuleID, RuleNumber FROM rules
			WHERE RulesSectionID = $P[section_id] AND RuleNumber = '$P[rule_number]'");
		$PreExistingRule = G::$DB->next_record(MYSQLI_ASSOC);
		if ($PreExistingRule) {
			self::renumber($P['section_id'], $P['rule_number'], 1);
		}

		G::$DB->query("
			INSERT INTO rules (RulesSectionID, RuleNumber, Heading, Description)
			VALUES ('$P[section_id]','$P[rule_number]',$P[heading],'$P[description]')");

		G::$Cache->delete_value('section_'.$P['section_id'].'_rules');
	}

	/**
	 * Validates the form input and then edits the rule
	 *
	 * @param array $Fields of input data
	 */
	public static function edit_rule($Fields) {
		$Val = new VALIDATE();
		$Val->SetFields('id', '1', 'number', 'The rule section id must be set', array('minlength'=>1));
		$Val->SetFields('section_id', '1', 'number', 'The rules section id must be set', array('minlength'=>1));
		$Val->SetFields('rule_number', '1', 'string', 'The rule name must be set and has a max length of 16 characters', array('minlength'=>1,'maxlength'=>16));
		$Val->SetFields('description', '0', 'string', 'The description has a max length of 65,535 characters', array('maxlength'=>65535));
		$Err = $Val->ValidateForm($Fields); // Validate the form
		if ($Err) {
			error($Err);
		}

		$P = array();
		$P = db_array($Fields); // Sanitize the form

		// Set the heading attribute
		$P['heading'] = (empty($P['heading']) ? 0 : 1);

		// Update the rule
		G::$DB->query("
			UPDATE rules
			SET
				RulesSectionID='$P[section_id]',
				RuleNumber='$P[rule_number]',
				Heading='$P[heading]',
				Description='$P[description]'
			WHERE RuleID=$P[id]");

		// Delete the rules cache for this rules section
		G::$DB->query('
			SELECT RulesSectionID
			FROM rules
			WHERE RuleID='.$P['id']);
		$Section = G::$DB->to_array(false, MYSQLI_ASSOC);
		G::$Cache->delete_value('section_'.$Section['RulesSectionID'].'_rules');
	}

	/**
	 * Deletes a section from the database. Foreign key constraint
	 * will also delete any rules belonging to this section.
	 *
	 * @param int $SectionID
	 */
	public static function delete_section($SectionID) {
		$SectionID = (int) $SectionID;
		G::$DB->query('DELETE FROM rules_sections WHERE RulesSectionID='.$SectionID);
		G::$Cache->delete_value('section_'.$SectionID);
		G::$Cache->delete_value('section_'.$SectionID.'_rules');
		G::$Cache->delete_value('rules_sections');
		G::$Cache->delete_value('default_rules_section_id');
	}

	/**
	 * Validates the form input and then creates the section
	 *
	 * @param array $Fields of input data
	 */
	public static function create_section($Fields) {
		$Val = new VALIDATE();
		$Val->SetFields('section_name', '1', 'string', 'The section name must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('language', '1', 'string', 'The language name must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('position', '0', 'number', 'The position must be number between 1 and 128', array('minlength'=>1,'maxlength'=>128));
		$Val->SetFields('slug', '0', 'string', 'The slug must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('description', '0', 'string', 'The description has a max length of 255 characters', array('maxlength'=>255));
		$Err = $Val->ValidateForm($Fields); // Validate the form
		if ($Err) {
			error($Err);
		}

		// Set has_filter and has_toc fields
		$Fields['has_filter'] = (empty($Fields['has_filter']) ? 0 : 1);
		$Fields['has_toc'] = (empty($Fields['has_toc']) ? 0 : 1);

		$P = array();
		$P = db_array($Fields); // Sanitize the form
		$P['language'] = ucwords(strtolower($P['language']));

		G::$DB->query("
			INSERT INTO rules_sections (SectionName, Language, Position, Description)
			VALUES ('$P[section_name]','$P[language]','$P[position]','$P[description]')");

		// Ensure that translated rules sections use the same position value and slug as the english version
		if ($P['language'] != 'English') {
			$InsertedID = G::$DB->inserted_id();
			G::$DB->query("
				UPDATE rules_sections a, rules_sections b
				SET a.Position=b.Position, a.Slug=b.Slug
				WHERE a.SectionName='$P[section_name]'
					AND b.SectionName='$P[section_name]'
					AND b.Language='English'
					AND a.Language!='English'");
		}

		// Delete the cache values so the cache gets updated next time this data is needed
		G::$Cache->delete_value('rules_sections');
		if( $P['position'] == 1 && $P['language'] == 'English' ) {
			G::$Cache->delete_value('default_rules_section_id');
		}
	}


	/**
	 * Validates the form input and then edits the section
	 *
	 * @param array $Fields of input data
	 */
	public static function edit_section($Fields) {
		$Val = new VALIDATE();
		$Val->SetFields('id', '1', 'number', 'The section id must be set', array('minlength'=>1));
		$Val->SetFields('section_name', '1', 'string', 'The section name must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('language', '1', 'string', 'The language name must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('position', '0', 'number', 'The position must be number between 1 and 128', array('minlength'=>1,'maxlength'=>128));
		$Val->SetFields('slug', '0', 'string', 'The slug must be set and has a max length of 32 characters', array('minlength'=>1,'maxlength'=>32));
		$Val->SetFields('description', '0', 'string', 'The description has a max length of 255 characters', array('maxlength'=>255));
		$Err = $Val->ValidateForm($Fields); // Validate the form
		if ($Err) {
			error($Err);
		}

		// Set has_filter and has_toc fields
		$Fields['has_filter'] = (empty($Fields['has_filter']) ? 0 : 1);
		$Fields['has_toc'] = (empty($Fields['has_toc']) ? 0 : 1);

		$P = array();
		$P = db_array($Fields); // Sanitize the form
		$P['language'] = ucwords(strtolower($P['language']));

		// Update the rules section
		G::$DB->query("
			UPDATE rules_sections
			SET
				SectionName='$P[section_name]',
				Language='$P[language]',
				Position='$P[position]',
				Slug='$P[slug]',
				HasFilter=$P[has_filter],
				HasTableOfContents=$P[has_toc],
				Description='$P[description]'
			WHERE RulesSectionID='$P[id]'");

		// Ensure that translated rules sections use the same position value and slug as the english version
		G::$DB->query("
			UPDATE rules_sections a, rules_sections b
			SET a.Position=b.Position, a.Slug=b.Slug
			WHERE a.SectionName='$P[section_name]'
				AND b.SectionName='$P[section_name]'
				AND b.Language='English'
				AND a.Language!='English'");

		G::$Cache->delete_value('section_'.$P['id']);
		G::$Cache->delete_value('rules_sections');
		G::$Cache->delete_value('default_rules_section_id');
	}
}
