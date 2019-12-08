<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 06.10.2018
 * Time: 00:15
 */

namespace P3rc1val\templates;

class TemplateParser {

    private $_html = '';
    private $_filePath = null;
    private $_contextVariables = [];

    private $_cssFiles = [];

    function __construct($html = '') {
        $this->_html = $html;
    }

    public function setHtml($html){
        $this->_html = $html;
    }

    public function setFilePath($path){
        $this->_filePath = $path;
    }

    function __set($name, $value) {
        $this->_contextVariables[$name] = $value;
    }

    function __get($name) {
        if(array_key_exists($name, $this->_contextVariables)){
            return $this->_contextVariables[$name];
        }
        return null;
    }

    private function getTemporaryFilePath(){
        $tmpFile = '.tmpInclude';
        file_put_contents($tmpFile, $this->_html);
        return $tmpFile;
    }

    private function cleanTmpFile($path){
        unlink($path);
    }
    public function getFilePath(){
        return $this->_filePath;
    }

    public function addCssFile($path){
        $this->_cssFiles[] = $path;
    }

    public function getStylesheets(){
        $string = '';
        foreach ($this->_cssFiles as $cssFile) {
            $string .= '<link rel="stylesheet" type="text/css" href="'.$cssFile.'.css"/>';
        }
        return $string;
    }

    public function importAssets(TemplateParser $parser){
        $this->_cssFiles = array_merge($this->_cssFiles, $parser->_cssFiles);
    }

    public function parse(){
        ob_start();
        if($this->_filePath != null){
            if(!file_exists($this->_filePath)){
                return false;
            }else{
                include($this->_filePath);
            }
        }else{
            $path = $this->getTemporaryFilePath();
            include($path);
            $this->cleanTmpFile($path);
        }
        return ob_get_clean();
    }
}