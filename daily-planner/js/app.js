// ============================================================
// DayFlow – Shared JS (app.js)
// Used by dashboard.html, tasks.html, habits.html, schedule.html
// ============================================================

// ── Auth guard ──────────────────────────────────────────────
function requireAuth() {
  const u = localStorage.getItem('dayflow_user');
  if (!u) { window.location.href = 'index.html'; return null; }
  return JSON.parse(u);
}

function logout() {
  localStorage.removeItem('dayflow_user');
  window.location.href = 'index.html';
}

// ── Modal helpers ────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
});

// ── Select helper ────────────────────────────────────────────
function setSelectValue(id, val) {
  const sel = document.getElementById(id);
  if (!sel) return;
  for (let o of sel.options) o.selected = (o.value === val);
}

// ── Default seed data ────────────────────────────────────────
const DEFAULT_TASKS = [
  { id:1, title:'Prepare project report', category:'Work',     priority:'High',   due:'2026-06-10', status:'In Progress', progress:60, notes:'Draft due Friday' },
  { id:2, title:'Morning run 5km',        category:'Health',   priority:'Medium', due:'2026-06-05', status:'Pending',     progress:0,  notes:'' },
  { id:3, title:'Read 30 pages',          category:'Personal', priority:'Low',    due:'2026-06-06', status:'Pending',     progress:0,  notes:'Currently reading Atomic Habits' },
  { id:4, title:'Team standup meeting',   category:'Work',     priority:'High',   due:'2026-06-04', status:'Done',        progress:100,notes:'' },
  { id:5, title:'Grocery shopping',       category:'Personal', priority:'Medium', due:'2026-06-07', status:'Pending',     progress:0,  notes:'Milk, eggs, bread' },
  { id:6, title:'Gym workout',            category:'Health',   priority:'Medium', due:'2026-06-05', status:'In Progress', progress:30, notes:'' },
  { id:7, title:'Review code PR #42',     category:'Work',     priority:'High',   due:'2026-06-05', status:'Pending',     progress:0,  notes:'' },
  { id:8, title:'Call dentist',           category:'Health',   priority:'Low',    due:'2026-06-08', status:'Done',        progress:100,notes:'' },
];

const DEFAULT_HABITS = [
  { id:1, name:'Morning Run',    icon:'🏃', category:'Health',   streak:12, target:30 },
  { id:2, name:'Read Daily',     icon:'📚', category:'Personal', streak:7,  target:21 },
  { id:3, name:'Drink 2L Water', icon:'💧', category:'Health',   streak:20, target:30 },
  { id:4, name:'Deep Work',      icon:'💼', category:'Work',     streak:5,  target:20 },
  { id:5, name:'Meditate',       icon:'🧘', category:'Personal', streak:3,  target:14 },
];

const DEFAULT_SCHEDULE = [
  { id:1, title:'Morning Workout',   time:'07:00', duration:'45 min',  location:'Gym',         type:'green' },
  { id:2, title:'Team Standup',      time:'09:00', duration:'30 min',  location:'Zoom',         type:'amber' },
  { id:3, title:'Deep Work Block',   time:'10:00', duration:'2 hours', location:'Office / Home',type:'amber' },
  { id:4, title:'Lunch Break',       time:'12:30', duration:'1 hour',  location:'Cafeteria',    type:'blue'  },
  { id:5, title:'Project Review',    time:'14:00', duration:'1 hour',  location:'Meeting Room', type:'amber' },
  { id:6, title:'Evening Walk',      time:'18:00', duration:'30 min',  location:'Park',         type:'green' },
  { id:7, title:'Reading / Journal', time:'21:00', duration:'30 min',  location:'Home',         type:'blue'  },
];

// ── Storage helpers ──────────────────────────────────────────
function getTasks()    { return JSON.parse(localStorage.getItem('dayflow_tasks')    || 'null') || DEFAULT_TASKS.map(t=>({...t})); }
function getHabits()   { return JSON.parse(localStorage.getItem('dayflow_habits')   || 'null') || DEFAULT_HABITS.map(h=>({...h})); }
function getSchedule() { return JSON.parse(localStorage.getItem('dayflow_schedule') || 'null') || DEFAULT_SCHEDULE.map(s=>({...s})); }

function saveTasks(arr)    { localStorage.setItem('dayflow_tasks',    JSON.stringify(arr)); }
function saveHabits(arr)   { localStorage.setItem('dayflow_habits',   JSON.stringify(arr)); }
function saveSchedule(arr) { localStorage.setItem('dayflow_schedule', JSON.stringify(arr)); }

function nextId(arr) { return arr.length ? Math.max(...arr.map(x=>x.id)) + 1 : 1; }

// ── Render sidebar ───────────────────────────────────────────
function renderSidebar(activePage) {
  const user = requireAuth();
  if (!user) return;
  const initial = user.name ? user.name[0].toUpperCase() : 'U';
  const pages = [
    { href:'dashboard.html', icon:'📊', label:'Dashboard' },
    { href:'tasks.html',     icon:'✅', label:'My Tasks'   },
    { href:'schedule.html',  icon:'📆', label:'Schedule'   },
    { href:'habits.html',    icon:'🔥', label:'Habits'     },
  ];
  const navLinks = pages.map(p =>
    `<a href="${p.href}" class="${p.href===activePage?'active':''}">
      <span class="nav-icon">${p.icon}</span>${p.label}
     </a>`
  ).join('');

  return `
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <div class="logo-icon">📅</div>
        <span>DayFlow</span>
      </div>
    </div>
    <div class="sidebar-user">
      <div class="sidebar-user-row">
        <div class="user-avatar">${initial}</div>
        <div class="user-info">
          <div class="user-name">${user.name}</div>
          <div class="user-role">${user.role}</div>
        </div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main Menu</div>
      ${navLinks}
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
        🚪 Sign Out
      </a>
    </div>
  </aside>`;
}

// ── Render topbar ─────────────────────────────────────────────
function renderTopbar(pageTitle) {
  const now  = new Date();
  const opts = { weekday:'long', day:'numeric', month:'long', year:'numeric' };
  const dateStr = now.toLocaleDateString('en-GB', opts);
  return `
  <div class="topbar">
    <div class="topbar-left">
      <h2>${pageTitle}</h2>
      <div class="topbar-date">${dateStr}</div>
    </div>
    <div class="topbar-right">
      <button class="topbar-btn" title="Notifications">🔔</button>
      <button class="topbar-btn" title="Settings">⚙️</button>
    </div>
  </div>`;
}

// ── Render footer ─────────────────────────────────────────────
function renderFooter() {
  return `<footer>
    <span>© 2026 DayFlow — Your Daily Planner</span>
    <span>Built with ❤️</span>
  </footer>`;
}
