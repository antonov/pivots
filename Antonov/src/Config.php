<?php

namespace Antonov\Pivots;


class Config
{
    /**
     * @var Config The reference to *Config* instance of this class
     */
    private static $instance;

    private $config;
    
    /**
     * Returns the *Config* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
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

    public function getConfig() {
        return $this->config;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}