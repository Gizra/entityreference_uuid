<?php

/**
 * @file
 * Contains UuidEntityReferenceBehavior
 */

class UuidEntityReferenceBehavior extends EntityReference_BehaviorHandler_Abstract {

  /**
   * Overrides EntityReference_BehaviorHandler_Abstract::schema_alter().
   *
   * Add UUID field.
   */
  public function schema_alter(&$schema, $field) {
    $schema['columns']['target_id']['not null'] = FALSE;
    $schema['columns']['uuid'] = array(
      'description' => 'The UUID of the referenced entity.',
      'type' => 'varchar',
      'length' => 128,
      'not null' => FALSE,
      'default' => NULL,
    );
  }

  /**
   * Overrides EntityReference_BehaviorHandler_Abstract::load().
   *
   * If only the UUID value is present, add the target-id to it.
   */
  public function load($entity_type, $entities, $field, $instances, $langcode, &$items) {
    if (!$items) {
      return;
    }
    $original_items = $items;
    $target_type = $field['settings']['target_type'];
    $uuid_field_name = variable_get('uuid_reference_field_name', 'field_uuid');
    if (!field_info_field($uuid_field_name)) {
      // UUID field doesn't exist.
      return;
    }

    foreach($original_items as $entity_id => $original_items) {
      foreach ($original_items as $delta => $item) {
        if (!empty($item['target_id']) || empty($item['uuid'])) {
          // Target ID is populated, or UUID is not.
          continue;
        }
        $query = new EntityFieldQuery();
        $result = $query->entityCondition('entity_type', $target_type)
          ->fieldCondition($uuid_field_name, 'value', $item['uuid'])
          ->range(0, 1)
          ->execute();

        if (!empty($result[$target_type])) {
          // Set the target ID.
          $items[$entity_id][$delta]['target_id'] = key($result[$target_type]);
        }
      }
    }
  }

  /**
   * Overrides EntityReference_BehaviorHandler_Abstract::presave().
   *
   * Save UUID along with the referenced entity ID.
   */
  public function presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
    $uuid_field_name = variable_get('uuid_reference_field_name', 'field_uuid');
    if (!field_info_field($uuid_field_name)) {
      // UUID field doesn't exist.
      return;
    }

    $field_name = $field['field_name'];
    $cardinality = $field['cardinality'];

    $wrapper = entity_metadata_wrapper($entity_type, $entity);

    $uuids = array();

    if ($cardinality == 1) {
      if ($id = $wrapper->{$field_name}->getIdentifier()) {
        $uuids[$id] = $wrapper->{$field_name}->{$uuid_field_name}->value();
      }
    }
    else {
      foreach ($wrapper->{$field_name} as $sub_wrapper) {
        $id = $sub_wrapper->getIdentifier();
        $uuids[$id] = $sub_wrapper->{$uuid_field_name}->value();
      }
    }


    foreach ($items as &$item) {
      if (!empty($item['target_id'])) {
        $id = $item['target_id'];
        $item['uuid'] = $uuids[$id];
      }
    }
  }
}
