@extends('layout.public')
@section('title', 'Shared Tasks of ' . $user->name)

@section('content')
  <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <div>
          <span style="font-size: 0.9rem; font-weight: 600; color: var(--accent-color); text-transform: uppercase; letter-spacing: 1px;">Shared Task List</span>
          <h1 style="font-family: var(--font-accent); font-size: 2.2rem; margin: 4px 0 0 0; color: var(--text-main);">
              <i class="fa-solid fa-square-check" style="color: var(--accent-color); margin-right: 8px;"></i> {{ $user->name }}'s Tasks
          </h1>
          <div style="margin-top: 8px; color: var(--text-muted); font-size: 0.9rem;">
              Shared publicly by <strong style="color: var(--text-main);">{{ $user->name }}</strong>
          </div>
      </div>
      
      <div style="display: flex; gap: 16px;">
          <div style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 12px 20px; border-radius: 16px; text-align: center; min-width: 100px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
              <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Active Tasks</div>
              <div id="active-tasks-count" style="font-size: 1.6rem; font-weight: 700; color: var(--accent-color); margin-top: 4px;">{{ $tasks->count() }}</div>
          </div>
          <div style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 12px 20px; border-radius: 16px; text-align: center; min-width: 100px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
              <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Completed</div>
              <div id="completed-tasks-count" style="font-size: 1.6rem; font-weight: 700; color: var(--completed-color); margin-top: 4px;">{{ $completedTasks->count() }}</div>
          </div>
      </div>
  </div>

  {{-- Tasks List --}}
  <div style="display: flex; flex-direction: column; gap: 24px;">
      
      {{-- Active Tasks Section --}}
      <div>
          <h2 style="font-family: var(--font-accent); font-size: 1.3rem; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
               <i class="fa-solid fa-list-check" style="color: var(--accent-color);"></i> Active Tasks
          </h2>
          
          <div id="active-tasks-list" style="display: flex; flex-direction: column; gap: 16px;">
              @forelse ($tasks as $task)
                  @php
                      $totalSubtasks = $task->subtasks->count();
                      $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                      $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
                      $isOverdue = \Carbon\Carbon::parse($task->due_date)->isPast();
                      $taskDetailUrl = route('shared.user.task', ['userToken' => $token, 'taskId' => $task->id]);
                  @endphp
                  
                  <div class="task {{ $isOverdue ? 'overdue' : '' }}" id="task-card-{{ $task->id }}" style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 20px; border-radius: 16px; position: relative; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 12px; cursor: pointer;" onclick="window.location.href='{{ $taskDetailUrl }}'">
                      
                      <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                          <div style="display: flex; align-items: center; gap: 8px;">
                              <span class="status-badge {{ $task->status }}">{{ $task->status }}</span>
                              <span class="task-catigory" style="margin: 0; font-size: 0.8rem; font-weight: 600;"><span>{{ $task->category_name }}</span></span>
                              <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); padding: 4px 8px; background: var(--subtask-bg); border-radius: 6px;">{{ $task->priority }} priority</span>
                          </div>
                          @if($isOverdue)
                              <span class="overdue-badge" style="background: rgba(239, 68, 68, 0.1); color: var(--overdue-color); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600;">
                                  <i class="fa-solid fa-circle-exclamation"></i> Overdue
                              </span>
                          @endif
                      </div>

                      <div style="font-family: var(--font-accent); font-size: 1.25rem; font-weight: 600; color: var(--text-main); margin: 4px 0;">
                          {{ $task->title }}
                      </div>

                      @if($task->description)
                          <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                              {{ $task->description }}
                          </p>
                      @endif

                      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px;">
                          <div style="font-size: 0.82rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                              <i class="fa-regular fa-calendar"></i> Due: {{ $task->formatted_date }}
                          </div>
                          
                          <div style="display: flex; align-items: center; gap: 12px; min-width: 150px;">
                              <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">{{ $completedSubtasks }}/{{ $totalSubtasks }} Subtasks</div>
                              <div style="height: 6px; background: var(--progress-bg); border-radius: 3px; overflow: hidden; flex-grow: 1;">
                                  <div style="width: {{ $progress }}%; height: 100%; background: var(--accent-color); border-radius: 3px;"></div>
                              </div>
                          </div>
                      </div>
                  </div>
              @empty
                  <div style="text-align: center; color: var(--text-muted); padding: 32px; background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color);">
                      No active tasks shared.
                  </div>
              @endforelse
          </div>
      </div>

      {{-- Completed Tasks Section --}}
      <div id="completed-tasks-section" style="{{ $completedTasks->count() === 0 ? 'display:none;' : '' }}">
          <div>
              <h2 style="font-family: var(--font-accent); font-size: 1.3rem; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                  <i class="fa-solid fa-circle-check" style="color: var(--completed-color);"></i> Completed Tasks
              </h2>
              
              <div id="completed-tasks-list" style="display: flex; flex-direction: column; gap: 16px;">
                  @foreach ($completedTasks as $task)
                      @php
                          $taskDetailUrl = route('shared.user.task', ['userToken' => $token, 'taskId' => $task->id]);
                      @endphp
                      <div class="task completed" id="task-card-{{ $task->id }}" style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 18px 20px; border-radius: 16px; position: relative; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; gap: 16px; cursor: pointer; opacity: 0.75;" onclick="window.location.href='{{ $taskDetailUrl }}'">
                          <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                              <div style="width: 20px; height: 20px; border-radius: 50%; background: var(--completed-color); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.75rem; flex-shrink: 0;">
                                  <i class="fa-solid fa-check"></i>
                              </div>
                              <div style="min-width: 0;">
                                  <div style="font-family: var(--font-accent); font-size: 1.1rem; font-weight: 600; color: var(--text-main); text-decoration: line-through; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                      {{ $task->title }}
                                  </div>
                                  <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                      Completed on: {{ \Carbon\Carbon::parse($task->updated_at)->format('d M Y') }}
                                  </div>
                              </div>
                          </div>
                          
                          <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.85rem;"></i>
                      </div>
                  @endforeach
              </div>
          </div>
      </div>
      
  </div>

  <style>
      .task:hover {
          transform: translateY(-3px);
          border-color: var(--accent-color) !important;
          box-shadow: 0 8px 25px rgba(59, 130, 246, 0.08) !important;
      }
      .task.completed:hover {
          border-color: var(--completed-color) !important;
          box-shadow: 0 8px 25px rgba(45, 166, 105, 0.08) !important;
      }
      @keyframes slideInTask {
          from { opacity: 0; transform: translateY(-12px); }
          to   { opacity: 1; transform: translateY(0); }
      }
      .task-new-anim { animation: slideInTask 0.4s ease forwards; }
  </style>
@endsection

@section('realtime-script')
<script>
(function() {
    const channelToken = '{{ $token }}';
    const pageToken    = channelToken;

    // Build a task card HTML (active task)
    function buildActiveCard(task) {
        const dueDate   = new Date(task.due_date);
        const now       = new Date();
        const isOverdue = dueDate < now && task.status !== 'completed';
        const detailUrl = '/shared/user/' + pageToken + '/task/' + task.id;
        const subtasks  = task.subtasks || [];
        const total     = subtasks.length;
        const done      = subtasks.filter(s => s.is_completed).length;
        const progress  = total > 0 ? Math.round((done / total) * 100) : 0;
        const formattedDate = dueDate.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        const categoryName  = task.category_name || 'no category';

        return `
        <div class="task ${isOverdue ? 'overdue' : ''} task-new-anim" id="task-card-${task.id}"
             style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 20px; border-radius: 16px;
                    position: relative; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.02);
                    display: flex; flex-direction: column; gap: 12px; cursor: pointer;"
             onclick="window.location.href='${detailUrl}'">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="status-badge ${task.status}">${task.status}</span>
                    <span class="task-catigory" style="margin: 0; font-size: 0.8rem; font-weight: 600;"><span>${categoryName}</span></span>
                    <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); padding: 4px 8px; background: var(--subtask-bg); border-radius: 6px;">${task.priority} priority</span>
                </div>
                ${isOverdue ? '<span class="overdue-badge" style="background: rgba(239,68,68,0.1); color: var(--overdue-color); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> Overdue</span>' : ''}
            </div>
            <div style="font-family: var(--font-accent); font-size: 1.25rem; font-weight: 600; color: var(--text-main); margin: 4px 0;">${task.title}</div>
            ${task.description ? `<p style="margin: 0; color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${task.description}</p>` : ''}
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 4px;">
                <div style="font-size: 0.82rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                    <i class="fa-regular fa-calendar"></i> Due: ${formattedDate}
                </div>
                <div style="display: flex; align-items: center; gap: 12px; min-width: 150px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">${done}/${total} Subtasks</div>
                    <div style="height: 6px; background: var(--progress-bg); border-radius: 3px; overflow: hidden; flex-grow: 1;">
                        <div style="width: ${progress}%; height: 100%; background: var(--accent-color); border-radius: 3px;"></div>
                    </div>
                </div>
            </div>
        </div>`;
    }

    // Build a completed task row HTML
    function buildCompletedCard(task) {
        const detailUrl = '/shared/user/' + pageToken + '/task/' + task.id;
        const updatedAt = new Date(task.updated_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
        return `
        <div class="task completed task-new-anim" id="task-card-${task.id}"
             style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 18px 20px; border-radius: 16px;
                    transition: all 0.3s ease; display: flex; align-items: center; justify-content: space-between;
                    gap: 16px; cursor: pointer; opacity: 0.75;"
             onclick="window.location.href='${detailUrl}'">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                <div style="width:20px;height:20px;border-radius:50%;background:var(--completed-color);display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.75rem;flex-shrink:0;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-family:var(--font-accent);font-size:1.1rem;font-weight:600;color:var(--text-main);text-decoration:line-through;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${task.title}</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">Completed on: ${updatedAt}</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color:var(--text-muted);font-size:0.85rem;"></i>
        </div>`;
    }

    function updateCounters() {
        const activeEl    = document.getElementById('active-tasks-count');
        const completedEl = document.getElementById('completed-tasks-count');
        const activeList  = document.getElementById('active-tasks-list');
        const completedSection = document.getElementById('completed-tasks-section');
        if (activeEl && activeList) {
            activeEl.textContent = activeList.querySelectorAll('.task:not(.completed)').length;
        }
        if (completedEl && completedSection) {
            completedEl.textContent = completedSection.querySelectorAll('.task.completed').length;
        }
    }

    function showLiveToast(msg) {
        let toast = document.getElementById('live-update-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'live-update-toast';
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:var(--accent-color);color:#fff;padding:12px 20px;border-radius:14px;font-size:0.85rem;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.15);opacity:0;transition:opacity 0.3s ease;display:flex;align-items:center;gap:8px;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<i class="fa-solid fa-bolt"></i> ' + msg;
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 3000);
    }

    // Listen on the public shared.user channel
    window.Echo.channel('shared.user.' + channelToken)
        .listen('.task.created', function(e) {
            const task = e.task;
            if (!task || task.status === 'completed') return;

            const activeList = document.getElementById('active-tasks-list');
            if (!activeList) return;

            // Remove "no tasks" placeholder if present
            const emptyMsg = activeList.querySelector('[data-empty]');
            if (emptyMsg) emptyMsg.remove();

            // Prepend new card (sorted by date would need server, prepend is fine)
            activeList.insertAdjacentHTML('afterbegin', buildActiveCard(task));
            updateCounters();
            showLiveToast('New task added: ' + task.title);
        })
        .listen('.task.updated', function(e) {
            const task = e.task;
            if (!task) return;

            const existing = document.getElementById('task-card-' + task.id);
            const activeList = document.getElementById('active-tasks-list');
            const completedSection = document.getElementById('completed-tasks-section');

            if (task.status === 'completed') {
                // Move from active to completed
                if (existing) existing.remove();
                if (completedSection) {
                    const list = completedSection.querySelector('div[style*="flex-direction"]') || completedSection.querySelector('#completed-tasks-list');
                    if (list) list.insertAdjacentHTML('afterbegin', buildCompletedCard(task));
                    completedSection.style.display = '';
                }
            } else {
                // Update or re-add in active list
                if (existing) {
                    existing.outerHTML = buildActiveCard(task);
                } else {
                    // It might have moved from completed back to active
                    const completedCard = completedSection ? completedSection.querySelector('#task-card-' + task.id) : null;
                    if (completedCard) completedCard.remove();
                    if (activeList) activeList.insertAdjacentHTML('afterbegin', buildActiveCard(task));
                }
            }
            updateCounters();
            showLiveToast('Task updated: ' + task.title);
        })
        .listen('.task.deleted', function(e) {
            const card = document.getElementById('task-card-' + e.task_id);
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => { card.remove(); updateCounters(); }, 350);
                showLiveToast('A task was removed');
            }
        });
})();
</script>
@endsection
