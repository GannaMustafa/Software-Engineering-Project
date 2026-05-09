<?php

class Pet {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByOwner($ownerId) {
        $stmt = $this->db->prepare("SELECT * FROM pets WHERE owner_id = ?");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
