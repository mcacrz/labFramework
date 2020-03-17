<?php
namespace System;

class Container{
    private $arrContainer = [];

    public function __construct(){}

    public function add($alias,$object){
        array_push($this->arrContainer, [$alias => $object]);
        return true;
    }

    public function get($alias){
        $value = array_reduce(
            array_filter($this->arrContainer, function ($item) use ($alias) {
                return (array_keys($item)[0] === $alias);
            }),
            function($acc,$item)use($alias){
                $acc = $item;
                return $acc[$alias]();
            },
            null
        );

        return (is_null($value)) ? false : $value;
    }  

    public function getList(){
        return array_map(function($item){ return array_keys($item)[0]; },$this->arrContainer);
    }
}