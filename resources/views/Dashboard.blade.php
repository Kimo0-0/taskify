@extends('layout.layout')
@section('title', $title ?? 'Dashboard')
@section($activeNav ?? 'Dashboard_nav', 'active')

@if(isset($activeNav) && $activeNav == 'Trash_nav')
  @section('body-class', 'page-trash')
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
          <input type="text" id="search-tasks" placeholder="Search tasks by title or description..." oninput="if(event.isTrusted) filterTasks()" autocomplete="off" data-form-type="other" data-lpignore="true" style="width: 100%; padding: 12px 16px 12px 44px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; box-sizing: border-box; transition: all 0.3s ease;">
      </div>

      <div style="display: flex; flex-wrap: wrap; gap: 12px; width: auto;">
          <select id="filter-category" onchange="if(event.isTrusted) filterTasks()" autocomplete="off" data-form-type="other" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
              <option value="">All Categories</option>
              @foreach ($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
          </select>

          <select id="filter-priority" onchange="if(event.isTrusted) filterTasks()" autocomplete="off" data-form-type="other" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
          </select>

          <select id="filter-status" onchange="if(event.isTrusted) filterTasks()" autocomplete="off" data-form-type="other" style="padding: 12px 16px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; font-weight: 500; cursor: pointer; outline: none; transition: all 0.3s ease;">
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
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" class="task-select-checkbox" data-id="{{ $task['id'] }}" onchange="toggleSelectTask(this)" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--accent-color); border-radius: 4px; border: 1px solid var(--border-color); background: var(--input-bg); transition: all 0.3s ease;">
                <div class="task-catigory" style="margin: 0;">
                    <span>{{ $task['category_name'] }}</span>
                </div>
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
              <button class="task-action share" onclick="openShareModal({{ $task['id'] }}, {{ $task['share_token'] ? 'true' : 'false' }}, '{{ $task['share_url'] ?? '' }}', {{ $task['share_can_edit'] ? 'true' : 'false' }}, {{ $task['share_can_complete'] ? 'true' : 'false' }})" title="Share Task">
                  <i class="fa-solid fa-share-nodes" style="{{ $task['share_token'] ? 'color: var(--accent-color);' : '' }}"></i>
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

  {{-- Floating Bulk Action Bar --}}
  <div id="bulk-action-bar" style="position: fixed; bottom: -100px; left: 50%; transform: translateX(-50%); background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); padding: 14px 28px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35); display: flex; align-items: center; gap: 20px; z-index: 10000; transition: bottom 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); color: #fff;">
      <div style="display: flex; align-items: center; gap: 8px;">
          <input type="checkbox" id="select-all-tasks" onchange="toggleSelectAll(this)" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent-color);">
          <label for="select-all-tasks" style="font-weight: 600; font-size: 0.95rem; cursor: pointer; font-family: var(--font-main);">Select All</label>
      </div>
      <div style="width: 1px; height: 24px; background: rgba(255, 255, 255, 0.2);"></div>
      <span id="bulk-select-count" style="font-family: var(--font-accent); font-weight: 600; font-size: 0.95rem;">0 Selected</span>
      <div style="display: flex; gap: 12px;">
          @if(isset($activeNav) && $activeNav == 'Trash_nav')
              <button onclick="bulkRestore()" style="padding: 10px 16px; font-size: 0.9rem; background: var(--completed-color); color: #fff; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                  <i class="fa-solid fa-arrow-rotate-left"></i> Restore Selected
              </button>
              <button onclick="bulkForceDelete()" style="padding: 10px 16px; font-size: 0.9rem; background: var(--overdue-color); color: #fff; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                  <i class="fa-solid fa-trash"></i> Delete Permanently
              </button>
          @else
              <button onclick="bulkDelete()" style="padding: 10px 16px; font-size: 0.9rem; background: var(--overdue-color); color: #fff; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                  <i class="fa-regular fa-trash-can"></i> Delete Selected
              </button>
          @endif
      </div>
  </div>

  {{-- Global Share Modal --}}
  <div id="share-modal-overlay" onclick="closeShareModal()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); z-index: 15000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
      <div onclick="event.stopPropagation()" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 32px; width: 90%; max-width: 460px; box-shadow: 0 25px 60px rgba(0,0,0,0.25); transform: scale(0.95); transition: transform 0.3s ease;" id="share-modal-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
              <h3 style="margin: 0; font-family: var(--font-accent); font-size: 1.3rem; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                  <i class="fa-solid fa-share-nodes" style="color: var(--accent-color);"></i> Share Task
              </h3>
              <button onclick="closeShareModal()" style="background: var(--close-btn-bg); border: 1px solid var(--border-color); color: var(--text-main); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="Close">
                  <i class="fa-solid fa-xmark"></i>
              </button>
          </div>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 20px 0; line-height: 1.5;">Allow anyone with the link to view this task and its attachments without logging in.</p>
          
          <div id="share-modal-link-section" style="display: none; margin-bottom: 20px; background: var(--subtask-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; display: none; flex-direction: column; gap: 12px;">
              <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Public Link</div>
              <div style="display: flex; align-items: center; gap: 8px;">
                  <input type="text" id="share-modal-url" value="" readonly style="flex: 1; background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 10px 14px; border-radius: 10px; font-size: 0.85rem; font-family: var(--font-main); outline: none;" onclick="this.select()">
                  <button onclick="copyShareModalUrl()" class="btn-aqua" style="padding: 10px 16px; border-radius: 10px; font-size: 0.85rem; border: none; font-weight: 600; white-space: nowrap; display: flex; align-items: center; gap: 6px; cursor: pointer;" id="share-modal-copy-btn">
                      <i class="fa-regular fa-copy"></i> Copy
                  </button>
              </div>
              <div style="border-top: 1px solid var(--border-color); padding-top: 10px; display: flex; flex-direction: column; gap: 8px;">
                  <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Link Permissions:</div>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                      <input type="checkbox" id="share-modal-can-complete" onchange="updateShareModalPermission('share_can_complete', this)" style="width: 14px; height: 14px; accent-color: var(--accent-color);">
                      Allow Completion (Complete Subtasks & Task)
                  </label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                      <input type="checkbox" id="share-modal-can-edit" onchange="updateShareModalPermission('share_can_edit', this)" style="width: 14px; height: 14px; accent-color: var(--accent-color);">
                      Allow Editing (Edit Title, Description, etc.)
                  </label>
              </div>
          </div>

          <input type="hidden" id="share-modal-task-id" value="">
          <button id="share-modal-toggle-btn" onclick="toggleShareFromModal()" class="btn-aqua" style="width: 100%; justify-content: center; padding: 14px; border-radius: 14px; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.25s ease; display: flex; align-items: center; gap: 8px;">
              <i class="fa-solid fa-link"></i> Enable Public Link
          </button>
      </div>
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
        <option value="" selected>Select Category (No Category)</option>
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

    // Cache the initial paginated tasks and pagination HTML to restore instantly on search reset
    // Cache the initial paginated tasks and pagination HTML to restore instantly on search reset
    let initialTasksHtml = document.querySelector('.tasks-list') ? document.querySelector('.tasks-list').innerHTML : '';
    let initialPaginationHtml = document.querySelector('.pagination-container') ? document.querySelector('.pagination-container').innerHTML : '';

    // Intercept standard pagination link clicks to make all transitions refresh-free!
    document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('.pagination-container a');
            if (pageLink) {
                e.preventDefault();
                const url = new URL(pageLink.href);
                const page = url.searchParams.get('page') || 1;
                window.history.pushState({page: page}, '', `?page=${page}`);
                refreshTaskList();
            }
        });
    });

    function buildTaskHtml(task) {
      const isCompleted = task.status === 'completed';
      const isHighPriority = task.priority.toLowerCase() === 'high';
      const isOverdue = !isCompleted && new Date(task.due_date) < new Date();
      const progress = task.subtasks.length > 0
        ? (task.subtasks.filter(s => s.is_completed).length / task.subtasks.length) * 100
        : (isCompleted ? 100 : 0);

      return `
          <div class="task ${isCompleted ? 'completed' : ''} ${isOverdue ? 'overdue' : ''}" id="task-${task.id}" data-priority="${task.priority.toLowerCase()}" data-category="${task.category_id ?? ''}" data-status="${task.status.toLowerCase()}" data-title="${task.title.toLowerCase()}" data-desc="${(task.description || '').toLowerCase()}">
              <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                  <div style="display: flex; align-items: center; gap: 10px;">
                      <input type="checkbox" class="task-select-checkbox" data-id="${task.id}" onchange="toggleSelectTask(this)" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--accent-color); border-radius: 4px; border: 1px solid var(--border-color); background: var(--input-bg); transition: all 0.3s ease;">
                      <div class="task-catigory" style="margin: 0;"><span>${task.category_name}</span></div>
                  </div>
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
                      <button class="task-action share" onclick="openShareModal(${task.id}, ${task.share_token ? 'true' : 'false'}, '${task.share_url || ''}', ${task.share_can_edit ? 'true' : 'false'}, ${task.share_can_complete ? 'true' : 'false'})" title="Share Task">
                          <i class="fa-solid fa-share-nodes" style="${task.share_token ? 'color: var(--accent-color);' : ''}"></i>
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
          // Dynamic slide up refresh!
          refreshTaskList();

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

      axios.post(`/tasks/${id}/restore`, {
          _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
          refreshTaskList();
          updateCounter(1);
        })
        .catch((error) => {
          alert("Error restoring task: " + (error.response.data.message || 'Unknown error'));
        });
    }

    function forceDeleteTask(id) {
      if(!confirm('WARNING: Are you sure you want to PERMANENTLY delete this task? This action cannot be undone!')) return;

      axios.delete(`/tasks/${id}/force-delete`, {
          headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(() => {
          refreshTaskList();
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

    let searchDebounceTimeout = null;

    function filterTasks(page = 1) {
      const query = document.getElementById('search-tasks').value.toLowerCase().trim();
      const category = document.getElementById('filter-category').value;
      const priority = document.getElementById('filter-priority').value;
      const status = document.getElementById('filter-status').value;

      // If all filters are empty, instantly restore the initial paginated tasks & pagination HTML
      if (!query && !category && !priority && !status) {
          const container = document.querySelector('.tasks-list');
          if (container && initialTasksHtml) {
              container.innerHTML = initialTasksHtml;
          }
          const pagContainer = document.querySelector('.pagination-container');
          if (pagContainer && initialPaginationHtml) {
              pagContainer.innerHTML = initialPaginationHtml;
              pagContainer.style.display = 'flex';
          }
          // Restart countdown timers for the active tasks
          if (typeof startCountdownTimers === 'function') {
              startCountdownTimers();
          }
          return;
      }

      // Perform paginated AJAX search & multi-filtering across the entire database!
      clearTimeout(searchDebounceTimeout);
      searchDebounceTimeout = setTimeout(() => {
          axios.get('/tasks/api-search', {
              params: {
                  search: query,
                  category_id: category,
                  priority: priority,
                  status: status,
                  active_nav: activePage,
                  page: page
              }
          })
          .then(response => {
              const result = response.data;
              const tasks = result.data;
              const container = document.querySelector('.tasks-list');
              container.innerHTML = '';

              if (tasks.length === 0) {
                  container.innerHTML = `
                      <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--text-muted); font-family: var(--font-main);">
                          <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 16px; color: var(--border-color);"></i>
                          <h3 style="margin: 0 0 8px 0; color: var(--text-main); font-size: 1.2rem;">No Tasks Found</h3>
                          <p style="margin: 0; font-size: 0.9rem;">We couldn't find any tasks matching your filters across the database.</p>
                      </div>`;
              } else {
                  tasks.forEach(task => {
                      container.insertAdjacentHTML('beforeend', buildTaskHtml(task));
                  });
              }

              // Dynamically render dynamic pagination links for current search result set
              const pagContainer = document.querySelector('.pagination-container');
              if (pagContainer) {
                  if (result.prev_page_url || result.next_page_url) {
                      pagContainer.style.display = 'flex';
                      pagContainer.innerHTML = `
                          <ul class="pagination">
                              <li class="page-item ${!result.prev_page_url ? 'disabled' : ''}">
                                  ${result.prev_page_url
                                    ? `<a class="page-link" onclick="filterTasks(${result.current_page - 1})">« Previous</a>`
                                    : `<span class="page-link">« Previous</span>`}
                              </li>
                              <li class="page-item ${!result.next_page_url ? 'disabled' : ''}">
                                  ${result.next_page_url
                                    ? `<a class="page-link" onclick="filterTasks(${result.current_page + 1})">Next »</a>`
                                    : `<span class="page-link">Next »</span>`}
                              </li>
                          </ul>`;
                  } else {
                      pagContainer.style.display = 'none';
                  }
              }

              // Restart countdown timers for new search cards
              if (typeof startCountdownTimers === 'function') {
                  startCountdownTimers();
              }
          })
          .catch(error => {
              console.error("Error searching tasks:", error);
          });
      }, 250); // 250ms Debounce
    }

    function resetFilters() {
      document.getElementById('search-tasks').value = '';
      document.getElementById('filter-category').value = '';
      document.getElementById('filter-priority').value = '';
      document.getElementById('filter-status').value = '';
      filterTasks();
    }

    // Refresh active task page dynamically without full-page reloads!
    function refreshTaskList() {
      const query = document.getElementById('search-tasks').value.toLowerCase().trim();
      const category = document.getElementById('filter-category').value;
      const priority = document.getElementById('filter-priority').value;
      const status = document.getElementById('filter-status').value;

      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page') || 1;

      axios.get('/tasks/api-search', {
          params: {
              search: query,
              category_id: category,
              priority: priority,
              status: status,
              active_nav: activePage,
              page: page
          }
      })
      .then(response => {
          const result = response.data;
          const tasks = result.data;
          const container = document.querySelector('.tasks-list');
          container.innerHTML = '';

          if (tasks.length === 0) {
              container.innerHTML = `
                  <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: var(--text-muted); font-family: var(--font-main);">
                      <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 16px; color: var(--border-color);"></i>
                      <h3 style="margin: 0 0 8px 0; color: var(--text-main); font-size: 1.2rem;">No Tasks Found</h3>
                      <p style="margin: 0; font-size: 0.9rem;">We couldn't find any tasks matching your filters.</p>
                  </div>`;
          } else {
              tasks.forEach(task => {
                  container.insertAdjacentHTML('beforeend', buildTaskHtml(task));
              });
          }

          // Cache current rendered tasks HTML to prevent search reset from displaying stale/deleted tasks!
          if (!query && !category && !priority && !status) {
              initialTasksHtml = container.innerHTML;
          }

          // Rebuild pagination dynamic buttons
          const pagContainer = document.querySelector('.pagination-container');
          if (pagContainer) {
              if (result.prev_page_url || result.next_page_url) {
                  pagContainer.style.display = 'flex';
                  const prevPage = result.prev_page_url ? new URL(result.prev_page_url).searchParams.get('page') : null;
                  const nextPage = result.next_page_url ? new URL(result.next_page_url).searchParams.get('page') : null;

                  pagContainer.innerHTML = `
                      <ul class="pagination">
                          <li class="page-item ${!prevPage ? 'disabled' : ''}">
                              ${prevPage
                                ? `<a class="page-link" href="#" onclick="changePage(event, ${prevPage})">« Previous</a>`
                                : `<span class="page-link">« Previous</span>`}
                          </li>
                          <li class="page-item ${!nextPage ? 'disabled' : ''}">
                              ${nextPage
                                ? `<a class="page-link" href="#" onclick="changePage(event, ${nextPage})">Next »</a>`
                                : `<span class="page-link">Next »</span>`}
                          </li>
                      </ul>`;

                  if (!query && !category && !priority && !status) {
                      initialPaginationHtml = pagContainer.innerHTML;
                  }
              } else {
                  pagContainer.style.display = 'none';
              }
          }

          if (typeof startCountdownTimers === 'function') {
              startCountdownTimers();
          }
      })
      .catch(error => {
          console.error("Error refreshing task list:", error);
      });
    }

    function changePage(event, page) {
        if (event) event.preventDefault();
        window.history.pushState({page: page}, '', `?page=${page}`);
        refreshTaskList();
    }

    // ================== Bulk Select & Action Helpers ==================
    function toggleSelectTask(checkbox) {
        const card = checkbox.closest('.task');
        if (checkbox.checked) {
            card.classList.add('selected-for-bulk');
        } else {
            card.classList.remove('selected-for-bulk');
        }
        updateBulkActionBar();
    }

    function toggleSelectAll(selectAllCheckbox) {
        const visibleCheckboxes = document.querySelectorAll('.tasks-list .task:not([style*="display: none"]) .task-select-checkbox');
        visibleCheckboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
            const card = cb.closest('.task');
            if (cb.checked) {
                card.classList.add('selected-for-bulk');
            } else {
                card.classList.remove('selected-for-bulk');
            }
        });
        updateBulkActionBar();
    }

    function updateBulkActionBar() {
        const checkedBoxes = document.querySelectorAll('.tasks-list .task .task-select-checkbox:checked');
        const count = checkedBoxes.length;

        const selectAllCheckbox = document.getElementById('select-all-tasks');
        const visibleCheckboxes = document.querySelectorAll('.tasks-list .task:not([style*="display: none"]) .task-select-checkbox');

        if (selectAllCheckbox && visibleCheckboxes.length > 0) {
            selectAllCheckbox.checked = (checkedBoxes.length === visibleCheckboxes.length);
        }

        const bar = document.getElementById('bulk-action-bar');
        const countSpan = document.getElementById('bulk-select-count');
        if (bar && countSpan) {
            countSpan.textContent = `${count} Selected`;
            if (count > 0) {
                bar.style.bottom = '30px'; // Slide up elegantly
            } else {
                bar.style.bottom = '-100px'; // Slide down hidden
            }
        }
    }

    function getSelectedIds() {
        const checked = document.querySelectorAll('.tasks-list .task .task-select-checkbox:checked');
        return Array.from(checked).map(cb => cb.dataset.id);
    }

    function bulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        if (!confirm(`Are you sure you want to move these ${ids.length} selected tasks to the trash?`)) return;

        axios.post('/tasks/bulk-delete', {
            ids: ids,
            _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
            refreshTaskList();
            updateCounter(-ids.length);
            resetBulkSelection();
        })
        .catch(error => {
            alert("Error deleting tasks: " + (error.response?.data?.message || 'Unknown error'));
        });
    }

    function bulkRestore() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        if (!confirm(`Are you sure you want to restore these ${ids.length} selected tasks?`)) return;

        axios.post('/tasks/bulk-restore', {
            ids: ids,
            _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
            refreshTaskList();
            updateCounter(ids.length);
            resetBulkSelection();
        })
        .catch(error => {
            alert("Error restoring tasks: " + (error.response?.data?.message || 'Unknown error'));
        });
    }

    function bulkForceDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        if (!confirm(`WARNING: Are you sure you want to PERMANENTLY delete these ${ids.length} selected tasks? This action CANNOT be undone!`)) return;

        axios.post('/tasks/bulk-force-delete', {
            ids: ids,
            _token: document.querySelector('input[name="_token"]').value
        })
        .then(() => {
            refreshTaskList();
            resetBulkSelection();
        })
        .catch(error => {
            alert("Error permanently deleting tasks: " + (error.response?.data?.message || 'Unknown error'));
        });
    }

    function resetBulkSelection() {
        document.querySelectorAll('.task-select-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.task').forEach(card => card.classList.remove('selected-for-bulk'));
        const selectAll = document.getElementById('select-all-tasks');
        if (selectAll) selectAll.checked = false;
        updateBulkActionBar();
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
        document.getElementById("update-category-id").value = task.category_id || "";
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
        category_id: document.getElementById("update-category-id").value || null,
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

    // ================== Share Modal Handlers ==================
    function openShareModal(taskId, isShared, shareUrl, shareCanEdit = false, shareCanComplete = false) {
        const overlay = document.getElementById('share-modal-overlay');
        const card = document.getElementById('share-modal-card');
        const linkSection = document.getElementById('share-modal-link-section');
        const urlInput = document.getElementById('share-modal-url');
        const toggleBtn = document.getElementById('share-modal-toggle-btn');
        const taskIdInput = document.getElementById('share-modal-task-id');
        const editCheckbox = document.getElementById('share-modal-can-edit');
        const completeCheckbox = document.getElementById('share-modal-can-complete');

        taskIdInput.value = taskId;

        if (isShared && shareUrl) {
            linkSection.style.display = 'flex';
            urlInput.value = shareUrl;
            toggleBtn.innerHTML = '<i class="fa-solid fa-link-slash"></i> Disable Public Link';
            toggleBtn.style.background = 'var(--overdue-color)';
            editCheckbox.checked = shareCanEdit;
            completeCheckbox.checked = shareCanComplete;
        } else {
            linkSection.style.display = 'none';
            urlInput.value = '';
            toggleBtn.innerHTML = '<i class="fa-solid fa-link"></i> Enable Public Link';
            toggleBtn.style.background = '';
            editCheckbox.checked = false;
            completeCheckbox.checked = false;
        }

        overlay.style.display = 'flex';
        setTimeout(() => {
            overlay.style.opacity = '1';
            card.style.transform = 'scale(1)';
        }, 10);
    }

    function closeShareModal() {
        const overlay = document.getElementById('share-modal-overlay');
        const card = document.getElementById('share-modal-card');
        overlay.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }

    function toggleShareFromModal() {
        const taskId = document.getElementById('share-modal-task-id').value;
        const toggleBtn = document.getElementById('share-modal-toggle-btn');
        const linkSection = document.getElementById('share-modal-link-section');
        const urlInput = document.getElementById('share-modal-url');
        const editCheckbox = document.getElementById('share-modal-can-edit');
        const completeCheckbox = document.getElementById('share-modal-can-complete');

        toggleBtn.disabled = true;
        toggleBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        axios.post(`/tasks/${taskId}/share`, {
            _token: document.querySelector('input[name="_token"]').value
        })
        .then(response => {
            const data = response.data;
            if (data.shared) {
                linkSection.style.display = 'flex';
                urlInput.value = data.share_url;
                toggleBtn.innerHTML = '<i class="fa-solid fa-link-slash"></i> Disable Public Link';
                toggleBtn.style.background = 'var(--overdue-color)';
                editCheckbox.checked = false;
                completeCheckbox.checked = false;
                // Update the share icon on the task card
                updateTaskShareIcon(taskId, true);
            } else {
                linkSection.style.display = 'none';
                urlInput.value = '';
                toggleBtn.innerHTML = '<i class="fa-solid fa-link"></i> Enable Public Link';
                toggleBtn.style.background = '';
                updateTaskShareIcon(taskId, false);
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error toggling task share settings');
        })
        .finally(() => {
            toggleBtn.disabled = false;
        });
    }

    function updateShareModalPermission(permissionType, checkbox) {
        const taskId = document.getElementById('share-modal-task-id').value;
        checkbox.disabled = true;

        const params = {
            _token: document.querySelector('input[name="_token"]').value
        };
        params[permissionType] = checkbox.checked ? 1 : 0;

        axios.post(`/tasks/${taskId}/share`, params)
        .then(response => {
            // Update the share button parameters on the page so if we reopen, they stick
            const taskCard = document.getElementById(`task-${taskId}`);
            if (taskCard) {
                const shareBtn = taskCard.querySelector('.task-action.share');
                if (shareBtn) {
                    const onclickAttr = shareBtn.getAttribute('onclick');
                    // Simple replacement of params or refresh task list to update onclick values
                }
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error updating share permissions');
            checkbox.checked = !checkbox.checked;
        })
        .finally(() => {
            checkbox.disabled = false;
        });
    }

    function updateTaskShareIcon(taskId, isShared) {
        const taskCard = document.getElementById(`task-${taskId}`);
        if (!taskCard) return;
        const shareBtn = taskCard.querySelector('.task-action.share i');
        if (shareBtn) {
            shareBtn.style.color = isShared ? 'var(--accent-color)' : '';
        }
    }

    function copyShareModalUrl() {
        const input = document.getElementById('share-modal-url');
        const btn = document.getElementById('share-modal-copy-btn');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value)
            .then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                btn.style.background = 'var(--completed-color)';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.style.background = '';
                }, 2000);
            })
            .catch(err => {
                alert('Failed to copy: ' + err);
            });
    }

    // Close share modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('share-modal-overlay');
            if (overlay && overlay.style.display === 'flex') {
                closeShareModal();
            }
        }
    });
  </script>
@endsection
