<?php

namespace themes899;

class Container {
    private $registry;

    private $components = array();
    
    public function __construct($registry) {
        $this->registry = $registry;
    }
    
    public function __get($key) {
        return $this->getComponent($key);
    }
    
    protected function setComponent($key) {
        if (isset($this->components[$key])) {
            return;
        }
        if (file_exists(DIR_SYSTEM . 'library/themes899/' . $key . '.php')) {
            $class = 'Themes899\\' . ucfirst($key);
            $this->components[$key] = new $class($this->registry);
        }else {
            throw new \Exception("Component not found");
        }
    }

    public function getComponent($key) {
        if(isset($this->components[$key])) {
            return $this->components[$key];
        }else{
            try {
                $this->setComponent($key);
                return $this->components[$key];
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return null;
    }
}

