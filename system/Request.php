<?php
namespace System;

class Request{
    public function __construct(){}

    public static function jsonBody()
    {
        return json_decode(file_get_contents('php://input'),true);
    }
}