<?php


class FamilyFriendlyFilterHelper{
	function fillMembershipTypeArrays(&$mem_ids,  &$org_ids){
	
		$cur_domain_id = "";
	
		$result = civicrm_api3('Domain', 'get', array(
				'sequential' => 1,
				'current_domain' => array('IS NOT NULL' => 1),
		));
	
	
		if( $result['is_error'] == 0 && $result['count'] == 1){
			if(isset( $result['id'] )){
				$cur_domain_id = $result['id'];
			}
		}
	
		// get membership ids and org contact ids.
		if( strlen(  $cur_domain_id ) > 0 ){
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
		}
	
	}
	
	
	
}