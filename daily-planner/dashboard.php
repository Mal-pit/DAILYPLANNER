<?php
require_once 'includes/config.php';
require_auth();

$user   = get_user();
$tasks  = get_tasks();
$habits = get_habits();

// Calculate stats from task data
$total_tasks    = count($tasks);
$done_tasks     = count(array_filter($tasks, fn($t) => $t['status'] === 'Done'));
$pending_tasks  = count(array_filter($tasks, fn($t) => $t['status'] === 'Pending'));
$in_prog_tasks  = count(array_filter($tasks, fn($t) => $t['status'] === 'In Progress'));
$completion_pct = $total_tasks > 0 ? round(($done_tasks / $total_tasks) * 100) : 0;

// Today's tasks (top 5)
$today_tasks = array_slice(array_filter($tasks, fn($t) => $t['status'] !== 'Done'), 0, 5);

// Category breakdown for chart
$categories = [];
foreach ($tasks as $t) {
    $categories[$t['category']] = ($categories[$t['category']] ?? 0) + 1;
}

// Priority breakdown for chart
$priorities = ['High' => 0, 'Medium' => 0, 'Low' => 0];
foreach ($tasks as $t) $priorities[$t['priority']]++;

$page_title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DayFlow Dashboard – Overview of your tasks, schedule and habits.">
  <title>Dashboard – DayFlow</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">

  <?php include 'includes/navbar.php'; ?>

  <div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <main class="page-content">

      <div class="fade-in" style="margin-bottom:24px;">
        <h2 style="font-size:1.65rem; margin-bottom:4px;">Good <?= (date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening')) ?>, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</h2>
        <p style="color:var(--ink-muted); font-size:.92rem;">Here's what your day looks like — <?= date('l, j F Y') ?></p>
      </div>

      <div class="stats-grid fade-in-2">
        <div class="stat-card stat-amber">
          <div class="stat-label">Total Tasks</div>
          <div class="stat-value"><?= $total_tasks ?></div>
          <div class="stat-sub">Across all categories</div>
          <div class="stat-icon">📋</div>
        </div>
        <div class="stat-card stat-sage">
          <div class="stat-label">Completed</div>
          <div class="stat-value"><?= $done_tasks ?></div>
          <div class="stat-sub"><?= $completion_pct ?>% completion rate</div>
          <div class="stat-icon">✅</div>
        </div>
        <div class="stat-card stat-rose">
          <div class="stat-label">Pending</div>
          <div class="stat-value"><?= $pending_tasks ?></div>
          <div class="stat-sub"><?= $in_prog_tasks ?> in progress</div>
          <div class="stat-icon">⏳</div>
        </div>
        <div class="stat-card stat-sky">
          <div class="stat-label">Habits Active</div>
          <div class="stat-value"><?= count($habits) ?></div>
          <div class="stat-sub">Longest streak: <?= max(array_column($habits,'streak')) ?> days</div>
          <div class="stat-icon">🔥</div>
        </div>
      </div>

      <div class="two-col fade-in-3">

        <div class="card">
          <div class="card-header">
            <h3>Task Overview</h3>
            <span class="badge badge-amber"><?= $completion_pct ?>% done</span>
          </div>
          <div class="card-body" style="display:flex; align-items:center; gap:28px; flex-wrap:wrap;">
            <div style="flex:0 0 200px; height:200px; position:relative;">
              <canvas id="donutChart"></canvas>
              <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                <div style="font-family:'Playfair Display',serif;font-size:1.7rem;font-weight:700;color:var(--ink)"><?= $completion_pct ?>%</div>
                <div style="font-size:.72rem;color:var(--ink-muted);">Complete</div>
              </div>
            </div>
            <div style="flex:1; min-width:120px;">
              <div style="display:flex;flex-direction:column;gap:12px;">
                <div>
                  <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;">
                    <span style="display:flex;align-items:center;gap:6px;"><span style="width:10px;height:10px;border-radius:50%;background:var(--sage);display:inline-block;"></span>Completed</span>
                    <strong><?= $done_tasks ?></strong>
                  </div>
                  <div class="progress-bar-wrap"><div class="progress-bar green" style="width:<?= $completion_pct ?>%"></div></div>
                </div>
                <div>
                  <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;">
                    <span style="display:flex;align-items:center;gap:6px;"><span style="width:10px;height:10px;border-radius:50%;background:var(--amber);display:inline-block;"></span>In Progress</span>
                    <strong><?= $in_prog_tasks ?></strong>
                  </div>
                  <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= round($in_prog_tasks/$total_tasks*100) ?>%"></div></div>
                </div>
                <div>
                  <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px;">
                    <span style="display:flex;align-items:center;gap:6px;"><span style="width:10px;height:10px;border-radius:50%;background:var(--rose);display:inline-block;"></span>Pending</span>
                    <strong><?= $pending_tasks ?></strong>
                  </div>
                  <div class="progress-bar-wrap"><div class="progress-bar red" style="width:<?= round($pending_tasks/$total_tasks*100) ?>%"></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>Tasks by Category</h3>
          </div>
          <div class="card-body">
            <canvas id="barChart" style="max-height:220px;"></canvas>
          </div>
        </div>

      </div>

      <div class="three-col fade-in-4">

        <div class="card">
          <div class="card-header">
            <h3>Today's Tasks</h3>
            <a href="tasks.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div class="card-body" style="padding-top:8px;">
            <?php if (empty($today_tasks)): ?>
              <div class="empty-state"><div class="empty-icon">🎉</div><p>All tasks completed!</p></div>
            <?php else: ?>
              <?php foreach (array_values($today_tasks) as $i => $task): ?>
              <div class="task-item">
                <div class="task-check <?= $task['status']==='Done' ? 'done' : '' ?>"></div>
                <div class="task-body">
                  <div class="task-title <?= $task['status']==='Done' ? 'done' : '' ?>"><?= htmlspecialchars($task['title']) ?></div>
                  <div class="task-meta">
                    <span>📁 <?= $task['category'] ?></span>
                    <span>📅 <?= $task['due'] ?></span>
                  </div>
                  <?php if ($task['progress'] > 0): ?>
                  <div class="progress-bar-wrap" style="margin-top:6px;">
                    <div class="progress-bar <?= $task['progress']>=100?'green':'' ?>" style="width:<?= $task['progress'] ?>%"></div>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="task-priority priority-<?= strtolower($task['priority']) ?>"><?= $task['priority'] ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:22px;">

          <div class="card">
            <div class="card-header"><h3>Calendar</h3></div>
            <div class="card-body">
              <div class="mini-calendar">
                <div class="cal-header">
                  <h4><?= date('F Y') ?></h4>
                </div>
                <div class="cal-grid">
                  <?php
                  $days = ['Su','Mo','Tu','We','Th','Fr','Sa'];
                  foreach ($days as $d) echo "<div class='cal-day-name'>$d</div>";

                  $first = mktime(0,0,0, date('n'), 1, date('Y'));
                  $start_dow = (int)date('w', $first);
                  $total_days = (int)date('t');
                  $today_day  = (int)date('j');
                  $event_days = [4, 7, 9, 12, 15, 18, 22]; // simulated event days

                  for ($i = 0; $i < $start_dow; $i++) echo "<div class='cal-day empty'></div>";
                  for ($d = 1; $d <= $total_days; $d++) {
                    $cls = $d === $today_day ? 'today' : '';
                    if (in_array($d, $event_days) && $d !== $today_day) $cls .= ' has-event';
                    echo "<div class='cal-day $cls'>$d</div>";
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3>Top Streaks</h3>
              <a href="habits.php" class="btn btn-sm btn-secondary">All Habits</a>
            </div>
            <div class="card-body" style="padding-top:10px;">
              <?php foreach (array_slice($habits, 0, 3) as $habit): ?>
              <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--border);">
                <span style="font-size:1.3rem;"><?= $habit['icon'] ?></span>
                <div style="flex:1;">
                  <div style="font-size:.88rem;font-weight:600;"><?= htmlspecialchars($habit['name']) ?></div>
                  <div class="progress-bar-wrap" style="margin-top:4px;">
                    <div class="progress-bar <?= $habit['streak']>=$habit['target']?'green':'' ?>"
                         style="width:<?= round($habit['streak']/$habit['target']*100) ?>%">
                    </div>
                  </div>
                </div>
                <div style="text-align:right;">
                  <div style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--amber);"><?= $habit['streak'] ?></div>
                  <div style="font-size:.7rem;color:var(--ink-muted);">days</div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

        </div>
      </div>

    </main>

    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<script>
// Donut Chart – Task status breakdown
const donutCtx = document.getElementById('donutChart').getContext('2d');
new Chart(donutCtx, {
  type: 'doughnut',
  data: {
    labels: ['Completed', 'In Progress', 'Pending'],
    datasets: [{
      data: [<?= $done_tasks ?>, <?= $in_prog_tasks ?>, <?= $pending_tasks ?>],
      backgroundColor: ['#6B8F6E', '#D4822A', '#C96B6B'],
      borderWidth: 0,
      hoverOffset: 6,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    cutout: '72%',
    plugins: { legend: { display: false } }
  }
});

const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($categories)) ?>,
    datasets: [{
      label: 'Tasks',
      data: <?= json_encode(array_values($categories)) ?>,
      backgroundColor: ['#F5C67F', '#6B8F6E', '#4A7FA5'],
      borderRadius: 8,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 12 } } },
      y: {
        beginAtZero: true,
        grid: { color: '#E8E2D9' },
        ticks: { stepSize: 1, font: { family: 'DM Sans', size: 12 } }
      }
    }
  }
});
</script>

</body>
</html>
