<?php
namespace Services;

use Libraries\Container;

trait setContainer{
    function container()
    {
        $c = new Container();

        return $c;
    }
}