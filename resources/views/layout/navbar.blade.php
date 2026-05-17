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
    <button id="theme-toggle-btn" onclick="toggleTheme()" title="Toggle Theme" style="background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer; transition: color 0.2s, transform 0.2s; padding: 0; display: flex; align-items: center; justify-content: center;">
      <i class="fa-regular fa-moon" id="theme-toggle-icon"></i>
    </button>
    <a href="/logout" title="Logout" style="color: var(--text-muted); font-size: 1.2rem; transition: color 0.2s; display: flex; align-items: center; justify-content: center;">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
    </a>
  </div>
</nav>
