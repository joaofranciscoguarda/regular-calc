<?php

namespace App\Http\Controllers;

use jcubic\Expression;

class Controller
{
    function resolve(String $exp) {
    $s = new Expression();
    return $s->evaluate($exp);
    }
}
