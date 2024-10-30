<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BookModel;
use App\Models\CategoryModel;

class BookController extends BaseController
{
    public function categories() {
        $categoryModel = new CategoryModel();
        
        return $this->response->setJSON([
            'error' => false,
            'categories' => $categoryModel->getCategories(),
        ]);
    }
    public function index()
    {
        return view('index');
    }
}
