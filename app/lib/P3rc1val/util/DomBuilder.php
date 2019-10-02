<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 02.07.2017
 * Time: 20:54
 */

namespace P3rc1val\util;

class DomBuilder {

    private $styles;
    private $scripts;
    private $inBodyScripts;
    private $body;
    private $head;
    private $htmlStyle;
    private $headStyle;
    private $bodyStyle;

    private $darkMode = false;

    private $jsonMode = false;

    function __construct() {
        $this->darkMode = (array_key_exists('darkmode', $_SESSION) and $_SESSION['darkmode'] == 1);
    }


    public function setCharset($charset){
        $element = new HtmlElement("meta");
        $element->charset = $charset;
        $this->head .= $element;
    }

    public function meta($name, $content){
        $element = new HtmlElement("meta");
        $element->name = $name;
        $element->content = $content;
        $this->head .= $element;
    }

    public function setHome($url){
        $element = new HtmlElement("base");
        $element->href = $url;
        $this->head .= $element;
    }


    public function embedStyle($link){
        $element = new HtmlElement("link");
        $element->rel = "stylesheet";
        $element->href = $link;
        $this->styles .= $element;
    }


    public function embedScript($link, $inHead = true){
        $element = new HtmlElement("script");
        $element->type = "text/javascript";
        $element->src = $link;
        if($inHead){
            $this->scripts .= $element;
        }else{
            $this->inBodyScripts .= $element;
        }
    }

    public function body($content){
        if(gettype($content) !== "string"){
            $content = print_r($content, true);
        }
        $this->body .= $content;
    }

    public function setBody($value){
        $this->body = $value;
    }

    public function jsonMode($mode = true){
        $this->jsonMode = $mode;
    }

    public function inJsonMode(){
        return $this->jsonMode;
    }

    public function build($print = false){
        if($this->jsonMode){
            $page = $this->body;
            header('Content-Type: application/json');
        }else{
            $page = new HtmlElement("html");
            $head = new HtmlElement("head");
            $body = new HtmlElement("body");

            if ($this->htmlStyle){$page->class = $this->htmlStyle;}
            if ($this->headStyle){$head->class = $this->headStyle;}
            if ($this->bodyStyle){$body->class = $this->bodyStyle;}

            $head->value = $this->head.$this->styles.$this->scripts;

            $body->value = $this->body;
            $body->value .= $this->inBodyScripts;

            $page->value = $head;
            $page->value .= $body;
        }
        if($print){
            echo($page);
        }else{
            return $page;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isDarkMode(){
        return $this->darkMode;
    }

    /**
     * @param bool $darkMode
     */
    public function setDarkMode($darkMode) {
        $this->darkMode = $darkMode;
    }
} 