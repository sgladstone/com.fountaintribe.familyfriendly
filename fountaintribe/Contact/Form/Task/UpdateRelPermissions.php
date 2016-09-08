<?php



class fountaintribe_Contact_Form_Task_UpdateRelPermissions extends CRM_Contact_Form_Task{


	/**
	 * Build the form
	 *
	 * @access public
	 * @return void
	 */
	function buildQuickForm( ) {
		//$count = $this->getLabelRows(TRUE);
		CRM_Utils_System::setTitle( ts('Clean up permissions on existing relationships within a household, spouses and children') );

		$notify_options = array();

		$notify_options[''] = "-- select --";
		$notify_options['1'] = "Yes";
		$notify_options['0'] = "No";

		
		 
		$verify_options = array();

		$verify_options[''] = "-- select --";
		$verify_options['verify_only'] = "Verification Only, do NOT create anything";
		$verify_options['create'] = "Create Users";
		 
		//$this->add ( 'select', 'verification_only', ts('Only Verify or Create Users?' ), $verify_options,  true);
		 
		 
		$this->addDefaultButtons( ts('Update Relationships Now') );
		$this->assign('found_rows', $count);
	}



	/**
	 * process the form after the input has been submitted and validated
	 *
	 * @access public
	 * @return None
	 */
	public function postProcess() {

		$status = array();

		 
		 
		if (!is_array($this->_contactIds) || empty($this->_contactIds)) {
			$this->_contactIds = array(0);
			$status[] = ts('No Contacts selected. Nothing to do.');
		}else{
			 
			 
			// Get user-selected choices from form.
			$params = $this->controller->exportValues( );
			 
			 
			 
			 
			//
			$user_input_valid = true;
			 
			 
			 
			 
			if( !($user_input_valid)){
				$status = implode( '<br/>', $status );
				CRM_Core_Session::setStatus( $status );
				return ;

				 
			}
			 
			// At this point we have gathered all the user form input.

			$params = array();
			$count = 0;
			foreach($this->_contactIds as $cid){
				$cid_list = $cid ;
				 
				$hh_sql = "SELECT r.contact_id_a AS cid_a, r.contact_id_b AS cid_b
				FROM civicrm_relationship AS r
				JOIN civicrm_relationship_type AS rt ON r.relationship_type_id  = rt.id
				JOIN civicrm_contact a_side ON a_side.id = r.contact_id_a
				JOIN civicrm_contact b_side ON b_side.id = r.contact_id_b
				WHERE (rt.name_a_b = 'Head of Household for' OR rt.name_a_b = 'Household Member of' )
				AND r.is_active =1
				AND a_side.is_deleted <> 1
				AND b_side.is_deleted <> 1
				and (r.contact_id_a in ( $cid_list)  or r.contact_id_b in ( $cid_list )  )
				";
				//print "<br>hh sql: ".$hh_sql;
				$household_member_ids = array();
				$household_id = "";
				$dao = CRM_Core_DAO::executeQuery($hh_sql, $params);
				while($dao->fetch()){
						
					$cid_a = $dao->cid_a;
					$cid_b = $dao->cid_b;
					$household_member_ids[] = $cid_a;
					$household_id = $cid_b; // b side is always a household.
				}
				$dao->free();
					
				$household_member_ids[] = $household_id;

				//print "<br>household member ids: ";
				//print_r( $household_member_ids) ;
				//print "<br>hh id: ".$household_id;
				if( count( $household_member_ids) > 0 && strlen($household_id) > 0){

					$ids_in_hh_str = implode(",", $household_member_ids) ;
					// household gets permission to everyone in it.(one-way)
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set  is_permission_b_a =1
					WHERE (rt.label_a_b = 'Household Member of'  OR rt.label_a_b = 'Head of Household for' )
					and r.is_active =1
					AND  r.contact_id_a IN ($ids_in_hh_str) AND r.contact_id_b = $household_id  ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();
					 
					// Heads of Household get permission to this household (two-way)
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set is_permission_a_b =1, is_permission_b_a =1
					WHERE rt.label_a_b =  'Head of Household for'
					and r.is_active =1
					AND  r.contact_id_a IN ($ids_in_hh_str)  AND r.contact_id_b = $household_id    ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();

					// spouses and partners get permission to this household (two-way)
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set is_permission_a_b =1, is_permission_b_a =1
					WHERE rt.label_a_b =  'Household Member of'
					and r.is_active =1
					AND r.contact_id_b = $household_id
					AND r.contact_id_a IN ( SELECT * FROM (
					(select r2.contact_id_b FROM civicrm_relationship r2 JOIN civicrm_relationship_type rt2
					ON r2.relationship_type_id = rt2.id
					WHERE ( lower(rt2.label_b_a) LIKE   '%spouse%' OR lower(rt2.label_b_a) LIKE '%partner%'    )
					AND r2.is_active = 1
					AND r2.contact_id_a IN ($ids_in_hh_str))
					UNION ALL (
					select r2.contact_id_a FROM civicrm_relationship r2 JOIN civicrm_relationship_type rt2
					ON r2.relationship_type_id = rt2.id
					WHERE  (lower(rt2.label_b_a) LIKE   '%spouse%' OR lower(rt2.label_b_a) LIKE '%partner%' )
					AND r2.is_active = 1
					AND r2.contact_id_b IN ($ids_in_hh_str)
					)
					) as spouse  )   ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();

					// Parents get permission to this household (two-way)
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set is_permission_a_b =1, is_permission_b_a =1
					WHERE rt.label_a_b =  'Household Member of'
					and r.is_active =1
					AND r.contact_id_b = $household_id
					AND r.contact_id_a IN ( SELECT * FROM (
					select r2.contact_id_b FROM civicrm_relationship r2 JOIN civicrm_relationship_type rt2
					ON r2.relationship_type_id = rt2.id
					WHERE rt2.label_b_a =  'Parent of'
					AND r2.is_active = 1
					AND r2.contact_id_a IN ($ids_in_hh_str) ) as parents  )   ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();
					 
					// Parents get permission to their children living in the same household (one-way)
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set  is_permission_b_a = 1, is_permission_a_b = 0
					WHERE (rt.label_b_a = 'Parent of' )
					and r.is_active =1
					AND r.contact_id_b IN ($ids_in_hh_str)
					AND r.contact_id_a IN ( $ids_in_hh_str )  ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();
					 
					// spouses/partners get two-way permission, even if not in same household.
					$sql = "update civicrm_relationship r join civicrm_relationship_type rt ON r.relationship_type_id = rt.id
					set is_permission_a_b =1, is_permission_b_a =1
					WHERE (lower(rt.label_a_b) like '%spouse%'  OR rt.label_a_b like '%partner%' )
					and r.is_active =1
					AND ( r.contact_id_a IN ($ids_in_hh_str) OR r.contact_id_b IN ( $ids_in_hh_str) )   ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();

					$count = $count + 1;
				}


			}


			$status_str = implode( '<br/>', $status );
			 
			$status_str_plaintext = implode( '\n', $status);
			 
			 
			 
			 

			$status_str =  $status_str."<br>relationships updated for $count contacts";

			CRM_Core_Session::setStatus( $status_str );



		}

	}



}
