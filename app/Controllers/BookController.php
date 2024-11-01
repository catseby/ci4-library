<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BookModel;
use App\Models\CategoryModel;
use App\Models\ImageModel;

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

    public function fetch() {
        $bookModel = new BookModel();
        $books = $bookModel->asArray()->findAll();

        return $this->response->setJSON([
            'error' => false,
            'books' => $books,
        ]);
    }

    public function add() {

        $data = [
            'isbn' => $this->request->getPost('isbn'),
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'category' => $this->request->getPost('category'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $bookModel = new BookModel();
        $bookModel->insert($data);

        $id = $bookModel->getInsertID();
        $files = $this->request->getFiles();

        foreach ($files['files'] as $file) {
            $file_name = $file->getRandomName();
            
            $file_data = [
                'book_id' => $id,
                'image' => $file_name,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $file->move('uploads', $file_name);
            $imageModel = new ImageModel();
            $imageModel->insert($file_data);
        }
    }

public function edit($id) {
    $bookModel = new BookModel();
    $book = $bookModel->find($id);

    return $this->response->setJSON([
        'error' => false,
        'book' => $book,
    ]);
}

public function update($id) {
    $bookModel = new BookModel();
    $book = $bookModel->find($id);

    $data = [
        'isbn' => $this->request->getPost('isbn'),
        'title' => $this->request->getPost('title'),
        'author' => $this->request->getPost('author'),
        'category' => $this->request->getPost('category'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $bookModel->update($id, $data);
    return;
}

    public function destroy($id) {
        $bookModel = new BookModel();
        $bookModel->delete($id);
    }
}
