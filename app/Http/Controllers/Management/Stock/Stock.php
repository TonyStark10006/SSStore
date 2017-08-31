<?php

namespace App\Http\Controllers\Management\Stock;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Stock extends Controller
{
    //
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
