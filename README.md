# Entity Reference UUID

This module adds a "uuid" column to the entity reference field.

When creating a new field, the ``uuid`` behavior should be enabled under the
``behaviors`` property.

Note: This behavoir must be enabled when the field is created, as otherwise the
schema cannot be altered by Drupal.

```php
$field = array(
  'entity_types' => array('node'),
  'settings' => array(
    'handler' => 'base',
    'target_type' => 'node',
    'handler_settings' => array(
      'behaviors' => array(
        'uuid' => array(
          'status' => TRUE,
        ),
      ),
    ),
  ),
  'field_name' => 'field_node',
  'type' => 'entityreference',
  'cardinality' => 1,
);
field_create_field($field);
```