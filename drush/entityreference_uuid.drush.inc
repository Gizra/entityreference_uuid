<?php

/**
 * @file
 * Drush support for the Entity reference UUID module.
 */

/**
 * Implements hook_drush_command().
 */
function entityreference_uuid_drush_command() {
  $items['entityreference-uuid-populate-field'] = array(
    'description' => 'Reload and save entities that have a reference to a UUID, but not to a target ID.',
    'options' => array(
      'id' => 'Entity ID to start from.',
    ),
    'arguments' => array(
      'field' => 'The field name to populate.',
    ),
    'examples' => array(
      'entityreference-uuid-populate-field field_foo' => 'Populate the "field_foo" entity reference.',
    ),
    'drupal dependencies' => array('entityreference_uuid'),
    'aliases' => array('euuid'),
  );
  return $items;
}


/*
 * Spawn a subshell which runs the same command we are currently running.
 */
function drush_entityreference_uuid_invoke_process($migrations = '') {
  $args = drush_get_arguments();

  // $args[0] is the command name, $args[1] is the list of migrations.
  if (empty($migrations)) {
    $command_args = array($args[1]);
  }
  else {
    $command_args = array($migrations);
  }
  $return = drush_invoke_process('@self', $args[0], $command_args);
  return $return;
}

/**
 * A Drush command callback.
 */
function drush_entityreference_uuid_populate_field() {
  $args = drush_get_arguments();

  if (empty($args[1])) {
    return drush_set_error(dt('You must pass the field name.'));
  }

  $field_name = $args[1];

  if (!field_info_field($field_name)) {
    return drush_set_error(dt('Field @name does not exist.', array('@name' => $field_name)));
  }

  $id = drush_get_option('id', 0);
  $field = field_info_field($field_name);
  $table_name = _field_sql_storage_tablename($field);

  $base_query = db_select($table_name, $table_name);
  $base_query
    ->fields($table_name, array('entity_id'))
    ->condition($field_name . '_uuid', '', '<>')
    ->condition($field_name . '_target_id')
    // We need only the first delta, as entity_load() will take care of all the
    // deltas.
    ->condition('delta', 0)
    ->orderBy('entity_id');

  $query = clone $base_query;
  $total = $query
    ->condition('entity_id', $id, '>')
    ->countQuery()
    ->execute()
    ->fetchField();


  if (!$total) {
    return drush_log(dt('No items found for populating'), 'status');
  }

  $counter = 0;

  while ($counter <= $total) {
    $query = clone $base_query;
    $result = $query
      ->range(0, 100)
      ->condition('entity_id', $id, '>')
      ->execute()
      ->fetchAllAssoc('entity_id');

    if (empty($result)) {
      // We are done.
      return drush_print(dt('Done.'));
    }

    $target_type = $field['settings']['target_type'];
    // by loading the entity, the "target_id" is populated.
    $entities = entity_load($target_type, array_keys($result));
    foreach ($entities as $entity) {
      ++$counter;
      $wrapper = entity_metadata_wrapper($target_type, $entity);
      // Re save the entity.
      $wrapper->save();

      $id = $wrapper->getIdentifier();


      $params = array(
        '@count' => $counter,
        '@total' => $total,
        '@id' => $wrapper->getIdentifier(),
        '@type' => $wrapper->type(),
      );

      drush_log(dt('@count / @total: Re-saved @id of type @type.', $params), 'status');
    }
  }
}
