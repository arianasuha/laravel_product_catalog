<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Alias to avoid conflict with App\Http\Controllers namespace

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
