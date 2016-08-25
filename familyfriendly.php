<?php

require_once 'familyfriendly.civix.php';

//  CRM\Contact\Page\View\Summary.tpl
function familyfriendly_civicrm_alterContent(  &$content, $context, $tplName, &$object ){
	
	
	if($context == 'page' && ($tplName == 'CRM/Contact/Page/View/Summary.tpl'  || $tplName == 'CRM\Contact\Page\View\Summary.tpl') ){
		
		require_once('utils/FamilyTools.php');
		$relTools = new FamilyTools();
		$contact_id = $_GET["cid"];
		
		$relTools->get_extra_household_info($contact_id , $content);
	
	}	
	
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function familyfriendly_civicrm_config(&$config) {
  _familyfriendly_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function familyfriendly_civicrm_xmlMenu(&$files) {
  _familyfriendly_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function familyfriendly_civicrm_install() {
  _familyfriendly_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function familyfriendly_civicrm_uninstall() {
  _familyfriendly_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function familyfriendly_civicrm_enable() {
  _familyfriendly_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function familyfriendly_civicrm_disable() {
  _familyfriendly_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function familyfriendly_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _familyfriendly_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function familyfriendly_civicrm_managed(&$entities) {
  _familyfriendly_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function familyfriendly_civicrm_caseTypes(&$caseTypes) {
  _familyfriendly_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function familyfriendly_civicrm_angularModules(&$angularModules) {
_familyfriendly_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function familyfriendly_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _familyfriendly_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function familyfriendly_civicrm_preProcess($formName, &$form) {

}

*/
