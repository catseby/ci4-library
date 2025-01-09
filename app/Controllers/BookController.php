<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BookModel;
use App\Models\CategoryModel;
use App\Models\ImageModel;
use App\Models\FormTemplateModel;


class BookController extends BaseController
{
    public function categories() {
        $categoryModel = new CategoryModel();
        
        return $this->response->setJSON([
            'error' => false,
            'categories' => $categoryModel->getCategories(),
        ]);
    }

    public function categoryForm() {
        $ftm = new FormTemplateModel();

        $form = $ftm->getForm('categories');

        return view("form_test",$form);
    }

    public function index()
    {
        return view('index');
    }

    public function fetch() {

        $categories = $this->request->getGet('categories');
        $tags = $this->request->getGet('tags');

        $bookModel = new BookModel();

        $imageModel = new ImageModel();
        $images = $imageModel->asArray()->findAll();

        if ($categories and $tags) {
            $books = $bookModel->filterByCategoryAndTags($categories,$tags);
            
            return $this->response->setJSON([
                'error' => false,
                'books' => $books,
                'images' => $images,
            ]);
        }
        else if ($categories) {
            $books = $bookModel->filterByCategory($categories);
            
            return $this->response->setJSON([
                'error' => false,
                'books' => $books,
                'images' => $images,
            ]);
        }
        else if ($tags) {
            $books = $bookModel->filterByTags($tags);
            
            return $this->response->setJSON([
                'error' => false,
                'books' => $books,
                'images' => $images,
            ]);
        }
        else {
            $books = $bookModel->asArray()->findAll();

            return $this->response->setJSON([
                'error' => false,
                'books' => $books,
                'images' => $images,
            ]);
        }
    }


    public function add() {

        $data = [
            'isbn' => $this->request->getPost('isbn'),
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'category' => $this->request->getPost('category'),
            'tags' => $this->request->getPost('tags'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $bookModel = new BookModel();
        $bookModel->insert($data);

        $id = $bookModel->getInsertID();
        $files = $this->request->getFiles();

        foreach ($files['files'] as $file) {
            $file_name = $file->getName();

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
    $imageModel = new ImageModel();

    $data = [
        'isbn' => $this->request->getPost('isbn'),
        'title' => $this->request->getPost('title'),
        'author' => $this->request->getPost('author'),
        'category' => $this->request->getPost('category'),
        'tags' => $this->request->getPost('tags'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $bookModel->update($id, $data);

    $images = $imageModel->asArray()->where('book_id', $id)->findAll();
    foreach ($images as $image) {
        unlink("./uploads/" . $image['image']);
    }
    $imageModel->where('book_id', $id)->delete();

    $files = $this->request->getFiles();
    foreach ($files['files'] as $file) {
        $file_name = $file->getName();

        $file_data = [
            'book_id' => $id,
            'image' => $file_name,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $file->move('uploads', $file_name);
        $imageModel = new ImageModel();
        $imageModel->insert($file_data);
    }

    return;
}

    public function destroy($id) {
        $bookModel = new BookModel();
        $imageModel = new ImageModel();

        $images = $imageModel->asArray()->where('book_id', $id)->findAll();
        $bookModel->delete($id);

        foreach ($images as $image) {
            unlink("./uploads/" . $image['image']);
        }
    }
}
