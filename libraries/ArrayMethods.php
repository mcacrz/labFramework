<?php
namespace Libraries;

class ArrayMethods {
  private $value;

  public function __construct($array = null){
    (!is_array($array)) ?: $this->value = $array;
  }
  
  public function __call($name,$arguments)
  {
    echo 'O método '.$name.' não existe na classe '.get_class($this);
    die();
  }

  public function filter($fn, $arr = null){
    $this->value = (isset($arr)) ? $arr : $this->value;

    $this->value = (is_array($this->value)) 
    ? array_filter($this->value,$fn)
    : [];

    return $this;
  }
  
  public function map($fn,$arr = null){
    $this->value = (isset($arr)) ? $arr : $this->value;
    
    $this->value = (is_array($this->value))
    ? array_map($fn, $this->value)
    : [];
    
    return $this;
  }

  public function reduce($fn, $iniValue = null, $arr = null){
    $this->value = (isset($arr)) ? $arr : $this->value;

    $this->value = (is_array($this->value))
    ? array_reduce($this->value,$fn,$iniValue)
    : [];

    return $this;
  }

  public function val(){
    return $this->value;
  }
};
