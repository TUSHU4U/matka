<?php
/**
 * Admin — Add Result
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

requireAdmin();
$adminPageTitle = 'Add Result';

$games  = getAllGames();
$errors = [];

// Pre-select game from query string
$preGameId = (int)($_GET['game_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $gameId     = (int)($_POST['game_id']     ?? 0);
    $resultDate = sanitize($_POST['result_date'] ?? '');
    $openPanna  = sanitize($_POST['open_panna']  ?? '');
    $openDigit  = sanitize($_POST['open_digit']  ?? '');
    $jodi       = sanitize($_POST['jodi']        ?? '');
    $closeDigit = sanitize($_POST['close_digit'] ?? '');
    $closePanna = sanitize($_POST['close_panna'] ?? '');
    $status     = in_array($_POST['status'] ?? '', ['pending','published']) ? $_POST['status'] : 'pending';

    // Validation
    if (!$gameId) $errors[] = 'Please select a game.';
    if (!$resultDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $resultDate)) $errors[] = 'Please enter a valid date.';
    if ($openPanna  && !preg_match('/^\d{1,3}$/', $openPanna))  $errors[] = 'Open panna must be 1–3 digits.';
    if ($openDigit  && !preg_match('/^\d$/', $openDigit))        $errors[] = 'Open digit must be single digit.';
    if ($jodi       && !preg_match('/^\d{2}$/', $jodi))          $errors[] = 'Jodi must be 2 digits.';
    if ($closeDigit && !preg_match('/^\d$/', $closeDigit))       $errors[] = 'Close digit must be single digit.';
    if ($closePanna && !preg_match('/^\d{1,3}$/', $closePanna)) $errors[] = 'Close panna must be 1–3 digits.';

    // Check for duplicate
    if (empty($errors) && $gameId && $resultDate) {
        $dup = pdo()->prepare("SELECT id FROM results WHERE game_id = :gid AND result_date = :d LIMIT 1");
        $dup->execute([':gid' => $gameId, ':d' => $resultDate]);
        if ($dup->fetch()) {
            $errors[] = 'A result for this game and date already exists. <a href="' . e(APP_URL) . '/admin/results.php?game_id=' . $gameId . '&date=' . e($resultDate) . '" style="color:var(--admin-gold)">View it here</a>.';
        }
    }

    if (empty($errors)) {
        $stmt = pdo()->prepare("
            INSERT INTO results (game_id, result_date, open_panna, open_digit, jodi, close_digit, close_panna, status)
            VALUES (:gid, :rd, :op, :od, :jo, :cd, :cp, :st)
        ");
        $stmt->execute([
            ':gid' => $gameId,
            ':rd'  => $resultDate,
            ':op'  => $openPanna  ?: null,
            ':od'  => $openDigit  ?: null,
            ':jo'  => $jodi       ?: null,
            ':cd'  => $closeDigit ?: null,
            ':cp'  => $closePanna ?: null,
            ':st'  => $status,
        ]);
        setFlash('success', 'Result added successfully!');
        header('Location: ' . APP_URL . '/admin/results.php');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row g-4 justify-content-center">
<div class="col-xl-7 col-lg-9">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title"><i class="bi bi-plus-circle text-gold"></i> Add New Result</div>
      <a href="<?= e(APP_URL) ?>/admin/results.php" class="btn-admin-secondary" style="font-size:12px;padding:6px 14px">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
    <div class="admin-card-body">
      <?php foreach ($errors as $err): ?>
        <div class="admin-alert admin-alert-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= $err ?></div>
      <?php endforeach; ?>

      <form method="POST" action="" class="dirty-track">
        <?= csrfField() ?>

        <!-- Game & Date -->
        <div class="row g-3 mb-4">
          <div class="col-md-7">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Game / Market *</label>
              <select name="game_id" id="game_id" class="admin-input admin-select" required>
                <option value="">— Select Game —</option>
                <?php foreach ($games as $g): ?>
                  <option value="<?= (int)$g['id'] ?>"
                          data-open="<?= e($g['open_time']) ?>" data-close="<?= e($g['close_time']) ?>"
                          <?= ((int)($_POST['game_id'] ?? $preGameId)) === (int)$g['id'] ? 'selected' : '' ?>>
                    <?= e($g['name']) ?> (<?= e(date('g:i A', strtotime($g['open_time']))) ?> – <?= e(date('g:i A', strtotime($g['close_time']))) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Result Date *</label>
              <div style="display:flex;gap:8px">
                <input type="date" name="result_date" id="result_date" class="admin-input"
                       value="<?= e($_POST['result_date'] ?? today()) ?>"
                       max="<?= e(today()) ?>" required>
                <button type="button" id="set-today"
                        class="btn-admin-secondary" style="padding:6px 12px;white-space:nowrap;font-size:12px"
                        title="Set to today">Today</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Open Section -->
        <div class="mb-4">
          <div class="result-section-label">
            <i class="bi bi-sunrise" style="color:#fbbf24"></i> Open Result
          </div>
          <div class="result-input-group" style="grid-template-columns:1fr auto 1fr;align-items:center;gap:16px">
            <div class="result-input-item">
              <div class="result-input-label">Open Panna</div>
              <input type="text" name="open_panna" id="open_panna"
                     class="result-input-field"
                     data-type="panna" data-digit-target="open_digit"
                     placeholder="123"
                     value="<?= e($_POST['open_panna'] ?? '') ?>"
                     maxlength="3" inputmode="numeric">
              <div class="input-hint mt-1">3-digit panel</div>
            </div>
            <div style="text-align:center;color:var(--admin-muted);font-size:18px">→</div>
            <div class="result-input-item">
              <div class="result-input-label">Open Digit</div>
              <input type="text" name="open_digit" id="open_digit"
                     class="result-input-field"
                     data-type="digit"
                     placeholder="5"
                     value="<?= e($_POST['open_digit'] ?? '') ?>"
                     maxlength="1" inputmode="numeric">
              <div class="input-hint mt-1">Single digit</div>
            </div>
          </div>
        </div>

        <!-- Jodi -->
        <div class="mb-4">
          <div class="result-section-label">
            <i class="bi bi-stars" style="color:var(--admin-gold)"></i> Jodi (Combined)
          </div>
          <div style="max-width:160px;margin:0 auto;text-align:center">
            <input type="text" name="jodi" id="jodi"
                   class="result-input-field"
                   data-type="jodi"
                   placeholder="55"
                   value="<?= e($_POST['jodi'] ?? '') ?>"
                   maxlength="2" inputmode="numeric"
                   style="font-size:36px;font-weight:900;color:var(--admin-gold);letter-spacing:6px">
            <div class="input-hint mt-2">2-digit jodi (auto-filled from digits)</div>
          </div>
        </div>

        <!-- Close Section -->
        <div class="mb-4">
          <div class="result-section-label">
            <i class="bi bi-sunset" style="color:#818cf8"></i> Close Result
          </div>
          <div class="result-input-group" style="grid-template-columns:1fr auto 1fr;align-items:center;gap:16px">
            <div class="result-input-item">
              <div class="result-input-label">Close Digit</div>
              <input type="text" name="close_digit" id="close_digit"
                     class="result-input-field"
                     data-type="digit"
                     placeholder="5"
                     value="<?= e($_POST['close_digit'] ?? '') ?>"
                     maxlength="1" inputmode="numeric">
              <div class="input-hint mt-1">Single digit</div>
            </div>
            <div style="text-align:center;color:var(--admin-muted);font-size:18px">→</div>
            <div class="result-input-item">
              <div class="result-input-label">Close Panna</div>
              <input type="text" name="close_panna" id="close_panna"
                     class="result-input-field"
                     data-type="panna" data-digit-target="close_digit"
                     placeholder="678"
                     value="<?= e($_POST['close_panna'] ?? '') ?>"
                     maxlength="3" inputmode="numeric">
              <div class="input-hint mt-1">3-digit panel</div>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="admin-form-group">
          <label class="admin-label">Publish Status</label>
          <div style="display:flex;gap:16px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
              <input type="radio" name="status" value="published"
                     <?= ($_POST['status'] ?? 'published') === 'published' ? 'checked' : '' ?>
                     style="accent-color:var(--admin-green)">
              <span class="badge-published">Published</span>
              <small style="color:var(--admin-muted)">Visible on website immediately</small>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
              <input type="radio" name="status" value="pending"
                     <?= ($_POST['status'] ?? '') === 'pending' ? 'checked' : '' ?>
                     style="accent-color:var(--admin-muted)">
              <span class="badge-pending">Pending</span>
              <small style="color:var(--admin-muted)">Save as draft</small>
            </label>
          </div>
        </div>

        <div class="d-flex gap-3 mt-4">
          <button type="submit" class="btn-admin-primary" style="flex:1;justify-content:center;padding:12px">
            <i class="bi bi-check-circle me-1"></i> Save Result
          </button>
          <a href="<?= e(APP_URL) ?>/admin/results.php" class="btn-admin-secondary" style="padding:12px 24px">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
