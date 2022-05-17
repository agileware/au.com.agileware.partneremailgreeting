<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:Job.Partneremailgreeting',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Update Partner Email Greeting',
      'description' => 'Update email greetings for contacts having Partner Of or Spouse Of relationships',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'Partneremailgreeting',
      'parameters' => '',
    ],
  ],
];
