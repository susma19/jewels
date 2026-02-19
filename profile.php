<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

$conn = db();
$stmt = $conn->prepare('SELECT name, email FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    header('Location: logout.php');
    exit;
}

include __DIR__ . '/header.php';
?>
<main class="section section-light">
  <div class="container">
    <div class="section-head left-head">
      <h1>Update Profile</h1>
      <p>Manage your account details.</p>
    </div>
    <div class="profile-form-wrap">
      <form class="auth-form profile-form" id="profileForm" action="update_profile.php" method="post">
        <label for="profileName">Full Name</label>
        <input id="profileName" name="name" type="text" value="<?= htmlspecialchars($user['name']) ?>" required />
        <label for="profileEmail">Email</label>
        <input id="profileEmail" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required />
        <label for="currentPassword">Current Password</label>
        <input id="currentPassword" name="current_password" type="password" placeholder="Required to save changes" required />
        <label for="newPassword">New Password (leave blank to keep)</label>
        <input id="newPassword" name="password" type="password" placeholder="Minimum 6 characters" minlength="6" />
        <button class="btn btn-solid" type="submit">Save Changes</button>
        <p id="profileMessage" class="form-message"></p>
      </form>
    </div>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
<script>
document.getElementById('profileForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.currentTarget;
  const msg = document.getElementById('profileMessage');
  const body = new FormData(form);
  try {
    const res = await fetch('update_profile.php', { method: 'POST', body });
    const data = await res.json();
    msg.textContent = data.message;
    if (data.success) {
      msg.style.color = '#2d7a3e';
      const trigger = document.getElementById('userDropdownTrigger');
      if (trigger) trigger.textContent = 'Hi, ' + form.querySelector('[name=name]').value + ' ▾';
    } else {
      msg.style.color = '#b33';
    }
  } catch (_) {
    msg.textContent = 'Server error. Please try again.';
    msg.style.color = '#b33';
  }
});
</script>
