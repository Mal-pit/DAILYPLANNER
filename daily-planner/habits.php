<?php
require_once 'includes/config.php';
require_auth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        add_habit($_POST);
        header('Location: habits.php?msg=added'); exit();
    }
    if ($action === 'update') {
        update_habit((int)$_POST['id'], $_POST);
        header('Location: habits.php?msg=updated'); exit();
    }
}
if ($action === 'delete' && isset($_GET['id'])) {
    delete_habit((int)$_GET['id']);
    header('Location: habits.php?msg=deleted'); exit();
}

$habits = get_habits();
$msg = $_GET['msg'] ?? '';

$total_habits = count($habits);
$avg_streak   = $total_habits ? round(array_sum(array_column($habits,'streak')) / $total_habits) : 0;
$max_streak   = $total_habits ? max(array_column($habits,'streak')) : 0;
$top_idx      = $total_habits ? array_search($max_streak, array_column($habits,'streak')) : 0;
$top_habit    = $habits[$top_idx] ?? ['icon'=>'🏆','name'=>'-'];

$cat_counts = [];
foreach ($habits as $h) $cat_counts[$h['category']] = ($cat_counts[$h['category']] ?? 0) + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Habits – DayFlow</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">

  <?php include 'includes/navbar.php'; ?>

  <div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <main class="page-content">

      <?php if ($msg === 'added'):   ?><div class="form-success fade-in">✅ Habit added!</div><?php endif; ?>
      <?php if ($msg === 'updated'): ?><div class="form-success fade-in">✏️ Habit updated!</div><?php endif; ?>
      <?php if ($msg === 'deleted'): ?><div class="form-error fade-in">🗑️ Habit deleted.</div><?php endif; ?>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;" class="fade-in">
        <div>
          <h2 style="font-size:1.6rem;">Habit Tracker</h2>
          <p style="color:var(--ink-muted);font-size:.88rem;">Build consistent routines, one day at a time</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addHabitModal')">+ Add Habit</button>
      </div>

      <div class="stats-grid fade-in" style="margin-bottom:24px;">
        <div class="stat-card stat-amber">
          <div class="stat-label">Total Habits</div>
          <div class="stat-value"><?= $total_habits ?></div>
          <div class="stat-sub">Active routines</div>
          <div class="stat-icon">📋</div>
        </div>
        <div class="stat-card stat-rose">
          <div class="stat-label">Avg Streak</div>
          <div class="stat-value"><?= $avg_streak ?></div>
          <div class="stat-sub">Days average</div>
          <div class="stat-icon">📈</div>
        </div>
        <div class="stat-card stat-sage">
          <div class="stat-label">Longest Streak</div>
          <div class="stat-value"><?= $max_streak ?></div>
          <div class="stat-sub"><?= htmlspecialchars($top_habit['icon'].' '.$top_habit['name']) ?></div>
          <div class="stat-icon">🔥</div>
        </div>
        <div class="stat-card stat-sky">
          <div class="stat-label">Categories</div>
          <div class="stat-value"><?= count($cat_counts) ?></div>
          <div class="stat-sub">Health, Work, Personal</div>
          <div class="stat-icon">🗂️</div>
        </div>
      </div>

      <div class="two-col fade-in-2">

        <div class="card">
          <div class="card-header">
            <h3>My Habits</h3>
            <span class="badge badge-amber"><?= $total_habits ?> habits</span>
          </div>
          <div class="card-body">
            <?php if (empty($habits)): ?>
              <p style="text-align:center;color:var(--ink-muted);padding:32px;">No habits yet. Add your first one!</p>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
              <?php foreach ($habits as $habit):
                $pct = $habit['target'] > 0 ? round($habit['streak'] / $habit['target'] * 100) : 0;
                $cat_colors = ['Health'=>'sage','Work'=>'sky','Personal'=>'amber'];
                $cc = $cat_colors[$habit['category']] ?? 'amber';
              ?>
              <div style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius);padding:18px;transition:box-shadow .2s,transform .2s;" class="habit-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                  <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:1.6rem;"><?= $habit['icon'] ?></span>
                    <div>
                      <div style="font-weight:600;font-size:.92rem;"><?= htmlspecialchars($habit['name']) ?></div>
                      <span class="badge badge-<?= $cc ?>" style="margin-top:2px;"><?= $habit['category'] ?></span>
                    </div>
                  </div>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-secondary btn-sm"
                      onclick="openEditHabit(<?= htmlspecialchars(json_encode($habit)) ?>)"
                      style="padding:2px 7px;font-size:.75rem;" title="Edit">✏️</button>
                    <a href="habits.php?action=delete&id=<?= $habit['id'] ?>"
                      class="btn btn-sm"
                      style="background:var(--rose);color:#fff;border-color:var(--rose);padding:2px 7px;font-size:.75rem;"
                      onclick="return confirm('Delete this habit?')" title="Delete">🗑️</a>
                  </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:8px;">
                  <div>
                    <div style="font-size:.75rem;color:var(--ink-muted);">Current streak</div>
                    <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--amber);line-height:1;"><?= $habit['streak'] ?> <span style="font-size:.85rem;font-family:'DM Sans',sans-serif;color:var(--ink-muted);">days</span></div>
                  </div>
                  <div style="text-align:right;">
                    <div style="font-size:.75rem;color:var(--ink-muted);">Goal</div>
                    <div style="font-size:.9rem;font-weight:600;"><?= $habit['target'] ?> days</div>
                  </div>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar <?= $pct>=100?'green':'' ?>" style="width:<?= min($pct,100) ?>%"></div>
                </div>
                <div style="font-size:.75rem;color:var(--ink-muted);margin-top:5px;text-align:right;"><?= $pct ?>% of goal</div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:22px;">
          <div class="card">
            <div class="card-header"><h3>Streak Comparison</h3></div>
            <div class="card-body">
              <canvas id="streakChart" style="max-height:240px;"></canvas>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><h3>Habits Overview</h3></div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Habit</th>
                    <th>Category</th>
                    <th>Streak</th>
                    <th>Goal</th>
                    <th>Progress</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($habits as $h):
                    $pct2 = $h['target'] > 0 ? round($h['streak']/$h['target']*100) : 0;
                    $cat_colors = ['Health'=>'badge-sage','Work'=>'badge-sky','Personal'=>'badge-amber'];
                    $bc = $cat_colors[$h['category']] ?? 'badge-muted';
                  ?>
                  <tr>
                    <td><span style="font-size:1.1rem;"><?= $h['icon'] ?></span> <?= htmlspecialchars($h['name']) ?></td>
                    <td><span class="badge <?= $bc ?>"><?= $h['category'] ?></span></td>
                    <td><strong style="color:var(--amber);"><?= $h['streak'] ?></strong> days</td>
                    <td><?= $h['target'] ?> days</td>
                    <td style="min-width:80px;">
                      <div style="font-size:.78rem;margin-bottom:3px;"><?= $pct2 ?>%</div>
                      <div class="progress-bar-wrap" style="height:6px;">
                        <div class="progress-bar <?= $pct2>=100?'green':'' ?>" style="width:<?= min($pct2,100) ?>%"></div>
                      </div>
                    </td>
                    <td>
                      <div style="display:flex;gap:5px;">
                        <button class="btn btn-secondary btn-sm"
                          onclick="openEditHabit(<?= htmlspecialchars(json_encode($h)) ?>)"
                          style="padding:3px 8px;" title="Edit">✏️</button>
                        <a href="habits.php?action=delete&id=<?= $h['id'] ?>"
                          class="btn btn-sm"
                          style="background:var(--rose);color:#fff;border-color:var(--rose);padding:3px 8px;"
                          onclick="return confirm('Delete?')" title="Delete">🗑️</a>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </main>

    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<div class="modal-overlay" id="addHabitModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Add New Habit</h3>
      <button class="modal-close" onclick="closeModal('addHabitModal')">✕</button>
    </div>
    <form method="POST" action="habits.php">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label>Habit Name *</label>
          <input type="text" name="name" placeholder="e.g. Walk 30 Minutes" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Category</label>
            <select name="category">
              <option value="Health">Health</option>
              <option value="Work">Work</option>
              <option value="Personal">Personal</option>
            </select>
          </div>
          <div class="form-group">
            <label>Monthly Goal (days)</label>
            <input type="number" name="target" value="30" min="1" max="365">
          </div>
        </div>
        <div class="form-group">
          <label>Icon (emoji)</label>
          <input type="text" name="icon" placeholder="e.g. 🏃" maxlength="4">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addHabitModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Habit</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editHabitModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Habit</h3>
      <button class="modal-close" onclick="closeModal('editHabitModal')">✕</button>
    </div>
    <form method="POST" action="habits.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_habit_id">
      <div class="modal-body">
        <div class="form-group">
          <label>Habit Name *</label>
          <input type="text" name="name" id="edit_habit_name" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Category</label>
            <select name="category" id="edit_habit_category">
              <option value="Health">Health</option>
              <option value="Work">Work</option>
              <option value="Personal">Personal</option>
            </select>
          </div>
          <div class="form-group">
            <label>Monthly Goal (days)</label>
            <input type="number" name="target" id="edit_habit_target" min="1" max="365">
          </div>
        </div>
        <div class="form-group">
          <label>Icon (emoji)</label>
          <input type="text" name="icon" id="edit_habit_icon" maxlength="4">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editHabitModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<style>
.habit-card:hover { box-shadow: 0 6px 24px var(--shadow-md); transform: translateY(-2px); }
</style>

<script>
<?php if (!empty($habits)): ?>
const streakCtx = document.getElementById('streakChart').getContext('2d');
new Chart(streakCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($h)=>$h['icon'].' '.substr($h['name'],0,14), $habits)) ?>,
    datasets: [
      {
        label: 'Current Streak',
        data: <?= json_encode(array_column($habits,'streak')) ?>,
        backgroundColor: '#D4822A',
        borderRadius: 6,
      },
      {
        label: 'Goal',
        data: <?= json_encode(array_column($habits,'target')) ?>,
        backgroundColor: 'rgba(212,130,42,.15)',
        borderRadius: 6,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { font: { family: 'DM Sans', size: 12 }, boxWidth: 12, padding: 14 } }
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 11 } } },
      y: { beginAtZero: true, grid: { color: '#E8E2D9' }, ticks: { font: { family: 'DM Sans' } } }
    }
  }
});
<?php endif; ?>

function openEditHabit(h) {
  document.getElementById('edit_habit_id').value     = h.id;
  document.getElementById('edit_habit_name').value   = h.name;
  document.getElementById('edit_habit_target').value = h.target;
  document.getElementById('edit_habit_icon').value   = h.icon;
  setSelectValue('edit_habit_category', h.category);
  openModal('editHabitModal');
}

function setSelectValue(id, val) {
  const sel = document.getElementById(id);
  for (let o of sel.options) { o.selected = (o.value === val); }
}
</script>

</body>
</html>
