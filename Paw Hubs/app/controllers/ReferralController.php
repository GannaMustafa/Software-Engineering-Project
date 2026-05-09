<?php

class ReferralController extends Controller {
    public function index() {
        header("Location: index.php?url=admin/referrals");
        exit;
    }

    private function handleAccessRule($db) {
        $subjectRole = trim($_POST['subject_role'] ?? '');
        $resourceType = trim($_POST['resource_type'] ?? '');
        $clinicScope = trim($_POST['clinic_scope'] ?? '');
        $permissionLevel = trim($_POST['permission_level'] ?? 'view');
        $accessDuration = trim($_POST['access_duration'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($subjectRole === '' || $resourceType === '') {
            return [null, ['Role and resource type are required.']];
        }

        $stmt = $db->prepare(
            "INSERT INTO access_controls (subject_role, resource_type, clinic_scope, permission_level, access_duration, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$subjectRole, $resourceType, $clinicScope, $permissionLevel, $accessDuration, $status, $_SESSION['user_id'] ?? null]);
        $this->audit($db, 'access_rule_created', 'access_controls', (int) $db->lastInsertId(), "Created $permissionLevel access for $subjectRole on $resourceType.");

        return ['Access rule saved successfully.', []];
    }

    private function referrals($db) {
        return $this->fetchAll(
            $db,
            "SELECT r.*, p.name AS pet_name, from_user.username AS sender_name, to_user.username AS receiver_name
             FROM referrals r
             LEFT JOIN pets p ON p.id = r.pet_id
             LEFT JOIN veterinarians from_v ON from_v.id = r.from_vet_id
             LEFT JOIN users from_user ON from_user.id = from_v.user_id
             LEFT JOIN veterinarians to_v ON to_v.id = r.to_vet_id
             LEFT JOIN users to_user ON to_user.id = to_v.user_id
             ORDER BY r.requested_at DESC, r.id DESC"
        );
    }

    private function accessControls($db) {
        return $this->fetchAll(
            $db,
            "SELECT ac.*, u.username AS created_by_name
             FROM access_controls ac
             LEFT JOIN users u ON u.id = ac.created_by
             ORDER BY ac.created_at DESC, ac.id DESC"
        );
    }

    private function transferLogs($db) {
        return $this->fetchAll(
            $db,
            "SELECT al.*, COALESCE(u.username, 'System') AS sender_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE LOWER(al.action) LIKE '%transfer%'
                OR LOWER(al.action) LIKE '%referral%'
                OR LOWER(al.action) LIKE '%file%'
                OR LOWER(COALESCE(al.entity_type, '')) IN ('lab_reports', 'referrals', 'medical_records')
             ORDER BY al.created_at DESC, al.id DESC
             LIMIT 100"
        );
    }

    private function securityAlerts($db) {
        return $this->fetchAll(
            $db,
            "SELECT al.*, COALESCE(u.username, 'System') AS actor_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE LOWER(al.action) LIKE '%unauthorized%'
                OR LOWER(al.action) LIKE '%denied%'
                OR LOWER(al.action) LIKE '%failed%'
                OR LOWER(al.action) LIKE '%download%'
                OR LOWER(al.action) LIKE '%access%'
                OR LOWER(COALESCE(al.details, '')) LIKE '%permission%'
             ORDER BY al.created_at DESC, al.id DESC
             LIMIT 100"
        );
    }

    private function audit($db, $action, $entityType, $entityId, $details) {
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, entity_type, entity_id, action, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'] ?? null, $entityType, $entityId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

   
}
