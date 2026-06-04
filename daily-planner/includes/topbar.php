<?php
$user = get_user();
?>
<header class="topbar">
  <div class="topbar-left" style="display:flex; align-items:center; gap:14px;">
    <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle menu">☰</button>
    <div>
      <h2><?= $page_title ?? 'Dashboard' ?></h2>
      <div class="topbar-date"><?= date('l, j F Y') ?></div>
    </div>
  </div>
  <div class="topbar-right">
    <a href="dashboard.php" title="Home"><button class="topbar-btn">🏠</button></a>
    <a href="tasks.php"     title="Tasks"><button class="topbar-btn">✅</button></a>
    <a href="logout.php"    title="Logout"><button class="topbar-btn">🚪</button></a>
  </div>
</header>
