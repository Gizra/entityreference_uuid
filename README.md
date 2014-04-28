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