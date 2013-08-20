<?php

namespace Dan\MainBundle\Service;

use Symfony\Component\Yaml\Yaml;

class Config
{

    private $data;
    
    static private function getRootDir()
    {
        return __DIR__.'/../../../../app';
    }

    public function __construct($file = null, $rootPath = null)
    {
        switch ($file) {
            case 'parameters':
                $file = self::getRootDir().'/config/parameters.yml';
                $rootPath = 'parameters';
                break;
            case 'data':
            case null:
                $file = self::getRootDir().'/config/data.yml';
                $rootPath = '';
                break;
        }
        
        $this->rootPath = $rootPath;
        $this->data = Yaml::parse(file_get_contents($file));
        $this->data = $this->get($rootPath);
    }


    public function get($path=null)
    {
        if (!($path = trim($path))) {
            return $this->data;
        }
        $path = explode('.', $path);
        $data = $this->data;
        foreach ($path as $part) {
            if (!isset($data[$part])) {
                throw new \Exception('Configuration key '.implode('.',$path).' not found (rootPath:'.$this->rootPath.')');
            }
            $data = $data[$part];
        }

        return $data;
    }

}