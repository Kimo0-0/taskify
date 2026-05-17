<nav>
    <div style="display: flex; align-items: center; gap: 12px;">
      <button class="btn btn-aqua lt-768" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
      <button class="btn btn-aqua gt-768" onclick="toggleAddForm()" id="create-task-btn">
        <i class="fa-solid fa-plus"></i> Create New Task
      </button>
      <a href="{{ route('dashboard') }}" id="Back-to-Dashboard" class="btn btn-aqua" style="display: none;">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

  <div class="right-nav" style="display: flex; align-items: center; gap: 20px;">
    <a href="/logout" title="Logout" style="color: var(--text-muted); font-size: 1.2rem; transition: color 0.2s;">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
    </a>
  </div>
</nav>
