<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FormTemplateSeeder extends Seeder
{
    public function run()
    {

        $sql = "SELECT table_name, " .
            "column_name, " .
            "CASE " .
            "WHEN domain_name IS NOT null THEN domain_name " .
            "ELSE data_type " .
            "END AS data_type " .
            "FROM information_schema.columns " .
            "WHERE table_schema = 'public' AND table_name NOT IN ('migrations','form_templates') " .
            "AND column_name NOT IN ('created_at', 'updated_at', 'id')" .
            "ORDER BY table_name, ordinal_position";

        $db = db_connect();

        $results = $db->query($sql)->getResultArray();

        $schemas = [];
        $forms = [];
        foreach ($results as $row) {
            $schema;
            $form;
            // if (in_array($row["table_name"], $schemas)) {
            //     $schema = $schemas[$row["table_name"]];
            // } 
            if (array_key_exists($row["table_name"], $schemas)) {
                $schema = $schemas[$row["table_name"]];
                $form = $forms[$row["table_name"]];
            } else {
                $schema = [
                    "type" => "object",
                    "title" => $row["table_name"],
                    "properties" => []
                ];
                $form = [];
            }

            // $property = [];
            switch ($row['data_type']) {

                case 'integer':
                    $schema['properties'][$row["column_name"]] = [
                        "type" => "integer",
                        "title" => ucfirst($row["column_name"]),
                        "required" => true
                    ];
                    array_push($form, ["key" => $row["column_name"]]);
                    break;

                case 'character varying':
                    $schema['properties'][$row["column_name"]] = [
                        "type" => "string",
                        "title" => ucfirst($row["column_name"]),
                        "required" => true
                    ];
                    array_push($form, ["key" => $row["column_name"]]);
                    break;

                case 'image':
                    $schema['properties'][$row["column_name"]] = [
                        "type" => "file",
                        "title" => ucfirst($row["column_name"])
                    ];
                    array_push(
                        $form,
                        [
                            "key" => $row["column_name"],
                            "type" => "file",
                            "image" => true,
                            "multiple" => true,
                            "accept" => ".png,.jpg"
                        ],
                        [
                            "id" => "image-display",
                            "type" => "section"
                        ]
                    );
                    break;

            }

            // $schema['properties'][$row["column_name"]] = $property;
            $schemas[$row["table_name"]] = $schema;
            $forms[$row["table_name"]] = $form;
        }

        foreach ($forms as $key => $value){
            array_push($value,
            [
                "type" => "submit",
                "title" => "Submit"
            ]);
            $forms[$key] = $value;
        }

        foreach($schemas as $key => $value) {
            $schema = json_encode($schemas[$key]);
            log_message("debug", $schema);
            $form = json_encode($forms[$key]);

            $input_sql = "INSERT INTO public.form_templates (name, schema, form)".
            "VALUES ('" . $key . "', '" . $schema . "', '" . $form  . "');";
            $db->query($input_sql);
        }
    }
}
