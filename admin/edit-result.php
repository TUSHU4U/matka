<?php
/**
 * Admin — Edit Result
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

requireAdmin();
$adminPageTitle = 'Edit Result';

$id     = (int)($_GET['id'] ?? 0);
$result = getResultById($id);
if (!$result) {
    setFlash('error', 'Result not found.');
    header('Location: ' . APP_URL . '/admin/results.php');
    exit;
}

$games  = getAllGames(false);
$errors = [];

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

    if (!$gameId) $errors[] = 'Please select a game.';
    if (!$resultDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $resultDate)) $errors[] = 'Please enter a valid date.';
    if ($openPanna  && !preg_match('/^\d{1,3}$/', $openPanna))  $errors[] = 'Open panna must be 1–3 digits.';
    if ($openDigit  && !preg_match('/^\d$/', $openDigit))        $errors[] = 'Open digit must be single digit.';
    if ($jodi       && !preg_match('/^\d{2}$/', $jodi))          $errors[] = 'Jodi must be 2 digits.';
    if ($closeDigit && !preg_match('/^\d$/', $closeDigit))       $errors[] = 'Close digit must be single digit.';
    if ($closePanna && !preg_match('/^\d{1,3}$/', $closePanna)) $errors[] = 'Close panna must be 1–3 digits.';

    // Duplicate check (excluding current record)
    if (empty($errors)) {
        $dup = pdo()->prepare("SELECT id FROM results WHERE game_id = :gid AND result_date = :d AND id != :id LIMIT 1");
        $dup->execute([':gid' => $gameId, ':d' => $resultDate, ':id' => $id]);
        if ($dup->fetch()) {
            $errors[] = 'Another result for this game and date already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = pdo()->prepare("
            UPDATE results SET
                game_id     = :gid,
                result_date = :rd,
                open_panna  = :op,
                open_digit  = :od,
                jodi        = :jo,
                close_digit = :cd,
                close_panna = :cp,
                status      = :st,
                updated_at  = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':gid' => $gameId, ':rd' => $resultDate,
            ':op'  => $openPanna  ?: null, ':od' => $openDigit  ?: null,
            ':jo'  => $jodi       ?: null, ':cd' => $closeDigit ?: null,
            ':cp'  => $closePanna ?: null, ':st' => $status, ':id' => $id,
        ]);
        setFlash('success', 'Result updated successfully!');
        header('Location: ' . APP_URL . '/admin/results.php');
        exit;
    }
    // Merge POST values back into $result for re-display
    $result = array_merge($result, compact('gameId','resultDate','openPanna','openDigit','jodi','closeDigit','closePanna','status'));
    $result['game_id']     = $gameId;
    $result['result_date'] = $resultDate;
    $result['open_panna']  = $openPanna;
    $result['open_digit']  = $openDigit;
    $result['jodi']        = $jodi;
    $result['close_digit'] = $closeDigit;
    $result['close_panna'] = $closePanna;
    $result['status']      = $status;
}

include __DIR__ . '/includes/header.php';
?>

<div class="row g-4 justify-content-center">
<div class="col-xl-7 col-lg-9">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">
        <i class="bi bi-pencil-square text-gold"></i>
        Edit Result — <span style="color:var(--admin-gold)"><?= e($result['game_name']) ?></span>
        <span style="font-size:12px;color:var(--admin-muted);margin-left:8px"><?= e(date('d M Y', strtotime($result['result_date']))) ?></span>
      </div>
      <a href="<?= e(APP_URL) ?>/admin/results.php" class="btn-admin-secondary" style="font-size:12px;padding:6px 14px">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
    <div class="admin-card-body">
      <?php foreach ($errors as $err): ?>
        <div class="admin-alert admin-alert-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="POST" action="" class="dirty-track">
        <?= csrfField() ?>

        <!-- Game & Date -->
        <div class="row g-3 mb-4">
          <div class="col-md-7">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Game / Market *</label>
              <select name="game_id" class="admin-input admin-select" required>
                <?php foreach ($games as $g): ?>
                  <option value="<?= (int)$g['id'] ?>"
                          <?= (int)$result['game_id'] === (int)$g['id'] ? 'selected' : '' ?>>
                    <?= e($g['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="admin-form-group mb-0">
              <label class="admin-label">Result Date *</label>
              <div style="display:flex;gap:8px">
                <input type="date" name="result_date" id="result_date"
                       class="admin-input"
                       value="<?= e($result['result_date']) ?>"
                       required>
                <button type="button" id="set-today"
                        class="btn-admin-secondary" style="padding:6px 12px;font-size:12px">Today</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Open -->
        <div class="mb-4">
          <div class="result-section-label"><i class="bi bi-sunrise" style="color:#fbbf24"></i> Open Result</div>
          <div class="result-input-group" style="grid-template-columns:1fr auto 1fr;align-items:center;gap:16px">
            <div class="result-input-item">
              <div class="result-input-label">Open Panna</div>
              <input type="text" name="open_panna" id="open_panna"
                     class="result-input-field" data-type="panna" data-digit-target="open_digit"
                     placeholder="123" value="<?= e($result['open_panna'] ?? '') ?>" maxlength="3" inputmode="numeric">
            </div>
            <div style="text-align:center;color:var(--admin-muted);font-size:18px">→</div>
            <div class="result-input-item">
              <div class="result-input-label">Open Digit</div>
              <input type="text" name="open_digit" id="open_digit"
                     class="result-input-field" data-type="digit"
                     placeholder="5" value="<?= e($result['open_digit'] ?? '') ?>" maxlength="1" inputmode="numeric">
            </div>
          </div>
        </div>

        <!-- Jodi -->
        <div class="mb-4">
          <div class="result-section-label"><i class="bi bi-stars" style="color:var(--admin-gold)"></i> Jodi</div>
          <div style="max-width:160px;margin:0 auto;text-align:center">
            <input type="text" name="jodi" id="jodi"
                   class="result-input-field" data-type="jodi"
                   placeholder="55" value="<?= e($result['jodi'] ?? '') ?>"
                   maxlength="2" inputmode="numeric"
                   style="font-size:36px;font-weight:900;color:var(--admin-gold);letter-spacing:6px">
          </div>
        </div>

        <!-- Close -->
        <div class="mb-4">
          <div class="result-section-label"><i class="bi bi-sunset" style="color:#818cf8"></i> Close Result</div>
          <div class="result-input-group" style="grid-template-columns:1fr auto 1fr;align-items:center;gap:16px">
            <div class="result-input-item">
              <div class="result-input-label">Close Digit</div>
              <input type="text" name="close_digit" id="close_digit"
                     class="result-input-field" data-type="digit"
                     placeholder="5" value="<?= e($result['close_digit'] ?? '') ?>" maxlength="1" inputmode="numeric">
            </div>
            <div style="text-align:center;color:var(--admin-muted);font-size:18px">→</div>
            <div class="result-input-item">
              <div class="result-input-label">Close Panna</div>
              <input type="text" name="close_panna" id="close_panna"
                     class="result-input-field" data-type="panna" data-digit-target="close_digit"
                     placeholder="678" value="<?= e($result['close_panna'] ?? '') ?>" maxlength="3" inputmode="numeric">
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="admin-form-group">
          <label class="admin-label">Publish Status</label>
          <div style="display:flex;gap:16px;flex-wrap:wrap">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
              <input type="radio" name="status" value="published"
                     <?= ($result['status'] ?? '') === 'published' ? 'checked' : '' ?>
                     style="accent-color:var(--admin-green)">
              <span class="badge-published">Published</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
              <input type="radio" name="status" value="pending"
                     <?= ($result['status'] ?? '') === 'pending' ? 'checked' : '' ?>
                     style="accent-color:var(--admin-muted)">
              <span class="badge-pending">Pending</span>
            </label>
          </div>
        </div>

        <div class="d-flex gap-3 mt-4">
          <button type="submit" class="btn-admin-primary" style="flex:1;justify-content:center;padding:12px">
            <i class="bi bi-check-circle me-1"></i> Update Result
          </button>
          <a href="<?= e(APP_URL) ?>/admin/results.php" class="btn-admin-secondary" style="padding:12px 24px">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
