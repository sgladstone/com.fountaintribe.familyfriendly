<?php



class fountaintribe_Contact_Form_Task_UpdateSharesAddressWith extends CRM_Contact_Form_Task{

	/**
	 * Build the form
	 *
	 * @access public
	 * @return void
	 */
	function buildQuickForm( ) {
		//$count = $this->getLabelRows(TRUE);
		CRM_Utils_System::setTitle( ts("Update 'Use another contact's address' on Home Address") );

		// $notify_options = array();


		if( isset($count)){
				
		}else{
			$count = "";
		}
		 
		$this->addDefaultButtons( ts('Update Addresses Now') );
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
			$addr_count = 0;

			$home_loc_type = "1"; // this is the "home" location type ID.

			foreach($this->_contactIds as $cid){
				$cid_list = $cid ;
				 
				$hh_sql = "SELECT r.contact_id_a AS cid_a, r.contact_id_b AS cid_b,
				a_addr.id as a_addr_id , hh_addr.id as hh_addr_id ,
				hh_addr.street_address as hh_street_address , hh_addr.city as hh_city,
				hh_addr.state_province_id as hh_state_province_id, hh_addr.postal_code as hh_postal_code
				FROM civicrm_relationship AS r
				JOIN civicrm_relationship_type AS rt ON r.relationship_type_id  = rt.id
				JOIN civicrm_contact a_side ON a_side.id = r.contact_id_a
				JOIN civicrm_contact b_side ON b_side.id = r.contact_id_b
				LEFT JOIN civicrm_address a_addr ON a_side.id = a_addr.contact_id
			 AND a_addr.location_type_id = $home_loc_type
			 LEFT JOIN civicrm_address hh_addr ON b_side.id = hh_addr.contact_id
			 AND hh_addr.location_type_id = $home_loc_type
			 WHERE (rt.name_a_b = 'Head of Household for' OR rt.name_a_b = 'Household Member of' )
			 AND r.is_active =1
			 AND a_side.is_deleted <> 1
			 AND b_side.is_deleted <> 1
			 and (r.contact_id_a in ( $cid_list)  or r.contact_id_b in ( $cid_list )  )
			 AND a_addr.master_id IS NULL
			 AND a_addr.id IS NOT NULL
			 AND (trim( lower(a_addr.street_address))  = trim(lower(hh_addr.street_address))
			 AND trim(lower(a_addr.city)) = trim(lower(hh_addr.city))
			 AND a_addr.state_province_id = hh_addr.state_province_id
			 AND a_addr.country_id = hh_addr.country_id
			 AND a_addr.postal_code = hh_addr.postal_code)
			 GROUP BY r.contact_id_a, a_addr.id
			 ";
				//print "<br>hh sql: ".$hh_sql;
				$household_member_ids = array();
				$household_member_addr_ids = array();
				$household_id = "";
				$household_addr_id = "";
				$dao = CRM_Core_DAO::executeQuery($hh_sql, $params);
				while($dao->fetch()){
						
					$cid_a = $dao->cid_a;
					$cid_b = $dao->cid_b;
					$household_member_ids[] = $cid_a;
					$household_id = $cid_b; // b side is always a household.

					$household_member_addr_ids[] = $dao->a_addr_id;
					$household_addr_id = $dao->hh_addr_id;
				}
				$dao->free();
					
				$household_member_ids[] = $household_id;

				//print "<br>household member ids: ";
				//print_r( $household_member_ids) ;
				//print "<br>hh id: ".$household_id;
				if( count( $household_member_addr_ids) > 0 && strlen($household_addr_id  ) > 0){

					$ids_in_hh_str = implode(",",  $household_member_addr_ids) ;
					// contact's address is marked as "shared with" the household address.
					$sql = "update civicrm_address addr
					set  addr.master_id = $household_addr_id
					WHERE  addr.id IN ($ids_in_hh_str) ";
					$dao = CRM_Core_DAO::executeQuery($sql, $params);
					$dao->free();


					 
					$addr_count = $addr_count + count( $household_member_addr_ids) ;
				}


			}


			$status_str = implode( '<br/>', $status );
			 
			$status_str_plaintext = implode( '\n', $status);
			 
			 
			 
			//Use another contact's address
			if( $addr_count > 0 ){
				$status_str =  $status_str."<br>$addr_count addresses were updated.";
			}else{
				$status_str =  $status_str."<br>No addresses were updated ";
			}

			CRM_Core_Session::setStatus( $status_str );



		}

	}






}
