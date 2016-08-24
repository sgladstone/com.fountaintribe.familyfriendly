<?php

class FamilyTools{
	

	function get_extra_household_info(&$contact_id , &$content){
		//if($context == 'page' && $tplName == 'CRM/Contact/Page/View/Summary.tpl' )
		//print "<br>object:";
		//print_r($object);
		//print "<br><br>Contact id: ".$_GET["cid"];
		//	$contact_id = $_GET["cid"];
	
		if(strlen($contact_id) == 0){
			return;
		}
	
		$spouse_info = "";
		$no_household_has_spouse = false;
		// Get household name and phone number, if this contact is part of a household.
		$sql = "SELECT distinct(ph.id), r.contact_id_b as hh_id, hh.display_name,
		c.contact_type as contact_type, c.contact_sub_type as contact_sub_type,
		 ph.is_primary, ph.phone , ph.location_type_id , ph.phone_type_id
		  FROM civicrm_contact c , civicrm_relationship r
		LEFT JOIN civicrm_phone ph on r.contact_id_b = ph.contact_id
		LEFT JOIN civicrm_contact hh on  r.contact_id_b = hh.id , civicrm_relationship_type rt
		where ( (c.id = r.contact_id_a) OR (c.id = r.contact_id_b)) AND r.relationship_type_id = rt.id
		AND rt.name_a_b IN ( 'Head of Household for' , 'Household Member of')
		and hh.is_deleted = 0
		AND r.is_active =1
		AND c.id = ".$contact_id;
	
		//$content = "<br><br>Yeah: ".$sql."<br>".$content;
		$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		$tmp_hh_name = "";
		$tmp_phones = array();
	
		while ( $dao->fetch( ) ) {
			$tmp_contact_type = $dao->contact_type;
			$tmp_contact_sub_type = $dao->contact_sub_type;
			$tmp_hh_name = $dao->display_name;
			$tmp_hh_id = $dao->hh_id;
			$cur_phone = array();
			$cur_phone['number'] =  $dao->phone;
			$cur_phone['is_primary'] = $dao->is_primary;
				
			$tmp_phones[] = $cur_phone;
				
	
		}
		$dao->free();
	
		if(strlen($tmp_hh_id) == 0){
			// This contact is not part of a household, see if they have a spouse or partner
			$sql = "Select if(cona.id = $contact_id , conb.display_name, cona.display_name) as spouse_display_name,
			if(cona.id = $contact_id , conb.id, cona.id) as  spouse_contact_id
			from civicrm_relationship r JOIN civicrm_relationship_type rt ON rt.id = r.relationship_type_id
			JOIN civicrm_contact cona ON r.contact_id_a = cona.id
			JOIN civicrm_contact conb ON r.contact_id_b = conb.id
			WHERE ( lower(rt.name_a_b) LIKE '%spouse%' )  AND lower(rt.name_a_b) NOT LIKE '%ex-spouse%' AND cona.is_deceased <> 1 AND  conb.is_deceased <> 1
			AND cona.is_deleted <> 1 AND  conb.is_deleted <> 1
			AND r.is_active =1
			AND (cona.id = $contact_id  OR conb.id = $contact_id )  ";
	
			$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
			if( $dao->fetch() ){
				$tmp_cid = $dao->spouse_contact_id ;
				$tmp_spouse_name = $dao->spouse_display_name;
				$tmp = "<a href='/civicrm/contact/view?reset=1&cid=".$tmp_cid."'>".$tmp_spouse_name."</a>";
	
				$spouse_info  = "Married to ".$tmp;
				// print "<br>spouse info: ".$spouse_info;
				$no_household_has_spouse = true;
			}
			$dao->free();
			 
	
			//print "<br>SQL: $sql";
				
			// return ;
		}
	
		if( $no_household_has_spouse  <> true ){
			$person_is_part_of_hh = false;
			if( $tmp_contact_type == 'Individual'){
				$line1 = "Part of the Household: <b><a href='/civicrm/contact/view?reset=1&cid=".$tmp_hh_id."'>".$tmp_hh_name."</b></a>";
				$person_is_part_of_hh = true;
			}
	
			$tmp_extra_info = $line1;
	
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
			 
			$tmp_age_calc = "((date_format($age_cutoff_date,'%Y') - date_format(c.birth_date,'%Y')) -
			(date_format($age_cutoff_date,'00-%m-%d') < date_format(c.birth_date,'00-%m-%d'))) ";
	
			if( strlen( $tmp_hh_id) > 0 ){
				$sql_family = "SELECT distinct(c.id) as contact_id , c.display_name, ".$tmp_age_calc." as age
  		from civicrm_relationship r ,
  		  civicrm_relationship_type rt, civicrm_contact c
  				WHERE r.relationship_type_id = rt.id
  				AND r.contact_id_a = c.id
  				AND rt.name_a_b IN ( 'Head of Household for' , 'Household Member of')
  				AND r.contact_id_b = ".$tmp_hh_id."
  				AND r.is_active = 1
  				AND c.is_deleted = 0
  				AND c.is_deceased = 0
  				order by c.birth_date";
	
				$dao_family =& CRM_Core_DAO::executeQuery( $sql_family,   CRM_Core_DAO::$_nullArray ) ;
					
				$family = array();
				while($dao_family->fetch()){
						
					$cur_age = $dao_family->age;
					$cur_display_name = $dao_family->display_name;
					if( strlen( $cur_age) > 0){
						if( $cur_age == '0'){
							$cur_age = "infant";
						}
						$formatted_age =  "(".$cur_age.")";
					}else{
						$formatted_age = "";
	
					}
					$cur_person['display_name'] = $cur_display_name.$formatted_age ;
					$cur_person['contact_id'] = $dao_family->contact_id;
					$family[] = $cur_person;
						
				}
	
				$dao_family->free();
	
			}
	
			$i = 1;
			$family_str = "";
	
			$tmp_size = count($family);
	
			if($person_is_part_of_hh){
				// Need to make sure we don't count the person whose summary we are on.
				$tmp_size = $tmp_size - 1;
			}
	
			foreach($family as $cur_person){
				$tmp_cid = $cur_person['contact_id'];
				$cur_name = $cur_person['display_name'];
				if(strcmp($tmp_cid , $contact_id)){
					if(strlen($cur_name) > 0 ){
						if($i == 1){
							//$family_str = "People in this Household: ";
							$family_str = "People in this household: ";
						}
						 
						$tmp_person_str = "<a href='/civicrm/contact/view?reset=1&cid=".$tmp_cid."'>".$cur_name."</a>";
	
						$family_str = $family_str.$tmp_person_str;
						 
						 
						if($i < $tmp_size ){
							$family_str = $family_str.", ";
						}
						 
						$i += 1;
					}
				}
	
			}
	
		}else{
	
			$line1 = $spouse_info;
			$tmp_extra_info = $line1;
		}
	
		//print "<br><br>phone str: ".$phone_str;
		if(strlen($line1) > 0 && strlen($family_str) > 0 ){
				
			$tmp_extra_info = $tmp_extra_info.", ";
			//print "<br>have something for phone, check extra info: ".$tmp_extra_info;
		}
	
		$tmp_extra_info = $tmp_extra_info.$family_str;
	
	
	
	
	
		// Deal with phone numbers.
		$i = 1;
		$phone_str = "";
		foreach($tmp_phones as $cur_phone){
			if(strlen($cur_phone['number']) > 0 ){
				if($i == 1){
					$phone_str = "Household Phone Numbers: ";
				}
				if($cur_phone['is_primary'] == 1){
					$phone_str = $phone_str."<b>".$cur_phone['number']."</b>";
				}else{
					$phone_str = $phone_str.$cur_phone['number'];
				}
	
				if($i < count($tmp_phones) ){
					$phone_str = $phone_str.", ";
				}
	
				$i += 1;
			}
	
		}
	
		if(strlen($family_str) > 0 || strlen($line1) > 0){
			$tmp_extra_info = $tmp_extra_info."<br>";
		}
	
		$tmp_extra_info = $tmp_extra_info.$phone_str;
	
		// Without the extra <br>, things look smushed in 4.3.x
		if( strlen($phone_str) > 0 ){
			$tmp_extra_info = $tmp_extra_info."<br>";
		}
	
	
		// Now for the final flourish, lets put it all together!
		$content = $tmp_extra_info." <br>".$content;
	
	
	}
	
	
	
}