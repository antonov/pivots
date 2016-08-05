<?php

namespace Antonov\Pivots;

// TODO: Some logging system should be implemented here.
class Pivot {
  protected $properties;
  protected $fields;
  protected $fields_config;
  protected $current_language;

  /**
  * Creates pivots basic structure and read the configuration of the pivot entity type.
  *
  * @param $type string
  */
  public function __construct($type) {
    $default_properties = [
      '_id',
      'type',
      'default_language',
      'languages',
      'url',
    ];
    foreach ($default_properties as $default_property) {
      $this->{$default_property.'Property'} = '';
    }

    $this->default_languageProperty = 'en';
    $this->typeProperty = $type;
    $this->languagesProperty = [];

    $this->readConfig($type);
  }

  /**
   * Set the current language of the Pivot.
   *
   * @param mixed $current_language
   */
  public function setCurrentLanguage($current_language) {
    $this->current_language = $current_language;
  }

  /**
  * Read the configuration of the entity type.
  *
  * @param $type string Entity type configuration to read (e.g. common_pages).
  */
  private function readConfig($type) {
    $config = Config::getInstance()->getConfig();
    $this->fields_config = $config->{$type};
  }

  /**
  * Retrieve the configuration of the field (to be used on foreach).
  * 
  * @return $field_config stdClass Object with configuration of the field.
  *
  */
  public function getFieldConfig(){
    foreach($this->fields_config as $field_config) {
      yield $field_config;
    }
  }

  /**
  * Save the pivot to json file and add the pivot id to the list.json file.
  */
  public function save() {
    $path = __DIR__ . '/../fixtures/' . $this->typeProperty;
    if (!file_exists($path)) {
      mkdir($path, 0777, TRUE);
    }
    file_put_contents( $path . '/document-' . $this->_idProperty . '.json', $this->encode());

    if (!file_exists($path . '/list.json')) {
      $list = [];
    } else {
      $list = json_decode(file_get_contents( $path . '/list.json'));
    }
    $list[] = $this->_idProperty;
    file_put_contents( $path . '/list.json',  json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  }

  /**
  * Returns the JSON encoded object of the pivot.
  *
  * @return string Json pivot.
  */
  protected function encode() {
    $pivot_object = $this->properties + ['fields' => $this->fields];
    return json_encode( (object)$pivot_object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  /**
  * Magic method to set the pivot data.
  *
  * @param $name string
  * @param $value string
  */
  public function __set($name, $value) {
    $suffix = "Property";
    if (strpos($name, "Property") !== FALSE) {
      $name = substr($name, 0, strpos($name, $suffix));
      $this->properties[$name] = $value;
    } else {
      if (!in_array($this->current_language, $this->properties['languages'])) {
        array_push($this->properties['languages'], $this->current_language);
      }
      $this->fields->$name->{$this->current_language}[] = $value;
    }
  }

  /**
  * Magic method to get the pivot data.
  *
  * @param $name string
  * 
  * @return string
  */
  public function __get($name) {
    $suffix = "Property";
    if (strpos($name, "Property") !== FALSE) {
      $name = substr($name, 0, strpos($name, $suffix));
      return $this->properties[$name];
    } else {
      return $this->fields[$name];
    }
  }

}