<?php

use Civi\Api4\Contact;
use Civi\Api4\Relationship;
use CRM_Partneremailgreeting_ExtensionUtil as E;

/**
 * Job.Partneremailgreeting API specification (optional)
 * This is used for documentation and validation.
 *
 * @param   array  $spec  description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_Partneremailgreeting_spec(&$spec) {
}

/**
 * Job.Partneremailgreeting API
 *
 * @param   array  $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_job_Partneremailgreeting($params) {
  try {

    // Get all active Partner and Spouse relationships, update email greeting for each contact in the relationship

    $relationships = Relationship::get()
                                 ->addWhere('is_active', '=', TRUE)
                                 ->addClause('OR', [
                                   'relationship_type_id:name',
                                   '=',
                                   'Partner of',
                                 ], [
                                   'relationship_type_id:name',
                                   '=',
                                   'Spouse of',
                                 ])
                                 ->execute();
    foreach ($relationships as $relationship) {
      $contacts = Contact::get()
                         ->addSelect('first_name')
                         ->addClause('OR', [
                           'id',
                           '=',
                           $relationship['contact_id_a'],
                         ], [
                           'id',
                           '=',
                           $relationship['contact_id_b'],
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
                 $relationship['contact_id_a'],
               ], [
                 'id',
                 '=',
                 $relationship['contact_id_b'],
               ])
               ->execute();
      }
      else {
        // If the first name is not set for either contact then reset to the standard greeting
        Contact::update()
               ->addValue('email_greeting_id:name', 'Dear {contact.first_name}')
               ->addClause('OR', [
                 'id',
                 '=',
                 $relationship['contact_id_a'],
               ], [
                 'id',
                 '=',
                 $relationship['contact_id_b'],
               ])
               ->execute();
      }
    }
    return civicrm_api3_create_success(TRUE, $params, 'Partneremailgreeting', 'Partneremailgreeting');
  }

  catch
  (API_Exception $e) {
    $errorMessage = $e->getMessage();
    CRM_Core_Error::debug_var('Job.Partneremailgreeting', $errorMessage);
    return civicrm_api3_create_error($errorMessage);
  }
}
