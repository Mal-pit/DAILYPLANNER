function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  sidebar.classList.toggle('open');
  overlay.classList.toggle('show');
}

function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('show');
}

function openModal(id) {
  document.getElementById(id).classList.add('open');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

window.addEventListener('resize', () => {
  if (window.innerWidth > 768) closeSidebar();
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.task-check').forEach(chk => {
    chk.addEventListener('click', () => {
      chk.classList.toggle('done');
      const titleEl = chk.closest('.task-item')?.querySelector('.task-title');
      if (titleEl) titleEl.classList.toggle('done');
    });
  });
});

setTimeout(() => {
  document.querySelectorAll('.form-error, .form-success').forEach(el => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 600);
  });
}, 4000);
