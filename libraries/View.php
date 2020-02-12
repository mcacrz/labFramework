<?php
namespace Libraries;

use Views;

class View
{
    public function __construct(){}

    public function view($view,$params){

        extract($params);

        include '../views/'.$view.'.php';

    }
}