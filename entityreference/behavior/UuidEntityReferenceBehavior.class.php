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
      // Field doesn't exist.
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
}
