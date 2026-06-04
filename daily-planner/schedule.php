<?php
require_once 'includes/config.php';
require_auth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        add_event($_POST);
        header('Location: schedule.php?msg=added'); exit();
    }
    if ($action === 'update') {
        update_event((int)$_POST['id'], $_POST);
        header('Location: schedule.php?msg=updated'); exit();
    }
}
if ($action === 'delete' && isset($_GET['id'])) {
    delete_event((int)$_GET['id']);
    header('Location: schedule.php?msg=deleted'); exit();
}

$schedule = get_schedule();
$msg = $_GET['msg'] ?? '';

$types = array_count_values(array_column($schedule, 'type'));
$work_cnt   = $types['amber'] ?? 0;
$health_cnt = $types['green'] ?? 0;
$other_cnt  = $types['blue']  ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule – DayFlow</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">

  <?php include 'includes/navbar.php'; ?>

  <div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <main class="page-content">

      <?php if ($msg === 'added'):   ?><div class="form-success fade-in">✅ Event added!</div><?php endif; ?>
      <?php if ($msg === 'updated'): ?><div class="form-success fade-in">✏️ Event updated!</div><?php endif; ?>
      <?php if ($msg === 'deleted'): ?><div class="form-error fade-in">🗑️ Event deleted.</div><?php endif; ?>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;" class="fade-in">
        <div>
          <h2 style="font-size:1.6rem;">Today's Schedule</h2>
          <p style="color:var(--ink-muted);font-size:.88rem;"><?= date('l, j F Y') ?> &mdash; <?= count($schedule) ?> events planned</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addEventModal')">+ Add Event</button>
      </div>
\
      <div class="stats-grid fade-in" style="margin-bottom:24px;">
        <div class="stat-card stat-amber">
          <div class="stat-label">Total Events</div>
          <div class="stat-value"><?= count($schedule) ?></div>
          <div class="stat-sub">Scheduled today</div>
          <div class="stat-icon">📆</div>
        </div>
        <div class="stat-card stat-rose">
          <div class="stat-label">Work / Study</div>
          <div class="stat-value"><?= $work_cnt ?></div>
          <div class="stat-sub">Focus sessions</div>
          <div class="stat-icon">💼</div>
        </div>
        <div class="stat-card stat-sage">
          <div class="stat-label">Health</div>
          <div class="stat-value"><?= $health_cnt ?></div>
          <div class="stat-sub">Wellness activities</div>
          <div class="stat-icon">💪</div>
        </div>
        <div class="stat-card stat-sky">
          <div class="stat-label">Other</div>
          <div class="stat-value"><?= $other_cnt ?></div>
          <div class="stat-sub">Personal &amp; errands</div>
          <div class="stat-icon">🌀</div>
        </div>
      </div>

      <div class="two-col fade-in-2">

        <div class="card">
          <div class="card-header">
            <h3>Time Blocks</h3>
            <div style="display:flex;gap:6px;">
              <span class="badge badge-amber">Work</span>
              <span class="badge badge-sage">Health</span>
              <span class="badge badge-sky">Other</span>
            </div>
          </div>
          <div class="card-body" style="padding-top:12px;">
            <?php foreach ($schedule as $event): ?>
            <div class="time-slot">
              <div class="slot-time"><?= $event['time'] ?></div>
              <div class="slot-event <?= $event['type'] ?>" style="flex:1;display:flex;align-items:center;justify-content:space-between;">
                <div>
                  <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                  <div class="event-sub">⏱ <?= $event['duration'] ?> &nbsp;📍 <?= htmlspecialchars($event['location']) ?></div>
                </div>
                <div style="display:flex;gap:5px;margin-left:12px;flex-shrink:0;">
                  <button class="btn btn-secondary btn-sm"
                    onclick="openEditEvent(<?= htmlspecialchars(json_encode($event)) ?>)"
                    style="padding:3px 8px;font-size:.78rem;" title="Edit">✏️</button>
                  <a href="schedule.php?action=delete&id=<?= $event['id'] ?>"
                    class="btn btn-sm"
                    style="background:var(--rose);color:#fff;border-color:var(--rose);padding:3px 8px;font-size:.78rem;"
                    onclick="return confirm('Delete this event?')" title="Delete">🗑️</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($schedule)): ?>
              <p style="text-align:center;color:var(--ink-muted);padding:32px;">No events yet. Add one!</p>
            <?php endif; ?>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:22px;">

          <div class="card">
            <div class="card-header"><h3>Time Distribution</h3></div>
            <div class="card-body">
              <canvas id="timeChart" style="max-height:220px;"></canvas>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3>Event Table</h3>
              <span class="badge badge-muted"><?= count($schedule) ?> rows</span>
            </div>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Time</th>
                    <th>Event</th>
                    <th>Duration</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($schedule as $event):
                    $badge_map  = ['amber'=>'badge-amber','green'=>'badge-sage','blue'=>'badge-sky'];
                    $type_label = ['amber'=>'Work','green'=>'Health','blue'=>'Personal'];
                    $bc = $badge_map[$event['type']] ?? 'badge-muted';
                    $tl = $type_label[$event['type']] ?? 'Other';
                  ?>
                  <tr>
                    <td style="font-weight:600;font-size:.88rem;color:var(--amber);"><?= $event['time'] ?></td>
                    <td style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($event['title']) ?></td>
                    <td style="font-size:.85rem;color:var(--ink-soft);"><?= $event['duration'] ?></td>
                    <td style="font-size:.85rem;color:var(--ink-muted);">📍 <?= htmlspecialchars($event['location']) ?></td>
                    <td><span class="badge <?= $bc ?>"><?= $tl ?></span></td>
                    <td>
                      <div style="display:flex;gap:5px;">
                        <button class="btn btn-secondary btn-sm"
                          onclick="openEditEvent(<?= htmlspecialchars(json_encode($event)) ?>)"
                          style="padding:3px 8px;" title="Edit">✏️</button>
                        <a href="schedule.php?action=delete&id=<?= $event['id'] ?>"
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

<div class="modal-overlay" id="addEventModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Add New Event</h3>
      <button class="modal-close" onclick="closeModal('addEventModal')">✕</button>
    </div>
    <form method="POST" action="schedule.php">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label>Event Title *</label>
          <input type="text" name="title" placeholder="e.g. Team Meeting" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="time" value="09:00">
          </div>
          <div class="form-group">
            <label>Duration</label>
            <input type="text" name="duration" placeholder="e.g. 1 hour">
          </div>
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" placeholder="e.g. Zoom / Library">
        </div>
        <div class="form-group">
          <label>Event Type</label>
          <select name="type">
            <option value="amber">Work / Study</option>
            <option value="green">Health / Wellness</option>
            <option value="blue">Personal / Other</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addEventModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Event</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editEventModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Event</h3>
      <button class="modal-close" onclick="closeModal('editEventModal')">✕</button>
    </div>
    <form method="POST" action="schedule.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_event_id">
      <div class="modal-body">
        <div class="form-group">
          <label>Event Title *</label>
          <input type="text" name="title" id="edit_event_title" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="time" id="edit_event_time">
          </div>
          <div class="form-group">
            <label>Duration</label>
            <input type="text" name="duration" id="edit_event_duration">
          </div>
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" id="edit_event_location">
        </div>
        <div class="form-group">
          <label>Event Type</label>
          <select name="type" id="edit_event_type">
            <option value="amber">Work / Study</option>
            <option value="green">Health / Wellness</option>
            <option value="blue">Personal / Other</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editEventModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
const timeCtx = document.getElementById('timeChart').getContext('2d');
new Chart(timeCtx, {
  type: 'bar',
  data: {
    labels: ['Work / Study', 'Health', 'Personal'],
    datasets: [{
      label: 'Events',
      data: [<?= $work_cnt ?>, <?= $health_cnt ?>, <?= $other_cnt ?>],
      backgroundColor: ['#D4822A', '#6B8F6E', '#4A7FA5'],
      borderRadius: 8, borderSkipped: false,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, grid: { color: '#E8E2D9' }, ticks: { stepSize: 1, font: { family: 'DM Sans' } } },
      y: { grid: { display: false }, ticks: { font: { family: 'DM Sans', size: 12 } } }
    }
  }
});

function openEditEvent(ev) {
  document.getElementById('edit_event_id').value       = ev.id;
  document.getElementById('edit_event_title').value    = ev.title;
  document.getElementById('edit_event_time').value     = ev.time;
  document.getElementById('edit_event_duration').value = ev.duration;
  document.getElementById('edit_event_location').value = ev.location;
  setSelectValue('edit_event_type', ev.type);
  openModal('editEventModal');
}

function setSelectValue(id, val) {
  const sel = document.getElementById(id);
  for (let o of sel.options) { o.selected = (o.value === val); }
}
</script>

</body>
</html>
