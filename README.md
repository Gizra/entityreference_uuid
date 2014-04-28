# Entity Reference UUID

This module adds a "uuid" column to the entity reference field.

![](https://www.evernote.com/shard/s206/sh/7e1f4338-4f84-4b6e-ada7-046845b8524d/3d4fbf4279997bb6b0d991bf2a0d2cbc/res/3895c433-890b-409a-98f7-129ab2afb533/skitch.png?resizeSmall&width=832)

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
