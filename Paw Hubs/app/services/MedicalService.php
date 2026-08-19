<?php

require_once '../app/models/MedicalRecord.php';

class MedicalService {
    private $medicalModel;

    public function __construct() {
        $this->medicalModel = new MedicalRecord();
    }

    public function getHealthSummary($petId) {
        $records = $this->medicalModel->getByPet($petId);

        if (!empty($records)) {
            foreach ($records as $record) {
                $this->logMedicalRecordEvent((int) ($record['id'] ?? 0), 'view', $petId, sprintf(
                    'User %s (id %d) viewed medical record #%d for pet #%d.',
                    $_SESSION['username'] ?? 'unknown',
                    $_SESSION['user_id'] ?? 0,
                    (int) ($record['id'] ?? 0),
                    $petId
                ));
            }
        } else {
            $this->logMedicalRecordEvent(null, 'view', $petId, sprintf(
                'User %s (id %d) viewed the medical record section for pet #%d with no records available.',
                htmlspecialchars($_SESSION['username'] ?? 'unknown'),
                $_SESSION['user_id'] ?? 0,
                $petId
            ));
        }

        return [
            "records" => $records,
            "status" => count($records) > 0 ? "Healthy" : "No records"
        ];
    }

    public function logMedicalRecordEvent($recordId, $action = 'view', $petId = null, $details = null) {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return;
        }

        $allowedActions = ['view', 'edit', 'delete', 'download'];
        $action = in_array($action, $allowedActions, true) ? $action : 'view';

        $recordId = $recordId !== null ? (int) $recordId : null;
        $petId = $petId !== null ? (int) $petId : null;

        $details = $details ?: sprintf(
                'User %s (id %d) performed %s on medical record #%s for pet #%s.',
                $_SESSION['username'] ?? 'unknown',
                $userId,
                ucfirst($action),
                $recordId ?? 'unknown',
                $petId ?? 'unknown'
            );
        $db = Database::getInstance()->getConnection();
        $this->writeAuditLog($db, $userId, $action, $details, 'medical_records', $recordId);
    }

    private function writeAuditLog($db, $userId, $action, $details, $entityType = null, $entityId = null) {
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, admin_id, action, details, entity_type, entity_id, ip_address) VALUES (?, NULL, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $details,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
