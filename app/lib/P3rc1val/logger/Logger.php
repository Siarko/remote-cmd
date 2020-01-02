<?php


namespace P3rc1val\logger;


use P3rc1val\config\EnvProvider;
use P3rc1val\util\SingletonType;

class Logger extends SingletonType {

    const CONFIG_KEY = 'ENABLED_LOGGERS';

    private $instances = [];

    private $enabled = [];

    public function __construct() {
        parent::__construct();
        $this->enabled = $this->readLoggerConfig();
    }


    public static function install() {
        register_shutdown_function(function(){
            self::saveAll();
        });
    }

    /**
     * Check if logger class should be enabled
     * @param $loggerName string Logger name
     * @return bool
     */
    public function isEnabled($loggerName){
        if($this->enabled === true){
            return true;
        }
        if(in_array($loggerName, $this->enabled)){
            //return true;
        }
        return false;
    }

    /**
     * @param string $className
     * @return AbstractLogger
     */
    public static function get($className = 'P3rc1val\logger\impl\StandardLogger'){
        $instance = new $className(self::getInstance());
        if($instance instanceof AbstractLogger){
            static::getInstance()->instances[] = $instance;
            return $instance;
        }

        return null;
    }

    public static function saveAll(){
        /* @var LoggerInterface $instance*/
        foreach (static::getInstance()->instances as $instance) {
            $instance->save();
        }
    }

    private function readLoggerConfig() {
        $config = EnvProvider::getEnvKey(self::CONFIG_KEY, []);
        if($config === '*'){
            return true;
        }
        $result = [];
        if(is_array($config)){
            foreach ($config as $name) {
                $result[] = $name;
            }
        }else{
            $result[] = $config;
        }
        return $result;
    }

}