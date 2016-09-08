<?php




class CRM_Familyfriendly_Form_Search_BirthdaySearch
extends CRM_Contact_Form_Search_Custom_Base
implements CRM_Contact_Form_Search_Interface
{

	

	function __construct(&$formValues) {
		 


		parent::__construct($formValues);
		// $this->_formValues = $formValues;

		/**
		 * Define the columns for search result rows
		 */
		$tmp_all_result_columns = array(
				ts('Name') => 'sort_name',
				ts('Birthday') => 'birth_month_and_day',
				ts('Birthday (sortable)') => 'birth_month_and_day_sortable',
				ts('Birth Year') => 'birth_year',
				ts('Age') => 'age',
				ts('Age on Subsequent Birthday') => 'next_age',
					
		);
			
			
		require_once('utils/Entitlement.php');
		$tmpEntitlement = new Entitlement();
		if( $tmpEntitlement->showJewishFeatures()){
		 $tmp_all_result_columns['Hebrew Birth Date'] =  'birth_date_hebrew';
		 $tmp_all_result_columns['Hebrew Birth Date Transliterated'] = 'birth_date_hebrew_trans';
		}

	 $tmp_all_result_columns['Occasion Type'] = 'oc_type';
	 $tmp_all_result_columns['Contact ID'] =  'contact_id';

	 $this->_columns = $tmp_all_result_columns;

	}



	function buildForm( &$form ) {
		/**
		 * You can define a custom title for the search form
		 */
		$this->setTitle('Find Upcoming Birthdays');

		/**
		 * Define the search form fields here
		 */

		require_once('utils/Entitlement.php');
		$tmpEntitlement = new Entitlement();

		$month =
		array( ''   => ' -- select -- ' , '1' => 'January', '2' => 'February', '3' => 'March',
				'4' => 'April', '5' => 'May' , '6' => 'June', '7' => 'July', '8' => 'August' , '9' => 'September' , '10' => 'October' , '11' => 'November' , '12' => 'December') ;


		$form->add  ('select', 'oc_month_start', ts('Start With Month'),
				$month,
				false);

		$form->add  ('select', 'oc_month_end', ts('Ends With Month'),
				$month,
				false);
		 

		$form->add( 'text',
				'oc_day_start',
				ts( ' Start With day' ) );

		$form->add( 'text',
				'oc_day_end',
				ts( ' End With day' ) );

		$relative_times_choices = array( '0' => 'Current Month', '1' => 'Next Month', '2' => '2 Months From Now' , '3' => '3 Months From Now', '4' => '4 Months From Now'
				, '5' => '5 Months From Now', '6' => '6 Months From Now', '7' => '7 Months From Now', '8' => '8 Months From Now', '9' => '9 Months From Now', '10' => '10 Months From Now'
				, '11' => '11 Months From Now', '12' => '12 Months From Now'  );
		 
		 
		 
		 

		$form->add( 'text',
				'current_age',
				ts( 'Age' ) );

		$form->add( 'text',
				'current_age_start',
				ts( 'Age Is At Least >=' ) );

		$form->add( 'text',
				'current_age_end',
				ts( 'Age Is No Higher Than <= ' ) );


		require_once('utils/CustomSearchTools.php');
		$searchTools = new CustomSearchTools();
		//$group_ids = $searchTools::getRegularGroupsforSelectList();

		$group_ids =   CRM_Core_PseudoConstant::group();

		$org_ids = $searchTools->getMembershipOrgsforSelectList();

		$mem_ids = $searchTools->getMembershipsforSelectList();
		/*
		 $select2style = array(
		 'multiple' => TRUE,
		 'style' => 'width: 100%; max-width: 60em;',
		 'class' => 'crm-select2',
		 'placeholder' => ts('- select -'),
		 );

		 $form->add('select', 'includeGroups',
		 ts('Include Group(s)'),
		 $groups,
		 FALSE,
		 $select2style
		 );

		 */
		if( $tmpEntitlement->isRunningCiviCRM_4_5()){

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



		}else{
			$form->add('select', 'group_of_contact', ts('Contact is in the group'), $group_ids, FALSE,
					array('id' => 'group_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
					);



			$form->add('select', 'membership_org_of_contact', ts('Contact has Membership In'), $org_ids, FALSE,
					array('id' => 'membership_org_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
					);

			$form->add('select', 'membership_type_of_contact', ts('Contact has the membership of type'), $mem_ids, FALSE,
					array('id' => 'membership_type_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
					);

			$form->add('select', 'relative_time', ts('Timeframe relative to today'), $relative_times_choices, FALSE,
					array('id' => 'relative_time', 'multiple' => 'multiple', 'title' => ts('-- select --'))
					);

		}







		$gender_options_tmp =  CRM_Contact_BAO_Contact::buildOptions('gender_id');

		$gender_options = array("" => "-- select --");
		foreach( $gender_options_tmp as $key => $val){
			$gender_options[$key] = $val;

		}

		$gender_select = $form->add  ('select', 'gender_choice', ts('Gender'),
				$gender_options,
				false);

		 

		$form->addDate('end_date', ts('Age Based on Date'), false, array( 'formatType' => 'custom' ) );

		$comm_prefs =  $searchTools->getCommunicationPreferencesForSelectList();

		$comm_prefs_select = $form->add  ('select', 'comm_prefs', ts('Communication Preference'),
				$comm_prefs,
				false);

		 
		$form->assign( 'elements', array( 'group_of_contact', 'membership_org_of_contact' , 'membership_type_of_contact' , 'relative_time' , 'oc_month_start', 'oc_month_end', 'oc_day_start', 'oc_day_end', 'gender_choice', 'current_age', 'current_age_start', 'current_age_end',  'end_date', 'comm_prefs') );


	}

	 
	function templateFile( ) {

		require_once('utils/Entitlement.php');
		$tmpEntitlement = new Entitlement();

		if( $tmpEntitlement->isRunningCiviCRM_4_5()){
			 
			return 'CRM/Contact/Form/Search/Custom.tpl';
		}else{
			return 'CRM/Contact/Form/Search/Custom/Sample.tpl';

		}
	}
	 
	 
	function all( $offset = 0, $rowcount = 0, $sort = null,
			$includeContactIDs = FALSE, $onlyIDs = FALSE ) {

				// SELECT clause must include contact_id as an alias for civicrm_contact.id



				/******************************************************************************/
				// Get data for contacts

				$grouby = "";
				if ( $onlyIDs ) {
					$select  = "contact_a.id as contact_id";
				} else {
					$groupby = " Group BY contact_a.id ";
					// Figure out how to format date for this locale
					$config = CRM_Core_Config::singleton( );

					$tmp_system_date_format = 	$config->dateInputFormat;
					if($tmp_system_date_format == 'dd/mm/yy'){
						$formatted_date_sql = " CONCAT( day(contact_a.birth_date) , ' ', monthname(contact_a.birth_date)  ) as birth_month_and_day ";

					}else if($tmp_system_date_format == 'mm/dd/yy'){
						$formatted_date_sql = " CONCAT( monthname(contact_a.birth_date) , ' ',  day(contact_a.birth_date)) as birth_month_and_day ";

					}else{
						print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;

					}

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
		    

		   $tmp_age_calc = "((date_format(".$age_cutoff_date.",'%Y') - date_format(contact_a.birth_date,'%Y')) -
    	          (date_format(".$age_cutoff_date.",'00-%m-%d') < date_format(contact_a.birth_date,'00-%m-%d')))";

		   $tmp_age_sql = "IF( ".$tmp_age_calc." > 0 , ".$tmp_age_calc." , 'Infant (Less than 1)' ) AS age ";

		   $tmp_next_age_sql = "((date_format(".$age_cutoff_date.",'%Y') - date_format(contact_a.birth_date,'%Y')) -
    		 (date_format(".$age_cutoff_date.",'00-%m-%d') < date_format(contact_a.birth_date,'00-%m-%d'))) + 1 as next_age";


		   $select = "contact_a.id as contact_id, ".$formatted_date_sql." ,
		   date_format(contact_a.birth_date, '%m-%d' ) as birth_month_and_day_sortable,
		   contact_a.sort_name as sort_name, $tmp_age_sql , year(contact_a.birth_date) as birth_year,
		   $tmp_next_age_sql ,  'birthday' as oc_type" ;

				}

				// make sure selected smart groups are cached in the cache table
				$group_of_contact = $this->_formValues['group_of_contact'];
				require_once('utils/CustomSearchTools.php');
				$searchTools = new CustomSearchTools();
				$searchTools::verifyGroupCacheTable($group_of_contact ) ;


				$from  = $this->from( );
				$where = $this->where( $includeContactIDs ) ;

				//$days_after_today = ($date_range_start_tmp + $date_range_end_tmp);
				//echo "<!--  date_range: " . $date_range . " -->";
				$sql = "SELECT $select
				FROM  $from
				WHERE $where
				".$groupby;
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
						$sql .=   "ORDER BY month(birth_date), day(birth_date)";
					}
				}

				if ( $rowcount > 0 && $offset >= 0 ) {
					$sql .= " LIMIT $offset, $rowcount ";
				}

				// print "<br>SQL: ".$sql;

				return $sql;
	}

	function from(){

		$tmp_from = "";
		$tmp_group_join = "";
		if(count( $this->_formValues['group_of_contact'] ) > 0 ){
			$tmp_group_join = "LEFT JOIN civicrm_group_contact as groups on contact_a.id = groups.contact_id".
					" LEFT JOIN civicrm_group_contact_cache as groupcache ON contact_a.id = groupcache.contact_id ";

			 
			 
		}
		 
		 
		$tmp_mem_join = "";
		if( count( $this->_formValues['membership_type_of_contact'] ) > 0 || count( $this->_formValues['membership_org_of_contact'] ) > 0     ){
			$tmp_mem_join = "LEFT JOIN civicrm_membership as memberships on contact_a.id = memberships.contact_id
	 	LEFT JOIN civicrm_membership_status as mem_status on memberships.status_id = mem_status.id
	 	LEFT JOIN civicrm_membership_type mt ON memberships.membership_type_id = mt.id ";
			 
		}

		 
		 
		if(strlen( $comm_prefs = $this->_formValues['comm_prefs']) > 0  ){
			$tmp_email_join = "LEFT JOIN civicrm_email ON contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1 ";
		}
		$tmp_from = " civicrm_contact contact_a
		$tmp_email_join ".$tmp_group_join.$tmp_mem_join;
			
		return $tmp_from ;
	}

	function where($includeContactIDs = false){

		$clauses = array( );

		$clauses[] = "contact_a.is_deleted <> 1";
		$clauses[] = "contact_a.is_deceased <> 1";

		$oc_month_start = $this->_formValues['oc_month_start'] ;
		$oc_month_end = $this->_formValues['oc_month_end'] ;

		$oc_day_start = $this->_formValues['oc_day_start'];
		$oc_day_end = $this->_formValues['oc_day_end'];



		$groups_of_individual = $this->_formValues['group_of_contact'];


		$gender_choice = $this->_formValues['gender_choice'];
		if( strlen($gender_choice) > 0 ){
			$clauses[] = "contact_a.gender_id = $gender_choice ";

		}

		require_once('utils/CustomSearchTools.php');
		$searchTools = new CustomSearchTools();


		$comm_prefs = $this->_formValues['comm_prefs'];

		$searchTools->updateWhereClauseForCommPrefs($comm_prefs, $clauses ) ;

		$tmp_sql_list = $searchTools->getSQLStringFromArray($groups_of_individual);
		if(strlen($tmp_sql_list) > 0 ){

			// need to check regular groups as well as smart groups.
			$clauses[] = "( (groups.group_id IN (".$tmp_sql_list.") AND groups.status = 'Added') OR ( groupcache.group_id IN (".$tmp_sql_list.")  )) " ;


		}

		$membership_types_of_con = $this->_formValues['membership_type_of_contact'];


		$tmp_membership_sql_list = $searchTools->convertArrayToSqlString( $membership_types_of_con ) ;
		if(strlen($tmp_membership_sql_list) > 0 ){
			$clauses[] = "memberships.membership_type_id IN (".$tmp_membership_sql_list.")" ;
			$clauses[] = "mem_status.is_current_member = '1'";
			$clauses[] = "mem_status.is_active = '1'";

		}

		// 'membership_org_of_contact'
		$membership_org_of_con = $this->_formValues['membership_org_of_contact'];
		$tmp_membership_org_sql_list = $searchTools->convertArrayToSqlString( $membership_org_of_con ) ;
		if(strlen($tmp_membership_org_sql_list) > 0 ){

			$clauses[] = "mt.member_of_contact_id IN (".$tmp_membership_org_sql_list.")" ;
			$clauses[] = "mt.is_active = '1'" ;
			$clauses[] = "mem_status.is_current_member = '1'";
			$clauses[] = "mem_status.is_active = '1'";

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
				$rel_time_str = $rel_time_str." month(birth_date) =  MONTH( date_add( now() ,  INTERVAL $relative_time MONTH) )   " ;
				$i = $i + 1;

			}
		}
		if( strlen( $rel_time_str) > 0){
			$rel_time_str = $rel_time_str.")";
			$clauses[] = $rel_time_str;
		}
		 
		 



		if( ($oc_month_start <> '' ) && is_numeric ($oc_month_start)){
			$clauses[] =  "month(birth_date) >= ".$oc_month_start ;
		}


		if( ($oc_month_end <> '' ) && is_numeric ($oc_month_end)){
			$clauses[]  = "month(birth_date) <= ".$oc_month_end;
		}



		if( ( $oc_day_start <> '') && is_numeric($oc_day_start) ){
			$clauses[] =  "day(birth_date) >= ".$oc_day_start;

		}

		if( ( $oc_day_end <> '') && is_numeric($oc_day_end) ){
			$clauses[] = "day(birth_date) <= ".$oc_day_end;

		}


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
		 


	 $tmp_age_sql = "((date_format(".$age_cutoff_date.",'%Y') - date_format(contact_a.birth_date,'%Y')) -
	  (date_format( ".$age_cutoff_date." ,'00-%m-%d') < date_format(contact_a.birth_date,'00-%m-%d')))";

	 $current_age = $this->_formValues['current_age'];
	 if( ( $current_age <> '') && is_numeric($current_age) ){
	 	$clauses[] = $tmp_age_sql." = ".$current_age;

	 }

	 $current_age_start = $this->_formValues['current_age_start'];
	 if( ( $current_age_start <> '') && is_numeric($current_age_start) ){
	 	$clauses[] = $tmp_age_sql." >= ".$current_age_start;

	 }


	 $current_age_end = $this->_formValues['current_age_end'];
	 if( ( $current_age_end <> '') && is_numeric($current_age_end) ){
	 	$clauses[] = $tmp_age_sql." <= ".$current_age_end;

	 }

	 $clauses[] =  "birth_date IS NOT NULL";

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

		require_once('utils/Entitlement.php');
		$tmpEntitlement = new Entitlement();
		if( $tmpEntitlement->showJewishFeatures()){

			require_once 'CRM/Hebrew/HebrewDates.php';

			$tmpHebCal = new HebrewCalendar();

			$hebrew_data = $tmpHebCal::retrieve_hebrew_demographic_dates( $row['contact_id']);
			//print_r($hebrew_data );
			$heb_date_of_birth =  $hebrew_data['hebrew_date_of_birth'];
			$heb_date_of_birth_hebrew =  $hebrew_data['hebrew_date_of_birth_hebrew'];
			$bar_bat_mitzvah_label = $hebrew_data['bar_bat_mitzvah_label'] ;
			$earliest_bar_bat_mitzvah_date = $hebrew_data['earliest_bar_bat_mitzvah_date'];


		 $row['birth_date_hebrew_trans'] =  $heb_date_of_birth;
		 $row['birth_date_hebrew'] = $heb_date_of_birth_hebrew;
		}

		 
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
	 
	function contactIDs( $offset = 0, $rowcount = 0, $sort = null) {
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