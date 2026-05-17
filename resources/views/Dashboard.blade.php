@extends('layout.layout')
@section('title', $title ?? 'Dashboard')
@section($activeNav ?? 'Dashboard_nav', 'active')

@if(isset($activeNav) && $activeNav == 'Trash_nav')
  @section('CustomCss')
    #create-task-btn { display: none !important; }
  @endsection
@endif

@section('content')
  <div style="margin: 24px 36px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <h1 style="font-family: var(--font-accent); font-size: 1.8rem; margin: 0; color: var(--text-main);">
          {{ $title ?? 'Dashboard' }}
      </h1>
      @if(isset($activeNav) && $activeNav == 'Trash_nav' && count($tasks) > 0)
        <button onclick="emptyTrash()" class="btn-danger" style="display: flex; align-items: center; gap: 8px; background: var(--overdue-color); color: #fff; padding: 10px 20px; border: none; border-radius: 12px; font-weight: 600; font-family: var(--font-main); cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);">
            <i class="fa-solid fa-trash-can"></i> Empty Trash
        </button>
      @endif
  </div>

  <div class="task-status">
    <div class="total-tasks tasks-card">
      <div class="status-info">
        <span class="status-title">Total Tasks</span>
        <span class="status-number" id="total-tasks-count">{{ $stats['total'] }}</span>
      </div>
      <div class="status-icon">
        <i class="fa-solid fa-list-check"></i>
      </div>
    </div>
    <div class="completed-tasks tasks-card">
      <div class="status-info">
        <span class="status-title">Completed</span>
        <span class="status-number">{{ $stats['completed'] }}</span>
      </div>
      <div class="status-icon">
        <i class="fa-solid fa-check-double"></i>
      </div>
    </div>
    <div class="overdue-tasks tasks-card">
      <div class="status-info">
        <span class="status-title">Overdue</span>
        <span class="status-number">{{ $stats['overdue'] }}</span>
      </div>
      <div class="status-icon">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
    </div>
  </div>

  {{-- Real-Time Search & Multi-Filter Bar --}}
  <div class="filter-bar" style="margin: 24px 36px; padding: 20px; background: var(--card-bg); border-radius: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); display: flex; flex-wrap: wrap; gap: 16px; align-items: center; border: 1px solid var(--border-color); transition: all 0.3s ease;">
      <div style="flex: 1; min-width: 250px; position: relative;">
          <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.95rem;"></i>
          <input type="text" id="search-tasks" placeholder="Search tasks by title or description..." oninput="filterTasks()" style="width: 100%; padding: 12px 16px 12px 44px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s ease;">
      </div>
      
      <div style="display: flex; flex-wrap: wrap; gap: 12px; width: auto;">
          <select id="filter-category" onchange="filterTasks()" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
              <option value="">All Categories</option>
              @foreach ($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
          </select>

          <select id="filter-priority" onchange="filterTasks()" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
          </select>

          <select id="filter-status" onchange="filterTasks()" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in progress">In Progress</option>
              <option value="completed">Completed</option>
          </select>
          
          <button onclick="resetFilters()" style="padding: 12px 16px; background: var(--border-color); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-filter-circle-xmark"></i> Reset
          </button>
      </div>
  </div>

  <div class="tasks-list">
    @foreach ($tasks as $task)
      @php
        $totalSubtasks = count($task['subtasks']);
        $completedSubtasks = collect($task['subtasks'])->where('is_completed', true)->count();
        $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : ($task['status'] == 'completed' ? 100 : 0);
      @endphp
      @php
        $isOverdueTask = ($task['status'] != 'completed' && \Carbon\Carbon::parse($task['due_date'])->isPast());
      @endphp
      <div class="task {{ $task['status'] == 'completed' ? 'completed' : '' }} {{ $isOverdueTask ? 'overdue' : '' }}" id="task-{{ $task['id'] }}" data-priority="{{ strtolower($task['priority']) }}" data-category="{{ $task['category_id'] ?? '' }}" data-status="{{ strtolower($task['status']) }}" data-title="{{ strtolower($task['title']) }}" data-desc="{{ strtolower($task['description'] ?? '') }}">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div class="task-catigory">
                <span>{{ $task['category_name'] }}</span>
            </div>
            <span class="status-badge {{ $task['status'] }}">{{ $task['status'] }}</span>
        </div>
        <div class="task-title">
          <a href="/task/{{ $task['id'] }}"> {{ $task['title'] }}</a>
          @if($isOverdueTask)
            <span class="overdue-badge"><i class="fa-solid fa-circle-exclamation"></i> Overdue</span>
          @endif
        </div>

        <div class="progress-container" title="{{ $totalSubtasks > 0 ? $completedSubtasks . '/' . $totalSubtasks . ' subtasks' : ($task['status'] == 'completed' ? 'Completed' : 'Pending') }}">
            <div class="progress-bar" style="width: {{ $progress }}%"></div>
        </div>

        <div class="task-countdown" data-due="{{ $task['due_date'] }}" data-status="{{ $task['status'] }}">
            @if($task['status'] == 'completed')
                <i class="fa-solid fa-circle-check"></i> <span class="countdown-text">Completed</span>
            @else
                <i class="fa-solid fa-hourglass-half"></i> <span class="countdown-text">Calculating...</span>
            @endif
        </div>

        <div class="task-bar">
          <div class="task-date">
            <i class="fa-regular fa-calendar"></i> {{ $task['formatted_date'] }}
          </div>
          <div class="task-actions">
            @if(isset($activeNav) && $activeNav == 'Trash_nav')
              <button class="task-action restore" onclick="restoreTask({{ $task['id'] }})" title="Restore Task" style="color: var(--completed-color); font-size: 1.15rem; transition: transform 0.2s;">
                  <i class="fa-solid fa-arrow-rotate-left"></i>
              </button>
              <button class="task-action delete-permanent" onclick="forceDeleteTask({{ $task['id'] }})" title="Delete Permanently" style="color: var(--overdue-color); font-size: 1.15rem; transition: transform 0.2s;">
                  <i class="fa-solid fa-trash"></i>
              </button>
            @else
              <button class="task-action complete" onclick="toggleComplete({{ $task['id'] }}, '{{ $task['status'] }}')" title="Mark as {{ $task['status'] == 'completed' ? 'pending' : 'completed' }}">
                  <i class="fa-solid {{ $task['status'] == 'completed' ? 'fa-circle-check' : 'fa-circle' }}" style="{{ $task['status'] == 'completed' ? 'color: var(--completed-color);' : '' }}"></i>
              </button>
              <button class="task-action important" onclick="toggleImportant({{ $task['id'] }}, '{{ $task['priority'] }}')" title="Mark as {{ $task['priority'] == 'high' ? 'Normal' : 'Important' }}">
                  <i class="{{ $task['priority'] == 'high' ? 'fa-solid' : 'fa-regular' }} fa-star" style="{{ $task['priority'] == 'high' ? 'color: #f59e0b;' : '' }}"></i>
              </button>
              <button class="task-action edit" onclick='openUpdateForm({{ json_encode($task) }})'>
                  <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="task-action delete" onclick="deleteTask({{ $task['id'] }})">
                  <i class="fa-regular fa-trash-can"></i>
              </button>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="pagination-container">
      {{ $tasks->links('pagination::simple-bootstrap-4') }}
  </div>

  {{-- Update Task Form --}}

  <div class="update_Task_Form">
    <form onsubmit="updateTask(); return false;" id="updateTaskForm" class="update_Task">
      @csrf
      <h2 class="form-title">Update Task</h2>
      <button type="button" onclick="toggleUpdateForm()" class="close-btn"><i class="fa-solid fa-xmark"></i></button>

      <input type="hidden" id="update-task-id">

      <label>Task Title</label>
      <input type="text" id="update-task-title" name="title">

      <label>Description</label>
      <textarea id="update-task-description" name="description" rows="3"></textarea>

      <div class="subtasks-section">
        <label>Subtasks</label>
        <div class="add-subtask" style="display: flex; gap: 8px; margin-top: 8px;">
          <input type="text" id="update-subtask-input" style="flex-grow: 1;">
          <button type="button" id="update-add-subtask-btn" class="btn-aqua"><i class="fa-solid fa-plus"></i></button>
        </div>
        <div id="update-subtask-list" style="margin-top: 12px;"></div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
              <label>Due Date</label>
              <input type="datetime-local" name="due_date" id="update-task-due" style="width: 100%;">
          </div>
          <div>
              <label>Priority</label>
              <select name="priority" id="update-task-priority" style="width: 100%;">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
              </select>
          </div>
      </div>

      <label>Category</label>
      <select name="category_id" id="update-category-id">
        <option disabled selected>Select Category</option>
        @foreach ($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>

      <button type="button" onclick="updateTask()" class="btn-aqua" style="justify-content: center; margin-top: 16px;">Update Task</button>
    </form>
  </div>

  <script>
    // Global variable to know which page we are on
    const activePage = '{{ $activeNav ?? "dashboard_nav" }}';

    // ================== UI Helpers ==================
    function buildTaskHtml(task) {
      const isCompleted = task.status === 'completed';
      const isHighPriority = task.priority.toLowerCase() === 'high';
      const isOverdue = !isCompleted && new Date(task.due_date) < new Date();
      const progress = task.subtasks.length > 0
        ? (task.subtasks.filter(s => s.is_completed).length / task.subtasks.length) * 100
        : (isCompleted ? 100 : 0);

      return `
          <div class="task ${isCompleted ? 'completed' : ''} ${isOverdue ? 'overdue' : ''}" id="task-${task.id}" data-priority="${task.priority.toLowerCase()}">
              <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                  <div class="task-catigory"><span>${task.category_name}</span></div>
                  <span class="status-badge ${task.status}">${task.status}</span>
              </div>
              <div class="task-title">
                  <a href="/task/${task.id}">${task.title}</a>
                  ${isOverdue ? '<span class="overdue-badge"><i class="fa-solid fa-circle-exclamation"></i> Overdue</span>' : ''}
              </div>
              <div class="progress-container" title="${task.subtasks.length > 0 ? `${task.subtasks.filter(s => s.is_completed).length}/${task.subtasks.length} subtasks` : (isCompleted ? 'Completed' : 'Pending')}">
                  <div class="progress-bar" style="width: ${progress}%"></div>
              </div>
              <div class="task-countdown" data-due="${task.due_date}" data-status="${task.status}">
                  ${isCompleted 
                    ? '<i class="fa-solid fa-circle-check"></i> <span class="countdown-text">Completed</span>' 
                    : '<i class="fa-solid fa-hourglass-half"></i> <span class="countdown-text">Calculating...</span>'}
              </div>
              <div class="task-bar">
                  <div class="task-date"><i class="fa-regular fa-calendar"></i> ${task.formatted_date}</div>
                  <div class="task-actions">
                      <button class="task-action complete" onclick="toggleComplete(${task.id}, '${task.status}')">
                          <i class="fa-solid ${isCompleted ? 'fa-circle-check' : 'fa-circle'}" style="${isCompleted ? 'color: var(--completed-color);' : ''}"></i>
                      </button>
                      <button class="task-action important" onclick="toggleImportant(${task.id}, '${task.priority}')">
                          <i class="${isHighPriority ? 'fa-solid' : 'fa-regular'} fa-star" style="${isHighPriority ? 'color: #f59e0b;' : ''}"></i>
                      </button>
                      <button class="task-action edit" onclick='openUpdateForm(${JSON.stringify(task)})'>
                          <i class="fa-regular fa-pen-to-square"></i>
                      </button>
                      <button class="task-action delete" onclick="deleteTask(${task.id})">
                          <i class="fa-regular fa-trash-can"></i>
                      </button>
                  </div>
              </div>
          </div>`;
    }

    function toggleImportant(id, currentPriority) {
      const newPriority = currentPriority.toLowerCase() === 'high' ? 'medium' : 'high';

      axios.put(`/tasks/${id}`, {
          priority: newPriority,
          _token: document.querySelector('input[name="_token"]').value
        })
        .then((response) => {
          const task = response.data.data;
          const taskEl = document.getElementById(`task-${task.id}`);

          if (activePage.toLowerCase() === 'important_nav' && task.priority.toLowerCase() !== 'high') {
              taskEl.remove();
          } else {
              taskEl.outerHTML = buildTaskHtml(task);
              if (typeof updateCountdowns === "function") updateCountdowns();
          }
        })
        .catch((error) => {
          alert("Error updating priority");
        });
    }

    function toggleComplete(id, currentStatus) {
      const newStatus = currentStatus === 'completed' ? 'pending' : 'completed';

      axios.put(`/tasks/${id}`, {
          status: newStatus,
          _token: document.querySelector('input[name="_token"]').value
        })
        .then((response) => {
          const task = response.data.data;
          const taskEl = document.getElementById(`task-${task.id}`);

          if (activePage.toLowerCase() === 'completed_nav' && task.status !== 'completed') {
              taskEl.remove();
          } else {
              taskEl.outerHTML = buildTaskHtml(task);
              if (typeof updateCountdowns === "function") updateCountdowns();
          }

          // Update stats on the fly
          const completedCount = document.querySelector('.completed-tasks .status-number');
          const overdueCount = document.querySelector('.overdue-tasks .status-number');

          if(completedCount) {
              let current = parseInt(completedCount.innerText);
              completedCount.innerText = newStatus === 'completed' ? current + 1 : current - 1;
          }

          if(overdueCount) {
              const isPastDue = new Date(task.due_date) < new Date();
              if(isPastDue) {
                  let current = parseInt(overdueCount.innerText) || 0;
                  if (newStatus === 'completed') {
                      overdueCount.innerText = Math.max(0, current - 1);
                  } else {
                      overdueCount.innerText = current + 1;
                  }
              }
          }
        })
        .catch((error) => {
          alert("Error updating status");
        });
    }

    function updateCounter(amount) {
      const countElement = document.getElementById("total-tasks-count");
      if (countElement) {
        let current = parseInt(countElement.innerText) || 0;
        countElement.innerText = Math.max(0, current + amount);
      }
    }

    function toggleUpdateForm() {
        document.querySelector('.update_Task_Form').classList.toggle('active');
    }

    // ================== Tasks ==================

    function deleteTask(id) {
      if(!confirm('Are you sure you want to delete this task?')) return;

      const taskEl = document.getElementById(`task-${id}`);
      const isCompleted = taskEl.classList.contains('completed');
      const isOverdue = taskEl.querySelector('.overdue-badge') !== null;

      axios.delete(`/tasks/${id}`, {
          headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(() => {
          taskEl.remove();

          // Update Total
          updateCounter(-1);

          // Update Completed
          if (isCompleted) {
              const completedCount = document.querySelector('.completed-tasks .status-number');
              if (completedCount) {
                  let current = parseInt(completedCount.innerText) || 0;
                  completedCount.innerText = Math.max(0, current - 1);
              }
          }

          // Update Overdue
          if (isOverdue) {
              const overdueCount = document.querySelector('.overdue-tasks .status-number');
              if (overdueCount) {
                  let current = parseInt(overdueCount.innerText) || 0;
                  overdueCount.innerText = Math.max(0, current - 1);
              }
          }
        })
        .catch((error) => {
          alert("Error deleting task");
        });
    }

    function restoreTask(id) {
      if(!confirm('Are you sure you want to restore this task?')) return;

      const taskEl = document.getElementById(`task-${id}`);
      axios.post(`/tasks/${id}/restore`, {
          _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
          taskEl.remove();
          updateCounter(1);
        })
        .catch((error) => {
          alert("Error restoring task: " + (error.response.data.message || 'Unknown error'));
        });
    }

    function forceDeleteTask(id) {
      if(!confirm('WARNING: Are you sure you want to PERMANENTLY delete this task? This action cannot be undone!')) return;

      const taskEl = document.getElementById(`task-${id}`);
      axios.delete(`/tasks/${id}/force-delete`, {
          headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(() => {
          taskEl.remove();
        })
        .catch((error) => {
          alert("Error permanently deleting task: " + (error.response.data.message || 'Unknown error'));
        });
    }

    function emptyTrash() {
      if(!confirm('WARNING: Are you sure you want to PERMANENTLY delete ALL tasks in the recycle bin? This action CANNOT be undone!')) return;

      axios.post('/tasks/empty-trash', {
          _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
          document.querySelectorAll('.tasks-list .task').forEach(el => el.remove());
          updateCounter(0);
          window.location.reload();
        })
        .catch((error) => {
          alert("Error emptying recycle bin: " + (error.response?.data?.message || 'Unknown error'));
        });
    }

    function filterTasks() {
      const query = document.getElementById('search-tasks').value.toLowerCase().trim();
      const category = document.getElementById('filter-category').value;
      const priority = document.getElementById('filter-priority').value;
      const status = document.getElementById('filter-status').value;

      document.querySelectorAll('.tasks-list .task').forEach(card => {
          const title = card.dataset.title || '';
          const desc = card.dataset.desc || '';
          const cardCat = card.dataset.category || '';
          const cardPri = card.dataset.priority || '';
          const cardStat = card.dataset.status || '';

          const matchesSearch = !query || title.includes(query) || desc.includes(query);
          const matchesCategory = !category || cardCat === category;
          const matchesPriority = !priority || cardPri === priority;
          const matchesStatus = !status || cardStat === status;

          if (matchesSearch && matchesCategory && matchesPriority && matchesStatus) {
              card.style.display = 'flex';
              setTimeout(() => {
                  card.style.opacity = '1';
                  card.style.transform = 'scale(1)';
              }, 10);
          } else {
              card.style.opacity = '0';
              card.style.transform = 'scale(0.95)';
              card.style.display = 'none';
          }
      });
    }

    function resetFilters() {
      document.getElementById('search-tasks').value = '';
      document.getElementById('filter-category').value = '';
      document.getElementById('filter-priority').value = '';
      document.getElementById('filter-status').value = '';
      filterTasks();
    }

    function openUpdateForm(task) {
      document.getElementById("update-task-id").value = task.id;
      document.getElementById("update-task-title").value = task.title;
      document.getElementById("update-task-description").value = task.description;
      document.getElementById("update-task-due").value = task.due_date;
      document.getElementById("update-task-priority").value = task.priority.toLowerCase();

      const list = document.getElementById("update-subtask-list");
      list.innerHTML = '';
      if (task.subtasks) {
        task.subtasks.forEach(subtask => {
          list.insertAdjacentHTML("beforeend", `
                <div class="subtask-item" data-title="${subtask.title}">
                    <span>${subtask.title}</span>
                    <button type="button" class="task-action delete" onclick="this.parentElement.remove()">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            `);
        });
      }

      setTimeout(() => {
        document.getElementById("update-category-id").value = task.category_id;
      }, 0);
      toggleUpdateForm();
    }

    function updateTask() {
      const id = document.getElementById("update-task-id").value;
      const taskEl = document.getElementById(`task-${id}`);

      // Capture old state
      const wasCompleted = taskEl.classList.contains('completed');
      const wasOverdue = taskEl.querySelector('.overdue-badge') !== null;

      const taskData = {
        title: document.getElementById("update-task-title").value,
        description: document.getElementById("update-task-description").value,
        due_date: document.getElementById("update-task-due").value,
        priority: document.getElementById("update-task-priority").value,
        category_id: document.getElementById("update-category-id").value,
        subtasks: getSubtasks("update-subtask-list"),
        _token: document.querySelector('input[name="_token"]').value,
      };

      axios.put(`/tasks/${id}`, taskData)
        .then((response) => {
          const task = response.data.data;
          const isNowCompleted = task.status === 'completed';
          const isNowOverdue = !isNowCompleted && new Date(task.due_date) < new Date();

          // Update HTML
          taskEl.outerHTML = buildTaskHtml(task);
          if (typeof updateCountdowns === "function") updateCountdowns();

          // Update Completed Counter
          if (wasCompleted !== isNowCompleted) {
              const completedCount = document.querySelector('.completed-tasks .status-number');
              if (completedCount) {
                  let current = parseInt(completedCount.innerText) || 0;
                  completedCount.innerText = Math.max(0, isNowCompleted ? current + 1 : current - 1);
              }
          }

          // Update Overdue Counter
          if (wasOverdue !== isNowOverdue) {
              const overdueCount = document.querySelector('.overdue-tasks .status-number');
              if (overdueCount) {
                  let current = parseInt(overdueCount.innerText) || 0;
                  overdueCount.innerText = Math.max(0, isNowOverdue ? current + 1 : current - 1);
              }
          }

          toggleUpdateForm();
        })
        .catch((error) => {
          alert("Error updating task");
        });
    }

    // ================== Subtasks ==================
    document.getElementById("update-add-subtask-btn").addEventListener("click", function() {
      addSubtaskToList("update-subtask-input", "update-subtask-list");
    });

    // ================== Countdown Timers ==================
    // ================== Desktop & In-App Notifications ==================
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function playNotificationBeep() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
            oscillator.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.15); // E5
            
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.4);
        } catch (e) {
            console.log("Audio play blocked or not supported");
        }
    }

    function showInAppToast(message) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 24px;
                right: 24px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 12px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.style.cssText = `
            background: #ef4444;
            color: #ffffff;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.35);
            font-family: var(--font-main);
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            pointer-events: auto;
            opacity: 0;
            transform: translateY(-20px) scale(0.9);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        `;
        
        toast.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i> <span>${message}</span>`;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0) scale(1)';
        }, 50);
        
        playNotificationBeep();

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px) scale(0.9)';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    function showTaskExpiredNotification(title) {
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            new Notification("Taskify: Task Expired!", {
                body: `The deadline for your task "${title}" has ended!`,
            });
        }
        showInAppToast(`Task Expired: "${title}" deadline has ended!`);
    }

    // ================== Countdown Timers ==================
    function updateCountdowns() {
        document.querySelectorAll('.task-countdown').forEach(el => {
            const dueDateStr = el.dataset.due;
            const taskStatus = el.dataset.status;
            const textEl = el.querySelector('.countdown-text');
            const iconEl = el.querySelector('i');
            
            if (taskStatus === 'completed') {
                el.style.display = 'flex';
                el.classList.remove('urgent');
                textEl.innerText = 'Completed';
                if (iconEl) {
                    iconEl.className = 'fa-solid fa-circle-check';
                }
                return;
            }
            
            const dueDate = new Date(dueDateStr);
            const now = new Date();
            const diffMs = dueDate - now;
            
            if (diffMs <= 0) {
                // Task is overdue
                el.classList.remove('urgent');
                const taskCard = el.closest('.task');
                if (taskCard) {
                    if (!taskCard.classList.contains('overdue')) {
                        taskCard.classList.add('overdue');
                        const titleEl = taskCard.querySelector('.task-title');
                        if (titleEl && !titleEl.querySelector('.overdue-badge')) {
                            titleEl.insertAdjacentHTML('beforeend', '<span class="overdue-badge"><i class="fa-solid fa-circle-exclamation"></i> Overdue</span>');
                        }
                        
                        // Just expired! Trigger notification
                        const taskTitle = taskCard.querySelector('.task-title a').innerText;
                        showTaskExpiredNotification(taskTitle);
                    }
                }
                textEl.innerText = 'Overdue';
                if (iconEl) {
                    iconEl.className = 'fa-solid fa-circle-exclamation';
                }
                return;
            }
            
            // Calculate time parts
            const diffSecs = Math.floor(diffMs / 1000);
            const days = Math.floor(diffSecs / 86400);
            const hours = Math.floor((diffSecs % 86400) / 3600);
            const minutes = Math.floor((diffSecs % 3600) / 60);
            const seconds = diffSecs % 60;
            
            // Format output string
            let timeStr = '';
            if (days > 0) {
                timeStr += `${days}d ${hours}h ${minutes}m`;
            } else if (hours > 0) {
                timeStr += `${hours}h ${minutes}m ${seconds}s`;
            } else {
                timeStr += `${minutes}m ${seconds}s remaining`;
            }
            
            textEl.innerText = timeStr;
            
            // If less than 24 hours remaining, mark as urgent
            if (diffMs < 24 * 60 * 60 * 1000) {
                el.classList.add('urgent');
                if (iconEl) {
                    iconEl.className = 'fa-solid fa-hourglass-start fa-spin';
                }
            } else {
                el.classList.remove('urgent');
                if (iconEl) {
                    iconEl.className = 'fa-solid fa-hourglass-half';
                }
            }
        });
    }
    
    // Start interval
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
  </script>
@endsection
