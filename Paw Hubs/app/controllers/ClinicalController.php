<?php

class ClinicalController extends Controller
{
    public function labHub()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $this->ensureLabHubSchema($db);
        $role = $_SESSION['role'] ?? 'pet_owner';
        $userId = (int) $_SESSION['user_id'];
        $vet = $this->fetchOne($db, "SELECT id FROM veterinarians WHERE user_id = ?", [$userId]);
        $owner = $this->fetchOne($db, "SELECT id FROM pet_owners WHERE user_id = ?", [$userId]);
        $vetId = $role === 'vet' ? (int) ($vet['id'] ?? 0) : null;
        $ownerId = $role === 'pet_owner' ? (int) ($owner['id'] ?? 0) : null;

        $message = null;
        $errors = [];
        $preview = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'upload_lab_report') {
                [$message, $errors, $preview] = $this->handleLabUpload($db, $role, $vetId, $ownerId);
            } elseif ($action === 'interpret_lab_result' && $role === 'vet') {
                [$message, $errors] = $this->handleLabInterpretation($db, $vetId);
            }
        }

        $reports = $this->labHubReports($db, $role, $vetId, $ownerId);
        foreach ($reports as $report) {
            $this->writeAudit($db, 'medical_record_accessed', 'lab_reports', (int) ($report['id'] ?? 0), 'Lab report listed in Lab Result Interpretation Hub.');
        }
        $stats = [
            'total' => count($reports),
            'critical' => count(array_filter($reports, fn($report) => strtolower($report['status'] ?? '') === 'critical')),
            'normal' => count(array_filter($reports, fn($report) => strtolower($report['status'] ?? '') === 'normal')),
            'pending' => count(array_filter($reports, fn($report) => strtolower($report['status'] ?? '') === 'pending')),
            'abnormal' => count(array_filter($reports, fn($report) => !empty($report['abnormal_flags']))),
            'reviewed' => count(array_filter($reports, fn($report) => strtolower($report['status'] ?? '') === 'completed'))
        ];

        $this->view('clinical/lab_hub', [
            'role' => $role,
            'pets' => $this->labHubPets($db, $role, $vetId, $ownerId),
            'vets' => $this->specialists($db),
            'reports' => $reports,
            'stats' => $stats,
            'message' => $message,
            'errors' => $errors,
            'preview' => $preview
        ]);
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $role = $_SESSION['role'] ?? 'pet_owner';
        if (!in_array($role, ['admin', 'vet', 'pet_owner'], true)) {
            http_response_code(403);
            die("Access denied.");
        }

        if ($role === 'admin') {
            header("Location: index.php?url=admin/clinical");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $this->ensureLabHubSchema($db);
        $this->ensureSurgeryRequestSchema($db);
        $userId = $_SESSION['user_id'];

        if ($role === 'vet') {
            $vet = $this->fetchOne($db, "SELECT id FROM veterinarians WHERE user_id = ?", [$userId]);
            $vetId = (int) ($vet['id'] ?? 0);
            $ownerId = null;
        } elseif ($role === 'pet_owner') {
            $owner = $this->fetchOne($db, "SELECT id FROM pet_owners WHERE user_id = ?", [$userId]);
            $ownerId = (int) ($owner['id'] ?? 0);
            $vetId = null;
        }

        $message = null;
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'request_surgery' && $role === 'pet_owner') {
                [$message, $errors] = $this->handleSurgeryRequest($db, $ownerId);
            } elseif ($action === 'send_owner_surgery_to_admin' && $role === 'vet') {
                [$message, $errors] = $this->handleOwnerSurgeryAdminForward($db, $vetId);
            } elseif ($action === 'upload_lab_report' && $role === 'vet') {
                [$message, $errors] = $this->handleLabUpload($db,$role,$vetId,$ownerId);
            } elseif ($action === 'initiate_referral' && $role === 'vet') {
                [$message, $errors] = $this->handleInitiateReferral($db, $vetId);
            } elseif ($action === 'submit_clinical_workflow' && $role === 'vet') {
                [$message, $errors] = $this->handleClinicalWorkflowRequest($db, $vetId);
            } elseif ($action === 'interpret_lab_result' && $role === 'vet') {
                [$message, $errors] = $this->handleLabInterpretation($db, $vetId);
            } elseif ($action === 'transfer_referral_case' && $role === 'vet') {
                [$message, $errors] = $this->handleReferralTransfer($db, $vetId);
            }
        }

        if ($role === 'vet') {
            $procedures = $this->procedures($db, $vetId);
            $labReports = $this->labReports($db, $vetId);
            $referrals = $this->referrals($db, $vetId);
            $stats = [
                'procedures' => count($procedures),
                'lab_reports' => count($labReports),
                'referrals' => count($referrals)
            ];
        } elseif ($role === 'pet_owner') {
            $procedures = $this->ownerProcedures($db, $ownerId);
            $labReports = $this->ownerLabReports($db, $ownerId);
            $referrals = $this->ownerReferrals($db, $ownerId);
            $stats = [
                'procedures' => count($procedures),
                'lab_reports' => count($labReports),
                'referrals' => count($referrals)
            ];
        }

        $this->view('clinical/index', [
            'role' => $role,
            'stats' => $stats,
            'procedures' => $procedures,
            'labReports' => $labReports,
            'referrals' => $referrals,
            'pets' => $role === 'vet' ? $this->allPets($db) : $this->ownerPets($db, $ownerId ?? 0),
            'specialists' => $this->specialists($db),
            'operatingRooms' => $role === 'vet' ? $this->availableOperatingRooms($db) : [],
            'equipment' => $role === 'vet' ? $this->availableSurgicalEquipment($db) : [],
            'message' => $message,
            'errors' => $errors
        ]);
    }

    public function surgeryManager()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $role = $_SESSION['role'] ?? 'pet_owner';
        if ($role !== 'vet') {
            if ($role === 'admin') {
                header("Location: index.php?url=admin/surgery");
                exit;
            }
            http_response_code(403);
            die("Access denied. Surgery Manager is available for vets only.");
        }

        $db = Database::getInstance()->getConnection();
        $this->ensureSurgeryRequestSchema($db);
        $userId = (int) $_SESSION['user_id'];
        $vet = $this->fetchOne($db, "SELECT id FROM veterinarians WHERE user_id = ?", [$userId]);
        $vetId = (int) ($vet['id'] ?? 0);

        $message = null;
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'submit_clinical_workflow') {
                [$message, $errors] = $this->handleClinicalWorkflowRequest($db, $vetId);
            } elseif ($action === 'send_owner_surgery_to_admin') {
                [$message, $errors] = $this->handleOwnerSurgeryAdminForward($db, $vetId);
            }
        }

        $procedures = $this->procedures($db, $vetId);
        $ownerSurgeryRequests = $this->ownerSurgeryRequestsForVet($db, $vetId);
        $permissions = $this->vetPermissions($db, $vetId);
        $workflowRequests = $this->vetWorkflowRequests($db, $vetId);
        $operatingRooms = $this->availableOperatingRooms($db);
        $equipment = $this->availableSurgicalEquipment($db);
        $specialists = $this->specialists($db);

        $this->view('clinical/vet_surgery_manager', [
            'role' => $role,
            'stats' => [
                'procedures' => count($procedures),
                'owner_requests' => count($ownerSurgeryRequests),
                'pending_admin' => count(array_filter($workflowRequests, fn($request) => strtolower($request['action_key'] ?? '') === 'surgery_booking' && strtolower($request['admin_status'] ?? '') === 'pending')),
                'approved' => count(array_filter($workflowRequests, fn($request) => strtolower($request['action_key'] ?? '') === 'surgery_booking' && strtolower($request['request_status'] ?? '') === 'approved'))
            ],
            'procedures' => $procedures,
            'ownerSurgeryRequests' => $ownerSurgeryRequests,
            'permissions' => $permissions,
            'workflowRequests' => $workflowRequests,
            'operatingRooms' => $operatingRooms,
            'equipment' => $equipment,
            'specialists' => $specialists,
            'message' => $message,
            'errors' => $errors
        ]);
    }

    public function referralsWorkflow()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $role = $_SESSION['role'] ?? 'pet_owner';
        if ($role !== 'vet') {
            if ($role === 'admin') {
                header("Location: index.php?url=admin/referrals");
                exit;
            }
            http_response_code(403);
            die("Access denied. Referrals Workflow is available for vets only.");
        }

        $db = Database::getInstance()->getConnection();
        $userId = (int) $_SESSION['user_id'];
        $vet = $this->fetchOne($db, "SELECT id FROM veterinarians WHERE user_id = ?", [$userId]);
        $vetId = (int) ($vet['id'] ?? 0);

        $message = null;
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'transfer_referral_case') {
            [$message, $errors] = $this->handleReferralTransfer($db, $vetId);
        }

        $procedures = $this->procedures($db, $vetId);
        $referrals = $this->referrals($db, $vetId);
        $specialists = $this->specialistDirectory($db, $vetId);

        $this->view('clinical/vet_referrals_workflow', [
            'role' => $role,
            'stats' => [
                'referrals' => $this->countReferrals($db, $vetId),
                'specialists' => count($specialists),
                'urgent' => count(array_filter($referrals, fn($referral) => strtolower($referral['priority'] ?? '') === 'urgent' || strtolower($referral['priority'] ?? '') === 'critical'))
            ],
            'referrals' => $referrals,
            'transferCases' => $this->transferCases($procedures, $referrals),
            'specialists' => $specialists,
            'message' => $message,
            'errors' => $errors
        ]);
    }

    public function resourceManager()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }

        $role = $_SESSION['role'] ?? 'pet_owner';
        if ($role !== 'admin') {
            http_response_code(403);
            die("Access denied. Surgery Resource Manager is available for admins only.");
        }

        header("Location: index.php?url=admin/clinical");
        exit;

        $db = Database::getInstance()->getConnection();
        $vetId = null;

        $scheduleMessage = null;
        $scheduleErrors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'schedule_procedure') {
            list($scheduleMessage, $scheduleErrors) = $this->handleProcedureBooking($db, $vetId, $role);
        }

        $procedures = $this->procedures($db, $vetId);
        $labReports = $this->labReports($db, $vetId);
        $referrals = $this->referrals($db, $vetId);
        $auditLogs = $this->auditLogs($db, $vetId, $role, 5);
        $adminWorkspace = [
            'rooms' => $this->operatingRooms($db),
            'equipment' => $this->surgicalEquipment($db),
            'bookings' => $this->procedureBookings($db, null, 8, true),
            'reports' => $this->clinicalReports($db),
            'accessControls' => $this->accessControls($db),
            'transferLogs' => $this->transferLogs($db),
            'securityAlerts' => $this->securityAlerts($db)
        ];

        $stats = [
            'procedures' => $this->countRows($db, 'medical_procedures', $vetId ? 'vet_id = ?' : null, $vetId ? [$vetId] : []),
            'lab_reports' => $this->countRows($db, 'lab_reports', $vetId ? 'vet_id = ?' : null, $vetId ? [$vetId] : []),
            'referrals' => $this->countReferrals($db, $vetId),
            'audit_logs' => count($auditLogs),
            'critical_labs' => $this->countRows($db, 'lab_reports', 'status = ?' . ($vetId ? ' AND vet_id = ?' : ''), array_merge(['critical'], $vetId ? [$vetId] : []))
        ];

        $pets = $this->pets($db);
        $operatingRooms = $this->operatingRooms($db);
        $equipment = $this->surgicalEquipment($db);
        $specialists = $this->specialists($db);
        $bookings = $this->procedureBookings($db, $vetId);

        $this->view('clinical/resource_manager', [
            'role' => $role,
            'stats' => $stats,
            'procedures' => $procedures,
            'labReports' => $labReports,
            'referrals' => $referrals,
            'auditLogs' => $auditLogs,
            'adminWorkspace' => $adminWorkspace,
            'pets' => $pets,
            'operatingRooms' => $operatingRooms,
            'equipment' => $equipment,
            'specialists' => $specialists,
            'bookings' => $bookings,
            'scheduleMessage' => $scheduleMessage,
            'scheduleErrors' => $scheduleErrors
        ]);
    }

    private function procedures($db, $vetId)
    {
        $where = $vetId ? "WHERE mp.vet_id = ? OR (mp.vet_id IS NULL AND LOWER(COALESCE(mp.status, '')) IN ('owner_requested', 'pending_vet_review'))" : '';
        return $this->fetchAll(
            $db,
            "SELECT mp.*, p.name AS pet_name, p.species, u.username AS owner_name, vu.username AS vet_name
             FROM medical_procedures mp
             LEFT JOIN pets p ON p.id = mp.pet_id
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             LEFT JOIN veterinarians v ON v.id = mp.vet_id
             LEFT JOIN users vu ON vu.id = v.user_id
             $where
             ORDER BY COALESCE(mp.procedure_date, DATE(mp.created_at)) DESC, mp.id DESC
             LIMIT 12",
            $vetId ? [$vetId] : []
        );
    }

    private function labReports($db, $vetId)
    {
        $where = $vetId ? 'WHERE lr.vet_id = ?' : '';
        return $this->fetchAll(
            $db,
            "SELECT lr.*, p.name AS pet_name, p.species, u.username AS owner_name, vu.username AS vet_name
             FROM lab_reports lr
             LEFT JOIN pets p ON p.id = lr.pet_id
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             LEFT JOIN veterinarians v ON v.id = lr.vet_id
             LEFT JOIN users vu ON vu.id = v.user_id
             $where
             ORDER BY COALESCE(lr.report_date, DATE(lr.created_at)) DESC, lr.id DESC
             LIMIT 12",
            $vetId ? [$vetId] : []
        );
    }

    private function handleLabUpload($db, $role, $vetId, $ownerId)
    {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $assignedVetId = (int) ($_POST['vet_id'] ?? 0);
        $testName = trim($_POST['test_name'] ?? '');
        $testType = trim($_POST['test_type'] ?? '');
        $resultSummary = trim($_POST['result_summary'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');
        $reportDate = trim($_POST['report_date'] ?? date('Y-m-d'));
        $rawValues = trim($_POST['raw_values'] ?? '');
        $referenceRanges = trim($_POST['reference_ranges'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $errors = [];

        if (!$petId || !$this->canAccessPet($db, $petId, $role, $vetId, $ownerId)) {
            $errors[] = 'Choose a valid pet record.';
        }
        if ($testName === '') {
            $errors[] = 'Test name is required.';
        }
        if ($resultSummary === '' && $rawValues === '') {
            $errors[] = 'Add a short result summary or paste the lab values.';
        }
        if (!in_array($status, ['pending', 'normal', 'critical', 'completed'], true)) {
            $status = 'pending';
        }
        if ($role === 'vet') {
            $assignedVetId = (int) $vetId;
        } elseif (!$assignedVetId) {
            $assignedVetId = null;
        }

        $filePath = $this->storeLabFile($errors);
        if (!empty($errors)) {
            return [null, $errors, null];
        }

        $parsed = $this->parseStructuredLabResults($testName, $testType, $resultSummary, $rawValues, $referenceRanges, $status, $notes);
        $testType = $testType !== '' ? $testType : $parsed['test_type'];
        if ($status === 'pending' && !empty($parsed['abnormal_flags'])) {
            $status = 'critical';
        }
        $insight = $this->buildLabInsight($testName, $resultSummary, $rawValues, $status, $notes, $parsed);
        $technicalData = json_encode($parsed, JSON_UNESCAPED_SLASHES);
        $abnormalFlags = implode("\n", $parsed['abnormal_flags']);
        $followUps = implode("\n", $parsed['follow_up_actions']);
        if ($role === 'vet' && $vetId) {
            $permission = $this->resolveVetActionPermission($db, $vetId, 'lab_reports');
            if (($permission['access_mode'] ?? 'request_admin') !== 'approve_user') {
                $ownerUserId = $this->petOwnerUserId($db, $petId);
                $payload = json_encode([
                    'test_name' => $testName,
                    'result_summary' => $resultSummary,
                    'raw_values' => $rawValues,
                    'reference_ranges' => $referenceRanges,
                    'test_type' => $testType,
                    'report_date' => $reportDate,
                    'status' => $status,
                    'file_path' => $filePath
                ]);
                $requestId = $this->createClinicalActionRequest(
                    $db,
                    'lab_reports',
                    'Lab Result Interpretation',
                    $petId,
                    $ownerUserId,
                    (int) $_SESSION['user_id'],
                    'vet',
                    $vetId,
                    $permission['access_mode'] ?? 'request_admin',
                    $payload,
                    $notes ?: $insight
                );
                $this->writeAudit($db, 'lab_workflow_requested', 'clinical_action_requests', $requestId, "Vet submitted lab workflow request for $testName.");
                return ['Lab action was routed through the configured approval workflow.', [], $insight];
            }
        }

        $stmt = $db->prepare(
            "INSERT INTO lab_reports
             (pet_id, vet_id, test_name, test_type, result_summary, raw_values, reference_ranges, interpretation, technical_data, abnormal_flags, follow_up_actions, status, report_date, file_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?)"
        );
        $stmt->execute([$petId, $assignedVetId, $testName, $testType, $resultSummary ?: $rawValues, $rawValues, $referenceRanges, $insight, $technicalData, $abnormalFlags, $followUps, $status, $reportDate, $filePath]);

        $reportId = (int) $db->lastInsertId();
        $this->saveLabToMedicalRecord($db, $petId, $assignedVetId, $testName, $insight, $status, $reportId);
        $this->notifyLabStakeholders($db, $petId, $assignedVetId, $testName, $status, $followUps);
        $this->writeAudit($db, 'lab_report_uploaded', 'lab_reports', $reportId, "Uploaded lab report $testName with simplified owner insight.");
        return ['Lab result uploaded and simplified insight generated.', [], $insight];
    }

    private function handleInitiateReferral($db, $vetId)
    {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $specialistId = (int) ($_POST['specialist_id'] ?? 0);
        $specialty = trim($_POST['specialty'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $errors = [];

        if (!$petId || !$this->canAccessPet($db, $petId, 'vet', $vetId, null)) {
            $errors[] = 'Choose a valid pet record.';
        }
        if (!$specialistId) {
            $errors[] = 'Select a specialist.';
        }
        if ($specialistId === $vetId) {
            $errors[] = 'Choose a different specialist than yourself.';
        }
        if ($specialty === '') {
            $errors[] = 'Specialty is required.';
        }
        if ($reason === '') {
            $errors[] = 'Referral reason is required.';
        }

        if (!empty($errors)) {
            return [null, $errors];
        }

        $stmt = $db->prepare(
            "INSERT INTO referrals (pet_id, from_vet_id, to_vet_id, specialty, reason, status, requested_at)\n             VALUES (?, ?, ?, ?, ?, 'pending', NOW())"
        );
        $stmt->execute([$petId, $vetId, $specialistId, $specialty, $reason]);
        $referralId = (int) $db->lastInsertId();

        $this->writeAudit($db, 'referral_initiated', 'referrals', $referralId, "Referral initiated to specialist #$specialistId.");
        return ['Referral initiated successfully.', []];
    }

    private function storeLabFile(&$errors)
    {
        if (empty($_FILES['lab_file']['name'])) {
            return null;
        }

        if ($_FILES['lab_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The lab file could not be uploaded.';
            return null;
        }

        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['lab_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            $errors[] = 'Upload a PDF or image file only.';
            return null;
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/lab-results';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileName = 'lab_' . uniqid('', true) . '.' . $extension;
        $target = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES['lab_file']['tmp_name'], $target)) {
            $errors[] = 'The lab file could not be saved.';
            return null;
        }

        return 'lab-results/' . $fileName;
    }

    private function buildLabInsight($testName, $summary, $rawValues, $status, $notes, $parsed = [])
    {
        $source = strtolower($testName . ' ' . $summary . ' ' . $rawValues . ' ' . $notes);
        $points = [];
        $testType = $parsed['test_type'] ?? 'General lab';
        $points[] = "This $testName result was categorized as $testType and organized into a simple owner-friendly summary.";

        if ($status === 'critical' || !empty($parsed['abnormal_flags']) || preg_match('/high|low|critical|positive|abnormal|elevated|decreased/', $source)) {
            $points[] = 'Some values may need veterinary review soon. Keep an eye on energy, appetite, vomiting, breathing, and hydration.';
        } elseif ($status === 'normal') {
            $points[] = 'The marked status is normal, so the result does not show an urgent flag from the submitted summary.';
        } else {
            $points[] = 'The report is pending review, so treat this as a first-pass explanation until a vet confirms it.';
        }

        if (preg_match('/cbc|wbc|white|rbc|blood|platelet|hemoglobin/', $source)) {
            $points[] = 'Blood-count values can reflect infection, anemia, inflammation, hydration, or clotting changes.';
        }
        if (preg_match('/kidney|creatinine|bun|urea/', $source)) {
            $points[] = 'Kidney markers are best read with hydration, urine changes, appetite, and repeat trends.';
        }
        if (preg_match('/liver|alt|ast|alp|bilirubin/', $source)) {
            $points[] = 'Liver markers can rise for several reasons, so medication history and symptoms matter.';
        }
        if (preg_match('/glucose|sugar|diabetes/', $source)) {
            $points[] = 'Glucose changes should be compared with eating time, stress level, thirst, and urination.';
        }
        foreach (($parsed['abnormal_flags'] ?? []) as $flag) {
            $points[] = 'Key finding: ' . $flag;
        }
        foreach (($parsed['follow_up_actions'] ?? []) as $action) {
            $points[] = 'Recommended follow-up: ' . $action;
        }

        $points[] = 'This is not a diagnosis. Share the original file with your veterinarian for final interpretation.';
        return implode("\n", $points);
    }

    private function parseStructuredLabResults($testName, $testType, $summary, $rawValues, $referenceRanges, $status, $notes)
    {
        $source = strtolower("$testName $testType $summary $rawValues $referenceRanges $notes");
        if ($testType === '') {
            if (preg_match('/cbc|wbc|rbc|platelet|hemoglobin|haemoglobin|blood count/', $source)) {
                $testType = 'Blood count';
            } elseif (preg_match('/kidney|creatinine|bun|urea|urine|urinalysis/', $source)) {
                $testType = 'Kidney and urinary';
            } elseif (preg_match('/liver|alt|ast|alp|bilirubin/', $source)) {
                $testType = 'Liver profile';
            } elseif (preg_match('/glucose|sugar|diabetes/', $source)) {
                $testType = 'Glucose';
            } elseif (preg_match('/x-ray|xray|scan|ultrasound|image|radiology/', $source)) {
                $testType = 'Diagnostic imaging';
            } else {
                $testType = 'General lab';
            }
        }

        $abnormalFlags = [];
        if (preg_match_all('/([A-Za-z][A-Za-z0-9 \/-]{1,28})\s*[:=]\s*([<>]?\s*\d+(?:\.\d+)?)\s*([A-Za-z\/%]+)?\s*(?:\((high|low|elevated|decreased|abnormal|critical)\)|\b(high|low|elevated|decreased|abnormal|critical)\b)?/i', $rawValues . "\n" . $summary, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $label = trim($match[1]);
                $value = trim($match[2] . ' ' . ($match[3] ?? ''));
                $flag = strtolower($match[4] ?? ($match[5] ?? ''));
                if ($flag !== '') {
                    $abnormalFlags[] = "$label is $flag at $value.";
                }
            }
        }
        if (empty($abnormalFlags) && preg_match_all('/\b(high|low|critical|positive|abnormal|elevated|decreased)\b[^.\n]*/i', $summary . "\n" . $rawValues, $flags)) {
            foreach (array_slice($flags[0], 0, 5) as $flagText) {
                $abnormalFlags[] = ucfirst(trim($flagText, " .\t\n\r\0\x0B")) . '.';
            }
        }

        $followUps = [];
        if ($status === 'critical' || !empty($abnormalFlags)) {
            $followUps[] = 'Vet should review this result and contact the owner if symptoms are present.';
        }
        if (preg_match('/kidney|creatinine|bun|urea/', $source)) {
            $followUps[] = 'Consider hydration review, urine testing, and repeat kidney values if the vet agrees.';
        }
        if (preg_match('/liver|alt|ast|alp|bilirubin/', $source)) {
            $followUps[] = 'Review medications, diet, appetite, vomiting, and whether repeat liver enzymes are needed.';
        }
        if (preg_match('/glucose|sugar|diabetes/', $source)) {
            $followUps[] = 'Compare glucose with meal timing and watch thirst, urination, and weight change.';
        }
        if (empty($followUps)) {
            $followUps[] = 'Keep the result in the pet medical record and compare it with future trends.';
        }

        return [
            'test_type' => $testType,
            'values_text' => $rawValues,
            'reference_ranges' => $referenceRanges,
            'historical_comparison' => 'Compare with previous saved lab reports for this pet.',
            'abnormal_flags' => array_values(array_unique($abnormalFlags)),
            'follow_up_actions' => array_values(array_unique($followUps))
        ];
    }

    private function labHubPets($db, $role, $vetId, $ownerId)
    {
        if ($role === 'pet_owner') {
            return $this->fetchAll($db, "SELECT p.*, u.username AS owner_name FROM pets p LEFT JOIN pet_owners po ON po.id = p.owner_id LEFT JOIN users u ON u.id = po.user_id WHERE p.owner_id = ? ORDER BY p.name ASC", [$ownerId]);
        }
        return $this->pets($db);
    }

    private function labHubReports($db, $role, $vetId, $ownerId)
    {
        $where = '';
        $params = [];
        if ($role === 'pet_owner') {
            $where = 'WHERE p.owner_id = ?';
            $params[] = $ownerId;
        } elseif ($role === 'vet' && $vetId) {
            $where = 'WHERE lr.vet_id = ? OR lr.vet_id IS NULL';
            $params[] = $vetId;
        }

        return $this->fetchAll(
            $db,
            "SELECT lr.*, p.name AS pet_name, p.species, u.username AS owner_name, vu.username AS vet_name
             FROM lab_reports lr
             LEFT JOIN pets p ON p.id = lr.pet_id
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             LEFT JOIN veterinarians v ON v.id = lr.vet_id
             LEFT JOIN users vu ON vu.id = v.user_id
             $where
             ORDER BY COALESCE(lr.report_date, DATE(lr.created_at)) DESC, lr.id DESC
             LIMIT 30",
            $params
        );
    }

    private function canAccessPet($db, $petId, $role, $vetId, $ownerId)
    {
        if ($role === 'admin') {
            return true;
        }
        if ($role === 'pet_owner') {
            return (bool) $this->fetchOne($db, "SELECT id FROM pets WHERE id = ? AND owner_id = ?", [$petId, $ownerId]);
        }
        if ($role === 'vet') {
            return (bool) $this->fetchOne($db, "SELECT id FROM pets WHERE id = ?", [$petId]);
        }
        return false;
    }

    private function writeAudit($db, $action, $entityType, $entityId, $details)
    {
        try {
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, entity_type, entity_id, action, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'] ?? null, $entityType, $entityId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? null]);
        } catch (Exception $e) {
            return;
        }
    }

    private function ensureLabHubSchema($db)
    {
        $columns = [
            'test_type' => "ALTER TABLE lab_reports ADD COLUMN test_type varchar(120) DEFAULT NULL AFTER test_name",
            'raw_values' => "ALTER TABLE lab_reports ADD COLUMN raw_values text DEFAULT NULL AFTER result_summary",
            'reference_ranges' => "ALTER TABLE lab_reports ADD COLUMN reference_ranges text DEFAULT NULL AFTER raw_values",
            'technical_data' => "ALTER TABLE lab_reports ADD COLUMN technical_data longtext DEFAULT NULL AFTER interpretation",
            'abnormal_flags' => "ALTER TABLE lab_reports ADD COLUMN abnormal_flags text DEFAULT NULL AFTER technical_data",
            'follow_up_actions' => "ALTER TABLE lab_reports ADD COLUMN follow_up_actions text DEFAULT NULL AFTER abnormal_flags",
            'vet_notes' => "ALTER TABLE lab_reports ADD COLUMN vet_notes text DEFAULT NULL AFTER follow_up_actions",
            'reviewed_at' => "ALTER TABLE lab_reports ADD COLUMN reviewed_at datetime DEFAULT NULL AFTER vet_notes"
        ];
        foreach ($columns as $column => $sql) {
            if (!$this->columnExists($db, 'lab_reports', $column)) {
                try {
                    $db->exec($sql);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }

    private function ensureSurgeryRequestSchema($db)
    {
        try {
            $db->exec(
                "CREATE TABLE IF NOT EXISTS surgery_requests (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    owner_id int(11) NOT NULL,
                    pet_id int(11) NOT NULL,
                    medical_procedure_id int(11) DEFAULT NULL,
                    procedure_type varchar(120) NOT NULL,
                    reason text NOT NULL,
                    urgency varchar(30) NOT NULL DEFAULT 'normal',
                    status varchar(40) NOT NULL DEFAULT 'pending',
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    updated_at timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY owner_id (owner_id),
                    KEY pet_id (pet_id),
                    KEY medical_procedure_id (medical_procedure_id),
                    KEY status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        } catch (Exception $e) {
            return;
        }

        $columns = [
            'medical_procedure_id' => "ALTER TABLE surgery_requests ADD COLUMN medical_procedure_id int(11) DEFAULT NULL AFTER pet_id",
            'created_at' => "ALTER TABLE surgery_requests ADD COLUMN created_at timestamp NOT NULL DEFAULT current_timestamp() AFTER status",
            'updated_at' => "ALTER TABLE surgery_requests ADD COLUMN updated_at timestamp NULL DEFAULT NULL AFTER created_at"
        ];
        foreach ($columns as $column => $sql) {
            if (!$this->columnExists($db, 'surgery_requests', $column)) {
                try {
                    $db->exec($sql);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }

    private function ownerSurgeryRequestsForVet($db, $vetId)
    {
        return $this->fetchAll(
            $db,
            "SELECT sr.*, mp.id AS procedure_id, mp.status AS procedure_status, p.name AS pet_name, p.species, u.username AS owner_name
             FROM surgery_requests sr
             LEFT JOIN medical_procedures mp ON mp.id = sr.medical_procedure_id
             LEFT JOIN pets p ON p.id = sr.pet_id
             LEFT JOIN pet_owners po ON po.id = sr.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             WHERE LOWER(COALESCE(sr.status, 'pending')) IN ('pending', 'pending_vet_review')
               AND (mp.vet_id IS NULL OR mp.vet_id = ? OR mp.id IS NULL)
             ORDER BY FIELD(sr.urgency, 'emergency', 'urgent', 'normal'), COALESCE(sr.created_at, sr.updated_at, NOW()) DESC, sr.id DESC",
            [$vetId]
        );
    }

    private function saveLabToMedicalRecord($db, $petId, $vetId, $testName, $insight, $status, $reportId)
    {
        try {
            $stmt = $db->prepare("INSERT INTO health_records (pet_id, title, description, record_date) VALUES (?, ?, ?, CURDATE())");
            $stmt->execute([$petId, "Lab result: $testName", "Status: $status\n$insight"]);
        } catch (Exception $e) {
        }

        if (!$vetId) {
            return;
        }
        try {
            $stmt = $db->prepare("INSERT INTO medical_records (pet_id, vet_id, diagnosis, treatment, lab_result) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$petId, $vetId, "Lab review pending: $testName", $insight, "lab_report#$reportId"]);
        } catch (Exception $e) {
        }
    }

    private function notifyLabStakeholders($db, $petId, $vetId, $testName, $status, $followUps)
    {
        try {
            $ownerUserId = $this->petOwnerUserId($db, $petId);
            $message = "New lab result for $testName is saved. Status: $status.";
            if ($followUps !== '') {
                $message .= "\n" . $followUps;
            }
            if ($ownerUserId) {
                $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$ownerUserId, 'Lab result updated', $message, 'lab_result']);
            }
            if ($vetId) {
                $vetUser = $this->fetchOne($db, "SELECT user_id FROM veterinarians WHERE id = ?", [$vetId]);
                if (!empty($vetUser['user_id']) && (int) $vetUser['user_id'] !== $ownerUserId) {
                    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([(int) $vetUser['user_id'], 'Lab result needs review', $message, 'lab_result']);
                }
            }
        } catch (Exception $e) {
        }
    }

    private function vetPermissions($db, $vetId)
    {
        return $this->fetchAll(
            $db,
            "SELECT vap.*, u.username AS updated_by_name
             FROM vet_action_permissions vap
             LEFT JOIN users u ON u.id = vap.updated_by
             WHERE vap.vet_id = ?
             ORDER BY FIELD(vap.action_key, 'lab_reports', 'referrals', 'surgery_booking', 'medical_records'), vap.id ASC",
            [$vetId]
        );
    }

    private function resolveVetActionPermission($db, $vetId, $actionKey)
    {
        $permission = $this->fetchOne(
            $db,
            "SELECT * FROM vet_action_permissions WHERE vet_id = ? AND action_key = ? AND is_active = 1 LIMIT 1",
            [$vetId, $actionKey]
        );
        if ($permission) {
            return $permission;
        }

        $defaults = [
            'lab_reports' => 'approve_user',
            'referrals' => 'request_admin',
            'surgery_booking' => 'request_admin',
            'medical_records' => 'request_user'
        ];

        return [
            'action_key' => $actionKey,
            'access_mode' => $defaults[$actionKey] ?? 'request_admin',
            'is_active' => 1
        ];
    }

    private function handleClinicalWorkflowRequest($db, $vetId)
    {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $actionKey = trim($_POST['action_key'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $procedureId = (int) ($_POST['procedure_id'] ?? 0);
        $bookingId = null;
        $errors = [];
        $titles = [
            'lab_reports' => 'Lab Result Interpretation',
            'referrals' => 'Veterinary Referral',
            'surgery_booking' => 'Surgery Booking',
            'medical_records' => 'Medical Record Release'
        ];

        if (!$petId) {
            $errors[] = 'Choose a pet first.';
        }
        if (!isset($titles[$actionKey])) {
            $errors[] = 'Choose a valid clinical action.';
        }
        if ($summary === '') {
            $errors[] = 'Add a short request summary.';
        }
        if ($actionKey === 'surgery_booking' && !$procedureId) {
            $errors[] = 'Choose the requested procedure first.';
        }
        if (!empty($errors)) {
            return [null, $errors];
        }

        if ($actionKey === 'surgery_booking') {
            $procedure = $this->fetchOne(
                $db,
                "SELECT mp.*, p.name AS pet_name
                 FROM medical_procedures mp
                 LEFT JOIN pets p ON p.id = mp.pet_id
                 WHERE mp.id = ? AND (mp.vet_id = ? OR mp.vet_id IS NULL)",
                [$procedureId, $vetId]
            );
            if (!$procedure) {
                return [null, ['The selected procedure is not available for this vet.']];
            }
            $petId = (int) ($procedure['pet_id'] ?? 0);
            $summary = $summary !== '' ? $summary : (($procedure['procedure_name'] ?? 'Procedure') . ' selected for admin approval.');
            $notes = trim($notes . "\nProcedure case #" . $procedureId . ' for ' . ($procedure['pet_name'] ?? 'selected pet'));

            [$resourceErrors, $resourcePlan] = $this->validateSurgeryResourcePlan($db, $procedure, $vetId);
            if (!empty($resourceErrors)) {
                return [null, $resourceErrors];
            }
            $bookingId = $this->createPendingProcedureBooking($db, $procedure, $vetId, $resourcePlan, $notes);
            $db->prepare("UPDATE medical_procedures SET vet_id = ?, status = 'pending_admin', procedure_date = ? WHERE id = ?")->execute([$vetId, substr($resourcePlan['start'], 0, 10), $procedureId]);
            try {
                $db->prepare("UPDATE surgery_requests SET status = 'sent_to_admin', updated_at = NOW() WHERE medical_procedure_id = ?")->execute([$procedureId]);
            } catch (Exception $e) {
            }
            $notes = trim($notes . "\nRequested room: " . $resourcePlan['room_name'] . "\nRequested equipment: " . $resourcePlan['equipment_name'] . "\nRequested staff: " . $resourcePlan['specialist_name'] . "\nRequested time: " . $resourcePlan['start'] . ' to ' . $resourcePlan['end']);
        }

        $permission = $actionKey === 'surgery_booking'
            ? ['action_key' => 'surgery_booking', 'access_mode' => 'request_admin', 'is_active' => 1]
            : $this->resolveVetActionPermission($db, $vetId, $actionKey);
        $ownerUserId = $this->petOwnerUserId($db, $petId);
        $payload = json_encode([
            'summary' => $summary,
            'notes' => $notes,
            'procedure_id' => $procedureId ?: null,
            'booking_id' => $bookingId
        ]);
        $requestId = $this->createClinicalActionRequest(
            $db,
            $actionKey,
            $titles[$actionKey],
            $petId,
            $ownerUserId,
            (int) $_SESSION['user_id'],
            'vet',
            $vetId,
            $permission['access_mode'] ?? 'request_admin',
            $payload,
            $notes
        );

        $messageMap = [
            'approve_user' => 'This action was approved directly for the user workflow.',
            'request_admin' => 'This action was sent to admin for approval.',
            'request_user' => 'This action is waiting for user approval.'
        ];
        $this->writeAudit($db, 'clinical_workflow_submitted', 'clinical_action_requests', $requestId, "Submitted {$titles[$actionKey]} workflow.");
        return [$messageMap[$permission['access_mode'] ?? 'request_admin'] ?? 'Workflow request created.', []];
    }

    private function handleOwnerSurgeryAdminForward($db, $vetId)
    {
        $requestId = (int) ($_POST['surgery_request_id'] ?? 0);
        $summary = trim($_POST['summary'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$requestId) {
            return [null, ['Choose a surgery request first.']];
        }

        $request = $this->fetchOne(
            $db,
            "SELECT sr.*, p.name AS pet_name
             FROM surgery_requests sr
             LEFT JOIN medical_procedures mp ON mp.id = sr.medical_procedure_id
             LEFT JOIN pets p ON p.id = sr.pet_id
             WHERE sr.id = ?
               AND (mp.vet_id IS NULL OR mp.vet_id = ? OR mp.id IS NULL)",
            [$requestId, $vetId]
        );
        if (!$request) {
            return [null, ['The selected surgery request is not available for this vet.']];
        }

        $procedureId = (int) ($request['medical_procedure_id'] ?? 0);
        if (!$procedureId) {
            $procedureName = ucwords(str_replace('_', ' ', $request['procedure_type'] ?? 'procedure')) . ' surgery request';
            $stmt = $db->prepare(
                "INSERT INTO medical_procedures (pet_id, vet_id, procedure_name, procedure_type, status, procedure_date, notes)
                 VALUES (?, ?, ?, ?, 'pending_admin', NULL, ?)"
            );
            $stmt->execute([
                (int) $request['pet_id'],
                $vetId,
                $procedureName,
                $request['procedure_type'] ?? 'surgery',
                "Owner request urgency: " . ($request['urgency'] ?? 'normal') . "\nReason: " . ($request['reason'] ?? '')
            ]);
            $procedureId = (int) $db->lastInsertId();
            $db->prepare("UPDATE surgery_requests SET medical_procedure_id = ? WHERE id = ?")->execute([$procedureId, $requestId]);
        }

        $_POST['action_key'] = 'surgery_booking';
        $_POST['procedure_id'] = $procedureId;
        $_POST['pet_id'] = (int) $request['pet_id'];
        $_POST['summary'] = $summary !== ''
            ? $summary
            : 'Owner surgery request for ' . ($request['pet_name'] ?? 'selected pet') . ' is ready for admin scheduling approval.';
        $_POST['notes'] = trim($notes . "\nOwner urgency: " . ($request['urgency'] ?? 'normal') . "\nOwner reason: " . ($request['reason'] ?? ''));

        return $this->handleClinicalWorkflowRequest($db, $vetId);
    }

    private function validateSurgeryResourcePlan($db, $procedure, $vetId)
    {
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $equipmentId = (int) ($_POST['equipment_id'] ?? 0);
        $specialistId = (int) ($_POST['specialist_id'] ?? 0);
        $date = trim($_POST['procedure_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $errors = [];

        if (!$roomId) {
            $errors[] = 'Select an available operating room.';
        }
        if (!$equipmentId) {
            $errors[] = 'Select available surgical equipment.';
        }
        if (!$specialistId) {
            $errors[] = 'Select available specialist staff.';
        }
        if ($date === '' || $startTime === '' || $endTime === '') {
            $errors[] = 'Choose the proposed surgery date, start time, and end time.';
        }

        $startDateTime = strtotime("$date $startTime");
        $endDateTime = strtotime("$date $endTime");
        if ($startDateTime === false || $endDateTime === false) {
            $errors[] = 'Invalid proposed surgery date or time.';
        } elseif ($startDateTime >= $endDateTime) {
            $errors[] = 'The proposed end time must be after the start time.';
        }

        if (!empty($errors)) {
            return [$errors, null];
        }

        $room = $this->fetchOne($db, "SELECT * FROM operating_rooms WHERE id = ? AND LOWER(COALESCE(status, 'available')) = 'available'", [$roomId]);
        $equipment = $this->fetchOne($db, "SELECT * FROM surgical_equipment WHERE id = ? AND LOWER(COALESCE(status, 'available')) = 'available'", [$equipmentId]);
        $specialist = $this->fetchOne(
            $db,
            "SELECT v.*, u.username
             FROM veterinarians v
             LEFT JOIN users u ON u.id = v.user_id
             WHERE v.id = ?",
            [$specialistId]
        );

        if (!$room) {
            $errors[] = 'The selected operating room is not available.';
        }
        if (!$equipment) {
            $errors[] = 'The selected equipment is not available.';
        }
        if (!$specialist) {
            $errors[] = 'The selected specialist staff member is not available.';
        }

        $start = date('Y-m-d H:i:s', $startDateTime);
        $end = date('Y-m-d H:i:s', $endDateTime);
        if (empty($errors)) {
            $conflict = $this->findBookingConflict($db, $roomId, $equipmentId, $specialistId, $start, $end);
            if ($conflict !== '') {
                $errors[] = $conflict;
            }
        }

        if (!empty($errors)) {
            return [$errors, null];
        }

        return [[], [
            'room_id' => $roomId,
            'room_name' => $room['name'] ?? 'Operating room',
            'equipment_id' => $equipmentId,
            'equipment_name' => $equipment['name'] ?? 'Equipment',
            'specialist_id' => $specialistId,
            'specialist_name' => $specialist['username'] ?? 'Specialist',
            'start' => $start,
            'end' => $end
        ]];
    }

    private function createPendingProcedureBooking($db, $procedure, $vetId, $resourcePlan, $notes)
    {
        $stmt = $db->prepare(
            "INSERT INTO procedure_bookings
             (pet_id, vet_id, room_id, equipment_id, specialist_id, procedure_name, procedure_type, start_time, end_time, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending_admin', ?)"
        );
        $stmt->execute([
            (int) $procedure['pet_id'],
            $vetId,
            (int) $resourcePlan['room_id'],
            (int) $resourcePlan['equipment_id'],
            (int) $resourcePlan['specialist_id'],
            $procedure['procedure_name'] ?? 'Surgery request',
            $procedure['procedure_type'] ?? 'surgery',
            $resourcePlan['start'],
            $resourcePlan['end'],
            $notes
        ]);

        return (int) $db->lastInsertId();
    }

    private function handleLabInterpretation($db, $vetId)
    {
        $reportId = (int) ($_POST['report_id'] ?? 0);
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $notes = trim($_POST['interpretation_notes'] ?? '');
        $linkedDisease = trim($_POST['linked_disease'] ?? '');
        $extraTests = trim($_POST['extra_tests'] ?? '');
        $errors = [];

        if (!$reportId) {
            $errors[] = 'Choose a lab result first.';
        }
        if ($diagnosis === '') {
            $errors[] = 'Diagnosis is required.';
        }
        if (!empty($errors)) {
            return [null, $errors];
        }

        $report = $this->fetchOne($db, "SELECT * FROM lab_reports WHERE id = ?", [$reportId]);
        if (!$report) {
            return [null, ['The selected lab report does not exist.']];
        }

        $details = [];
        $details[] = "Diagnosis: $diagnosis";
        if ($notes !== '') {
            $details[] = "Notes: $notes";
        }
        if ($linkedDisease !== '') {
            $details[] = "Linked disease: $linkedDisease";
        }
        if ($extraTests !== '') {
            $details[] = "Additional tests: $extraTests";
        }

        $existingInsight = trim((string) ($report['interpretation'] ?? ''));
        $reviewedInsight = trim($existingInsight . "\nVet review: " . implode("\n", $details));
        $stmt = $db->prepare("UPDATE lab_reports SET interpretation = ?, vet_notes = ?, status = 'completed', vet_id = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->execute([$reviewedInsight, implode("\n", $details), $vetId, $reportId]);
        $this->saveLabToMedicalRecord($db, (int) $report['pet_id'], $vetId, $report['test_name'], $reviewedInsight, 'completed', $reportId);
        $this->writeAudit($db, 'lab_interpreted', 'lab_reports', $reportId, "Vet completed interpretation for {$report['test_name']}.");
        return ['Lab interpretation saved successfully.', []];
    }

    private function handleReferralTransfer($db, $vetId)
    {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $toVetId = (int) ($_POST['to_vet_id'] ?? 0);
        $specialty = trim($_POST['specialty'] ?? '');
        $priority = trim($_POST['priority'] ?? 'normal');
        $reason = trim($_POST['reason'] ?? '');
        $errors = [];

        if (!$petId) {
            $errors[] = 'Choose a pet case first.';
        }
        if (!$toVetId) {
            $errors[] = 'Choose a specialist doctor.';
        }
        if ($specialty === '') {
            $errors[] = 'Specialty is required.';
        }
        if ($reason === '') {
            $errors[] = 'Referral reason is required.';
        }
        if ($toVetId === $vetId) {
            $errors[] = 'Choose another specialist doctor.';
        }
        if (!empty($errors)) {
            return [null, $errors];
        }

        $allowedTransfer = $this->fetchOne(
            $db,
            "SELECT DISTINCT p.id
             FROM pets p
             LEFT JOIN medical_procedures mp ON mp.pet_id = p.id AND mp.vet_id = ?
             LEFT JOIN referrals r ON r.pet_id = p.id AND (r.from_vet_id = ? OR r.to_vet_id = ?)
             WHERE p.id = ?
               AND (mp.id IS NOT NULL OR r.id IS NOT NULL)",
            [$vetId, $vetId, $vetId, $petId]
        );
        if (!$allowedTransfer) {
            return [null, ['You can only transfer cases already linked to your procedures or referrals.']];
        }

        $stmt = $db->prepare(
            "INSERT INTO referrals (pet_id, from_vet_id, to_vet_id, specialty, reason, priority, status, notes)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
        );
        $stmt->execute([$petId, $vetId, $toVetId, $specialty, $reason, $priority, 'Transferred from vet dashboard']);
        $this->writeAudit($db, 'referral_created', 'referrals', (int) $db->lastInsertId(), "Transferred case to specialist.");
        return ['Referral case transferred successfully.', []];
    }

    private function createClinicalActionRequest($db, $actionKey, $title, $petId, $ownerUserId, $requesterUserId, $requesterRole, $targetVetId, $accessMode, $payload, $notes)
    {
        $ownerStatus = $accessMode === 'request_user' ? 'pending' : 'not_needed';
        $adminStatus = $accessMode === 'request_admin' ? 'pending' : 'not_needed';
        $requestStatus = $accessMode === 'approve_user' ? 'approved' : 'pending';

        $stmt = $db->prepare(
            "INSERT INTO clinical_action_requests
             (action_key, action_title, pet_id, owner_user_id, requester_user_id, requester_role, target_vet_id, owner_status, admin_status, request_status, payload, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$actionKey, $title, $petId, $ownerUserId, $requesterUserId, $requesterRole, $targetVetId, $ownerStatus, $adminStatus, $requestStatus, $payload, $notes]);
        return (int) $db->lastInsertId();
    }

    private function petOwnerUserId($db, $petId)
    {
        $row = $this->fetchOne(
            $db,
            "SELECT po.user_id
             FROM pets p
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             WHERE p.id = ?",
            [$petId]
        );
        return (int) ($row['user_id'] ?? 0);
    }

    private function vetWorkflowRequests($db, $vetId)
    {
        return $this->fetchAll(
            $db,
            "SELECT car.*, p.name AS pet_name, owner_u.username AS owner_name, requester_u.username AS requester_name
             FROM clinical_action_requests car
             LEFT JOIN pets p ON p.id = car.pet_id
             LEFT JOIN users owner_u ON owner_u.id = car.owner_user_id
             LEFT JOIN users requester_u ON requester_u.id = car.requester_user_id
             WHERE car.target_vet_id = ? OR car.requester_user_id = ?
             ORDER BY car.updated_at DESC, car.id DESC
             LIMIT 20",
            [$vetId, $_SESSION['user_id']]
        );
    }

    private function specialistDirectory($db, $vetId)
    {
        $specialists = $this->fetchAll(
            $db,
            "SELECT v.id, u.username, u.email, v.specialization,
                    COUNT(DISTINCT pb.id) AS surgeries,
                    COUNT(DISTINCT r.id) AS referrals_count
             FROM veterinarians v
             LEFT JOIN users u ON u.id = v.user_id
             LEFT JOIN procedure_bookings pb ON pb.specialist_id = v.id
             LEFT JOIN referrals r ON r.to_vet_id = v.id
             WHERE v.id != ?
             GROUP BY v.id, u.username, u.email, v.specialization
             ORDER BY referrals_count DESC, surgeries DESC, u.username ASC",
            [$vetId]
        );

        foreach ($specialists as &$specialist) {
            $specialist['rating'] = $this->doctorRating($db, (int) $specialist['id']);
        }
        unset($specialist);

        return $specialists;
    }

    private function doctorRating($db, $vetId)
    {
        if ($this->columnExists($db, 'reviews', 'vet_id')) {
            $row = $this->fetchOne($db, "SELECT ROUND(AVG(rating), 1) AS rating FROM reviews WHERE vet_id = ?", [$vetId]);
            if (!empty($row['rating'])) {
                return $row['rating'];
            }
        }
        if ($this->columnExists($db, 'reviews', 'doctor_id')) {
            $row = $this->fetchOne($db, "SELECT ROUND(AVG(rating), 1) AS rating FROM reviews WHERE doctor_id = ?", [$vetId]);
            if (!empty($row['rating'])) {
                return $row['rating'];
            }
        }
        return '4.7';
    }

    private function incomingLabStats($labReports)
    {
        return [
            'new' => count(array_filter($labReports, fn($report) => strtolower($report['status'] ?? '') === 'pending')),
            'critical' => count(array_filter($labReports, fn($report) => strtolower($report['status'] ?? '') === 'critical')),
            'uninterpreted' => count(array_filter($labReports, fn($report) => trim((string) ($report['interpretation'] ?? '')) === ''))
        ];
    }

    private function incomingLabReports($labReports)
    {
        return array_values(array_filter(
            $labReports,
            fn($report) => in_array(strtolower($report['status'] ?? ''), ['pending', 'critical'], true)
                || trim((string) ($report['interpretation'] ?? '')) === ''
        ));
    }

    private function transferCases($procedures, $referrals)
    {
        $cases = [];

        foreach ($procedures as $procedure) {
            $petId = (int) ($procedure['pet_id'] ?? 0);
            if (!$petId || isset($cases[$petId])) {
                continue;
            }
            $cases[$petId] = [
                'pet_id' => $petId,
                'pet_name' => $procedure['pet_name'] ?? 'Unknown pet',
                'species' => $procedure['species'] ?? 'Pet',
                'source' => 'Procedure case',
                'summary' => $procedure['procedure_name'] ?? 'Clinical procedure'
            ];
        }

        foreach ($referrals as $referral) {
            $petId = (int) ($referral['pet_id'] ?? 0);
            if (!$petId || isset($cases[$petId])) {
                continue;
            }
            $cases[$petId] = [
                'pet_id' => $petId,
                'pet_name' => $referral['pet_name'] ?? 'Unknown pet',
                'species' => 'Referral',
                'source' => 'Referral case',
                'summary' => $referral['specialty'] ?? ($referral['reason'] ?? 'Clinical referral')
            ];
        }

        return array_values($cases);
    }

    private function columnExists($db, $table, $column)
    {
        $stmt = $db->prepare(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?"
        );
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function referrals($db, $vetId)
    {
        $where = $vetId ? 'WHERE r.from_vet_id = ? OR r.to_vet_id = ?' : '';
        return $this->fetchAll(
            $db,
            "SELECT r.*, p.name AS pet_name, from_user.username AS from_vet, to_user.username AS to_vet
             FROM referrals r
             LEFT JOIN pets p ON p.id = r.pet_id
             LEFT JOIN veterinarians from_v ON from_v.id = r.from_vet_id
             LEFT JOIN users from_user ON from_user.id = from_v.user_id
             LEFT JOIN veterinarians to_v ON to_v.id = r.to_vet_id
             LEFT JOIN users to_user ON to_user.id = to_v.user_id
             $where
             ORDER BY r.requested_at DESC, r.id DESC
             LIMIT 12",
            $vetId ? [$vetId, $vetId] : []
        );
    }

    private function auditLogs($db, $vetId, $role, $limit = 5)
    {
        $limit = (int) $limit;
        if ($limit <= 0) {
            $limit = 5;
        }

        if ($role === 'vet' && $vetId) {
            return $this->fetchAll(
                $db,
                "SELECT al.*, u.username
                 FROM audit_logs al
                 LEFT JOIN admins a ON a.id = al.admin_id
                 LEFT JOIN users u ON u.id = COALESCE(al.user_id, a.user_id)
                 WHERE al.user_id = (SELECT user_id FROM veterinarians WHERE id = ?)
                 ORDER BY al.created_at DESC
                 LIMIT $limit",
                [$vetId]
            );
        }

        return $this->fetchAll(
            $db,
            "SELECT al.*, u.username
             FROM audit_logs al
             LEFT JOIN admins a ON a.id = al.admin_id
             LEFT JOIN users u ON u.id = COALESCE(al.user_id, a.user_id)
             ORDER BY al.created_at DESC
             LIMIT $limit",
            []
        );
    }

    private function countReferrals($db, $vetId)
    {
        if ($vetId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE from_vet_id = ? OR to_vet_id = ?");
            $stmt->execute([$vetId, $vetId]);
            return (int) $stmt->fetchColumn();
        }

        return $this->countRows($db, 'referrals');
    }

    private function countRows($db, $table, $where = null, $params = [])
    {
        $sql = "SELECT COUNT(*) FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function pets($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT p.id, p.name, p.species, u.username AS owner_name
             FROM pets p
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             ORDER BY p.name ASC",
            []
        );
    }

    private function operatingRooms($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT * FROM operating_rooms ORDER BY name ASC",
            []
        );
    }

    private function availableOperatingRooms($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT * FROM operating_rooms WHERE LOWER(COALESCE(status, 'available')) = 'available' ORDER BY name ASC",
            []
        );
    }

    private function surgicalEquipment($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT * FROM surgical_equipment ORDER BY name ASC",
            []
        );
    }

    private function availableSurgicalEquipment($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT * FROM surgical_equipment WHERE LOWER(COALESCE(status, 'available')) = 'available' ORDER BY name ASC",
            []
        );
    }

    private function specialists($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT v.id, u.username, v.specialization
             FROM veterinarians v
             LEFT JOIN users u ON u.id = v.user_id
             ORDER BY u.username ASC",
            []
        );
    }

    private function allPets($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT p.id, p.name, p.species, u.username AS owner_name
             FROM pets p
             LEFT JOIN pet_owners po ON po.id = p.owner_id
             LEFT JOIN users u ON u.id = po.user_id
             ORDER BY p.name ASC",
            []
        );
    }

    private function ownerPets($db, $ownerId)
    {
        if (!$ownerId) return [];
        return $this->fetchAll(
            $db,
            "SELECT p.id, p.name, p.species
             FROM pets p
             WHERE p.owner_id = ?
             ORDER BY p.name ASC",
            [$ownerId]
        );
    }

    private function ownerProcedures($db, $ownerId)
    {
        if (!$ownerId) return [];
        return $this->fetchAll(
            $db,
            "SELECT mp.*, p.name AS pet_name, u.username AS vet_name
             FROM medical_procedures mp
             LEFT JOIN pets p ON p.id = mp.pet_id
             LEFT JOIN veterinarians v ON v.id = mp.vet_id
             LEFT JOIN users u ON u.id = v.user_id
             WHERE p.owner_id = ?
             ORDER BY mp.procedure_date DESC, mp.id DESC
             LIMIT 10",
            [$ownerId]
        );
    }

    private function ownerLabReports($db, $ownerId)
    {
        if (!$ownerId) return [];
        return $this->fetchAll(
            $db,
            "SELECT lr.*, p.name AS pet_name, u.username AS vet_name
             FROM lab_reports lr
             LEFT JOIN pets p ON p.id = lr.pet_id
             LEFT JOIN veterinarians v ON v.id = lr.vet_id
             LEFT JOIN users u ON u.id = v.user_id
             WHERE p.owner_id = ?
             ORDER BY COALESCE(lr.report_date, DATE(lr.created_at)) DESC, lr.id DESC
             LIMIT 10",
            [$ownerId]
        );
    }

    private function ownerReferrals($db, $ownerId)
    {
        if (!$ownerId) return [];
        return $this->fetchAll(
            $db,
            "SELECT r.*, p.name AS pet_name, from_u.username AS from_vet, to_u.username AS to_vet
             FROM referrals r
             LEFT JOIN pets p ON p.id = r.pet_id
             LEFT JOIN veterinarians from_v ON from_v.id = r.from_vet_id
             LEFT JOIN users from_u ON from_u.id = from_v.user_id
             LEFT JOIN veterinarians to_v ON to_v.id = r.to_vet_id
             LEFT JOIN users to_u ON to_u.id = to_v.user_id
             WHERE p.owner_id = ?
             ORDER BY r.requested_at DESC, r.id DESC
             LIMIT 10",
            [$ownerId]
        );
    }

    private function handleSurgeryRequest($db, $ownerId)
    {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $procedureType = trim($_POST['procedure_type'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $urgency = trim($_POST['urgency'] ?? 'normal');
        $errors = [];

        if (!$petId) {
            $errors[] = 'Select a pet.';
        }
        if (!$procedureType) {
            $errors[] = 'Specify the procedure type.';
        }
        if (!$reason) {
            $errors[] = 'Provide a reason for the surgery.';
        }
        if (!in_array($urgency, ['normal', 'urgent', 'emergency'])) {
            $errors[] = 'Invalid urgency level.';
        }
        if ($petId && !$this->fetchOne($db, "SELECT id FROM pets WHERE id = ? AND owner_id = ?", [$petId, $ownerId])) {
            $errors[] = 'Choose one of your own pets.';
        }

        if (!empty($errors)) {
            return [null, $errors];
        }

        // Check availability (simplified)
        $available = $this->checkSurgeryAvailability($db, $procedureType);

        if (!$available) {
            $errors[] = 'No available OR, equipment, or specialist for this procedure.';
            return [null, $errors];
        }

        $procedureName = ucwords(str_replace('_', ' ', $procedureType)) . ' surgery request';
        $stmt = $db->prepare(
            "INSERT INTO medical_procedures (pet_id, vet_id, procedure_name, procedure_type, status, procedure_date, notes)
             VALUES (?, NULL, ?, ?, 'owner_requested', NULL, ?)"
        );
        $stmt->execute([$petId, $procedureName, $procedureType, "Owner request urgency: $urgency\nReason: $reason"]);
        $procedureId = (int) $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO surgery_requests (owner_id, pet_id, medical_procedure_id, procedure_type, reason, urgency, status) VALUES (?, ?, ?, ?, ?, ?, 'pending_vet_review')");
        $stmt->execute([$ownerId, $petId, $procedureId, $procedureType, $reason, $urgency]);
        $requestId = (int) $db->lastInsertId();

        $this->writeAudit($db, 'surgery_request', 'surgery_requests', $requestId, 'Surgery scheduling requested by owner.');

        return ['Surgery request submitted successfully. It is now visible for vet review.', []];
    }

    private function checkSurgeryAvailability($db, $procedureType)
    {
        // Simplified check: assume always available for now
        return true;
    }



    private function compileMedicalRecords($db, $petId)
    {
        // Simplified: fetch recent procedures and lab reports
        $procedures = $this->fetchAll($db, "SELECT * FROM medical_procedures WHERE pet_id = ? ORDER BY procedure_date DESC LIMIT 5", [$petId]);
        $labReports = $this->fetchAll($db, "SELECT * FROM lab_reports WHERE pet_id = ? ORDER BY COALESCE(report_date, DATE(created_at)) DESC LIMIT 5", [$petId]);
        return ['procedures' => $procedures, 'lab_reports' => $labReports];
    }

    private function encryptRecords($records)
    {
        // Simplified encryption
        return base64_encode(json_encode($records));
    }

    private function notifySpecialist($db, $specialistId, $referralId)
    {
        // Simplified notification
        // In real app, send email or in-app notification
    }



    private function uploadLabFile()
    {
        // Simplified file upload
        if ($_FILES['lab_file']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        $uploadDir = __DIR__ . '/../../public/uploads/lab_reports/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['lab_file']['name']);
        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['lab_file']['tmp_name'], $targetPath)) {
            return 'uploads/lab_reports/' . $filename;
        }
        return false;
    }

    private function parseLabResults($filePath, $testName)
    {
        // Simplified parsing
        return ['test' => $testName, 'values' => []];
    }

    private function generateInsights($parsedData)
    {
        // Simplified insights
        return ['owner_friendly' => 'Results look normal.', 'vet_details' => 'Detailed analysis required.'];
    }

    private function flagAbnormalities($parsedData)
    {
        // Simplified flagging
        return ['abnormal' => false];
    }

    private function procedureBookings($db, $vetId, $limit = null, $latest = false)
    {
        $where = $vetId ? 'WHERE pb.vet_id = ? OR pb.specialist_id = ?' : '';
        $orderDir = $latest ? 'DESC' : 'ASC';
        $sql = "SELECT pb.*, p.name AS pet_name, r.name AS room_name, e.name AS equipment_name, s.username AS specialist_name
             FROM procedure_bookings pb
             LEFT JOIN pets p ON p.id = pb.pet_id
             LEFT JOIN operating_rooms r ON r.id = pb.room_id
             LEFT JOIN surgical_equipment e ON e.id = pb.equipment_id
             LEFT JOIN veterinarians v ON v.id = pb.specialist_id
             LEFT JOIN users s ON s.id = v.user_id
             $where
             ORDER BY pb.start_time $orderDir";
        $params = $vetId ? [$vetId, $vetId] : [];
        if ($limit !== null) {
            $limit = (int) $limit;
            if ($limit > 0) {
                $sql .= " LIMIT $limit";
            }
        }

        return $this->fetchAll($db, $sql, $params);
    }

    private function handleProcedureBooking($db, $vetId, $role)
    {
        $errors = [];
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $procedureName = trim($_POST['procedure_name'] ?? '');
        $procedureType = trim($_POST['procedure_type'] ?? '');
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $equipmentId = (int) ($_POST['equipment_id'] ?? 0);
        $specialistId = (int) ($_POST['specialist_id'] ?? 0);
        $date = trim($_POST['procedure_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime = trim($_POST['end_time'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$petId) {
            $errors[] = 'Choose the patient record.';
        }
        if ($procedureName === '') {
            $errors[] = 'Procedure name cannot be empty.';
        }
        if (!$roomId) {
            $errors[] = 'Select an operating room.';
        }
        if (!$equipmentId) {
            $errors[] = 'Select surgical equipment.';
        }
        if (!$specialistId) {
            $errors[] = 'Select a specialist for this procedure.';
        }
        if (!$date || !$startTime || !$endTime) {
            $errors[] = 'Date and both start/end time are required.';
        }

        $startDateTime = strtotime("$date $startTime");
        $endDateTime = strtotime("$date $endTime");
        if ($startDateTime === false || $endDateTime === false) {
            $errors[] = 'Invalid date or time format.';
        } elseif ($startDateTime >= $endDateTime) {
            $errors[] = 'End time must be after start time.';
        }

        $start = $end = null;
        if (empty($errors)) {
            $start = date('Y-m-d H:i:s', $startDateTime);
            $end = date('Y-m-d H:i:s', $endDateTime);
            $conflict = $this->findBookingConflict($db, $roomId, $equipmentId, $specialistId, $start, $end);
            if ($conflict) {
                $errors[] = $conflict;
            }
        }

        if (!empty($errors)) {
            return [null, $errors];
        }

        $stmt = $db->prepare(
            "INSERT INTO procedure_bookings (pet_id, vet_id, room_id, equipment_id, specialist_id, procedure_name, procedure_type, start_time, end_time, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)"
        );
        $stmt->execute([
            $petId,
            $vetId,
            $roomId,
            $equipmentId,
            $specialistId,
            $procedureName,
            $procedureType,
            $start,
            $end,
            $notes
        ]);

        return ['Procedure scheduled successfully. The resource manager blocked any double-booking automatically.', []];
    }

    private function findBookingConflict($db, $roomId, $equipmentId, $specialistId, $start, $end)
    {
        $problems = [];

        foreach (
            [
                ['field' => 'room_id', 'id' => $roomId, 'label' => 'Operating room'],
                ['field' => 'equipment_id', 'id' => $equipmentId, 'label' => 'Equipment'],
                ['field' => 'specialist_id', 'id' => $specialistId, 'label' => 'Specialist']
            ] as $resource
        ) {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM procedure_bookings
                 WHERE status NOT IN ('cancelled', 'rejected')
                   AND {$resource['field']} = ?
                   AND NOT (end_time <= ? OR start_time >= ?)"
            );
            $stmt->execute([$resource['id'], $start, $end]);
            if ((int) $stmt->fetchColumn() > 0) {
                $message = sprintf('%s is already booked for the selected time window.', $resource['label']);
                if ($resource['field'] === 'room_id') {
                    $availableRoom = $this->availableRoomForWindow($db, $start, $end);
                    if ($availableRoom) {
                        $message .= sprintf(
                            ' Suggested alternative: %s is available from %s to %s on the same day.',
                            $availableRoom['name'],
                            date('H:i', strtotime($start)),
                            date('H:i', strtotime($end))
                        );
                    }
                }
                $problems[] = $message;
            }
        }

        return implode(' ', $problems);
    }

    private function availableRoomForWindow($db, $start, $end)
    {
        $stmt = $db->prepare(
            "SELECT r.*
             FROM operating_rooms r
             WHERE LOWER(COALESCE(r.status, 'available')) != 'unavailable'
               AND NOT EXISTS (
                   SELECT 1
                   FROM procedure_bookings pb
                   WHERE pb.room_id = r.id
                     AND pb.status != 'cancelled'
                     AND NOT (pb.end_time <= ? OR pb.start_time >= ?)
               )
             ORDER BY r.name ASC
             LIMIT 1"
        );
        $stmt->execute([$start, $end]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function clinicalReports($db)
    {
        return [
            'monthly' => $this->fetchAll($db, "SELECT DATE_FORMAT(start_time, '%Y-%m') AS label, COUNT(*) AS total FROM procedure_bookings GROUP BY DATE_FORMAT(start_time, '%Y-%m') ORDER BY label DESC LIMIT 6"),
            'rooms' => $this->fetchAll($db, "SELECT r.name AS label, COUNT(pb.id) AS total FROM operating_rooms r LEFT JOIN procedure_bookings pb ON pb.room_id = r.id GROUP BY r.id, r.name ORDER BY total DESC LIMIT 6"),
            'equipment' => $this->fetchAll($db, "SELECT e.name AS label, COUNT(pb.id) AS total FROM surgical_equipment e LEFT JOIN procedure_bookings pb ON pb.equipment_id = e.id GROUP BY e.id, e.name ORDER BY total DESC LIMIT 6")
        ];
    }

    private function accessControls($db)
    {
        return $this->fetchAll(
            $db,
            "SELECT ac.*, u.username AS created_by_name
             FROM access_controls ac
             LEFT JOIN users u ON u.id = ac.created_by
             ORDER BY ac.created_at DESC, ac.id DESC
             LIMIT 8"
        );
    }

    private function transferLogs($db)
    {
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
             LIMIT 8"
        );
    }

    private function securityAlerts($db)
    {
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
             LIMIT 8"
        );
    }

    private function vetCanReviewPetRequest($db, $vetId, $petId)
    {
        if (!$vetId || !$petId) {
            return false;
        }

        $row = $this->fetchOne(
            $db,
            "SELECT id
         FROM medical_procedures
         WHERE pet_id = ?
           AND vet_id = ?
         LIMIT 1",
            [$petId, $vetId]
        );

        return !empty($row);
    }

    private function vetRequests($db, $vetId)
    {
        $vetFilter = $vetId
            ? "AND EXISTS (
            SELECT 1
            FROM medical_procedures mp
            WHERE mp.pet_id = vr.pet_id
              AND mp.vet_id = ?
        )"
            : "";

        $params = $vetId ? [$vetId] : [];

        return $this->fetchAll(
            $db,
            "SELECT vr.*, p.name AS pet_name, p.species, u.username AS owner_name
         FROM vet_requests vr
         LEFT JOIN pets p ON p.id = vr.pet_id
         LEFT JOIN users u ON u.id = vr.owner_user_id
         WHERE vr.status = 'pending'
           $vetFilter
         ORDER BY
           CASE WHEN vr.priority = 'urgent' THEN 0 ELSE 1 END,
           vr.created_at DESC,
           vr.id DESC
         LIMIT 10",
            $params
        );
    }

    private function handleVetRequestResolution($db, $vetId)
    {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $resolution = $_POST['resolution'] ?? 'approved';
        $allowed = ['approved', 'completed', 'rejected'];

        if ($requestId <= 0 || !in_array($resolution, $allowed, true)) {
            return [null, ['Invalid vet request action.']];
        }

        $request = $this->fetchOne($db, "SELECT * FROM vet_requests WHERE id = ?", [$requestId]);
        if (!$request) {
            return [null, ['Vet request was not found.']];
        }

        if ($vetId && !$this->vetCanReviewPetRequest($db, $vetId, (int) ($request['pet_id'] ?? 0))) {
            return [null, ['This request is not linked to one of your pet cases.']];
        }

        if ($resolution === 'completed' && ($request['related_type'] ?? '') === 'vaccine' && !empty($request['related_id'])) {
            $stmt = $db->prepare("UPDATE vaccines SET status = 'completed' WHERE id = ?");
            $stmt->execute([(int) $request['related_id']]);

            if (($request['status'] ?? '') !== 'completed' && !empty($request['owner_user_id'])) {
                $loyalityPointsEarned = 5;
                $stmt = $db->prepare("INSERT INTO loyalty_points (user_id, points) VALUES (?, ?)");
                $stmt->execute([(int) $request['owner_user_id'], $loyalityPointsEarned]);
            }
        }

        $reviewerUserId = $_SESSION['user_id'] ?? null;
        $stmt = $db->prepare("
        UPDATE vet_requests
        SET status = ?, reviewed_by = ?, reviewed_at = NOW()
        WHERE id = ?
        ");

        $stmt->execute([$resolution, $reviewerUserId, $requestId]);
        if (
            in_array($resolution, ['approved', 'completed'], true)
            && ($request['request_type'] ?? '') === 'renal_care_diet_approval'
            && !empty($request['owner_user_id'])
        ) {
            $notify = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, is_read)
                VALUES (?, ?, ?, ?, 0)
            ");

            $notify->execute([
                (int) $request['owner_user_id'],
                'Diet approved',
                'Your vet approved Renal Care Diet. You can now buy it from the marketplace.',
                'diet_approval',
                0
            ]);
        }
        return ['Vet request updated successfully.', []];
    }

    private function handleRequestHealthRecord($db, $vetId)
    {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $petId = (int) ($_POST['pet_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($requestId <= 0 || $petId <= 0 || $title === '' || $description === '') {
            return [null, ['Please provide a title and notes for the health record.']];
        }

        $request = $this->fetchOne($db, "SELECT request_type FROM vet_requests WHERE id = ?", [$requestId]);
        if (($request['request_type'] ?? '') === 'chronic_alert' && stripos($title, 'chronic') === false) {
            $title = 'Chronic - ' . $title;
        }

        $stmt = $db->prepare("
            INSERT INTO health_records (pet_id, title, description, record_date)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([$petId, $title, $description]);

        if (($request['request_type'] ?? '') === 'chronic_alert') {
            $stmt = $db->prepare("UPDATE pets SET medical_notes = ? WHERE id = ?");
            $stmt->execute([$description, $petId]);
        }

        $stmt = $db->prepare("
            UPDATE vet_requests
            SET status = 'completed', reviewed_by = ?, reviewed_at = NOW()
            WHERE id = ?
        ");
        $reviewerUserId = $_SESSION['user_id'] ?? null;
        $stmt->execute([$reviewerUserId, $requestId]);

        return ['Health record added and request completed.', []];
    }

}

