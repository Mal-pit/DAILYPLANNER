<?php
require_once 'includes/config.php';
require_auth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        add_task($_POST);
        header('Location: tasks.php?msg=added'); exit();
    }
    if ($action === 'update') {
        update_task((int)$_POST['id'], $_POST);
        header('Location: tasks.php?msg=updated'); exit();
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    delete_task((int)$_GET['id']);
    header('Location: tasks.php?msg=deleted'); exit();
}

$tasks = get_tasks();

$filter_status   = $_GET['status']   ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$filter_category = $_GET['category'] ?? 'all';
$search          = trim($_GET['search'] ?? '');

$filtered = array_filter($tasks, function($t) use ($filter_status, $filter_priority, $filter_category, $search) {
    if ($filter_status   !== 'all' && $t['status']   !== $filter_status)   return false;
    if ($filter_priority !== 'all' && $t['priority'] !== $filter_priority) return false;
    if ($filter_category !== 'all' && $t['category'] !== $filter_category) return false;
    if ($search && stripos($t['title'], $search) === false)                 return false;
    return true;
});

$priorities = ['High'=>0,'Medium'=>0,'Low'=>0];
foreach ($tasks as $t) $priorities[$t['priority']]++;

$msg = $_GET['msg'] ?? '';

$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_task = find_task((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks – DayFlow</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">

  <?php include 'includes/navbar.php'; ?>

  <div class="main-content">
    <?php include 'includes/topbar.php'; ?>

    <main class="page-content">

      <?php if ($msg === 'added'):   ?><div class="form-success fade-in">✅ Task added successfully!</div><?php endif; ?>
      <?php if ($msg === 'updated'): ?><div class="form-success fade-in">✏️ Task updated successfully!</div><?php endif; ?>
      <?php if ($msg === 'deleted'): ?><div class="form-error fade-in">🗑️ Task deleted.</div><?php endif; ?>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;" class="fade-in">
        <div>
          <h2 style="font-size:1.6rem;">My Tasks</h2>
          <p style="color:var(--ink-muted);font-size:.88rem;"><?= count($filtered) ?> of <?= count($tasks) ?> tasks shown</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addTaskModal')">+ Add Task</button>
      </div>

      <form method="GET" action="tasks.php">
        <div class="card fade-in" style="margin-bottom:22px;">
          <div class="card-body" style="padding:16px 20px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
              <div class="form-group" style="flex:2;min-width:160px;margin-bottom:0;">
                <label>Search</label>
                <input type="text" name="search" placeholder="Search tasks…" value="<?= htmlspecialchars($search) ?>">
              </div>
              <div class="form-group" style="flex:1;min-width:120px;margin-bottom:0;">
                <label>Status</label>
                <select name="status">
                  <option value="all"         <?= $filter_status==='all'         ?'selected':'' ?>>All</option>
                  <option value="Pending"     <?= $filter_status==='Pending'     ?'selected':'' ?>>Pending</option>
                  <option value="In Progress" <?= $filter_status==='In Progress' ?'selected':'' ?>>In Progress</option>
                  <option value="Done"        <?= $filter_status==='Done'        ?'selected':'' ?>>Done</option>
                </select>
              </div>
              <div class="form-group" style="flex:1;min-width:120px;margin-bottom:0;">
                <label>Priority</label>
                <select name="priority">
                  <option value="all"    <?= $filter_priority==='all'    ?'selected':'' ?>>All</option>
                  <option value="High"   <?= $filter_priority==='High'   ?'selected':'' ?>>High</option>
                  <option value="Medium" <?= $filter_priority==='Medium' ?'selected':'' ?>>Medium</option>
                  <option value="Low"    <?= $filter_priority==='Low'    ?'selected':'' ?>>Low</option>
                </select>
              </div>
              <div class="form-group" style="flex:1;min-width:120px;margin-bottom:0;">
                <label>Category</label>
                <select name="category">
                  <option value="all"      <?= $filter_category==='all'      ?'selected':'' ?>>All</option>
                  <option value="Work"     <?= $filter_category==='Work'     ?'selected':'' ?>>Work</option>
                  <option value="Health"   <?= $filter_category==='Health'   ?'selected':'' ?>>Health</option>
                  <option value="Personal" <?= $filter_category==='Personal' ?'selected':'' ?>>Personal</option>
                </select>
              </div>
              <div style="display:flex;gap:8px;margin-bottom:0;align-self:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="tasks.php" class="btn btn-secondary btn-sm">Clear</a>
              </div>
            </div>
          </div>
        </div>
      </form>

      <div class="three-col fade-in-2">

        <div class="card">
          <div class="card-header">
            <h3>Task List</h3>
            <span class="badge badge-amber"><?= count($filtered) ?> items</span>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Task</th>
                  <th>Category</th>
                  <th>Priority</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($filtered)): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--ink-muted);padding:32px;">No tasks match your filters.</td></tr>
                <?php else: ?>
                <?php foreach (array_values($filtered) as $i => $task): ?>
                <tr>
                  <td style="color:var(--ink-muted);font-size:.82rem;"><?= $i + 1 ?></td>
                  <td>
                    <strong style="font-size:.9rem;"><?= htmlspecialchars($task['title']) ?></strong>
                    <?php if (!empty($task['notes'])): ?>
                      <div style="font-size:.78rem;color:var(--ink-muted);margin-top:2px;"><?= htmlspecialchars(substr($task['notes'],0,50)) ?><?= strlen($task['notes'])>50?'…':'' ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php
                    $cat_badge = ['Work'=>'badge-sky','Health'=>'badge-sage','Personal'=>'badge-amber'];
                    $bc = $cat_badge[$task['category']] ?? 'badge-muted';
                    ?>
                    <span class="badge <?= $bc ?>"><?= $task['category'] ?></span>
                  </td>
                  <td><span class="task-priority priority-<?= strtolower($task['priority']) ?>"><?= $task['priority'] ?></span></td>
                  <td style="font-size:.85rem;color:var(--ink-soft);"><?= $task['due'] ?></td>
                  <td>
                    <?php
                    $status_badge = ['Done'=>'badge-sage','In Progress'=>'badge-amber','Pending'=>'badge-muted'];
                    $sb = $status_badge[$task['status']] ?? 'badge-muted';
                    ?>
                    <span class="badge <?= $sb ?>"><?= $task['status'] ?></span>
                  </td>
                  <td style="min-width:80px;">
                    <div style="font-size:.78rem;color:var(--ink-muted);margin-bottom:3px;"><?= $task['progress'] ?>%</div>
                    <div class="progress-bar-wrap" style="height:6px;">
                      <div class="progress-bar <?= $task['progress']>=100?'green':'' ?>" style="width:<?= $task['progress'] ?>%"></div>
                    </div>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px;">
                      <button class="btn btn-secondary btn-sm"
                        onclick="openEditTask(<?= htmlspecialchars(json_encode($task)) ?>)"
                        title="Edit">✏️</button>
                      <a href="tasks.php?action=delete&id=<?= $task['id'] ?>"
                        class="btn btn-sm"
                        style="background:var(--rose);color:#fff;border-color:var(--rose);"
                        onclick="return confirm('Delete this task?')"
                        title="Delete">🗑️</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:22px;">
          <div class="card">
            <div class="card-header"><h3>By Priority</h3></div>
            <div class="card-body">
              <canvas id="priorityPie" style="max-height:200px;"></canvas>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><h3>Quick Stats</h3></div>
            <div class="card-body">
              <?php
              $done_c  = count(array_filter($tasks, fn($t)=>$t['status']==='Done'));
              $total_c = count($tasks);
              $pct     = $total_c ? round($done_c/$total_c*100) : 0;
              ?>
              <div style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:700;color:var(--amber);line-height:1;margin-bottom:4px;"><?= $pct ?>%</div>
              <div style="font-size:.82rem;color:var(--ink-muted);margin-bottom:20px;">Overall Completion</div>
              <div class="progress-bar-wrap"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
              <div style="margin-top:18px;display:flex;flex-direction:column;gap:8px;">
                <?php
                $statuses = ['Done'=>'sage','In Progress'=>'amber','Pending'=>'rose'];
                foreach ($statuses as $s => $color):
                  $cnt = count(array_filter($tasks, fn($t)=>$t['status']===$s));
                ?>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;">
                  <span style="display:flex;align-items:center;gap:7px;">
                    <span style="width:9px;height:9px;border-radius:50%;background:var(--<?= $color ?>);display:inline-block;"></span>
                    <?= $s ?>
                  </span>
                  <strong><?= $cnt ?></strong>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

      </div>

    </main>

    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<div class="modal-overlay" id="addTaskModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Add New Task</h3>
      <button class="modal-close" onclick="closeModal('addTaskModal')">✕</button>
    </div>
    <form method="POST" action="tasks.php">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group">
          <label>Task Title *</label>
          <input type="text" name="title" placeholder="What do you need to do?" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Category</label>
            <select name="category">
              <option value="Work">Work</option>
              <option value="Health">Health</option>
              <option value="Personal">Personal</option>
            </select>
          </div>
          <div class="form-group">
            <label>Priority</label>
            <select name="priority">
              <option value="High">High</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Due Date</label>
            <input type="date" name="due" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Done">Done</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Progress (%)</label>
          <input type="number" name="progress" value="0" min="0" max="100">
        </div>
        <div class="form-group">
          <label>Notes (optional)</label>
          <textarea name="notes" placeholder="Any additional details…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addTaskModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Task</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editTaskModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit Task</h3>
      <button class="modal-close" onclick="closeModal('editTaskModal')">✕</button>
    </div>
    <form method="POST" action="tasks.php">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_task_id">
      <div class="modal-body">
        <div class="form-group">
          <label>Task Title *</label>
          <input type="text" name="title" id="edit_task_title" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Category</label>
            <select name="category" id="edit_task_category">
              <option value="Work">Work</option>
              <option value="Health">Health</option>
              <option value="Personal">Personal</option>
            </select>
          </div>
          <div class="form-group">
            <label>Priority</label>
            <select name="priority" id="edit_task_priority">
              <option value="High">High</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label>Due Date</label>
            <input type="date" name="due" id="edit_task_due">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit_task_status">
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Done">Done</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Progress (%)</label>
          <input type="number" name="progress" id="edit_task_progress" min="0" max="100">
        </div>
        <div class="form-group">
          <label>Notes (optional)</label>
          <textarea name="notes" id="edit_task_notes"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('editTaskModal')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
// Priority Pie Chart
const ppCtx = document.getElementById('priorityPie').getContext('2d');
new Chart(ppCtx, {
  type: 'pie',
  data: {
    labels: ['High', 'Medium', 'Low'],
    datasets: [{
      data: [<?= $priorities['High'] ?>, <?= $priorities['Medium'] ?>, <?= $priorities['Low'] ?>],
      backgroundColor: ['#C96B6B', '#D4822A', '#6B8F6E'],
      borderWidth: 3, borderColor: '#FFFDF9', hoverOffset: 8,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { font: { family: 'DM Sans', size: 12 }, padding: 14, boxWidth: 12 } }
    }
  }
});

// Open edit modal and pre-fill fields
function openEditTask(task) {
  document.getElementById('edit_task_id').value       = task.id;
  document.getElementById('edit_task_title').value    = task.title;
  document.getElementById('edit_task_due').value      = task.due;
  document.getElementById('edit_task_progress').value = task.progress;
  document.getElementById('edit_task_notes').value    = task.notes || '';
  setSelectValue('edit_task_category', task.category);
  setSelectValue('edit_task_priority',  task.priority);
  setSelectValue('edit_task_status',    task.status);
  openModal('editTaskModal');
}

function setSelectValue(id, val) {
  const sel = document.getElementById(id);
  for (let o of sel.options) { o.selected = (o.value === val); }
}
</script>

</body>
</html>
