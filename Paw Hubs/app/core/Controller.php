<?php

class Controller {
    public function view($view, $data = []) {
        if (file_exists('../app/views/' . $view . '.php')) {
            extract($data);
            require_once '../app/views/' . $view . '.php';
        } else {
            die("View does not exist.");
        }
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    protected function fetchOne($db, $query, $params = []) {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchAll($db, $query, $params = []) {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
