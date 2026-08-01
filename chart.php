<?php
/**
 * Chart Page — Historical Results Table
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
startSecureSession();

$siteName = getSetting('site_name', APP_NAME);
$games    = getAllGames();

// Selected game
$slug     = sanitize($_GET['game'] ?? ($games[0]['slug'] ?? ''));
$game     = $slug ? getGameBySlug($slug) : ($games[0] ?? null);

// Date range (default: last 30 days)
$to   = today();
$from = date('Y-m-d', strtotime('-29 days'));

if (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) {
    $from = $_GET['from'];
}
if (!empty($_GET['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])) {
    $to = $_GET['to'];
}

$results = $game ? getResultsByDateRange($game['id'], $from, $to) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $game ? e($game['name']) . ' Chart' : 'Result Chart' ?> — <?= e($siteName) ?></title>
  <meta name="description" content="<?= $game ? e($game['name']) . ' Satta Matka result chart — daily jodi and panel history.' : '' ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/style.css">
</head>
<body>

<!-- Header Top -->
<div class="header-top">★ <?= e($siteName) ?> — RESULT CHART HISTORY ★</div>

<!-- Header -->
<header class="site-header">
  <div class="container">
    <div class="main-nav d-flex align-items-center justify-content-between">
      <a href="<?= e(APP_URL) ?>/" class="navbar-brand-custom">
        <div class="brand-logo">RK</div>
        <div class="brand-text">
          <span class="brand-name"><?= e($siteName) ?></span>
          <span class="brand-sub">Result Chart</span>
        </div>
      </a>
      <ul class="nav-menu" id="nav-menu">
        <li><a href="<?= e(APP_URL) ?>/">🏠 Home</a></li>
        <li><a href="<?= e(APP_URL) ?>/chart.php" class="active">📊 Chart</a></li>
        <li><a href="<?= e(APP_URL) ?>/contact.php">📞 Contact</a></li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <div id="live-clock" style="font-family:var(--font-number);font-size:13px;color:var(--text-secondary)"></div>
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
          <i class="bi bi-list"></i>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- Main -->
<main class="section">
  <div class="container">

    <!-- Page Title -->
    <div class="section-title mb-4">
      <div class="icon">📊</div>
      <h1 style="font-size:20px;font-weight:700;color:var(--text-primary)">
        <?= $game ? e($game['name']) . ' Result Chart' : 'Select a Market' ?>
      </h1>
      <div class="line"></div>
    </div>

    <!-- Filters -->
    <div class="glass p-4 mb-4">
      <form method="GET" action="" class="row g-3 align-items-end">
        <!-- Game selector -->
        <div class="col-md-4">
          <label class="form-label-dark">Market / Game</label>
          <select name="game" class="form-control-dark" onchange="this.form.submit()">
            <?php foreach ($games as $g): ?>
              <option value="<?= e($g['slug']) ?>" <?= ($game && $g['id'] === $game['id']) ? 'selected' : '' ?>>
                <?= e($g['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- From date -->
        <div class="col-md-3">
          <label class="form-label-dark">From Date</label>
          <input type="date" name="from" value="<?= e($from) ?>" class="form-control-dark">
        </div>
        <!-- To date -->
        <div class="col-md-3">
          <label class="form-label-dark">To Date</label>
          <input type="date" name="to" value="<?= e($to) ?>" max="<?= e(today()) ?>" class="form-control-dark">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn-gold w-100">
            <i class="bi bi-search me-1"></i> Search
          </button>
        </div>
      </form>
    </div>

    <!-- Quick Date Ranges -->
    <div class="d-flex gap-2 flex-wrap mb-4">
      <?php
      $ranges = [
          '7 Days'  => ['from' => date('Y-m-d', strtotime('-6 days')),  'to' => today()],
          '15 Days' => ['from' => date('Y-m-d', strtotime('-14 days')), 'to' => today()],
          '30 Days' => ['from' => date('Y-m-d', strtotime('-29 days')), 'to' => today()],
          '60 Days' => ['from' => date('Y-m-d', strtotime('-59 days')), 'to' => today()],
      ];
      foreach ($ranges as $label => $r):
          $active = ($from === $r['from'] && $to === $r['to']) ? 'active' : '';
      ?>
        <a href="?game=<?= e($slug) ?>&from=<?= e($r['from']) ?>&to=<?= e($r['to']) ?>"
           class="date-btn <?= $active ?>" style="padding:7px 16px;font-size:12px">
          <?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Chart Table -->
    <?php if ($game): ?>
    <div class="glass p-0" style="overflow:hidden">
      <div class="d-flex align-items-center justify-content-between p-3"
           style="border-bottom:1px solid var(--border-subtle)">
        <div>
          <span class="game-name" style="font-size:16px"><?= e($game['name']) ?></span>
          <span style="font-size:12px;color:var(--text-muted);margin-left:10px">
            <?= e(date('d M Y', strtotime($from))) ?> — <?= e(date('d M Y', strtotime($to))) ?>
          </span>
        </div>
        <span class="status-badge <?= $game['category'] === 'night' ? '' : 'live' ?>"
              style="<?= $game['category'] === 'night' ? 'background:rgba(139,92,246,.15);color:#a78bfa;border-color:rgba(139,92,246,.3)' : '' ?>">
          <?= $game['category'] === 'day' ? '☀️ Day' : '🌙 Night' ?>
        </span>
      </div>

      <?php if (empty($results)): ?>
        <div class="text-center py-5">
          <div style="font-size:48px;margin-bottom:12px">📭</div>
          <p style="color:var(--text-muted)">No results found for this date range.</p>
        </div>
      <?php else: ?>
      <div style="overflow-x:auto">
        <table class="chart-table">
          <thead>
            <tr>
              <th style="text-align:left">Date</th>
              <th>Open Panna</th>
              <th>Open Digit</th>
              <th>Jodi</th>
              <th>Close Digit</th>
              <th>Close Panna</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
              <td style="text-align:left;color:var(--text-secondary)">
                <?= e(date('d M Y', strtotime($r['result_date']))) ?><br>
                <small style="font-size:10px;color:var(--text-muted)"><?= e(date('D', strtotime($r['result_date']))) ?></small>
              </td>
              <td class="<?= empty($r['open_panna'])  ? 'empty-cell' : '' ?>"><?= $r['open_panna']  ? e($r['open_panna'])  : '***' ?></td>
              <td class="<?= empty($r['open_digit'])  ? 'empty-cell' : '' ?>"><?= $r['open_digit']  ? e($r['open_digit'])  : '*' ?></td>
              <td class="jodi-cell <?= empty($r['jodi']) ? 'empty-cell' : '' ?>"><?= $r['jodi'] ? e($r['jodi']) : '**' ?></td>
              <td class="<?= empty($r['close_digit']) ? 'empty-cell' : '' ?>"><?= $r['close_digit'] ? e($r['close_digit']) : '*' ?></td>
              <td class="<?= empty($r['close_panna']) ? 'empty-cell' : '' ?>"><?= $r['close_panna'] ? e($r['close_panna']) : '***' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="p-3 text-center" style="border-top:1px solid var(--border-subtle)">
        <span style="font-size:12px;color:var(--text-muted)">Showing <?= count($results) ?> results</span>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<button id="scroll-top" aria-label="Scroll to top"
  style="position:fixed;bottom:24px;right:24px;width:44px;height:44px;background:var(--gradient-gold);border:none;border-radius:12px;color:#000;font-size:20px;cursor:pointer;opacity:0;transition:opacity 0.3s;z-index:999;display:flex;align-items:center;justify-content:center">
  <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.APP_URL = '<?= e(APP_URL) ?>';</script>
<script src="<?= e(APP_URL) ?>/assets/js/main.js"></script>
</body>
</html>
