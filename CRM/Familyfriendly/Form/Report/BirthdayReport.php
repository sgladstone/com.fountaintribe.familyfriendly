<?php

class CRM_Familyfriendly_Form_Report_BirthdayReport extends CRM_Report_Form {


	protected $_summary = NULL;
	protected $_emailField_a = FALSE;
	protected $_emailField_b = FALSE;
	protected $_customGroupExtends = array(
			'Contact', 'Individual');
	public $_drilldownReport = array('contact/detail' => 'Link to Detail Report');

	function __construct() {

		$contact_type = CRM_Contact_BAO_ContactType::getSelectElements(FALSE, TRUE, '_');

		$age_choices_array =  array( 0 => 'Infant');
		$max_age_filter = 150;
		for ($i = 1; $i <= $max_age_filter; $i++){
			$age_choices_array[$i] = $i;

		}

		// TODO: If HebrewCalendar extension is enabled, then get Hebrew birthday. 
		
		if( 1 == 0  ){
			$disable_jewish_features = FALSE;
		 	$tmp_all_result_columns['Hebrew Birth Date'] =  'birth_date_hebrew';
		 	$tmp_all_result_columns['Hebrew Birth Date Transliterated'] = 'birth_date_hebrew_trans';
		}else{

	 		 $disable_jewish_features = TRUE;
	   
		}
		

		//
		$cur_domain_id = "-1";
			
		$result = civicrm_api3('Domain', 'get', array(
				'sequential' => 1,
				'current_domain' => "",
		));
			
		if( $result['is_error'] == 0 ){
			$cur_domain_id = $result['id'];
		
		}
		
		// get membership ids and org contact ids.
		$mem_ids = array();
		$org_ids = array();
		$api_result = civicrm_api3('MembershipType', 'get', array(
				'sequential' => 1,
				'is_active' => 1,
				'domain_id' =>  $cur_domain_id ,
				'options' => array('sort' => "name"),
		));
		
		if( $api_result['is_error'] == 0 ){
			$tmp_api_values = $api_result['values'];
			foreach($tmp_api_values as $cur){
		
				$tmp_id = $cur['id'];
				$mem_ids[$tmp_id] = $cur['name'];
					
				$org_id = $cur['member_of_contact_id'];
				// get display name of org
				$result = civicrm_api3('Contact', 'getsingle', array(
						'sequential' => 1,
						'id' => $org_id ,
				));
				$org_ids[$org_id] = $result['display_name'];
		
					
			}
		
		}	

	 $gender_options =  CRM_Contact_BAO_Contact::buildOptions('gender_id');
	  
	 $this->_columns = array(
	 		'civicrm_contact' =>
	 		array(
	 				'dao' => 'CRM_Contact_DAO_Contact',
	 				'fields' =>
	 				array(
	 						'sort_name_a' =>
	 						array('title' => ts('Name'),
	 								'name' => 'sort_name',
	 								'required' => TRUE,
	 						),
	 						'id' =>
	 						array(
	 								'no_display' => TRUE,
	 								'required' => TRUE,
	 						),
	 						'contact_birthdate_formatted' =>
	 						array(
	 								'title' => ts('Birthday (formatted)'),
	 								'dbAlias' => " CONCAT( monthname(birth_date) , ' ',  day(birth_date))",
	 								'default' => TRUE,
	 						),
	 						'occasion_date' =>
	 						array(
	 								'title' => ts('Birthday (sortable)'),
	 								'dbAlias' => " date_format(birth_date, '%m-%d' ) " ,
	 						),
	 						'contact_birthyear' =>
	 						array(
	 								'title' => ts('Birth Year'),
	 								'dbAlias' => " year(birth_date) ",
	 								'default' => TRUE,
	 						),
	 						'birth_date_hebrew' =>
	 						array(
	 								'title' => ts('Hebrew Birth Date'),
	 								'dbAlias' => " '' ",
	 								'no_display' => $disable_jewish_features ,
	 						),
	 						'birth_date_hebrew_trans' =>
	 						array(
	 								'title' => ts('Hebrew Birth Date - Transliterated'),
	 								'dbAlias' => " '' ",
	 								'no_display' => $disable_jewish_features ,
	 						),

	 						'contact_age' =>
	 						array(
	 								'title' => ts('Age (today)'),
	 								'dbAlias' => " TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) ",

	 						),
	 						'contact_next_age' =>
	 						array(
	 								'title' => ts('Age on Next Birthday'),
	 								'dbAlias' => " TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) + 1 ",
	 								'default' => TRUE,
	 						),
	 						 
	 						'gender_id' =>
	 						array('title' => ts('Gender'),
	 						),
	 						'contact_occasion_type' =>
	 						array(
	 								'title' => ts('Occasion Type'),
	 								'dbAlias' => " 'Birthday' ",
	 								'default' => TRUE,
	 						),
	 				),
	 				'filters' =>
	 				array(
	 						'sort_name_a' =>
	 						array('title' => ts('Contact'),
	 								'name' => 'sort_name',
	 								'operator' => 'like',
	 								'type' => CRM_Report_Form::OP_STRING,
	 						),
	 						'occasion_date' =>
	 						array(
	 								'dbAlias' => " concat( YEAR( CURDATE()),   date_format(birth_date, '%m%d' ) ) ",
	 								'title' => ts('Date Range'),
	 								'operatorType' => CRM_Report_Form::OP_DATE,
	 								'type' => CRM_Utils_Type::T_DATE
	 						),
	 						'contact_age' =>
	 						array('title' => ts('Age'),
	 								'dbAlias' => " TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) ",
	 								'operatorType' => CRM_Report_Form::OP_MULTISELECT,
	 								'type' => CRM_Utils_Type::T_INT,
	 								'options' => $age_choices_array,
	 						),
	 						'gender_id' =>
	 						array(
	 								'name' => 'gender_id',
	 								'title' => ts('Gender'),
	 								'operatorType' => CRM_Report_Form::OP_MULTISELECT,
	 								'options' => $gender_options,
	 						),
	 						'membership_org' =>
	 						array( 'title' => ts('Membership Organization'),
	 								'name' => " membership_org ",
	 								'membership_org' => TRUE,
	 								'operatorType' => CRM_Report_Form::OP_MULTISELECT,
	 								'type' => CRM_Utils_Type::T_INT,
	 								'options' => $org_ids,
	 						),

	 						'membership_type' =>
	 						array( 'title' => ts('Membership Type'),
	 								'name' => " membership_type ",
	 								'membership_type' => TRUE,
	 								'operatorType' => CRM_Report_Form::OP_MULTISELECT,
	 								'type' => CRM_Utils_Type::T_INT,
	 								'options' => $mem_ids,
	 						),



	 				),
	 				'order_bys' =>
	 				array(
	 						'sort_name' =>
	 						array('title' => ts('Last Name, First Name'),
	 						),
	 						'gender_id' =>
	 						array(
	 								'name' => 'gender_id',
	 								'title' => ts('Gender'),
	 						),
	 						'birth_date' =>
	 						array(
	 								'dbAlias' => " date_format(birth_date, '%m%d' )",
	 								'title' => ts('Birth Date (mm-dd)'),
	 						),
	 						'birth_month' =>
	 						array(
	 								'dbAlias' => " date_format(birth_date, '%m' )",
	 								'title' => ts('Birth Month'),
	 						),
	 						'contact_age' =>
	 						array(
	 								'dbAlias' => "  TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) ",
	 								'title' => ts('Age'),
	 						),
	 				),
	 				'grouping' => 'conact_a_fields',
	 		),
	 		 
	 		'civicrm_address' =>
	 		array(
	 				'dao' => 'CRM_Core_DAO_Address',
	 				'filters' =>
	 				array(


	 				),
	 				'grouping' => 'contact-fields',
	 		),
	 		'civicrm_group' =>
	 		array(
	 				'dao' => 'CRM_Contact_DAO_Group',
	 				'alias' => 'cgroup',
	 				'filters' =>
	 				array(
	 						'gid' =>
	 						array(
	 								'name' => 'group_id',
	 								'title' => ts('Group'),
	 								'operatorType' => CRM_Report_Form::OP_MULTISELECT,
	 								'group' => TRUE,
	 								'type' => CRM_Utils_Type::T_INT,
	 								'options' => CRM_Core_PseudoConstant::nestedGroup(),
	 						),
	 				),
	 		),
	 );

	 $this->_tagFilter = TRUE;
	 parent::__construct();
	}

	function preProcess() {
		parent::preProcess();
	}

	function select() {
		$select = $this->_columnHeaders = array();
		foreach ($this->_columns as $tableName => $table) {
			if (array_key_exists('fields', $table)) {
				foreach ($table['fields'] as $fieldName => $field) {
					if (CRM_Utils_Array::value('required', $field) ||
							CRM_Utils_Array::value($fieldName, $this->_params['fields'])
							) {

								if ($fieldName == 'email_a') {
									$this->_emailField_a = TRUE;
								}
								if ($fieldName == 'email_b') {
									$this->_emailField_b = TRUE;
								}
								$select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
								$this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
								$this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
							}
				}
			}
		}

		$this->_select = "SELECT " . implode(', ', $select) . " ";
	}

	function from() {
		$this->_from = "
		FROM civicrm_contact {$this->_aliases['civicrm_contact']}


		 

		{$this->_aclFrom} ";

		if (!empty($this->_params['country_id_value']) ||
				!empty($this->_params['state_province_id_value'])
				) {
					$this->_from .= "
					INNER  JOIN civicrm_address {$this->_aliases['civicrm_address']}
					ON (( {$this->_aliases['civicrm_address']}.contact_id =
					{$this->_aliases['civicrm_contact']}.id  OR
					{$this->_aliases['civicrm_address']}.contact_id =
					{$this->_aliases['civicrm_contact_b']}.id ) AND
					{$this->_aliases['civicrm_address']}.is_primary = 1 ) ";
				}



				// include Email Field
				if ($this->_emailField_a) {
					$this->_from .= "
					LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
					ON ( {$this->_aliases['civicrm_contact']}.id =
					{$this->_aliases['civicrm_email']}.contact_id AND
					{$this->_aliases['civicrm_email']}.is_primary = 1 )";
				}
				if ($this->_emailField_b) {
					$this->_from .= "
					LEFT JOIN civicrm_email {$this->_aliases['civicrm_email_b']}
					ON ( {$this->_aliases['civicrm_contact_b']}.id =
					{$this->_aliases['civicrm_email_b']}.contact_id AND
					{$this->_aliases['civicrm_email_b']}.is_primary = 1 )";
				}
	}

	function where() {
		$whereClauses = $havingClauses = array();

		foreach ($this->_columns as $tableName => $table) {
			if (array_key_exists('filters', $table)) {
				foreach ($table['filters'] as $fieldName => $field) {

					$clause = NULL;
					if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
						$relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
						$from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
						$to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

						$clause = $this->dateClause($field['dbAlias'], $relative, $from, $to, $field['type']);
					}
					else {
						$op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
						if ($op) {

							if ($tableName == 'civicrm_relationship_type' &&
									($fieldName == 'contact_type_a' || $fieldName == 'contact_type_b')
									) {
										$cTypes = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
										$contactTypes = $contactSubTypes = array();
										if (!empty($cTypes)) {
											foreach ($cTypes as $ctype) {
												$getTypes = CRM_Utils_System::explode('_', $ctype, 2);
												if ($getTypes[1] && !in_array($getTypes[1], $contactSubTypes)) {
													$contactSubTypes[] = $getTypes[1];
												}
												elseif ($getTypes[0] && !in_array($getTypes[0], $contactTypes)) {
													$contactTypes[] = $getTypes[0];
												}
											}
										}

										if (!empty($contactTypes)) {
											$clause = $this->whereClause($field,
													$op,
													$contactTypes,
													CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
													CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
													);
										}

										if (!empty($contactSubTypes)) {
											if ($fieldName == 'contact_type_a') {
												$field['name'] = 'contact_sub_type_a';
											}
											else {
												$field['name'] = 'contact_sub_type_b';
											}
											$field['dbAlias'] = $field['alias'] . '.' . $field['name'];
											$subTypeClause = $this->whereClause($field,
													$op,
													$contactSubTypes,
													CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
													CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
													);
											if ($clause) {
												$clause = '(' . $clause . ' OR ' . $subTypeClause . ')';
											}
											else {
												$clause = $subTypeClause;
											}
										}
									}
									else {

										$clause = $this->whereClause($field,
												$op,
												CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
												CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
												CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
												);
									}
						}
					}

					if (!empty($clause)) {
						if (CRM_Utils_Array::value('having', $field)) {
							$havingClauses[] = $clause;
						}
						else {
							$whereClauses[] = $clause;
						}
					}
				}
			}
		}


		$whereClauses[] = " is_deceased <> 1 ";
		$whereClauses[] = " is_deleted <> 1 ";
		$whereClauses[] = " contact_type = 'Individual' ";
		$whereClauses[] = " birth_date IS NOT NULL ";
		 
		// require_once('utils/CustomSearchTools.php');
		// $searchTools = new CustomSearchTools();

		// $contact_field_name = "t1.underlying_contact_id";

		// $searchTools->updateWhereClauseForMemberships( $membership_types_of_contact,  $membership_orgs_of_contact, $contact_field_name,  $clauses   ) ;

		if (empty($whereClauses)) {
			$this->_where = 'WHERE ( 1 ) ';
			$this->_having = '';
		}
		else {
			$this->_where = 'WHERE ' . implode(' AND ', $whereClauses);
		}



		if ($this->_aclWhere) {
			//   $this->_where .= " AND {$this->_aclWhere} ";
		}

		//  print "<br>debug: ".$this->_where;

		if (!empty($havingClauses)) {
			// use this clause to construct group by clause.
			$this->_having = 'HAVING ' . implode(' AND ', $havingClauses);
		}
	}

	function statistics(&$rows) {
		$statistics = parent::statistics($rows);

		$isStatusFilter = FALSE;
		$relStatus = NULL;

		if (CRM_Utils_Array::value('filters', $statistics)) {
			foreach ($statistics['filters'] as $id => $value) {
				//for displaying relationship type filter

			}
		}

		return $statistics;
	}

	function groupBy() {
		$this->_groupBy = " ";
		$groupBy = array();



	}

	function BAK_orderBy() {
		$this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name ";
	}

	function postProcess() {
		$this->beginPostProcess();

		$this->relationType = NULL;
		$relType = array();
		if (CRM_Utils_Array::value('relationship_type_id_value', $this->_params)) {
			$relType = explode('_', $this->_params['relationship_type_id_value']);

			$this->relationType = $relType[1] . '_' . $relType[2];
			$this->_params['relationship_type_id_value'] = intval($relType[0]);
		}

		//$this->buildACLClause(array($this->_aliases['civicrm_contact'], $this->_aliases['civicrm_contact_b']));
		$this->buildACLClause(array($this->_aliases['civicrm_contact']));
		$sql = $this->buildQuery();
		$this->buildRows($sql, $rows);

		$this->formatDisplay($rows);
		$this->doTemplateAssignment($rows);

		if (!empty($relType)) {
			// store its old value, CRM-5837
			$this->_params['relationship_type_id_value'] = implode('_', $relType);
		}
		$this->endPostProcess($rows);
	}

	function alterDisplay(&$rows) {
		// custom code to alter rows
		$entryFound = FALSE;

		$genders = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id', array('localize' => TRUE));

		// TODO: Check if Hebrew Calendar extension is enabled. 
		$tmp_show_jewish_features = false;
		
		foreach ($rows as $rowNum => $row) {
			// print "<br><br>";
			// print_r( $row);
			//civicrm_contact_birth_date_hebrew
			if( $tmp_show_jewish_features ){
				if( array_key_exists('civicrm_contact_birth_date_hebrew', $row) ||  array_key_exists('civicrm_contact_birth_date_hebrew_trans', $row) ) {

					require_once 'CRM/Hebrew/HebrewDates.php';

					$tmpHebCal = new HebrewCalendar();

					$hebrew_data = $tmpHebCal::retrieve_hebrew_demographic_dates( $row['civicrm_contact_id']);
					//print "<br>Hebrew data: ";
					//print_r($hebrew_data );
					$heb_date_of_birth =  $hebrew_data['hebrew_date_of_birth'];
					$heb_date_of_birth_hebrew =  $hebrew_data['hebrew_date_of_birth_hebrew'];
					$bar_bat_mitzvah_label = $hebrew_data['bar_bat_mitzvah_label'] ;
					$earliest_bar_bat_mitzvah_date = $hebrew_data['earliest_bar_bat_mitzvah_date'];


					$rows[$rowNum]['civicrm_contact_birth_date_hebrew_trans'] =  $heb_date_of_birth;
					$rows[$rowNum]['civicrm_contact_birth_date_hebrew'] = $heb_date_of_birth_hebrew;
				}
				$entryFound = TRUE;
			}

			if (array_key_exists('civicrm_contact_gender_id', $row)) {
				if ($value = $row['civicrm_contact_gender_id']) {
					$rows[$rowNum]['civicrm_contact_gender_id'] = $genders[$value];
				}
				$entryFound = TRUE;
			}


			// handle country
			if (array_key_exists('civicrm_address_country_id', $row)) {
				if ($value = $row['civicrm_address_country_id']) {
					$rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
				}
				$entryFound = TRUE;
			}

			if (array_key_exists('civicrm_address_state_province_id', $row)) {
				if ($value = $row['civicrm_address_state_province_id']) {
					$rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
				}
				$entryFound = TRUE;
			}

			if (array_key_exists('civicrm_contact_sort_name_a', $row) &&
					array_key_exists('civicrm_contact_id', $row)
					) {
						$url = CRM_Report_Utils_Report::getNextUrl('contact/detail',
								'reset=1&force=1&id_op=eq&id_value=' . $row['civicrm_contact_id'],
								$this->_absoluteUrl, $this->_id, $this->_drilldownReport
								);
						$rows[$rowNum]['civicrm_contact_sort_name_a_link'] = $url;
						$rows[$rowNum]['civicrm_contact_sort_name_a_hover'] = ts("View Contact details for this contact.");
						$entryFound = TRUE;
					}

					if (array_key_exists('civicrm_contact_b_sort_name_b', $row) &&
							array_key_exists('civicrm_contact_b_id', $row)
							) {
								$url = CRM_Report_Utils_Report::getNextUrl('contact/detail',
										'reset=1&force=1&id_op=eq&id_value=' . $row['civicrm_contact_b_id'],
										$this->_absoluteUrl, $this->_id, $this->_drilldownReport
										);
								$rows[$rowNum]['civicrm_contact_b_sort_name_b_link'] = $url;
								$rows[$rowNum]['civicrm_contact_b_sort_name_b_hover'] = ts("View Contact details for this contact.");
								$entryFound = TRUE;
							}

							// skip looking further in rows, if first row itself doesn't
							// have the column we need
							if (!$entryFound) {
								break;
							}
		}
	}
}