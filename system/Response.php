<?php
namespace System;

class Response{
    public function __construct(){}

    public function jsonOuput($arr){
        echo json_encode($arr);
    }
}