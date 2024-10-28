<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class BookController extends BaseController
{
    public function index()
    {
        return view('index');
    }
}
