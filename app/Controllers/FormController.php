<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormModel;

class FormController extends BaseController
{
    public function index($table){
        $formModel = new FormModel();

        $results = $formModel->getForm($table);

        $schema = [
            "type" => "object",
            "title" => ucfirst($table),
            "properties" => []
        ];
        $form = [];

        foreach($results as $index => $result) {
            $property = [
                'type' => $result['schema_type'],
                'title' => $result['column_title'],
                'required' => true
            ];

            $field = [
                'key' => $result['column_name'],
                'placeholder' => $result['column_title']
            ];

            $schema['properties'][$result['column_name']] = $property;
            array_push($form, $field);
        }

        array_push(
            $form,
            [
                "type" => "submit",
                "title" => "Create"
            ]
        );

        $link = $table . '/add/';
        $data = [
            'name' => $table,
            'schema' => json_encode($schema),
            'form' => json_encode($form),
            'link' => $link,
            'type' => 'post',
            'values' => '{}'
        ];
        
        return view("form_test", $data);
    }
}   
