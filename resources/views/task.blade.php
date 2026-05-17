@extends('layout.layout')
@section('title', $task['title'])
@section('Dashboard_nav', 'active')

@section('CustomCss')
  #create-task-btn { display: none !important; }
  #Back-to-Dashboard { display: flex !important; }
  
  .subtask-item.completed span {
      text-decoration: line-through;
      color: #9ca3af;
  }
  
  .custom-checkbox.checked {
      background: var(--accent-color);
      border-color: var(--accent-color);
  }
@endsection

@section('content')
  <div class="task_content" style="max-width: 800px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div>
            <h1 style="font-family: var(--font-accent); font-size: 2.2rem; margin: 0 0 8px 0; color: var(--text-main);">
                {{ $task['title'] }}
            </h1>
            <span class="task-catigory"><span>{{ $task['category_name'] }}</span></span>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">DUE DATE</div>
            <div style="font-family: var(--font-accent); font-weight: 600; color: var(--accent-color);">
                <i class="fa-regular fa-calendar"></i> {{ $task->formatted_date }}
            </div>
        </div>
    </div>

    @php
        $totalSubtasks = $task->subtasks->count();
        $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
        $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
    @endphp

    <div style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span id="progress-text" style="font-weight: 600; color: var(--text-muted);">{{ round($progress) }}% Complete</span>
            <span id="subtask-count" style="font-size: 0.9rem; color: var(--text-muted);">{{ $completedSubtasks }}/{{ $totalSubtasks }} Subtasks</span>
        </div>
        <div class="progress-container" style="height: 12px; background: #f3f4f6;">
            <div id="main-progress-bar" class="progress-bar" style="width: {{ $progress }}%; height: 100%; border-radius: 6px;"></div>
        </div>
    </div>

    <div style="margin-bottom: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-align-left" style="color: var(--accent-color);"></i> Description
        </h3>
        <p style="color: var(--text-muted); line-height: 1.6; background: #f9fafb; padding: 20px; border-radius: 12px; border-left: 4px solid #e5e7eb;">
            {{ $task['description'] ?: 'No description provided.' }}
        </p>
    </div>

    <div class="subtasks-section">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-check" style="color: var(--accent-color);"></i> Sub-tasks
        </h3>
        <div id="subtasks-list" style="display: flex; flex-direction: column; gap: 12px;">
            @forelse ($task->subtasks as $subtask)
                <div class="subtask-item {{ $subtask->is_completed ? 'completed' : '' }}" id="subtask-{{ $subtask->id }}" style="background: #fff; border: 1px solid #f3f4f6; padding: 16px; border-radius: 12px; transition: all 0.2s;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; width: 100%;" onclick="toggleSubtask({{ $subtask->id }})">
                        <div class="custom-checkbox {{ $subtask->is_completed ? 'checked' : '' }}"></div>
                        <span style="font-weight: 500;">
                            {{ $subtask['title'] }}
                        </span>
                    </label>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 20px;">
                    No sub-tasks for this task.
                </div>
            @endforelse
        </div>
    </div>
  </div>

  <script>
    function toggleSubtask(id) {
        axios.post(`/subtasks/${id}/toggle`, {
            _token: '{{ csrf_token() }}'
        })
        .then(response => {
            const subtask = response.data.data;
            const item = document.getElementById(`subtask-${subtask.id}`);
            const checkbox = item.querySelector('.custom-checkbox');
            
            if (subtask.is_completed) {
                item.classList.add('completed');
                checkbox.classList.add('checked');
            } else {
                item.classList.remove('completed');
                checkbox.classList.remove('checked');
            }
            
            updateProgressBar();
        })
        .catch(error => {
            console.error(error);
            alert('Error toggling subtask');
        });
    }

    function updateProgressBar() {
        const total = document.querySelectorAll('.subtask-item').length;
        const completed = document.querySelectorAll('.subtask-item.completed').length;
        const progress = total > 0 ? (completed / total) * 100 : 0;
        
        document.getElementById('main-progress-bar').style.width = `${progress}%`;
        document.getElementById('progress-text').innerText = `${Math.round(progress)}% Complete`;
        document.getElementById('subtask-count').innerText = `${completed}/${total} Subtasks`;
    }
  </script>
@endsection
