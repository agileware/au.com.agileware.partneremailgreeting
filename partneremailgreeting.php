<?php

require_once 'partneremailgreeting.civix.php';

// phpcs:disable
use Civi\Api4\Contact;
use Civi\Api4\RelationshipType;
use Civi\Core\Event\PostEvent;
use CRM_Partneremailgreeting_ExtensionUtil as E;

// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function partneremailgreeting_civicrm_config(&$config) {
  _partneremailgreeting_civix_civicrm_config($config);

  if (isset(Civi::$statics[__FUNCTION__])) {
    return;
  }
  Civi::$statics[__FUNCTION__] = 1;

  \Civi::dispatcher()
       ->addListener('hook_civicrm_post', 'partneremailgreeting_relationshipchange');

}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function partneremailgreeting_civicrm_install() {
  _partneremailgreeting_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function partneremailgreeting_civicrm_postInstall() {
  _partneremailgreeting_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function partneremailgreeting_civicrm_uninstall() {
  _partneremailgreeting_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function partneremailgreeting_civicrm_enable() {
  _partneremailgreeting_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function partneremailgreeting_civicrm_disable() {
  _partneremailgreeting_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function partneremailgreeting_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _partneremailgreeting_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function partneremailgreeting_civicrm_entityTypes(&$entityTypes) {
  _partneremailgreeting_civix_civicrm_entityTypes($entityTypes);
}

function partneremailgreeting_relationshipchange(PostEvent $event) {
  try {
    if ($event->object instanceof CRM_Contact_DAO_Relationship) {

      $relationshipTypes = RelationshipType::get()
                                           ->addSelect('id')
                                           ->addWhere('contact_type_a', '=', 'Individual')
                                           ->addClause('OR', [
                                             'name_a_b',
                                             '=',
                                             'Spouse of',
                                           ], [
                                             'name_a_b',
                                             '=',
                                             'Partner of',
                                           ])
                                           ->setLimit(2)
                                           ->execute()
                                           ->getArrayCopy();

      $relationship_match = FALSE;

      if ($event->object->relationship_type_id == $relationshipTypes[0]['id'] || $event->object->relationship_type_id == $relationshipTypes[1]['id']) {
        $relationship_match = TRUE;
      }

      $custom_greeting = FALSE;

      if ($event->action == 'create' && $relationship_match) {
        $custom_greeting = TRUE;
      }
      elseif ($event->action == 'edit' && $relationship_match && $event->object->is_active == '1') {
        $custom_greeting = TRUE;
      }

      if ($relationship_match && $custom_greeting) {
        $contacts = Contact::get()
                           ->addSelect('first_name')
                           ->addClause('OR', [
                             'id',
                             '=',
                             $event->object->contact_id_a,
                           ], [
                             'id',
                             '=',
                             $event->object->contact_id_b,
                           ])
                           ->setLimit(2)
                           ->execute()->getArrayCopy();

        // Check that the first name is set for both contacts before setting the customised greeting
        if ($contacts[0]['first_name'] && $contacts[1]['first_name']) {
          Contact::update()
                 ->addValue('email_greeting_custom', 'Dear ' . $contacts[0]['first_name'] . ' and ' . $contacts[1]['first_name'])
                 ->addValue('email_greeting_id:name', 'Customized')
                 ->addClause('OR', [
                   'id',
                   '=',
                   $event->object->contact_id_a,
                 ], [
                   'id',
                   '=',
                   $event->object->contact_id_b,
                 ])
                 ->execute();
        }
        else {
          // If the first name is not set for either contact then reset to the standard greeting
          $custom_greeting = FALSE;
        }
      }

      if ($relationship_match && !$custom_greeting) {
        Contact::update()
               ->addValue('email_greeting_id:name', 'Dear {contact.first_name}')
               ->addClause('OR', [
                 'id',
                 '=',
                 $event->object->contact_id_a,
               ], [
                 'id',
                 '=',
                 $event->object->contact_id_b,
               ])
               ->execute();
      }
    }
  }
  catch (API_Exception $e) {
    $errorMessage = $e->getMessage();
    CRM_Core_Error::debug_var('partneremailgreeting::partneremailgreeting_relationshipchange', $errorMessage);
  }
}