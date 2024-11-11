<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['isbn', 'title', 'author', 'category', 'tags', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function filterByCategoryAndTags($categories, $tags)
    {

        $array = explode(",", $tags);
        $quoted_array = array_map(function ($item) {
            return '"' . $item . '"';
        }, $array);
        $quoted_string = implode(",", $quoted_array);

        $sql = "
        SELECT * FROM public.books WHERE category::jsonb @> '[" . $categories . "]'::jsonb AND tags::jsonb @> '[" . $quoted_string . "]'::jsonb;
        ";

        $query = $this->db->query($sql);

        $rows = $query->getResultArray();

        return $rows;
    }

    public function filterByCategory($categories)
    {
        $sql = "
        SELECT * FROM public.books WHERE category::jsonb @> '[" . $categories . "]'::jsonb;
        ";

        $query = $this->db->query($sql);

        $rows = $query->getResultArray();

        return $rows;
    }

    public function filterByTags($tags)
    {
        $array = explode(",", $tags);
        $quoted_array = array_map(function ($item) {
            return '"' . $item . '"';
        }, $array);
        $quoted_string = implode(",", $quoted_array);

        $sql = "
        SELECT * FROM public.books WHERE tags::jsonb @> '[" . $quoted_string . "]'::jsonb;
        ";

        $query = $this->db->query($sql);

        $rows = $query->getResultArray();

        return $rows;
    }
}
