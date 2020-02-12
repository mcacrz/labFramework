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

    public function __construct(){
        $this->curl = new Libraries\Curl();
        $this->request = new Libraries\Request();
        $this->response = new Libraries\Response();
        $this->view = new Libraries\View();
        $this->container = $this->container();
    }
}