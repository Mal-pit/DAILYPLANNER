<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user = get_user();

function nav_link($file, $icon, $label, $current) {
    $active = ($current === $file) ? 'active' : '';
    echo "<a href=\"$file\" class=\"$active\"><span class=\"nav-icon\">$icon</span> $label</a>";
}
?>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <div class="logo-icon">📅</div>
      <span>DayFlow</span>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-user-row">
      <div class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
      </div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <?php nav_link('dashboard.php', '🏠', 'Dashboard',   $current_page); ?>
    <?php nav_link('tasks.php',     '✅', 'My Tasks',    $current_page); ?>
    <?php nav_link('schedule.php',  '🗓️', 'Schedule',    $current_page); ?>

    <div class="nav-section-label">Tracking</div>
    <?php nav_link('habits.php',    '🔥', 'Habit Tracker', $current_page); ?>

    <div class="nav-section-label">Account</div>
    <a href="logout.php"><span class="nav-icon">🚪</span> Logout</a>
  </nav>

  <div class="sidebar-footer" style="font-size:.75rem; color:var(--ink-muted);">
    IMS566 &mdash; Daily Planner v1.0
  </div>
</aside>
