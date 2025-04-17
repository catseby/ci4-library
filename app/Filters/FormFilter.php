<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FormFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');
        $user = $auth->user();

        $uri = $request->getUri();
        $table_name = $uri->getSegment(2);

        log_message("debug", $table_name);
        $type = $uri->getSegment(3);

        $premissions = $this->getPremissions($table_name, $user);

        if (!$premissions[$type]) {
            return service('response')
                ->setStatusCode(403) // Forbidden
                ->setBody('Access Denied Filter');
        }
    }

    public function getPremissions($table_name, $user)
    {
        $db = db_connect();
        $table = $db->query("SELECT * FROM table_metadata WHERE table_name = '" . $table_name . "';")->getResultArray()[0];

        $show = json_decode($table["show_permissions"]);
        $add = json_decode($table["add_permissions"]);
        $edit = json_decode($table["edit_permissions"]);

        $actions = ["fetch" => $show, "add" => $add, "edit" => $edit, 'delete' => $edit];

        $premissions = ["show_created" => false, "edit_created" => false];

        foreach ($actions as $key => $action) {
            $premissions[$key] = true;
            if ($action != null) {
                foreach ($action as $premission) {
                    if ($key == "fetch" && $premission == "user.created") {
                        $premissions["show_created"] = true;
                    } else if ($key == "edit" && $premission == "user.created") {
                        $premissions["edit_created"] = true;
                    } else if (!$user->can($premission)) {
                        $premissions[$key] = false;
                        break;
                    }
                }
            }
        }

        return $premissions;
    }

    public function columnPremissionDenied($required_permissions, $user)
    {

        if ($required_permissions == null) {
            return false;
        }

        $permissions = json_decode($required_permissions);

        foreach ($permissions as $permission) {
            if (!$user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function getAllowedColumns($table_name)
    {
        $db = db_connect();

        $column_sql = "SELECT column_name, required FROM public.form_metadata WHERE table_name = '" . $table_name . "' ORDER BY order_position ASC;";
        $columns_entries = $db->query($column_sql)->getResultArray();

        $auth = service('auth');
        $user = $auth->user();

        $filter = new FormFilter();

        $allowed = [];

        foreach ($columns_entries as $j => $column_entry) {
            $column_metadata = $db->query("SELECT required_permissions FROM public.column_metadata WHERE column_name = '" . $column_entry["column_name"] . "';")->getResultArray()[0];

            if ($column_entry["required"] == "t" || !$filter->columnPremissionDenied($column_metadata["required_permissions"], $user)) {
                array_push($allowed, $column_entry['column_name']);
            }
        }

        return $allowed;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
