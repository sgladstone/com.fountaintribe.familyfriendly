<?php

/**
 * A custom contact search
 */
// 
class CRM_Familyfriendly_Form_Search_HouseholdCouplesSearch extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
	protected $_formValues;
	protected $_tableName = null;
	
	function __construct( &$formValues ) {
		$this->_formValues = $formValues;
	
		/**
		 * Define the columns for search result rows
		 */
		$tmp_columns =  array(
	
				ts('Contact/Adult A (sort)')=> 'sort_name' ,
				ts('Adult A Mobile Phone') => 'adult_a_phone',
				ts('Adult A Email') => 'adult_a_email',
				ts('Adult A Age') => 'adult_a_age',
				ts('Adult/Spouse/Partner B (sort)') => 'spouse_b_sort_name' ,
				ts('Adult B Mobile Phone') => 'spouse_b_phone',
				ts('Adult B Email') => 'spouse_b_email',
				ts('Adult B Age') => 'adult_b_age',
				ts('Contact/Adult A Display Name') => 'adult_a_display_name',
				ts('Adult B Display Name') => 'spouse_b_display_name',
			//	ts('Joint Greeting') => 'joint_greeting',
				ts('Children') => 'children',
				ts('Street Address') => 'street_address',
				ts('Supplemental address') => 'supplemental_address_1',
				ts('City') => 'city',
				ts('State') => 'state_abbreviation',
				ts('Postal Code') => 'postal_code',
				ts('Household') => 'household_sort_name',
				ts('Household Phone') => 'household_phone' ,
				ts('Household ID') => 'household_id' ,
				ts('Adult A Contact ID') => 'contact_id',
				/*
				 ts('Child 1') => 'kid1',
		ts('Child 2') => 'kid2',
		ts('Child 3') => 'kid3',
		ts('Child 4') => 'kid4',
		ts('Child 5') => 'kid5',
		ts('Child 6') => 'kid6',
		*/
					
				// ts('Household ID') => 'household_id'
				// ts('Contact/Household ID')   => 'contact_id',
				// ts('Household ID') => 'household_id',
	
		);
			
		if( (isset( $this->_formValues['membership_type_of_contact']) &&count( $this->_formValues['membership_type_of_contact'] ) > 0)
				|| ( isset($this->_formValues['membership_org_of_contact']) && count( $this->_formValues['membership_org_of_contact'] ) > 0 )    ){
			 
			$tmp_columns['Membership Join Date'] = 'mem_join_date';
			$tmp_columns['Membership Type'] = 'mem_type_name';
	
		}
	
		$this->_columns = $tmp_columns;
	}
	
	
	
	function buildForm( &$form ) {
		/**
		 * You can define a custom title for the search form
		 */
		$this->setTitle('Households and Couples Search');
	
		/**
		 * Define the search form fields here
		 */
	
	
	
		//require_once('utils/CustomSearchTools.php');
		//$searchTools = new CustomSearchTools();
		//$group_ids = $searchTools->getRegularGroupsforSelectList();
		
		$group_ids = array();
		
		$group_result = civicrm_api3('Group', 'get', array(
				'sequential' => 1,
				'is_active' => 1,
				'is_hidden' => 0,
				'options' => array('sort' => "title"),
		));
		
		if( $group_result['is_error'] == 0 ){
			$tmp_api_values = $group_result['values'];
			foreach($tmp_api_values as $cur){
				$grp_id = $cur['id'];
		
				$group_ids[$grp_id] = $cur['title'];
		
		
			}
		}
		
		
		// get membership ids and org contact ids.
		$mem_ids = array();
		$org_ids = array();
		$api_result = civicrm_api3('MembershipType', 'get', array(
				'sequential' => 1,
				'is_active' => 1,
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
		 
		$select2style = array(
				'multiple' => TRUE,
				'style' => 'width:100%; max-width: 100em;',
				'class' => 'crm-select2',
				'placeholder' => ts('- select -'),
		);
		
		$form->add('select', 'group_of_contact',
				ts('Contact is in the group(s)'),
				$group_ids,
				FALSE,
				$select2style
				);
		
		$form->add('select', 'membership_type_of_contact',
				ts('Contact has the membership of type(s)'),
				$mem_ids,
				FALSE,
				$select2style);
		 
		$form->add('select', 'membership_org_of_contact',
				ts('Contact has Membership In'),
				$org_ids,
				FALSE,
				$select2style);
		 
		/*
		$form->add('select', 'group_of_contact', ts('Contact is in the group'), $group_ids, FALSE,
				array('id' => 'group_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
				);
	
	
	
	
		 
	
		$form->add('select', 'membership_type_of_contact', ts('Contact has the membership of type'), $mem_ids, FALSE,
				array('id' => 'membership_type_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
				);
	
		
		$form->add('select', 'membership_org_of_contact', ts('Contact has Membership In'), $org_ids, FALSE,
				array('id' => 'membership_org_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
				);
	
	*/
		$primary_mem_options = array();
		$primary_mem_options['primary_only']  = "The Primary Member";
		$primary_mem_options['any_member'] = "Any Member (related or primary)";
	
	
		$form->add  ('select', 'membership_primary_choice', ts('Who should be Adult A?'),
				$primary_mem_options,
				false);
		 
		 
	
	
		$form->addDate('end_date', ts('Age Based on Date'), false, array( 'formatType' => 'custom' ) );
	
	
		/*
	
	
		require_once('utils/CustomSearchTools.php');
		$searchTools = new CustomSearchTools();
		$group_ids = $searchTools->getRegularGroupsforSelectList();
	
	
	
		$tmp_select = $form->add  ('select', 'group_of_individual', ts('Individual is in the group'),
		$group_ids,
		false);
		 
		$tmp_select->setMultiple(true);
	
		$mem_ids = $searchTools->getMembershipsforSelectList();
		$tmp_mem_select = $form->add  ('select', 'membership_type_of_contact', ts('Contact has the membership of type'),
		$mem_ids,
		false);
		 
		$tmp_mem_select->setMultiple(true);
	
	
		*/
	
	
	
		/**
		 * If you are using the sample template, this array tells the template fields to render
		 * for the search form.
		 */
		$form->assign( 'elements', array( 'group_of_contact', 'membership_org_of_contact' , 'membership_type_of_contact' , 'membership_primary_choice',  'end_date' ) );
	
	
	}
	
	/**
	 * Define the smarty template used to layout the search form and results listings.
	 */
	function templateFile( ) {
		return 'CRM/Contact/Form/Search/Custom/Sample.tpl';
	}
	 
	/**
	 * Construct the search query
	 */
	function all( $offset = 0, $rowcount = 0, $sort = null,
			$includeContactIDs = false, $onlyIDs = false ) {
	
	
	
	
				/******************************************************************************/
				// Get data for contacts
	
				if ( $onlyIDs ) {
					$select  = "ifnull( sp.spouse_a_id, contact_a.id )  as contact_id, hh.id as household_id, sp.spouse_b_id as spouse_b_id";
					 
					$outer_select = " t1.contact_id as contact_id  ";
				} else {
					 
					/*
					  
					ts('Contact/Adult A (sort)')=> 'sort_name' ,
					ts('Adult/Spouse/Partner B (sort)') => 'spouse_b_sort_name' ,
					ts('Contact/Adult A Display Name') => 'adult_a_display_name',
					ts('Adult B Display Name') => 'spouse_b_display_name',
					ts('Household') => 'household_sort_name',
					*/
					 
	
					$endDate = CRM_Utils_Date::processDate( $this->_formValues['end_date'] );
			  if ( $endDate ) {
			  	$yyyy = substr( $endDate , 0, 4);
			  	$mm = substr( $endDate , 4, 2);
			  	$dd = substr( $endDate , 6, 2);
			  	 
			  	$tmp = $yyyy."-".$mm."-".$dd ;
			  	$age_cutoff_date =  "'".$tmp."'";
			  }else{
			  	$age_cutoff_date = "now()";
			  	 
			  }
			   
	
			  $tmp_age_calc_a = "((date_format($age_cutoff_date,'%Y') - date_format(con_a.birth_date,'%Y')) - (date_format($age_cutoff_date,'00-%m-%d') < date_format(con_a.birth_date,'00-%m-%d')))";
	
			  $tmp_age_sql_a = " ".$tmp_age_calc_a."  AS adult_a_age ";
	
			  $tmp_age_calc_b = "((date_format($age_cutoff_date,'%Y') - date_format(t1.spouse_b_birth_date,'%Y')) - (date_format($age_cutoff_date,'00-%m-%d') < date_format(t1.spouse_b_birth_date,'00-%m-%d')))";
	
			  $tmp_age_sql_b = " ".$tmp_age_calc_b."  AS adult_b_age ";
	
	
			  $select =   "  ifnull( sp.spouse_a_sort_name, contact_a.sort_name )  as sort_name  ,  ifnull( sp.spouse_a_id, contact_a.id )  as contact_id ,
		  ifnull(sp.spouse_a_display_name, contact_a.display_name) as adult_a_display_name,
		  hh.sort_name as household_sort_name, hh.id as household_id,
		  sp.spouse_b_sort_name as spouse_b_sort_name,
		  sp.spouse_b_display_name as spouse_b_display_name,
		  sp.spouse_b_birth_date as spouse_b_birth_date,
		  sp.spouse_b_id as spouse_b_id ";
	
			  if( count( $this->_formValues['membership_type_of_contact'] ) > 0 || count( $this->_formValues['membership_org_of_contact'] ) > 0     ){
			  	$select =  $select." ,  group_concat(distinct memberships_a.join_date) as mem_join_date, group_concat(distinct mt.name ) as mem_type_name ";
	
			  }
	
			  /* $outer_select = " t1.hh_contact_id , t1.household_sort_name, t1.contact_id, t1.sort_name, t1.spouse_a_sort_name, t1.spouse_b_sort_name,
			   t1.adult_a_display_name,  t1.spouse_b_display_name  ";  */
			   
			  /*
			   $max_kids = 0 ;
			   $kid_select = "";
			   for($kid=1; $kid<= $max_kids; $kid++){
			   $kid_select = $kid_select." kc".$kid.".display_name as kid".$kid."  , ";
			   	
			   	
			   }
			    
			   */
	
			  // $kid_select = " GROUP_CONCAT( concat( kc.display_name, '(', ".$tmp_age_calc_child." , ')'  ) as children , ";
			   
			  $tmp_age_calc_child = "ifnull (  ((date_format($age_cutoff_date,'%Y') - date_format(kc.birth_date,'%Y')) - (date_format($age_cutoff_date,'00-%m-%d') < date_format(kc.birth_date,'00-%m-%d'))), 'no age' )";
	
			  //$tmp_age_sql_child = " GROUP_CONCAT( ".$tmp_age_calc_child." )   AS children_age,  ";
	
	          $tmp_age_sql_child = "";
			  // $tmp_age_calc_child
			  $kid_select = " GROUP_CONCAT( concat( kc.display_name, '(', ".$tmp_age_calc_child." , ') '  )) as children , ";
			   
			  $outer_select = " t1.* , group_concat( distinct phone_a.phone )  as adult_a_phone, group_concat( distinct phone_b.phone)  as spouse_b_phone, email_a.email as adult_a_email, email_b.email as spouse_b_email,
     		 phone_hh.phone as household_phone, address_a.street_address, address_a.supplemental_address_1, address_a.city, state_a.abbreviation as state_abbreviation,  address_a.postal_code, ".$kid_select.$tmp_age_sql_child."
     		 ".$tmp_age_sql_a.", ".$tmp_age_sql_b."  ";
	
				}
	
				$from  = $this->from( );
				$where = $this->where( $includeContactIDs ) ;
	
				// GROUP_CONCAT( kc.display_name ) as children
				$kid_join_sql = "";
				$parent_child_rel_id = "1";
	
	
	
				$kid_join_sql = " LEFT JOIN civicrm_relationship kr ON t1.contact_id = kr.contact_id_b AND kr.is_active = 1 AND kr.relationship_type_id = ".$parent_child_rel_id." LEFT JOIN civicrm_contact kc ON kr.contact_id_a = kc.id AND kc.is_deleted <> 1 AND kc.is_deceased <> 1 ";
					
	
	
				// phone_type_id = 2  ==> mobile phone.
	
				$sql = "
SELECT ".$outer_select." FROM ( SELECT $select
	FROM  $from
	WHERE $where
	GROUP BY ifnull( rel.contact_id_b,  contact_a.id ) ) as t1
	LEFT JOIN civicrm_contact con_a ON t1.contact_id  = con_a.id
	LEFT JOIN civicrm_phone phone_a ON t1.contact_id = phone_a.contact_id AND phone_a.phone_type_id =2
	LEFT JOIN civicrm_phone phone_b ON t1.spouse_b_id = phone_b.contact_id AND phone_b.phone_type_id =2
	LEFT JOIN civicrm_phone phone_hh ON t1.household_id = phone_hh.contact_id AND phone_hh.is_primary =1
	LEFT JOIN civicrm_email email_a ON t1.contact_id = email_a.contact_id AND email_a.is_primary =1
	LEFT JOIN civicrm_email email_b ON t1.spouse_b_id = email_b.contact_id AND email_b.is_primary =1
	LEFT JOIN civicrm_address address_a ON t1.contact_id = address_a.contact_id AND address_a.is_primary =1
	LEFT JOIN civicrm_state_province state_a ON address_a.state_province_id = state_a.id ".$kid_join_sql."
 group by t1.contact_id";
	
	
				// , ifnull( spouseA.id,  contact_a.id )
				//order by month(birth_date), oc_day";
	
				//for only contact ids ignore order.
				if ( !$onlyIDs ) {
					// Define ORDER BY for query in $sort, with default value
					if ( ! empty( $sort ) ) {
						if ( is_string( $sort ) ) {
							$sql .= " ORDER BY $sort ";
						} else {
							$sql .= " ORDER BY " . trim( $sort->orderBy() );
						}
					} else {
						//$sql .=   "ORDER BY month(rel.start_date), day(rel.start_date)";
					}
				}
	
				if ( $rowcount > 0 && $offset >= 0 ) {
					$sql .= " LIMIT $offset, $rowcount ";
				}
				//	 print "<Br><br>sql: ".$sql;
	
				return $sql;
	}
	
	function from(){
		$tmp_from = "";
		 
		$tmp_group_join = "";
		 
		$group_of_contact = $this->_formValues['group_of_contact'];
		 
		if(count( $group_of_contact ) > 0 ){
			require_once('utils/CustomSearchTools.php');
			$searchTools = new CustomSearchTools();
			$searchTools::verifyGroupCacheTable($group_of_contact ) ;
	
			$tmp_group_join = "LEFT JOIN civicrm_group_contact as groups_a on contact_a.id = groups_a.contact_id ".
					" LEFT JOIN civicrm_group_contact_cache as groupcache_a ON contact_a.id = groupcache_a.contact_id ";
		}
	
	
		/*
	
	
		$tmp_group_join = "";
		if(count( $this->_formValues['group_of_contact'] ) > 0 ){
		$tmp_group_join = "LEFT JOIN civicrm_group_contact as groups on contact_a.id = groups.contact_id".
		" LEFT JOIN civicrm_group_contact_cache as groupcache ON contact_a.id = groupcache.contact_id ";
	
		 
		 
		}
		 
		 
		 
		$tmp_mem_join = "";
		if( count( $this->_formValues['membership_type_of_contact'] ) > 0 ){
		$tmp_mem_join = "LEFT JOIN civicrm_membership as memberships_a on contact_a.id = memberships_a.contact_id
		LEFT JOIN civicrm_membership_status as mem_status_a on memberships_a.status_id = mem_status_a.id";
		 
		}
		*/
		$tmp_mem_join = "";
		if( count( $this->_formValues['membership_type_of_contact'] ) > 0 || count( $this->_formValues['membership_org_of_contact'] ) > 0     ){
			// Join on contact id of underlying individual, even if data is summarized by household.
			$tmp_mem_join = "LEFT JOIN civicrm_membership as memberships_a on contact_a.id  = memberships_a.contact_id
	 	 LEFT JOIN civicrm_membership_status as mem_status on memberships_a.status_id = mem_status.id
	 	 LEFT JOIN civicrm_membership_type mt ON memberships_a.membership_type_id = mt.id ";
			 
		}
		 
		 
		
		$tmp_hh_rel_ids_arr = array();
		
		$result = civicrm_api3('RelationshipType', 'get', array(
				'sequential' => 1,
				'name_a_b' => "Household Member of",
		));
		if( $result['is_error'] == 0 && $result['count'] == 1 ){
			$tmp_hh_rel_ids_arr[] = $result['id'];
		}
			
		
		$result = civicrm_api3('RelationshipType', 'get', array(
				'sequential' => 1,
				'name_a_b' => "Head of Household for",
		));
		if( $result['is_error'] == 0 && $result['count'] == 1 ){
			$tmp_hh_rel_ids_arr[] = $result['id'];
		}
		 
		
		$tmp_rel_type_ids = implode(", ", $tmp_hh_rel_ids_arr );
	
	
		
		//$tmp_rel_type_ids = "7, 6";   // Household member of , Head of Household
		$tmp_from_sql_hh_join = " LEFT JOIN civicrm_relationship rel ON contact_a.id = rel.contact_id_a AND rel.is_active = 1 AND rel.relationship_type_id IN ( ".$tmp_rel_type_ids." ) ";
		 
		/*
		 $tmp_from =  " civicrm_contact AS contact_a ".$tmp_from_sql_hh_join."
		 LEFT JOIN civicrm_contact as hh ON rel.contact_id_b = hh.id AND hh.is_deleted <> 1
		 LEFT JOIN civicrm_relationship spouse_rel ON (contact_a.id = spouse_rel.contact_id_a || contact_a.id = spouse_rel.contact_id_b) AND spouse_rel.is_active = 1 AND spouse_rel.relationship_type_id IN ( 2 )
		 LEFT JOIN civicrm_contact spouseB ON  spouse_rel.contact_id_b = spouseB.id AND spouseB.is_deleted <> 1
		 LEFT JOIN civicrm_contact spouseA ON  spouse_rel.contact_id_a = spouseA.id AND spouseA.is_deleted <> 1
		 $tmp_group_join
		 $tmp_mem_join ";
		 */
	
	
		$tmp_from =  " civicrm_contact AS contact_a ".$tmp_from_sql_hh_join."
	LEFT JOIN civicrm_contact as hh ON rel.contact_id_b = hh.id AND hh.is_deleted <> 1
	LEFT JOIN (
		SELECT spouse_rel.contact_id_a, spouse_rel.contact_id_b,
		spouseA.id as spouse_a_id, spouseA.sort_name as spouse_a_sort_name, spouseA.display_name as spouse_a_display_name,
		spouseB.id as spouse_b_id, spouseB.sort_name as spouse_b_sort_name, spouseB.display_name as spouse_b_display_name ,
		spouseB.birth_date as spouse_b_birth_date
		FROM civicrm_relationship spouse_rel
		LEFT JOIN civicrm_contact spouseB ON  spouse_rel.contact_id_b = spouseB.id AND spouseB.is_deleted <> 1
		LEFT JOIN civicrm_contact spouseA ON  spouse_rel.contact_id_a = spouseA.id AND spouseA.is_deleted <> 1
		WHERE  spouse_rel.relationship_type_id IN ( 2 ) AND spouse_rel.is_active = 1
		AND spouseA.is_deceased <> 1
		AND spouseB.is_deceased <> 1
		) as sp ON ( contact_a.id = sp.contact_id_a || contact_a.id = sp.contact_id_b)
	 ".$tmp_group_join.$tmp_mem_join;
	
	
		return $tmp_from ;
	
	}
	
	function where($includeContactIDs = false){
	
		$clauses = array( );
	
		// 'group_of_contact', 'membership_org_of_contact' , 'membership_type_of_contact'
	
		$group_of_contact = $this->_formValues['group_of_contact'];
	
		$tmp_sql_list = implode(",", $group_of_contact );
	
	
		//print "<br> sql list: ".$tmp_sql_list;
		if(strlen($tmp_sql_list) > 0 ){
			$clauses[] = "( (groups_a.group_id IN (".$tmp_sql_list.") AND groups_a.status = 'Added' ) OR ( groupcache_a.group_id IN (".$tmp_sql_list.")  ) )";
	
			// $clauses[] = "( (groups.group_id IN (".$tmp_sql_list.") AND groups.status = 'Added') OR ( groupcache.group_id IN (".$tmp_sql_list.")  )) " ;
		}
	
		$primary_member_choice = $this->_formValues['membership_primary_choice'];
	
		$primary_mem_filter = "";
		if( $primary_member_choice == "primary_only" ){
	
			$primary_mem_filter = " memberships_a.owner_membership_id is NULL ";
	
		}else{
	
		}
	
		$membership_types_of_con = $this->_formValues['membership_type_of_contact'];
	
	
		
		$tmp_membership_sql_list = implode(",", $membership_types_of_con );
		
		if(strlen($tmp_membership_sql_list) > 0 ){
			$clauses[] = "memberships_a.membership_type_id IN (".$tmp_membership_sql_list.") ";
			$clauses[] = " mem_status.is_current_member = '1' ";
			$clauses[] = " mem_status.is_active = '1' ";
			if( strlen( $primary_mem_filter) > 0 ){
				$clauses[] = $primary_mem_filter;  // is the primary member
			}
				
	
	
		}
	

	
		$membership_org_of_con = $this->_formValues['membership_org_of_contact'];
		
		
		$tmp_membership_org_sql_list = implode( ",", $membership_org_of_con );
		
		if(strlen($tmp_membership_org_sql_list) > 0 ){
			
				
			$clauses[] = "mt.member_of_contact_id IN (".$tmp_membership_org_sql_list.")" ;
			$clauses[] = "mt.is_active = '1'" ;
			if( strlen( $primary_mem_filter) > 0 ){
				$clauses[] = $primary_mem_filter;  // is the primary member
			}
			$clauses[] = "mem_status.is_current_member = '1'";
			$clauses[] = "mem_status.is_active = '1'";
			
	
		}
	
	
	
		//$clauses[] = "contact_a.contact_type = 'Individual'";
		// $clauses[] = "(rel.id IS NULL OR (reltype.name_a_b like  '%Spouse%'   OR  reltype.name_a_b like  '%spouse%'))";
		$clauses[] = "contact_a.contact_type <> 'Household' ";
		$clauses[] = "contact_a.contact_type <> 'Organization' ";
		$clauses[] = "contact_a.is_deleted <> 1";
		$clauses[] = "contact_a.is_deceased <> 1";
		//	$clauses[] = "(rel.id IS NULL OR contact_b.is_deleted <> 1)";
		//$clauses[] = "(rel.id IS NULL OR contact_b.is_deceased <> 1)";
	
		if ( $includeContactIDs ) {
			$contactIDs = array( );
			foreach ( $this->_formValues as $id => $value ) {
				if ( $value &&
						substr( $id, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
							$contactIDs[] = substr( $id, CRM_Core_Form::CB_PREFIX_LEN );
						}
			}
	
			if ( ! empty( $contactIDs ) ) {
				$contactIDs = implode( ', ', $contactIDs );
				$clauses[] = "contact_a.id IN ( $contactIDs )";
			}
		}
	
		$partial_where_clause = implode( ' AND ', $clauses );
	
		return $partial_where_clause ;
	
	
	}
	
	
	
	function alterRow( &$row ) {
		 
	  //TODO: add API for 'jointgreeting'
		$params = array(
				'version' => 3,
				'sequential' => 1,
				'contact_id' => $row['contact_id'],
		);
		$result = civicrm_api('JointGreetings', 'getsingle', $params);
	
	//	$row['joint_greeting'] = $result['greetings.joint_casual'];
	
	}
	
	
	/*
	 * Functions below generally don't need to be modified
	 */
	function count( ) {
		$sql = $this->all( );
		 
		$dao = CRM_Core_DAO::executeQuery( $sql,
				CRM_Core_DAO::$_nullArray );
		return $dao->N;
	}
	 // $offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = false
	function contactIDs( $offset = 0, $rowcount = 0, $sort = null,  $returnSQL = false) {
		return $this->all( $offset, $rowcount, $sort, false, true );
	}
	 
	function &columns( ) {
		return $this->_columns;
	}
	
	function setTitle( $title ) {
		if ( $title ) {
			CRM_Utils_System::setTitle( $title );
		} else {
			CRM_Utils_System::setTitle(ts('Search'));
		}
	}
	
	function summary( ) {
		return null;
	}
	
}