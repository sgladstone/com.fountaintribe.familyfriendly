<?php



class CRM_Familyfriendly_Form_Search_ChildToAdultSearch
extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

	protected $_formValues;
	protected $_tableName = null;
	
	protected $_aclFrom = NULL;
	protected $_aclWhere = NULL;

	function __construct( &$formValues ) {
		$this->_formValues = $formValues;

		/**
		 * Define the columns for search result rows
		 */
		$this->_columns = array(
				ts('Adult Name') => 'sort_name',
				ts('Relationship to Child') => 'rel_desc',
				ts('Child Name')   => 'child_name',
				ts('Child ID') => 'child_id',
				ts('Child Age') => 'child_age',
				//  ts('Group Ids') => 'regular_groups_ids',
				ts('Child in Groups(Only groups from criteria above)') => 'group_titles',
				//ts('Smart Groups Ids') => 'smart_groups_ids',
				// ts('Smart Groups') => 'smart_groups',
				ts('Adult Email') => 'email',
				ts('Adult Primary Phone') => 'phone',
				ts('Adult Mobile Phone') => 'mobile_phone',
				ts('Adult Street Address') => 'street_address' ,
				ts('Adult Supplemental Address 1') => 'supplemental_address_1',
				ts('Adult City') => 'city' ,
				ts('Adult State') => 'abbreviation' ,
				ts('Adult Postal Code') => 'postal_code' ,
				ts('Adult Country')  => 'country',
				ts('Adult ID') => 'adult_id',
		);
	}



	function buildForm( &$form ) {
		/**
		 * You can define a custom title for the search form
		 */
		$this->setTitle('Children to Adult Search');

		/**
		 * Define the search form fields here
		 */

		
	 $group_ids =   CRM_Core_PseudoConstant::nestedGroup();   


	 /*
	  $tmp_select = $form->add  ('select', 'group_of_child', ts('Child is in the group'),
	  $group_ids,
	  true);

	  $tmp_select->setMultiple(true);
	  */
	 
	 /*
	 $form->add('select', 'group_of_contact', ts('Child in Groups'), $group_ids, TRUE,
	 		array('id' => 'group_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
	 		);
	 		
	 		*/
	 
	 $select2style = array(
	 		'multiple' => TRUE,
	 		'style' => 'width:100%; max-width: 100em;',
	 		'class' => 'crm-select2',
	 		'placeholder' => ts('- select -'),
	 );
	 
	 $form->add('select', 'group_of_contact',
	 		ts('Child in Groups(s)'),
	 		$group_ids,
	 		TRUE,
	 		$select2style
	 		);


	 $form->addDate('end_date', ts('Age Based on Date'), false, array( 'formatType' => 'custom' ) );

	 /**
	  * If you are using the sample template, this array tells the template fields to render
	  * for the search form.
	  */
	 $form->assign( 'elements', array( 'group_of_contact', 'end_date') );


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
					$select = "contact_a.id as contact_id";
				} else {
					 
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
		    
		   $tmp_age_calc = "((date_format($age_cutoff_date,'%Y') - date_format(contact_b.birth_date,'%Y')) -
		   (date_format($age_cutoff_date,'00-%m-%d') < date_format(contact_b.birth_date,'00-%m-%d'))) as child_age, ";



		    
		    
		   $select = "contact_a.id as contact_id,  contact_b.id as child_id,  contact_a.sort_name as sort_name, concat(' is the ', reltype.name_b_a ) as rel_desc,
		 contact_b.sort_name as child_name,  civicrm_email.email, civicrm_phone.phone, group_concat( distinct mobile_phone.phone) as mobile_phone,
		  civicrm_address.street_address,
		  contact_a.id as adult_id,
		 civicrm_address.supplemental_address_1, civicrm_address.city , civicrm_address.postal_code,
		 civicrm_state_province.abbreviation, country.name as country,  ".$tmp_age_calc."
		 group_concat( DISTINCT groups.group_id) as regular_groups_ids,
		  group_concat( DISTINCT groupcache.group_id) as smart_groups_ids,
		 concat(  ifnull( group_concat( DISTINCT group_master.title), '')  , ', ' ,
		 ifnull( group_concat( DISTINCT smartgroup_master.title), '' ) ) as group_titles  " ;

				}

				// make sure selected smart groups are cached in the cache table
				$group_of_contact = $this->_formValues['group_of_contact'];

				$from  = $this->from( );
				$where = $this->where( $includeContactIDs ) ;

				
				$sql = "SELECT $select
				FROM  $from
				WHERE $where
				GROUP BY contact_a.id, contact_b.id ";
				// group by contact_id, child_id";
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
						$sql .=   "ORDER BY contact_a.id";
					}
				}

				if ( $rowcount > 0 && $offset >= 0 ) {
					$sql .= " LIMIT $offset, $rowcount ";
				}


			
				return $sql;
	}

	function from(){
		
		$this->buildACLClause('contact_b');
		
		$group_id_of_child = $this->_formValues['group_of_contact'] ;


		if( count( $group_id_of_child) > 0   ){
			$tmp_sql_list = implode(",", $group_id_of_child);
			$grp_from_sql = "left join civicrm_group_contact as groups on contact_b.id = groups.contact_id AND (groups.group_id in ( $tmp_sql_list ) AND groups.status = 'Added')
		LEFT JOIN civicrm_group as group_master ON groups.group_id = group_master.id
		LEFT JOIN civicrm_group_contact_cache as groupcache ON contact_b.id = groupcache.contact_id AND groupcache.group_id IN (".$tmp_sql_list.")
	LEFT JOIN civicrm_group as smartgroup_master ON groupcache.group_id = smartgroup_master.id";
		}	

		return " civicrm_contact AS contact_b
		JOIN civicrm_relationship as rel ON rel.contact_id_a = contact_b.id
		JOIN civicrm_contact as contact_a
		ON rel.contact_id_b = contact_a.id AND contact_a.is_deceased <> 1 AND contact_a.is_deleted <> 1
		left join civicrm_email on contact_a.id = civicrm_email.contact_id
		left join civicrm_phone on contact_a.id = civicrm_phone.contact_id and civicrm_phone.is_primary = 1
		left JOIN civicrm_phone mobile_phone ON contact_a.id = mobile_phone.contact_id AND mobile_phone.phone_type_id = 2
		left join civicrm_address on contact_a.id = civicrm_address.contact_id
		left join civicrm_state_province on civicrm_address.state_province_id = civicrm_state_province.id
		LEFT JOIN civicrm_country country ON civicrm_address.country_id = country.id ".$grp_from_sql.	
	" LEFT JOIN civicrm_relationship_type as reltype ON reltype.ID = rel.relationship_type_id {$this->_aclFrom} ";
	}

	function where($includeContactIDs = false){


		$clauses = array( );

		$group_id_of_child = $this->_formValues['group_of_contact'] ;



		$tmp_sql_list = implode(",", $group_id_of_child);

		$clauses[] = "contact_b.contact_type = 'Individual'";
		$clauses[] = "contact_b.is_deceased <> 1";
		$clauses[] = "contact_b.is_deleted <> 1";
		$clauses[] = "( reltype.name_a_b =  'Child Of' OR  reltype.name_b_a = 'Parent of' OR reltype.name_a_b =  'Step-child of' OR reltype.name_b_a = 'Step-parent of' ) ";
		$clauses[] = "rel.is_active =1";

		if(strlen($tmp_sql_list) > 0 ){
			$clauses[] = "(  (groups.group_id in ( $tmp_sql_list ) AND groups.status = 'Added') OR
			(groupcache.group_id IN (".$tmp_sql_list.") ) ) ";
		}


		$clauses[] = "(civicrm_email.is_primary = 1 OR civicrm_email.email is null) ";
		$clauses[] = "(civicrm_phone.is_primary = 1 OR civicrm_phone.phone is null)";
		$clauses[] = "(civicrm_address.is_primary = 1 OR civicrm_address.street_address is null)";
		//$clauses[] = "rel.start_date IS NOT NULL";

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
				$clauses[] = "contact_b.id IN ( $contactIDs )";
			}
		}

	// needed to enforce CiviCRM ACLs.
	if ($this->_aclWhere) {
		$clauses[] =  " {$this->_aclWhere} ";
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
	 
	function contactIDs( $offset = 0, $rowcount = 0, $sort = null , $returnSQL = false) {
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
	
	/**
	 * @param string $tableAlias
	 */
	public function buildACLClause($tableAlias = 'contact') {
		list($this->_aclFrom, $this->_aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause($tableAlias);
	}
}