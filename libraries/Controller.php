<?php
namespace Libraries;

use Libraries;
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
        $this->curl = new Libraries\Curl();
        $this->request = new Libraries\Request();
        $this->response = new Libraries\Response();
        $this->view = new Libraries\View();
        $this->container = $this->container();
    }

    protected function arrayMethods($array = null)
    {
        return new Libraries\ArrayMethods((!is_array($array)) ?: $array);
    } 

    protected function db($db)
    {
        return ($db === null) ? false : new Libraries\DB($db);
    }
}