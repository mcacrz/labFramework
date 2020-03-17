<?php
namespace System;

use System;
use Services;

class Controller{
    use Services\setContainer;

    protected $view;
    protected $curl;
    protected $request;
    protected $response;
    protected $container;
    protected $db;

    public function __construct(){
        $this->curl = new System\Curl();
        $this->request = new System\Request();
        $this->response = new System\Response();
        $this->view = new System\View();
        $this->container = $this->container();
    }

    protected function arrayMethods($array = null)
    {
        return new System\ArrayMethods((!is_array($array)) ?: $array);
    } 

    protected function db($db)
    {
        return ($db === null) ? false : new System\DB($db);
    }
}