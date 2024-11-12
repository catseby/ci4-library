<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormModel;
use App\Models\FormTemplateModel;
use CodeIgniter\HTTP\ResponseInterface;

class FormController extends BaseController
{
    public function index($name)
    {
        $ftm = new FormTemplateModel();
        $template = $ftm->getForm($name);

        $link = $name . '/add';

        $data = [
            'name' => $template['name'],
            'schema' => $template['schema'],
            'form' => $template['form'],
            'link' => $link,
            'type' => 'post',
            'values' => '{}'
        ];

        return view("form_test", $data);
    }

    public function add($name)
    {
        $json = $this->request->getJSON();

        $db = db_connect();

        $data = [];
        foreach ($json as $key => $value) {
            $data[$key] = $value;
        }

        $query = 'INSERT INTO public.' . $name . ' (' . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ');';
        $db->query($query, array_values($data));
    }

    public function edit($name,$id){
        $ftm = new FormTemplateModel();
        $template = $ftm->getForm($name);

        $db = db_connect();

        $sql = 'SELECT * FROM public.' . $name . ' WHERE id = ' . $id;
        $query = $db->query($sql);

        $link = $name . '/' . $id . '/edit';
        $data = [
            'name' => $template['name'],
            'schema' => $template['schema'],
            'form' => $template['form'],
            'link' => $link,
            'type' => 'post',
            'values' => json_encode($query->getResult())
        ];

        return view("form_test", $data);
    }

    public function destroy($name, $id)
    {
        $db = db_connect();

        $query = 'DELETE FROM public.' . $name . ' WHERE id = ' . $id;
        $db->query($query);

        $data = [
            'name' => "Entry from " . $name . ' was deleted.',
            'schema' => '{}',
            'form' => '{}',
            'link' => "",
            'type' => 'get',
        ];

        return view("form_test", $data);

    }
}
