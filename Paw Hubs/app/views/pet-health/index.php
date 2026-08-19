<?php
/**
 * @var string   $selectedPetImage
 * @var array|null $selectedPet
 * @var array    $pets
 * @var string   $username
 * @var string   $userAddress
 * @var int      $loyaltyPoints
 * @var int      $healthScore
 * @var array    $vaccines
 * @var array    $vaccineOptions
 * @var int      $completedVaccinesCount
 * @var int      $totalVaccinesCount
 * @var int      $upcomingVaccinesCount
 * @var array    $todayMetrics
 * @var int|null $latestBCS
 * @var array    $weightData
 * @var array|null $alert
 * @var array    $chronicLogs
 * @var array    $trends
 * @var array    $healthRecords
 * @var string   $chronicConditionLabel
 * @var bool     $vetCanAdd
 * @var bool     $passportApproved
 * @var string|null $passportNumber
 * @var string   $passportStatusText
 * @var string   $passportDestination
 * @var string|null $passportIssuedDate
 * @var string   $passportVetName
 * @var string   $passportDocUrl
 * @var string   $microchipStatus
 * @var string   $microchipReference
 * @var string   $rabiesStatus
 * @var string   $rabiesValidUntil
 * @var string   $healthCertificateStatus
 * @var string   $tapewormStatus
 * @var string   $pawHubsPath
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PawCare - Pet Health Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="pet-health.css" rel="stylesheet">
</head>

<body>

  <?php require_once $pawHubsPath . '/app/views/partials/navbar.php'; ?>

  <main>


    <div class="dropdownBox">
      <button class="btn btn-ghost dropdown-toggle d-flex align-items-center px-3" type="button" id="petSelector"
        data-bs-toggle="dropdown" aria-expanded="false"
        style="border-radius: 12px; min-width: 280px; width: 100%; justify-content: space-between;">

        <div class="d-flex align-items-center gap-2">
          <img src="<?= htmlspecialchars($selectedPetImage) ?>" class="rounded-circle" id="selectedPetAvatar"
            alt="Pet" width="32" height="32">
          <div class="text-start">
            <div class="fw-semibold small lh-1" id="selectedPetName">
              <?= htmlspecialchars($selectedPet['name'] ?? 'No pet selected') ?>
            </div>
            <div class="text-muted small lh-1" style="font-size: 0.7rem;" id="selectedPetBreed">
              <?= htmlspecialchars($selectedPet['species'] ?? 'Add a pet to track health') ?>
            </div>
          </div>
        </div>

        <i class="bi bi-chevron-down text-muted" style="font-size: 0.7rem;"></i>
      </button>

      <ul class="dropdown-menu shadow border-0 p-2 dropdown-menu-end" style="border-radius: 16px; margin-top: 8px;">
        <li class="px-3 py-2">
          <div class="text-muted small fw-semibold">My Pets</div>
        </li>
        <li><hr class="dropdown-divider my-1"></li>

        <?php foreach ($pets as $pet): ?>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-2 rounded-3
              <?= $pet['id'] == ($selectedPet['id'] ?? null) ? 'active' : '' ?>"
              href="?pet_id=<?= $pet['id'] ?>">
              <img src="../Paw Hubs/public/uploads/pets/<?= htmlspecialchars($pet['image'] ?? 'default-pet.png') ?>"
                class="rounded-circle" width="40" alt="<?= htmlspecialchars($pet['name']) ?>">
              <div class="flex-grow-1">
                <div class="fw-semibold small"><?= htmlspecialchars($pet['name']) ?></div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                  <?= htmlspecialchars($pet['species']) ?> &middot; <?= htmlspecialchars((string) $pet['age']) ?> yrs
                </div>
              </div>
              <?php if ($pet['id'] == ($selectedPet['id'] ?? null)): ?>
                <span class="badge-soft b-ok">Active</span>
              <?php endif; ?>
            </a>
          </li>
          <li><hr class="dropdown-divider my-2"></li>
        <?php endforeach; ?>

        <li>
          <a class="dropdown-item text-center text-brand fw-semibold py-2"
            href="../Paw Hubs/public/index.php?url=my-pets" id="addPetBtn">
            <i class="bi bi-plus-circle me-1"></i> Add New Pet
          </a>
        </li>
      </ul>
    </div>


    <?php if (!empty($pets)): ?>

    <div class="pet-hero mb-4">
      <div class="pet-avatar">
        <?php if (!empty($selectedPet['image']) && $selectedPet['image'] !== 'default.png' && $selectedPet['image'] !== 'default-pet.png'): ?>
          <img src="<?= htmlspecialchars($selectedPetImage) ?>"
            alt="<?= htmlspecialchars($selectedPet['name']) ?>"
            style="width: 100%; height: 100%; object-fit: cover; border-radius: 24px;">
        <?php else: ?>
          <?php
            $species = strtolower($selectedPet['species'] ?? '');
            $emoji   = (strpos($species, 'cat') !== false || strpos($species, 'feline') !== false) ? '😺' : '🐶';
          ?>
          <span style="font-size: 3.4rem;"><?= $emoji ?></span>
        <?php endif; ?>
      </div>

      <div class="flex-grow-1">
        <div class="pet-hero-title">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <h4 class="mb-0"><?= htmlspecialchars($selectedPet['name']) ?></h4>
              <span class="chip"><span class="dot"></span> Healthy</span>
            </div>
            <div class="text-muted">
              <?= htmlspecialchars("{$selectedPet['species']} . {$selectedPet['gender']} . {$selectedPet['age']} yrs . {$selectedPet['weight']} kg") ?>
            </div>
          </div>

          <div class="dropdown pet-switcher">
            <button class="pet-switcher-btn dropdown-toggle" type="button" id="petSelectorHero"
              data-bs-toggle="dropdown" aria-expanded="false">
              <span class="pet-switcher-copy">
                <span class="pet-switcher-label">Choose pet to check</span>
                <span class="pet-switcher-name"><?= htmlspecialchars($selectedPet['name'] ?? 'No pet selected') ?></span>
              </span>
              <i class="bi bi-chevron-down"></i>
            </button>
            <ul class="dropdown-menu pet-switcher-menu shadow border-0 p-2" aria-labelledby="petSelectorHero">
              <li class="px-3 py-2">
                <div class="text-muted small fw-semibold">Switch pet</div>
              </li>
              <?php foreach ($pets as $pet): ?>
                <?php
                  $petImg = '../Paw Hubs/public/uploads/pets/default-pet.png';
                  if (!empty($pet['image']) && $pet['image'] !== 'default.png' && $pet['image'] !== 'default-pet.png') {
                    $petImg = '../Paw Hubs/public/uploads/pets/' . rawurlencode($pet['image']);
                  }
                ?>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-2 rounded-3
                    <?= $pet['id'] == ($selectedPet['id'] ?? null) ? 'active' : '' ?>"
                    href="?pet_id=<?= urlencode((string) $pet['id']) ?>">
                    <img src="<?= htmlspecialchars($petImg) ?>" class="rounded-circle pet-switcher-thumb"
                      alt="<?= htmlspecialchars($pet['name']) ?>">
                    <div class="flex-grow-1">
                      <div class="fw-semibold small"><?= htmlspecialchars($pet['name']) ?></div>
                      <div class="text-muted small" style="font-size: 0.75rem;">
                        <?= htmlspecialchars($pet['species']) ?> &middot; <?= htmlspecialchars((string) $pet['age']) ?> yrs
                      </div>
                    </div>
                    <?php if ($pet['id'] == ($selectedPet['id'] ?? null)): ?>
                      <span class="badge-soft b-ok">Active</span>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endforeach; ?>
              <li><hr class="dropdown-divider my-2"></li>
              <li>
                <a class="dropdown-item text-center text-brand fw-semibold py-2"
                  href="../Paw Hubs/public/index.php?url=user/profile#pets" id="addPetBtnHero">
                  <i class="bi bi-plus-circle me-1"></i> Add New Pet
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <span class="chip"><i class="bi bi-shield-check" style="color:var(--brand-deep)"></i>
            Vaccines: <?= (int) $completedVaccinesCount ?>/<?= (int) $totalVaccinesCount ?>
          </span>
          <span class="chip"><i class="bi bi-cpu" style="color:#3d8b95"></i>
            Microchip: <?= htmlspecialchars($microchipStatus) ?>
          </span>
          <span class="chip"><i class="bi bi-bandaid" style="color:#7f8f55"></i>
            Chronic: <?= htmlspecialchars($chronicConditionLabel) ?>
          </span>
          <span class="chip"><i class="bi bi-stars" style="color:#7f8f55"></i>
            Loyalty: <?= (int) $loyaltyPoints ?> pts
          </span>
          <span class="chip"><i class="bi bi-geo-alt text-danger"></i><?= htmlspecialchars($userAddress) ?></span>
        </div>
      </div>

      <div class="text-center">
        <div class="health-score" style="--score: <?= (int) $healthScore ?>%;">
          <span><?= (int) $healthScore ?>%</span>
        </div>
        <div class="small text-muted mt-2">Health Points</div>
      </div>

      <div class="d-none d-md-flex flex-column gap-2">
        <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#logEntryModal">
          <i class="bi bi-plus-lg"></i> Log Entry
        </button>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              style="vertical-align:-0.15em">
              <path d="m18 2 4 4"/><path d="m17 7 3-3"/>
              <path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/>
              <path d="m9 11 4 4"/><path d="m5 19-3 3"/><path d="m14 4 6 6"/>
            </svg>
          </div>
          <div>
            <div class="v"><?= (int) $upcomingVaccinesCount ?></div>
            <div class="l">Upcoming Vaccines</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico green"><i class="bi bi-activity"></i></div>
          <div>
            <div class="v"><?= htmlspecialchars((string) $selectedPet['weight']) ?> kg</div>
            <div class="l">Current Weight</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico sky"><i class="bi bi-droplet"></i></div>
          <div>
            <div class="v">
              <?= $todayMetrics['water_intake'] !== null ? htmlspecialchars((string) $todayMetrics['water_intake']) : '-' ?>
              <small class="text-muted fs-6">ml</small>
            </div>
            <div class="l">Water Intake</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico mint"><i class="bi bi-heart-pulse"></i></div>
          <div>
            <div class="v"><?= $latestBCS ? "BCS {$latestBCS}/9" : "No Data" ?></div>
            <div class="l">Body Condition Score</div>
          </div>
        </div>
      </div>
    </div>


    <div class="row g-4">

      <div class="col-lg-8">

        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Weight & Nutrition Trend</h5>
              <div class="text-muted small">BCS-aware analytics with sudden-change alerts</div>
            </div>
            <div class="d-flex gap-1">
              <button class="tab-pill">1M</button>
              <button class="tab-pill active">6M</button>
              <button class="tab-pill">1Y</button>
              <button class="tab-pill">All</button>
            </div>
          </div>
          <canvas id="weightChart" height="110"></canvas>

          <?php if ($alert): ?>
            <div class="alert mt-3 mb-0 d-flex align-items-center gap-2 rounded-3 border-0"
              style="background:#fff5e6;color:#9a5b15">
              <i class="bi bi-exclamation-triangle-fill"></i>
              Sudden weight <?= $alert['type'] === 'gain' ? 'gain' : 'loss' ?> detected:
              <strong><?= $alert['value'] ?>% in <?= $alert['days'] ?> days</strong>
              (<?= $alert['old'] ?> to <?= $alert['new'] ?> kg). Consider reviewing diet &amp; activity.
            </div>
          <?php endif; ?>
        </div>

        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Automated Vaccination Scheduler</h5>
              <div class="text-muted small">Auto-calculated by species, age &amp; history</div>
            </div>
            <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#scheduleVaccineModal">
              <i class="bi bi-calendar-plus"></i> Schedule
            </button>
          </div>

          <div class="vacc-list">
            <?php if (!empty($vaccines)): ?>
              <?php
                $iconColors = ['teal', 'warn', 'danger', 'green', 'sky'];
              ?>
              <?php foreach ($vaccines as $index => $vaccine): ?>
                <?php
                  $iconColor = $iconColors[$index % count($iconColors)];
                  $frequency = $vaccineOptions[$vaccine['vaccine_name']]['frequency'] ?? 'Scheduled';
                ?>
                <div class="vacc-row">
                  <div class="d-flex gap-3 align-items-center">
                    <div class="ico <?= $iconColor ?>" style="width:42px;height:42px;border-radius:12px">
                      <i class="bi bi-eyedropper"></i>
                    </div>
                    <div>
                      <div class="fw-bold"><?= htmlspecialchars($vaccine['vaccine_name']) ?></div>
                      <div class="text-muted small">Due date &middot; <?= htmlspecialchars($frequency) ?></div>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold"><?= htmlspecialchars($vaccine['formatted_date']) ?></div>
                    <span class="badge-soft b-<?= htmlspecialchars($vaccine['status_badge']) ?>">
                      <?= htmlspecialchars($vaccine['status_text']) ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="vacc-empty">No vaccines scheduled yet.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Chronic Condition Tracker</h5>
              <div class="text-muted small">Daily metrics for ongoing care plans</div>
            </div>
          </div>

          <div class="row g-3">
            <?php
              $metrics = [
                ['key' => 'insulin',        'label' => 'Insulin (today)',  'unit' => 'units'],
                ['key' => 'water_intake',   'label' => 'Water Intake',     'unit' => 'ml'],
                ['key' => 'mobility_score', 'label' => 'Mobility Score',   'unit' => '/10'],
              ];
            ?>
            <?php foreach ($metrics as $m): ?>
              <?php $trend = $trends[$m['key']] ?? 0; ?>
              <div class="col-6 col-md-3">
                <div class="metric">
                  <h6><?= $m['label'] ?></h6>
                  <div class="num">
                    <?= $todayMetrics[$m['key']] ?? '-' ?>
                    <small class="text-muted fs-6"><?= $m['unit'] ?></small>
                  </div>
                  <div class="small <?= $trend > 0 ? 'trend-up' : 'trend-dn' ?>">
                    <i class="bi bi-<?= $trend > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                    <?= abs($trend) ?>% vs avg
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-3"><canvas id="chronicChart" height="80"></canvas></div>
        </div>

      </div>

      <div class="col-lg-4">

        <div class="passport mb-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="text-uppercase small" style="letter-spacing:.18em;color:#dff1ec">Pet Travel Passport</div>
              <h4 class="mt-1 mb-0">
                <?= htmlspecialchars($selectedPet['name'] ?? 'Selected pet') ?>
                <span class="passport-number">
                  <?= $passportNumber ? htmlspecialchars(' - ' . $passportNumber) : '' ?>
                </span>
              </h4>
              <div class="passport-status mt-2 <?= $passportApproved ? 'is-approved' : 'is-pending' ?>">
                <i class="bi bi-<?= $passportApproved ? 'patch-check-fill' : 'hourglass-split' ?>"></i>
                <?= htmlspecialchars($passportStatusText) ?>
              </div>
            </div>
            <div class="seal"><i class="bi bi-patch-check"></i></div>
          </div>

          <div class="mt-3">
            <div class="row-line"><span>Destination</span><span><?= htmlspecialchars($passportDestination) ?></span></div>
            <div class="row-line"><span>Microchip</span><span><?= htmlspecialchars($microchipStatus . ' - ' . $microchipReference) ?></span></div>
            <div class="row-line"><span>Rabies Cert.</span><span><?= htmlspecialchars($rabiesStatus . ' - ' . $rabiesValidUntil) ?></span></div>
            <div class="row-line"><span>Health Cert.</span><span><?= htmlspecialchars($healthCertificateStatus) ?></span></div>
            <div class="row-line"><span>Tapeworm Tx.</span><span><?= htmlspecialchars($tapewormStatus) ?></span></div>
            <div class="row-line"><span>Vet Signature</span>
              <span><?= htmlspecialchars($passportApproved ? $passportVetName : 'Waiting for approval') ?></span>
            </div>
          </div>

          <div class="mt-3">
            <?php if ($passportApproved): ?>
              <button class="btn-brand w-100 passport-doc-btn" type="button"
                data-bs-toggle="modal" data-bs-target="#passportDocsModal">
                <i class="bi bi-file-earmark-richtext"></i> Generate Passport Docs &amp; QR
              </button>
            <?php else: ?>
              <form method="POST" class="passport-request-form">
                <input type="hidden" name="action" value="request_passport">
                <input type="hidden" name="pet_id" value="<?= $selectedPet['id'] ?? '' ?>">
                <input type="text" name="destination_country" class="form-control"
                  placeholder="Destination country" style="border-radius:12px">
                <button class="btn-brand flex-grow-1" style="background:#fff;color:var(--brand-deep)">
                  <i class="bi bi-file-earmark-medical"></i> Request Passport
                </button>
              </form>
            <?php endif; ?>

            <form method="POST" class="mt-2">
              <input type="hidden" name="action" value="request_microchip_surgery">
              <input type="hidden" name="pet_id" value="<?= $selectedPet['id'] ?? '' ?>">
              <?php if (!$passportApproved): ?>
                <button class="btn-ghost w-100" type="submit">
                  <i class="bi bi-cpu"></i> Request Microchip Surgery
                </button>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- Reminders -->
        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <h5 class="mb-0">Reminders</h5>
            <a href="#" class="small text-decoration-none" style="color:var(--brand-deep)">View all &rarr;</a>
          </div>

          <?php if (!empty($vaccines)): ?>
            <?php foreach (array_slice($vaccines, 0, 3) as $vaccine): ?>
              <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                <div class="ico warn" style="width:38px;height:38px;border-radius:12px;">
                  <i class="bi bi-eyedropper"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-semibold"><?= htmlspecialchars($vaccine['vaccine_name']) ?></div>
                  <div class="text-muted small">Due <?= htmlspecialchars($vaccine['formatted_date']) ?></div>
                </div>
                <span class="badge-soft b-<?= htmlspecialchars($vaccine['status_badge']) ?>">
                  <?= htmlspecialchars($vaccine['status_text']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted py-4">No vaccine reminders yet.</div>
          <?php endif; ?>
        </div>

        <!-- Health Records -->
        <div class="card-soft p-4">
          <div class="section-title">
            <h5>Health Records</h5>
            <?php if ($vetCanAdd): ?>
              <button class="btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#addHealthRecordModal">
                <i class="bi bi-plus-lg"></i> Add
              </button>
            <?php endif; ?>
          </div>

          <div class="timeline">
            <?php if (!empty($healthRecords)): ?>
              <?php foreach ($healthRecords as $record): ?>
                <div class="tl-item">
                  <div class="fw-semibold"><?= htmlspecialchars($record['title']) ?></div>
                  <div class="text-muted small">
                    <?= date('M d, Y', strtotime($record['record_date'])) ?>
                    &middot; <?= htmlspecialchars($username) ?>
                  </div>
                  <div class="small mt-1"><?= nl2br(htmlspecialchars($record['description'])) ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-journal-text fs-3 mb-2 d-block"></i>
                No health records yet.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Add Health Record Modal (Vet Only) -->
        <?php if ($vetCanAdd): ?>
          <div class="modal fade" id="addHealthRecordModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-3 px-4">
                  <h5 class="modal-title fw-bold">Add Health Record</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                  <form method="POST" action="">
                    <input type="hidden" name="action" value="add_health_record">
                    <input type="hidden" name="pet_id" value="<?= $selectedPet['id'] ?? '' ?>">
                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Record Title</label>
                      <input type="text" name="title" class="form-control" placeholder="e.g., Annual Checkup" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Date</label>
                      <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Description</label>
                      <textarea name="description" class="form-control" rows="3"
                        placeholder="Clinical notes, findings, recommendations..." required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn-brand flex-fill">Save Record</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div><!-- /col-lg-4 -->

    </div><!-- /row -->

    <?php else: ?>
      <h5 style="padding-left: 40%;">No pets added, Add pet to track!</h5>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════════
         PASSPORT DOCS MODAL
    ══════════════════════════════════════════════════════════════════════════ -->
    <?php if ($selectedPet && $passportApproved): ?>
      <div class="modal fade" id="passportDocsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content passport-doc-modal">
            <div class="modal-header border-0 pb-0">
              <div>
                <div class="text-uppercase small text-muted fw-semibold">Approved passport documents</div>
                <h5 class="modal-title fw-bold"><?= htmlspecialchars($selectedPet['name']) ?> travel passport</h5>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="passport-doc-layout">
                <section class="passport-document" id="passportDocument">
                  <div class="passport-doc-header">
                    <div>
                      <div class="text-uppercase small text-muted fw-semibold">PawCare Pet Passport</div>
                      <h3><?= htmlspecialchars($passportNumber ?? 'Approved passport') ?></h3>
                    </div>
                    <div class="passport-doc-seal"><i class="bi bi-patch-check-fill"></i></div>
                  </div>
                  <div class="passport-doc-grid">
                    <div><span>Pet name</span>    <strong><?= htmlspecialchars($selectedPet['name']) ?></strong></div>
                    <div><span>Species</span>     <strong><?= htmlspecialchars($selectedPet['species']) ?></strong></div>
                    <div><span>Gender</span>      <strong><?= htmlspecialchars($selectedPet['gender'] ?? 'Unknown') ?></strong></div>
                    <div><span>Age</span>         <strong><?= htmlspecialchars((string) $selectedPet['age']) ?> yrs</strong></div>
                    <div><span>Weight</span>      <strong><?= htmlspecialchars((string) $selectedPet['weight']) ?> kg</strong></div>
                    <div><span>Owner</span>       <strong><?= htmlspecialchars($username) ?></strong></div>
                    <div><span>Destination</span> <strong><?= htmlspecialchars($passportDestination) ?></strong></div>
                    <div><span>Issued</span>      <strong><?= htmlspecialchars($passportIssuedDate ? date('M d, Y', strtotime($passportIssuedDate)) : 'N/A') ?></strong></div>
                    <div><span>Microchip</span>   <strong><?= htmlspecialchars($microchipStatus . ' - ' . $microchipReference) ?></strong></div>
                    <div><span>Rabies certificate</span> <strong><?= htmlspecialchars($rabiesStatus . ' - ' . $rabiesValidUntil) ?></strong></div>
                    <div><span>Health certificate</span> <strong><?= htmlspecialchars($healthCertificateStatus) ?></strong></div>
                    <div><span>Vet signature</span>      <strong><?= htmlspecialchars($passportVetName) ?></strong></div>
                  </div>
                </section>

                <aside class="passport-qr-panel">
                  <div id="passportQrCode" data-doc-url="<?= htmlspecialchars($passportDocUrl) ?>"></div>
                  <button class="btn-brand w-100 mt-2" type="button"
                    onclick="setTimeout(() => window.print(), 300)">
                    <i class="bi bi-printer"></i> Print / Save PDF
                  </button>
                </aside>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════════
         SCHEDULE VACCINE MODAL
    ══════════════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="scheduleVaccineModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
          <div class="modal-header border-0 pb-0 pt-3 px-4">
            <h5 class="modal-title fw-bold">Schedule Vaccine</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body px-4 py-3">
            <form method="POST">
              <input type="hidden" name="action" value="add_vaccine">
              <input type="hidden" name="pet_id" value="<?= $selectedPet['id'] ?? '' ?>">
              <div class="mb-3">
                <label class="form-label fw-semibold small">Vaccine</label>
                <select name="vaccine_name" class="form-control" required>
                  <option value="">Choose vaccine</option>
                  <?php
                    $scheduledNames = array_column($vaccines, 'vaccine_name');
                  ?>
                  <?php foreach ($vaccineOptions as $name => $info): ?>
                    <?php if (!in_array($name, $scheduledNames)): ?>
                      <option value="<?= htmlspecialchars($name) ?>">
                        <?= htmlspecialchars($name) ?> - <?= htmlspecialchars($info['frequency']) ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small">Due Date</label>
                <input type="date" name="due_date" class="form-control" required>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-brand flex-fill">Save Schedule</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         LOG ENTRY MODAL
    ══════════════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="logEntryModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
          <div class="modal-header border-0 pb-0 pt-3 px-4">
            <h5 class="modal-title fw-bold">Log Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body px-4 py-3">
            <form id="logEntryForm" method="POST">
              <input type="hidden" name="action" value="log_entry">
              <input type="hidden" name="pet_id" value="<?= $selectedPet['id'] ?? '' ?>">

              <div class="mb-3">
                <label class="form-label fw-semibold small">Entry Type</label>
                <div class="d-flex gap-2">
                  <input type="radio" class="btn-check" name="entry_type" id="typeWeight" value="weight" checked>
                  <label class="btn btn-outline-secondary flex-fill" for="typeWeight">Weight Only</label>
                  <input type="radio" class="btn-check" name="entry_type" id="typeChronic" value="chronic">
                  <label class="btn btn-outline-secondary flex-fill" for="typeChronic">Weight + Chronic</label>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Weight (kg)</label>
                <input type="number" step="0.01" name="weight" class="form-control"
                  placeholder="e.g., 28.4" required>
              </div>

              <div id="chronicFields" class="d-none border-top pt-3 mt-3">
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label fw-semibold small">Insulin (units)</label>
                    <input type="number" step="0.1" name="insulin_units" class="form-control" placeholder="0">
                  </div>
                  <div class="col-6">
                    <label class="form-label fw-semibold small">Water Intake (ml)</label>
                    <input type="number" name="water_ml" class="form-control" placeholder="0">
                  </div>
                  <div class="col-6">
                    <label class="form-label fw-semibold small">Mobility Score (1-10)</label>
                    <input type="number" name="mobility_score" class="form-control" min="1" max="10" placeholder="0">
                  </div>
                  <div class="col-6">
                    <label class="form-label fw-semibold small">BCS (1-9)</label>
                    <input type="number" name="bcs_score" class="form-control" min="1" max="9" placeholder="0">
                  </div>
                </div>
              </div>

              <div class="mt-4 d-flex gap-2">
                <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-brand flex-fill">Save Log</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Pet switched toast -->
    <?php if (isset($_GET['pet_id']) && $selectedPet): ?>
      <div id="phpToast" class="position-fixed bottom-0 end-0 m-3 card-soft px-4 py-3 shadow"
        style="z-index: 1100; animation: slideIn 0.3s ease;">
        <div class="fw-semibold text-brand-deep">
          Switched to <?= htmlspecialchars($selectedPet['name']) ?> 🐾
        </div>
      </div>
      <script>
        setTimeout(() => {
          const toast = document.getElementById('phpToast');
          if (toast) {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
          }
        }, 1000);
      </script>
    <?php endif; ?>

  </main>

  <!-- ════════════════════════════════════════════════════════════════════════
       SCRIPTS
  ════════════════════════════════════════════════════════════════════════════ -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    window.PET_DATA = {
      weightLogs:  <?= json_encode($weightData  ?? [], JSON_NUMERIC_CHECK) ?>,
      alert:       <?= json_encode($alert       ?? null) ?>,
      chronicLogs: <?= json_encode($chronicLogs,         JSON_NUMERIC_CHECK) ?>
    };

    document.addEventListener('DOMContentLoaded', () => {
      if (typeof initRealWeightChart === 'function' && window.PET_DATA.weightLogs?.length > 0) {
        initRealWeightChart(window.PET_DATA.weightLogs, window.PET_DATA.alert);
      }
    });

    document.getElementById('logEntryModal')?.addEventListener('show.bs.modal', function () {
      const petIdInput = document.querySelector('#logEntryForm input[name="pet_id"]');
      if (petIdInput) petIdInput.value = <?= json_encode($selectedPet['id'] ?? '') ?>;
      document.getElementById('chronicFields')?.classList.add('d-none');
      const weightType = document.getElementById('typeWeight');
      if (weightType) weightType.checked = true;
    });

    document.querySelectorAll('input[name="entry_type"]').forEach(radio => {
      radio.addEventListener('change', function () {
        const chronicDiv = document.getElementById('chronicFields');
        this.value === 'chronic'
          ? chronicDiv.classList.remove('d-none')
          : chronicDiv.classList.add('d-none');
      });
    });
  </script>

  <script src="pet-health.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    document.getElementById('passportDocsModal')?.addEventListener('shown.bs.modal', () => {
      const qrTarget = document.getElementById('passportQrCode');
      if (!qrTarget || qrTarget.dataset.rendered === '1') return;
      const docUrl = qrTarget.dataset.docUrl || window.location.href;
      if (typeof QRCode === 'function') {
        new QRCode(qrTarget, {
          text: docUrl, width: 160, height: 160,
          colorDark: '#1f2a36', colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H
        });
        qrTarget.dataset.rendered = '1';
      } else {
        const fallback = document.createElement('a');
        fallback.href = docUrl;
        fallback.textContent = 'Open passport documents';
        fallback.target = '_blank';
        qrTarget.appendChild(fallback);
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      const params = new URLSearchParams(window.location.search);
      if (params.get('print') === '1') {
        const modal = document.getElementById('passportDocsModal');
        if (modal) {
          modal.classList.remove('fade');
          modal.classList.add('show');
          modal.style.display    = 'block';
          modal.style.opacity    = '1';
          modal.style.visibility = 'visible';
          modal.style.position   = 'static';
          modal.style.transform  = 'none';
          modal.setAttribute('aria-hidden', 'false');
        }
        setTimeout(() => window.print(), 200);
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php require_once $pawHubsPath . '/app/views/partials/footer.php'; ?>

</body>
</html>
