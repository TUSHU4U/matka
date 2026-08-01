<?php
/**
 * Admin — User Management
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

requireAdmin();
$adminPageTitle = 'Admin Users';
$errors   = [];
$editing  = null;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'add' || $action === 'edit') {
        $username = sanitize($_POST['username'] ?? '');
        $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '';
        $password = $_POST['password'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
        if ($action === 'add' && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($action === 'edit' && $password !== '' && strlen($password) < 8) $errors[] = 'New password must be at least 8 characters.';

        if (empty($errors)) {
            if ($action === 'add') {
                // Check username uniqueness
                $dup = pdo()->prepare("SELECT id FROM admin_users WHERE username = :u LIMIT 1");
                $dup->execute([':u' => $username]);
                if ($dup->fetch()) {
                    $errors[] = 'Username already exists.';
                } else {
                    $hash = hashPassword($password);
                    pdo()->prepare("INSERT INTO admin_users (username, password_hash, email, is_active) VALUES (:u,:pw,:e,:a)")
                         ->execute([':u'=>$username,':pw'=>$hash,':e'=>$email,':a'=>$isActive]);
                    setFlash('success', "User '$username' added.");
                    header('Location: ' . APP_URL . '/admin/users.php'); exit;
                }
            } else {
                $uid  = (int)($_POST['id'] ?? 0);
                $sets = ['username = :u', 'email = :e', 'is_active = :a'];
                $params = [':u'=>$username, ':e'=>$email, ':a'=>$isActive, ':id'=>$uid];
                if ($password !== '') {
                    $sets[] = 'password_hash = :pw';
                    $params[':pw'] = hashPassword($password);
                }
                pdo()->prepare("UPDATE admin_users SET " . implode(', ', $sets) . " WHERE id = :id")
                     ->execute($params);
                setFlash('success', "User '$username' updated.");
                header('Location: ' . APP_URL . '/admin/users.php'); exit;
            }
        }
    }

    if ($action === 'delete') {
        $uid = (int)($_POST['id'] ?? 0);
        // Prevent self-delete
        if ($uid === (int)$_SESSION['admin_id']) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            // Ensure at least one admin remains
            $count = (int) pdo()->query("SELECT COUNT(*) FROM admin_users WHERE is_active = 1")->fetchColumn();
            if ($count <= 1) {
                setFlash('error', 'Cannot delete the last admin user.');
            } else {
                pdo()->prepare("DELETE FROM admin_users WHERE id = :id")->execute([':id' => $uid]);
                setFlash('success', 'User deleted.');
            }
        }
        header('Location: ' . APP_URL . '/admin/users.php'); exit;
    }
}

if (!empty($_GET['edit'])) {
    $editing = pdo()->prepare("SELECT * FROM admin_users WHERE id = :id LIMIT 1");
    $editing->execute([':id' => (int)$_GET['edit']]);
    $editing = $editing->fetch() ?: null;
}

$users = pdo()->query("SELECT * FROM admin_users ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="row g-4">

<!-- Add/Edit User Form -->
<div class="col-lg-4">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">
        <i class="bi bi-person-<?= $editing ? 'gear' : 'plus' ?> text-gold"></i>
        <?= $editing ? 'Edit User' : 'Add New User' ?>
      </div>
      <?php if ($editing): ?>
        <a href="?" class="btn-admin-secondary" style="font-size:11px;padding:5px 12px">
          <i class="bi bi-x"></i> Cancel
        </a>
      <?php endif; ?>
    </div>
    <div class="admin-card-body">
      <?php foreach ($errors as $err): ?>
        <div class="admin-alert admin-alert-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="POST" action="" class="dirty-track">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
        <?php if ($editing): ?>
          <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
        <?php endif; ?>

        <div class="admin-form-group">
          <label class="admin-label">Username *</label>
          <input type="text" name="username" class="admin-input"
                 value="<?= e($editing['username'] ?? '') ?>"
                 placeholder="admin" minlength="3" maxlength="50" required>
        </div>
        <div class="admin-form-group">
          <label class="admin-label">Email</label>
          <input type="email" name="email" class="admin-input"
                 value="<?= e($editing['email'] ?? '') ?>"
                 placeholder="admin@example.com">
        </div>
        <div class="admin-form-group">
          <label class="admin-label">Password <?= $editing ? '(leave blank to keep)' : '*' ?></label>
          <div style="position:relative">
            <input type="password" name="password" id="new-password" class="admin-input"
                   placeholder="<?= $editing ? 'Leave blank to keep current' : 'Min 8 characters' ?>"
                   <?= $editing ? '' : 'required minlength="8"' ?>>
            <button type="button" data-toggle-password="new-password"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--admin-muted);cursor:pointer;font-size:15px">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3 mb-4">
          <label class="toggle-switch">
            <input type="checkbox" name="is_active" <?= (!$editing || $editing['is_active']) ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
          <span class="admin-label" style="margin-bottom:0">Active</span>
        </div>
        <button type="submit" class="btn-admin-primary w-100" style="justify-content:center">
          <i class="bi bi-<?= $editing ? 'check-lg' : 'person-plus' ?>"></i>
          <?= $editing ? 'Update User' : 'Add User' ?>
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Users Table -->
<div class="col-lg-8">
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">
        <i class="bi bi-people text-gold"></i> Admin Users (<?= count($users) ?>)
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Last Login</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--admin-muted);padding:30px">No users.</td></tr>
          <?php else: ?>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td style="color:var(--admin-muted)"><?= $i+1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(240,165,0,0.12);display:flex;align-items:center;justify-content:center;color:var(--admin-gold);font-weight:700;font-size:13px">
                  <?= strtoupper(substr(e($u['username']), 0, 1)) ?>
                </div>
                <div>
                  <div style="font-weight:700"><?= e($u['username']) ?></div>
                  <?php if ((int)$u['id'] === (int)$_SESSION['admin_id']): ?>
                    <div style="font-size:10px;color:var(--admin-gold)">You</div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td style="color:var(--admin-muted)"><?= $u['email'] ? e($u['email']) : '—' ?></td>
            <td style="color:var(--admin-muted);font-size:12px">
              <?= $u['last_login'] ? e(date('d M Y H:i', strtotime($u['last_login']))) : 'Never' ?>
            </td>
            <td><span class="badge-<?= $u['is_active'] ? 'active' : 'inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <div class="d-flex gap-2">
                <a href="?edit=<?= (int)$u['id'] ?>" class="btn-admin-edit"><i class="bi bi-pencil"></i></a>
                <?php if ((int)$u['id'] !== (int)$_SESSION['admin_id']): ?>
                <form method="POST" action="" style="display:inline">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="btn-admin-danger"
                          data-confirm="Delete user '<?= e(addslashes($u['username'])) ?>'?">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
