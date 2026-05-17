@extends('layout.layout')
@section('title', $title ?? 'Dashboard')
@section($activeNav ?? 'Dashboard_nav', 'active')

@section('content')
  <div style="margin: 24px 36px 0;">
      <h1 style="font-family: var(--font-accent); font-size: 1.8rem; margin: 0; color: var(--text-main);">
          {{ $title ?? 'Dashboard' }}
      </h1>
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

  <div class="tasks-list">
    @foreach ($tasks as $task)
      @php
        $totalSubtasks = count($task['subtasks']);
        $completedSubtasks = collect($task['subtasks'])->where('is_completed', true)->count();
        $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
      @endphp
      <div class="task {{ $task['status'] == 'completed' ? 'completed' : '' }}" id="task-{{ $task['id'] }}" data-priority="{{ strtolower($task['priority']) }}">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div class="task-catigory">
                <span>{{ $task['category_name'] }}</span>
            </div>
            <span class="status-badge {{ $task['status'] }}">{{ $task['status'] }}</span>
        </div>
        <div class="task-title">
          <a href="/task/{{ $task['id'] }}"> {{ $task['title'] }}</a>
          @if($task['status'] != 'completed' && \Carbon\Carbon::parse($task['due_date'])->isPast())
            <span class="overdue-badge">Overdue</span>
          @endif
        </div>

        @if($totalSubtasks > 0)
        <div class="progress-container" title="{{ $completedSubtasks }}/{{ $totalSubtasks }} subtasks">
            <div class="progress-bar" style="width: {{ $progress }}%"></div>
        </div>
        @endif

        <div class="task-bar">
          <div class="task-date">
            <i class="fa-regular fa-calendar"></i> {{ $task['formatted_date'] }}
          </div>
          <div class="task-actions">
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
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="pagination-container" style="margin: 20px 36px;">
      {{ $tasks->links() }}
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
        : 0;

      return `
          <div class="task ${isCompleted ? 'completed' : ''}" id="task-${task.id}" data-priority="${task.priority.toLowerCase()}">
              <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                  <div class="task-catigory"><span>${task.category_name}</span></div>
                  <span class="status-badge ${task.status}">${task.status}</span>
              </div>
              <div class="task-title">
                  <a href="/task/${task.id}">${task.title}</a>
                  ${isOverdue ? '<span class="overdue-badge">Overdue</span>' : ''}
              </div>
              ${task.subtasks.length > 0 ? `
              <div class="progress-container">
                  <div class="progress-bar" style="width: ${progress}%"></div>
              </div>` : ''}
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

          if (activePage === 'important_nav' && task.priority.toLowerCase() !== 'high') {
              taskEl.remove();
          } else {
              taskEl.outerHTML = buildTaskHtml(task);
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

          if (activePage === 'completed_nav' && task.status !== 'completed') {
              taskEl.remove();
          } else {
              taskEl.outerHTML = buildTaskHtml(task);
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
  </script>
@endsection
