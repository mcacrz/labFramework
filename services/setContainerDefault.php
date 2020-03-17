<?php
namespace Services;

use System\Container;

trait setContainer{
    function container()
    {
        $c = new Container();

        return $c;
    }
}