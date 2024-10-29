<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CategoryModel;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categoryModel = new CategoryModel();
        $categories = [
            "Fiction",
            "Non-Fiction",
            "Mystery",
            "Fantasy",
            "Science Fiction",
            "Historical Fiction",
            "Romance",
            "Thriller",
            "Biography",
            "Self-Help",
            "Graphic Novel",
            "Young Adult",
            "Children's",
            "Horror",
            "Poetry"
        ];

        foreach ($categories as $category_name) {
            $data = [
                'category_name' => $category_name,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $categoryModel->insert($data);
        }
    }
}
