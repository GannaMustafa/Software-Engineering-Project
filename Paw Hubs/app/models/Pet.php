<?php

class Pet {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByOwner($ownerId) {
        $stmt = $this->db->prepare(
            "SELECT p.*
             FROM pets p
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             WHERE p.owner_id = ? OR po.user_id = ?"
        );
        $stmt->execute([$ownerId, $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
