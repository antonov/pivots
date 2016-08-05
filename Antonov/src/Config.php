<?php

namespace Antonov\Pivots;

class Config
{
    /**
     * @var $instance Config The reference to *Config* instance of this class
     */
    private static $instance;

    /**
     * @var $config stdClass Contains an object with parsed configuration.
     */
    private $config;
    
    /**
     * Returns the instance of this class.
     *
     * @return Config The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    /**
     * Read the configuration files under /config directory
     */
    protected function __construct()
    {
        $this->config = json_decode(file_get_contents( __DIR__ . '/../config/config.json'));
        $entities = array_filter((array) $this->config, function($key){
            return (strpos($key, 'entities') !== FALSE);
        }, ARRAY_FILTER_USE_KEY);
        foreach ($entities as $entity_type => $entity ) {
            foreach ($entity as $entity_name) {
                $config = json_decode(file_get_contents( __DIR__ . '/../config/entity_types/'.$entity_name.'.json'));
                $this->config->{$entity_name} = $config;
            }
        }
    }

    /**
    * Returns the configuration object.
    * 
    * @return stdClass
    */
    public function getConfig() {
        return $this->config;
    }
}