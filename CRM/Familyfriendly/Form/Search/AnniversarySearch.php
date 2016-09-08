<?php

/**
 *
* @package CRM
* $Id$
*
*/



class CRM_Familyfriendly_Form_Search_AnniversarySearch
extends CRM_Contact_Form_Search_Custom_Base
implements CRM_Contact_Form_Search_Interface {

	protected $_formValues;
	protected $_tableName = null;

	function __construct( &$formValues ) {
		$this->_formValues = $formValues;

		/**
		 * Define the columns for search result rows
		 */
		
		// TODO: check if "fancy tokens" extension is enabled, so we can get joint_greeting
		if( 1 ==0 ){
		$this->_columns = array(
				ts('Name') => 'sort_name',
				ts('Spouse Name')   => 'name_b',
				ts('Joint Greeting') => 'joint_greeting',
				ts('Date') => 'oc_date',
				ts('Date (sortable)') => 'anniversary_month_and_day_sortable',
				ts('Year of Wedding') => 'wedding_year',
				// ts('Current Num. Years Married') => 'marriage_length',
				ts('Upcoming Num. Years Married') => 'upcoming_length',
				ts('Occasion Type' ) => 'oc_type',
				ts('Contact ID') => 'contact_id',
		);
		}else{
			$this->_columns = array(
					ts('Name') => 'sort_name',
					ts('Spouse Name')   => 'name_b',
					ts('Date') => 'oc_date',
					ts('Date (sortable)') => 'anniversary_month_and_day_sortable',
					ts('Year of Wedding') => 'wedding_year',
					// ts('Current Num. Years Married') => 'marriage_length',
					ts('Upcoming Num. Years Married') => 'upcoming_length',
					ts('Occasion Type' ) => 'oc_type',
					ts('Contact ID') => 'contact_id',
			);
			
			
		}
	}



	function buildForm( &$form ) {
		/**
		 * You can define a custom title for the search form
		 */
		$this->setTitle('Find Upcoming Anniversaries');

		/**
		 * Define the search form fields here
		 */

		
		$month =
		array( ''   => ' -- select -- ' , '1' => 'January', '2' => 'February', '3' => 'March',
				'4' => 'April', '5' => 'May' , '6' => 'June', '7' => 'July', '8' => 'August' , '9' => 'September' , '10' => 'October' , '11' => 'November' , '12' => 'December') ;


		$form->add  ('select', 'oc_month_start', ts('Start With Month'),
				$month,
				false);

		$form->add  ('select', 'oc_month_end', ts('Ends With Month'),
				$month,
				false);

		/*
		 $form->add( 'text',
		 'oc_month_start',
		 ts( ' Start With Month' ) );

		 $form->add( 'text',
		 'oc_month_end',
		 ts( ' End With Month' ) );


		 */

		$relative_times_choices = array( '0' => 'Current Month', '1' => 'Next Month', '2' => '2 Months From Now' , '3' => '3 Months From Now', '4' => '4 Months From Now'
				, '5' => '5 Months From Now', '6' => '6 Months From Now', '7' => '7 Months From Now', '8' => '8 Months From Now', '9' => '9 Months From Now', '10' => '10 Months From Now'
				, '11' => '11 Months From Now', '12' => '12 Months From Now'  );
		 
		$form->add('select', 'relative_time', ts('Timeframe relative to today'), $relative_times_choices, FALSE,
				array('id' => 'relative_time', 'multiple' => 'multiple', 'title' => ts('-- select --'))
				);
		 
		 
		 
		 

		$form->add( 'text',
				'oc_day_start',
				ts( ' Start With day' ) );

		$form->add( 'text',
				'oc_day_end',
				ts( ' End With day' ) );

		$form->add( 'text',
				'years_married',
				ts( 'Number of Years Married' ) );

		
	

		$group_ids =   CRM_Core_PseudoConstant::nestedGroup();

	
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
			
		

	 	$select2style = array(
	 			'multiple' => TRUE,
	 			'style' => 'width: 100%; max-width: 60em;',
	 			'class' => 'crm-select2',
	 			'placeholder' => ts('- select -'),
	 	);
	 	//

	 	$form->add('select', 'group_of_contact',
	 			ts('Contact is in the group'),
	 			$group_ids,
	 			FALSE,
	 			$select2style
	 			);

	 	$form->add('select', 'membership_org_of_contact',
	 			ts('Contact has Membership In'),
	 			$org_ids,
	 			FALSE,
	 			$select2style
	 			);

	 	$form->add('select', 'membership_type_of_contact',
	 			ts('Contact has the membership of type'),
	 			$mem_ids,
	 			FALSE,
	 			$select2style
	 			);

	 	$form->add('select', 'relative_time',
	 			ts('Timeframe relative to today'),
	 			$relative_times_choices,
	 			FALSE,
	 			$select2style
	 			);


	 	// Get communication preferences
	 	$comm_prefs =  array();
	 	$api_result = civicrm_api3('OptionValue', 'get', array(
	 			'sequential' => 1,
	 			'option_group_id' => "preferred_communication_method",
	 			'is_active' => 1,
	 			'options' => array('sort' => "label"),
	 	));
	 	$comm_prefs[''] = '  -- Select -- ';;
	 	if( $api_result['is_error'] == 0 ){
	 		$tmp_api_values = $api_result['values'];
	 		foreach($tmp_api_values as $cur){
	 	
	 			$tmp_id = $cur['id'];
	 			$comm_prefs[$tmp_id] = $cur['label'];
	 				
	 		}
	 	}

	 $comm_prefs_select = $form->add  ('select', 'comm_prefs', ts('Communication Preference'),
	 		$comm_prefs,
	 		false);



	 /**
	  * If you are using the sample template, this array tells the template fields to render
	  * for the search form.
	  */
	 $form->assign( 'elements', array( 'group_of_contact', 'membership_org_of_contact',  'membership_type_of_contact' , 'years_married', 'relative_time' , 'oc_month_start', 'oc_month_end', 'oc_day_start', 'oc_day_end', 'comm_prefs') );


	}

	function alterRow( &$row ) {
		 
		
		// TODO: check if "fancy tokens" extension is enabled, so we can get joint_greeting
		if( 1 ==0 ){
			$params = array(
					'version' => 3,
					'sequential' => 1,
					'contact_id' => $row['contact_id'],
			);
			$result = civicrm_api('JointGreetings', 'getsingle', $params);
	
			$row['joint_greeting'] = $result['greetings.joint_casual'];
		}

	}


	/**
	 * Define the smarty template used to layout the search form and results listings.
	 */
	function templateFile( ) {
			 
		return 'CRM/Contact/Form/Search/Custom.tpl';
		
		 
	}
	 
	/**
	 * Construct the search query
	 */
	function all( $offset = 0, $rowcount = 0, $sort = null,
			$includeContactIDs = false, $onlyIDs = false ) {

				// SELECT clause must include contact_id as an alias for civicrm_contact.id
				/*
				 SELECT contact_a.display_name, contact_b.display_name, rel.start_date
				 FROM civicrm_contact AS contact_a
				 LEFT JOIN civicrm_relationship AS rel ON rel.contact_id_a = contact_a.id
				 LEFT JOIN civicrm_contact AS contact_b ON rel.contact_id_b = contact_b.id
				 LEFT JOIN civicrm_relationship_type AS reltype ON reltype.ID = rel.relationship_type_id
				 WHERE contact_a.contact_type =  'Individual'
				 AND reltype.name_a_b =  'Spouse Of'
				 AND rel.is_active =1

				 */


				/******************************************************************************/
				// Get data for contacts

				if ( $onlyIDs ) {
					$select  = "contact_a.id as contact_id";
					//	$outer_select = "contact_id as contact_id" ;
				} else {
					//	$outer_select = " * ";
					//$tmp_age_sql = "((date_format(now(),'%Y') - date_format(rel.start_date,'%Y')) - (date_format(now(),'00-%m-%d') < date_format(rel.start_date,'00-%m-%d'))) AS marriage_length ";
					// Figure out how to format date for this locale
					$config = CRM_Core_Config::singleton( );

					$tmp_system_date_format = 	$config->dateInputFormat;
					if($tmp_system_date_format == 'dd/mm/yy'){
						$formatted_date_sql =  " CONCAT( day(rel.start_date) , ' ', monthname(rel.start_date) )  as oc_date ";

					}else if($tmp_system_date_format == 'mm/dd/yy'){
						$formatted_date_sql =  " CONCAT( monthname(rel.start_date), ' ', day(rel.start_date))  as oc_date ";

					}else{
						print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;

					}



					$tmp_upcoming_length =  "( ((date_format(now(),'%Y') - date_format(rel.start_date,'%Y')) - (date_format(now(),'00-%m-%d') < date_format(rel.start_date,'00-%m-%d'))) + 1) AS upcoming_length ";

					$select = "contact_a.id as contact_id, contact_a.sort_name as sort_name, contact_b.sort_name as name_b, ".$formatted_date_sql."  ,
					date_format(rel.start_date, '%m-%d' ) as anniversary_month_and_day_sortable,
					$tmp_upcoming_length,   year(rel.start_date) as wedding_year,
					'Anniversary' as oc_type" ;

				}


				$group_of_contact = $this->_formValues['group_of_contact'];

				$from  = $this->from( );
				$where = $this->where( $includeContactIDs ) ;

				//$days_after_today = ($date_range_start_tmp + $date_range_end_tmp);
				//echo "<!--  date_range: " . $date_range . " -->";
				/*
				 $sql = "Select $outer_select from (
				 SELECT DISTINCT $select
				 FROM  $from
				 WHERE $where ";
				 */
				//order by month(birth_date), oc_day";

				if ( $onlyIDs ) {
					$groupBy = "";
				}else{
					$groupBy = " GROUP BY contact_a.id " ;
				}

				$sql = "SELECT $select
				FROM  $from
				WHERE $where ".$groupBy;


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
						$sql .=   " ORDER BY month(rel.start_date), day(rel.start_date)";
					}
				}

				// $sql .= " ) as t1 WHERE 1=1 "   ;

				if ( $rowcount > 0 && $offset >= 0 ) {
					$sql .= " LIMIT $offset, $rowcount ";
				}

				//   print "<br><br>SQL:  ".$sql;
				return $sql;
	}

	function from(){
		$tmp_from = "";
		 
		$tmp_group_join = "";
		if(count( $this->_formValues['group_of_contact'] ) > 0 ){
			$tmp_group_join = "left join civicrm_group_contact as groups_a on contact_a.id = groups_a.contact_id
				   left join civicrm_group_contact as groups_b on contact_b.id = groups_b.contact_id
				    LEFT JOIN civicrm_group_contact_cache as groupcache_a ON contact_a.id = groupcache_a.contact_id
				    LEFT JOIN civicrm_group_contact_cache as groupcache_b ON contact_b.id = groupcache_b.contact_id ";
			 
			 
		}

		$tmp_mem_join = "";
		if( count( $this->_formValues['membership_type_of_contact'] ) > 0 || count( $this->_formValues['membership_org_of_contact'] ) > 0     ){
			$tmp_mem_join = "LEFT JOIN civicrm_membership as memberships_a on contact_a.id = memberships_a.contact_id
	 			 LEFT JOIN civicrm_membership_status as mem_status_a on memberships_a.status_id = mem_status_a.id
	 			 LEFT JOIN civicrm_membership_type mt_a ON memberships_a.membership_type_id = mt_a.id
	 			 LEFT JOIN civicrm_membership as memberships_b on contact_b.id = memberships_b.contact_id
	 			 LEFT JOIN civicrm_membership_status as mem_status_b on memberships_b.status_id = mem_status_b.id
	 			 LEFT JOIN civicrm_membership_type mt_b ON memberships_b.membership_type_id = mt_b.id";
			 
		}

		if(strlen( $comm_prefs = $this->_formValues['comm_prefs']) > 0  ){
			$tmp_email_join = "LEFT JOIN civicrm_email ON contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1 ";
		}else{
			$tmp_email_join = "";
		}

		$tmp_from =  " civicrm_contact AS contact_a
		LEFT JOIN civicrm_relationship AS rel ON rel.contact_id_a = contact_a.id
		LEFT JOIN civicrm_contact AS contact_b ON rel.contact_id_b = contact_b.id
		LEFT JOIN civicrm_relationship_type AS reltype ON reltype.ID = rel.relationship_type_id
		$tmp_email_join
		$tmp_group_join
		$tmp_mem_join ";

		return $tmp_from ;

	}

	function where($includeContactIDs = false){

		$clauses = array( );

		$oc_month_start = $this->_formValues['oc_month_start'] ;
		$oc_month_end = $this->_formValues['oc_month_end'] ;

		$oc_day_start = $this->_formValues['oc_day_start'];
		$oc_day_end = $this->_formValues['oc_day_end'];

		$years_married = $this->_formValues['years_married'];

		$groups_of_individual = $this->_formValues['group_of_contact'];

		
		$comm_prefs = $this->_formValues['comm_prefs'];

		// TODO: check comm_prefs



		$tmp_sql_list = implode( ",", $groups_of_individual);



		//print "<br> sql list: ".$tmp_sql_list;
		if(strlen($tmp_sql_list) > 0 ){
			$clauses[] = "( ( groups_a.group_id IN (".$tmp_sql_list.") AND groups_a.status = 'Added') OR
				( groups_b.group_id IN (".$tmp_sql_list.") AND groups_b.status = 'Added') OR
				( groupcache_a.group_id IN (".$tmp_sql_list.") ) OR
				( groupcache_b.group_id IN (".$tmp_sql_list.") )     ) ";
		}


		$membership_types_of_con = $this->_formValues['membership_type_of_contact'];


		$tmp_membership_sql_list = implode( ",",  $membership_types_of_con ) ;
		if(strlen($tmp_membership_sql_list) > 0 ){
			$clauses[] = "(  (memberships_a.membership_type_id IN (".$tmp_membership_sql_list.") AND mem_status_a.is_current_member = '1' AND mem_status_a.is_active = '1' ) OR
				 (memberships_b.membership_type_id IN (".$tmp_membership_sql_list.") AND mem_status_b.is_current_member = '1' AND mem_status_b.is_active = '1' )  )";

		}


		// 'membership_org_of_contact'

		$membership_org_of_con = $this->_formValues['membership_org_of_contact'];
		$tmp_membership_org_sql_list = implode( ",",  $membership_org_of_con ) ;
		if(strlen($tmp_membership_org_sql_list) > 0 ){
			// print "<br>membership orgs: <br>".$tmp_membership_org_sql_list;
				
			$clauses[] = "(  (mt_a.member_of_contact_id IN (".$tmp_membership_org_sql_list." )  AND mt_a.is_active = '1' AND mem_status_a.is_current_member = '1' AND mem_status_a.is_active = '1' ) OR
					 (mt_b.member_of_contact_id IN (".$tmp_membership_org_sql_list." )  AND mt_b.is_active = '1' AND mem_status_b.is_current_member = '1' AND mem_status_b.is_active = '1' )  ) ";
				
				
				
				
				
				
			//print_r($clauses);

		}

		$relative_time_array = $this->_formValues['relative_time'];

		if( is_array( $relative_time_array ) && count($relative_time_array) > 0){
			 
			$i = 0;
			foreach( $relative_time_array as $relative_time){
				if( $i == 0){
					$rel_time_str = "(";
				}else if( $i > 0 && strlen($rel_time_str) > 2 ){
					$rel_time_str = $rel_time_str." OR ";
				}
				$rel_time_str = $rel_time_str." month(rel.start_date) =  MONTH( date_add( now() ,  INTERVAL $relative_time MONTH) )   " ;
				$i = $i + 1;

			}
		}
		if(isset( $rel_time_str ) &&  strlen( $rel_time_str) > 0){
			$rel_time_str = $rel_time_str.")";
			$clauses[] = $rel_time_str;
		}
		 
		 

		if( ($oc_month_start <> '' ) && is_numeric ($oc_month_start)){
			$clauses[] =  "month(rel.start_date) >= ".$oc_month_start ;
		}


		if( ($oc_month_end <> '' ) && is_numeric ($oc_month_end)){
			$clauses[]  = "month(rel.start_date) <= ".$oc_month_end;
		}



		if( ( $oc_day_start <> '') && is_numeric($oc_day_start) ){
			$clauses[] =  "day(rel.start_date) >= ".$oc_day_start;

		}

		if( ( $oc_day_end <> '') && is_numeric($oc_day_end) ){
			$clauses[] = "day(rel.start_date) <= ".$oc_day_end;

		}

		if( ( $years_married <> '' ) && is_numeric($years_married) ){
			$clauses[] = "( ((date_format(now(),'%Y') - date_format(rel.start_date,'%Y')) - (date_format(now(),'00-%m-%d') < date_format(rel.start_date,'00-%m-%d'))) + 1) = ".$years_married ;

		}

		$clauses[] = "contact_a.contact_type = 'Individual'";
		$clauses[] = "reltype.name_a_b =  'Spouse Of'";
		$clauses[] = "rel.is_active =1";
		$clauses[] = "rel.start_date IS NOT NULL";
		$clauses[] = "contact_a.is_deleted <> 1";
		$clauses[] = "contact_a.is_deceased <> 1";
		$clauses[] = "contact_b.is_deleted <> 1";
		$clauses[] = "contact_b.is_deceased <> 1";

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

	/*
	 * Functions below generally don't need to be modified
	 */
	function count( ) {
		$sql = $this->all( );
		 
		$dao = CRM_Core_DAO::executeQuery( $sql,
				CRM_Core_DAO::$_nullArray );
		return $dao->N;
	}
	 
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