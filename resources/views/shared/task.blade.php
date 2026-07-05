@extends('layout.public')
@section('title', $task['title'])

@section('content')
  <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
      <a href="javascript:history.back()" style="display: inline-flex; align-items: center; gap: 8px; color: var(--accent-color); text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(-4px)'" onmouseout="this.style.transform='none'">
          <i class="fa-solid fa-arrow-left"></i> Back
      </a>

      {{-- Action Buttons --}}
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          @if($canComplete ?? false)
              <button id="toggle-complete-btn" onclick="toggleTaskComplete()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; cursor: pointer; border: none; transition: all 0.25s ease; background: {{ $task->status == 'completed' ? 'var(--border-color)' : 'var(--completed-color)' }}; color: {{ $task->status == 'completed' ? 'var(--text-main)' : '#fff' }};">
                  <i class="fa-solid {{ $task->status == 'completed' ? 'fa-rotate-left' : 'fa-circle-check' }}"></i>
                  <span id="complete-btn-text">{{ $task->status == 'completed' ? 'Mark as Pending' : 'Mark as Completed' }}</span>
              </button>
          @endif
          @if($canEdit ?? false)
              <button onclick="openEditModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; cursor: pointer; border: none; transition: all 0.25s ease; background: var(--accent-color); color: #fff;">
                  <i class="fa-regular fa-pen-to-square"></i> Edit Task
              </button>
          @endif
      </div>
  </div>

  <div class="task-details-card" style="background: var(--card-bg); padding: 32px; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 id="task-title-display" style="font-family: var(--font-accent); font-size: 2.2rem; margin: 0 0 8px 0; color: var(--text-main);">
                {{ $task['title'] }}
            </h1>
            <span class="task-catigory" style="margin: 0; background: rgba(59, 130, 246, 0.1); color: var(--accent-color); padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600;">
                <span>{{ $task['category_name'] }}</span>
            </span>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">DUE DATE</div>
            <div style="font-family: var(--font-accent); font-weight: 600; color: var(--accent-color); font-size: 1.1rem;">
                <i class="fa-regular fa-calendar"></i> {{ $task->formatted_date }}
            </div>
            <div style="margin-top: 6px;">
                <span id="task-status-badge" class="status-badge {{ $task['status'] }}" style="padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-block;">{{ $task['status'] }}</span>
            </div>
        </div>
    </div>

    @php
        $totalSubtasks = $task->subtasks->count();
        $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
        $progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : ($task['status'] == 'completed' ? 100 : 0);
    @endphp

    <div style="margin-bottom: 32px; background: var(--subtask-bg); padding: 20px; border-radius: 16px; border: 1px solid var(--border-color);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span id="progress-text" style="font-weight: 600; color: var(--text-muted);">{{ round($progress) }}% Complete</span>
            <span id="subtask-count" style="font-size: 0.9rem; color: var(--text-muted);">{{ $completedSubtasks }}/{{ $totalSubtasks }} Subtasks</span>
        </div>
        <div class="progress-container" style="height: 12px; background: var(--progress-bg); border-radius: 6px; overflow: hidden; width: 100%;">
            <div id="main-progress-bar" class="progress-bar" style="width: {{ $progress }}%; height: 100%; border-radius: 6px; background: var(--accent-color); transition: width 0.3s ease;"></div>
        </div>
    </div>

    <div style="margin-bottom: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-align-left" style="color: var(--accent-color);"></i> Description
        </h3>
        <p id="task-desc-display" class="description-box" style="background: var(--subtask-bg); padding: 18px; border-radius: 12px; border: 1px solid var(--border-color); color: var(--text-main); margin: 0; line-height: 1.6; font-size: 0.95rem; white-space: pre-line;">
            {{ $task['description'] ?: 'No description provided.' }}
        </p>
    </div>

    <div class="subtasks-section" style="margin-bottom: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-list-check" style="color: var(--accent-color);"></i> Sub-tasks
        </h3>
        <div id="subtasks-list" style="display: flex; flex-direction: column; gap: 12px;">
            @forelse ($task->subtasks as $subtask)
                <div class="subtask-item subtask-detail-item {{ $subtask->is_completed ? 'completed' : '' }}" id="subtask-{{ $subtask->id }}" style="background: var(--subtask-bg); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; opacity: {{ $subtask->is_completed ? 0.7 : 1 }}; transition: all 0.25s ease; {{ ($canComplete ?? false) ? 'cursor: pointer;' : '' }}"
                    {{ ($canComplete ?? false) ? "onclick=\"toggleSubtask({$subtask->id}, this)\"" : '' }}>
                    <div class="custom-checkbox {{ $subtask->is_completed ? 'checked' : '' }}" id="subtask-check-{{ $subtask->id }}" style="width: 20px; height: 20px; border: 2px solid {{ $subtask->is_completed ? 'var(--completed-color)' : 'var(--text-muted)' }}; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: {{ $subtask->is_completed ? 'var(--completed-color)' : 'transparent' }}; flex-shrink: 0; transition: all 0.2s ease;">
                        @if($subtask->is_completed)
                            <i class="fa-solid fa-check" style="color: #fff; font-size: 0.8rem;"></i>
                        @endif
                    </div>
                    <span style="font-weight: 500; text-decoration: {{ $subtask->is_completed ? 'line-through' : 'none' }}; color: var(--text-main);">
                        {{ $subtask['title'] }}
                    </span>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 20px; background: var(--subtask-bg); border-radius: 12px; border: 1px solid var(--border-color);">
                    No sub-tasks for this task.
                </div>
            @endforelse
        </div>
    </div>

    {{-- File Attachments Section --}}
    <div class="attachments-section" style="margin-top: 40px; border-top: 1px solid var(--border-color); padding-top: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-paperclip" style="color: var(--accent-color);"></i> Attachments
        </h3>

        {{-- Dynamic Attachments Grid for images (Lightbox Gallery) --}}
        <div id="attachments-grid" style="display: {{ $task->attachments->where('is_image', true)->count() > 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 16px; margin-bottom: 20px;">
            @foreach($task->attachments->where('is_image', true) as $attachment)
                @php
                    $guestDownloadUrl = route('shared.attachment.download', ['id' => $attachment->id, 'token' => $token]);
                @endphp
                <div class="attachment-card image-card" id="attachment-{{ $attachment->id }}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.3s ease;" onclick="openLightbox('{{ $attachment->file_url }}', 'image')">
                    <img src="{{ $attachment->file_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                    </div>
                    <a href="{{ $guestDownloadUrl }}" onclick="event.stopPropagation();" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(59, 130, 246, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15); text-decoration: none;" title="Download file">
                        <i class="fa-solid fa-cloud-arrow-down" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Dynamic Attachments Grid for videos --}}
        <div id="attachments-videos" style="display: {{ $task->attachments->where('is_video', true)->count() > 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
            @foreach($task->attachments->where('is_video', true) as $attachment)
                @php
                    $guestDownloadUrl = route('shared.attachment.download', ['id' => $attachment->id, 'token' => $token]);
                @endphp
                <div class="attachment-card video-card" id="attachment-{{ $attachment->id }}" style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); transition: all 0.3s ease; background: #000;">
                    <video style="width: 100%; display: block; border-radius: 12px 12px 0 0; max-height: 220px; object-fit: cover;" preload="metadata" muted>
                        <source src="{{ $attachment->file_url }}" type="{{ $attachment->file_type }}">
                    </video>
                    <div class="video-play-overlay" onclick="openLightbox('{{ $attachment->file_url }}', 'video')" style="position: absolute; top: 0; left: 0; right: 0; bottom: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: rgba(0,0,0,0.25); transition: all 0.3s ease;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.3s ease;">
                            <i class="fa-solid fa-play" style="color: var(--accent-color); font-size: 1.2rem; margin-left: 3px;"></i>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--subtask-bg);">
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 600; color: var(--text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">{{ $attachment->file_name }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-accent);">{{ $attachment->formatted_size }}</div>
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                            <a href="{{ $guestDownloadUrl }}" class="btn-aqua" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; display: flex; align-items: center; gap: 4px; text-decoration: none; font-weight: 600;" title="Download">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Other Files List --}}
        <div id="attachments-list" style="display: {{ $task->attachments->where('is_image', false)->where('is_video', false)->count() > 0 ? 'flex' : 'none' }}; flex-direction: column; gap: 12px;">
            @foreach($task->attachments->where('is_image', false)->where('is_video', false) as $attachment)
                @php
                    $guestDownloadUrl = route('shared.attachment.download', ['id' => $attachment->id, 'token' => $token]);
                    $icon = 'fa-file';
                    if(str_contains($attachment->file_type, 'pdf')) $icon = 'fa-file-pdf';
                    elseif(str_contains($attachment->file_type, 'word') || str_contains($attachment->file_type, 'document')) $icon = 'fa-file-word';
                    elseif(str_contains($attachment->file_type, 'excel') || str_contains($attachment->file_type, 'sheet')) $icon = 'fa-file-excel';
                @endphp
                <div class="attachment-row" id="attachment-{{ $attachment->id }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--subtask-bg); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex-grow: 1;">
                        <i class="fa-solid {{ $icon }}" style="font-size: 1.6rem; color: var(--accent-color);"></i>
                        <div style="min-width: 0;">
                            <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">{{ $attachment->file_name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-accent);">{{ $attachment->formatted_size }}</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-shrink: 0; margin-left: 12px;">
                        <a href="{{ $guestDownloadUrl }}" class="btn-aqua" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
                            <i class="fa-solid fa-cloud-arrow-down"></i> Download
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
  </div>

  {{-- Edit Task Modal (only rendered if canEdit) --}}
  @if($canEdit ?? false)
  <div id="edit-task-overlay" onclick="closeEditModal()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); z-index: 15000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
      <div onclick="event.stopPropagation()" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 32px; width: 90%; max-width: 520px; box-shadow: 0 25px 60px rgba(0,0,0,0.25); transform: scale(0.95); transition: transform 0.3s ease;" id="edit-task-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
              <h3 style="margin: 0; font-family: var(--font-accent); font-size: 1.3rem; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                  <i class="fa-regular fa-pen-to-square" style="color: var(--accent-color);"></i> Edit Task
              </h3>
              <button onclick="closeEditModal()" style="background: var(--close-btn-bg, var(--subtask-bg)); border: 1px solid var(--border-color); color: var(--text-main); width: 36px; height: 36px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                  <i class="fa-solid fa-xmark"></i>
              </button>
          </div>

          <div style="display: flex; flex-direction: column; gap: 16px;">
              <div>
                  <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px;">Title</label>
                  <input type="text" id="edit-title" value="{{ $task['title'] }}" style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; box-sizing: border-box; outline: none;">
              </div>
              <div>
                  <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px;">Description</label>
                  <textarea id="edit-description" rows="4" style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.95rem; box-sizing: border-box; outline: none; resize: vertical;">{{ $task['description'] }}</textarea>
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                  <div>
                      <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px;">Due Date</label>
                      <input type="datetime-local" id="edit-due-date" value="{{ \Carbon\Carbon::parse($task['due_date'])->format('Y-m-d\TH:i') }}" style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.9rem; box-sizing: border-box; outline: none;">
                  </div>
                  <div>
                      <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px;">Priority</label>
                      <select id="edit-priority" style="width: 100%; padding: 11px 14px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); border-radius: 12px; font-family: var(--font-main); font-size: 0.9rem; box-sizing: border-box; outline: none;">
                          <option value="low" {{ $task['priority'] == 'low' ? 'selected' : '' }}>Low</option>
                          <option value="medium" {{ $task['priority'] == 'medium' ? 'selected' : '' }}>Medium</option>
                          <option value="high" {{ $task['priority'] == 'high' ? 'selected' : '' }}>High</option>
                      </select>
                  </div>
              </div>
              <button onclick="saveTaskEdit()" id="save-edit-btn" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; border-radius: 14px; font-size: 0.95rem; font-weight: 700; border: none; cursor: pointer; background: var(--accent-color); color: #fff; transition: all 0.25s ease; margin-top: 4px;">
                  <i class="fa-solid fa-floppy-disk"></i> Save Changes
              </button>
          </div>
      </div>
  </div>
  @endif

  {{-- Fullscreen Lightbox Overlay --}}
  <div id="lightbox" onclick="closeLightbox()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 20000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; cursor: pointer;">
      <button onclick="closeLightbox()" style="position: absolute; top: 24px; right: 24px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; transition: all 0.3s ease; z-index: 20001;" title="Close">
          <i class="fa-solid fa-xmark"></i>
      </button>
      <img id="lightbox-img" src="" style="max-width: 90%; max-height: 85%; object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.3s ease; display: none;" onclick="event.stopPropagation()">
      <video id="lightbox-video" controls autoplay style="max-width: 90%; max-height: 85%; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.3s ease; display: none;" onclick="event.stopPropagation()"></video>
  </div>

  <script>
    const SHARE_TOKEN = '{{ $token }}';
    const TASK_ID     = {{ $task->id }};

    // ================== Lightbox ==================
    function openLightbox(url, type) {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        const video = document.getElementById('lightbox-video');
        lightbox.style.display = 'flex';
        setTimeout(() => lightbox.style.opacity = '1', 10);
        if (type === 'image') {
            img.src = url; img.style.display = 'block'; video.style.display = 'none'; video.pause();
            setTimeout(() => img.style.transform = 'scale(1)', 10);
        } else if (type === 'video') {
            video.src = url; video.style.display = 'block'; img.style.display = 'none';
            setTimeout(() => video.style.transform = 'scale(1)', 10);
        }
    }
    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        const video = document.getElementById('lightbox-video');
        lightbox.style.opacity = '0';
        img.style.transform = 'scale(0.95)';
        video.style.transform = 'scale(0.95)';
        setTimeout(() => { lightbox.style.display = 'none'; img.src = ''; video.src = ''; video.pause(); }, 300);
    }

    // ================== Toggle Task Complete ==================
    function toggleTaskComplete() {
        const btn  = document.getElementById('toggle-complete-btn');
        const text = document.getElementById('complete-btn-text');
        btn.disabled = true;
        btn.style.opacity = '0.6';

        axios.post(`/shared/task/${TASK_ID}/toggle/${SHARE_TOKEN}`)
        .then(response => {
            const task   = response.data.data;
            const isDone = task.status === 'completed';

            // Update badge
            const badge = document.getElementById('task-status-badge');
            if (badge) {
                badge.className = `status-badge ${task.status}`;
                badge.textContent = task.status;
            }
            // Update button
            text.textContent = isDone ? 'Mark as Pending' : 'Mark as Completed';
            btn.style.background = isDone ? 'var(--border-color)' : 'var(--completed-color)';
            btn.style.color      = isDone ? 'var(--text-main)' : '#fff';
            const icon = btn.querySelector('i');
            if (icon) icon.className = `fa-solid ${isDone ? 'fa-rotate-left' : 'fa-circle-check'}`;
        })
        .catch(() => alert('Failed to update task status. You may not have permission.'))
        .finally(() => { btn.disabled = false; btn.style.opacity = '1'; });
    }

    // ================== Toggle Subtask Complete ==================
    function toggleSubtask(subtaskId, el) {
        el.style.opacity = '0.5';

        axios.post(`/shared/subtask/${subtaskId}/toggle/${SHARE_TOKEN}`)
        .then(response => {
            const subtask  = response.data.data;
            const isDone   = subtask.is_completed;
            const checkEl  = document.getElementById(`subtask-check-${subtaskId}`);
            const textEl   = el.querySelector('span');

            el.classList.toggle('completed', isDone);
            el.style.opacity = isDone ? '0.7' : '1';
            textEl.style.textDecoration = isDone ? 'line-through' : 'none';

            if (checkEl) {
                checkEl.style.background    = isDone ? 'var(--completed-color)' : 'transparent';
                checkEl.style.borderColor   = isDone ? 'var(--completed-color)' : 'var(--text-muted)';
                checkEl.innerHTML           = isDone ? '<i class="fa-solid fa-check" style="color:#fff;font-size:0.8rem;"></i>' : '';
            }

            // Recalculate progress bar
            const allItems  = document.querySelectorAll('#subtasks-list .subtask-item');
            const doneItems = document.querySelectorAll('#subtasks-list .subtask-item.completed');
            const total     = allItems.length;
            const done      = doneItems.length;
            const pct       = total > 0 ? Math.round((done / total) * 100) : 0;

            const bar  = document.getElementById('main-progress-bar');
            const prog = document.getElementById('progress-text');
            const cnt  = document.getElementById('subtask-count');
            if (bar)  bar.style.width = `${pct}%`;
            if (prog) prog.textContent = `${pct}% Complete`;
            if (cnt)  cnt.textContent  = `${done}/${total} Subtasks`;
        })
        .catch(() => { el.style.opacity = '1'; alert('Failed to update subtask.'); });
    }

    // ================== Edit Task Modal ==================
    function openEditModal() {
        const overlay = document.getElementById('edit-task-overlay');
        const card    = document.getElementById('edit-task-card');
        if (!overlay) return;
        overlay.style.display = 'flex';
        setTimeout(() => { overlay.style.opacity = '1'; card.style.transform = 'scale(1)'; }, 10);
    }
    function closeEditModal() {
        const overlay = document.getElementById('edit-task-overlay');
        const card    = document.getElementById('edit-task-card');
        if (!overlay) return;
        overlay.style.opacity = '0';
        card.style.transform  = 'scale(0.95)';
        setTimeout(() => overlay.style.display = 'none', 300);
    }
    function saveTaskEdit() {
        const btn = document.getElementById('save-edit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        axios.post(`/shared/task/${TASK_ID}/update/${SHARE_TOKEN}`, {
            title:       document.getElementById('edit-title').value,
            description: document.getElementById('edit-description').value,
            due_date:    document.getElementById('edit-due-date').value,
            priority:    document.getElementById('edit-priority').value,
        })
        .then(response => {
            const task  = response.data.data;
            const title = document.getElementById('task-title-display');
            const desc  = document.getElementById('task-desc-display');
            if (title) title.textContent = task.title;
            if (desc)  desc.textContent  = task.description || 'No description provided.';
            closeEditModal();
        })
        .catch(() => alert('Failed to save changes. You may not have permission.'))
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes'; });
    }

    // Close edit modal on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('edit-task-overlay');
            if (overlay && overlay.style.display === 'flex') closeEditModal();
            if (document.getElementById('lightbox')?.style.display === 'flex') closeLightbox();
        }
    });
  </script>
@endsection

@section('realtime-script')
<script>
(function() {
    const taskId       = {{ $task->id }};
    const shareToken   = '{{ $token }}';
    @if(isset($isCategoryContext) && $isCategoryContext)
    const channelName  = 'shared.category.' + shareToken;
    @elseif(isset($isUserContext) && $isUserContext)
    const channelName  = 'shared.user.' + shareToken;
    @else
    {{-- standalone shared task - broadcast on private + user/category channels. --}}
    {{-- We use the user's channel if available --}}
    @php
        $taskUser = $task->user;
        $channelTokenForJs = $taskUser && $taskUser->share_token ? $taskUser->share_token : null;
    @endphp
    const channelName  = '{{ $channelTokenForJs ? 'shared.user.' . $channelTokenForJs : '' }}';
    @endif

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

    function applyTaskUpdate(task) {
        if (String(task.id) !== String(taskId)) return;

        // Update title
        const titleEl = document.getElementById('task-title-display');
        if (titleEl) titleEl.textContent = task.title;

        // Update description
        const descEl = document.getElementById('task-desc-display');
        if (descEl) descEl.textContent = task.description || 'No description provided.';

        // Update status badge
        const statusBadge = document.getElementById('task-status-badge');
        if (statusBadge) {
            statusBadge.textContent = task.status;
            statusBadge.className = 'status-badge ' + task.status;
        }

        // Update complete button
        const btn = document.getElementById('toggle-complete-btn');
        const btnText = document.getElementById('complete-btn-text');
        if (btn && btnText) {
            const isCompleted = task.status === 'completed';
            btn.style.background = isCompleted ? 'var(--border-color)' : 'var(--completed-color)';
            btn.style.color = isCompleted ? 'var(--text-main)' : '#fff';
            btnText.textContent = isCompleted ? 'Mark as Pending' : 'Mark as Completed';
        }

        // Update subtasks
        if (task.subtasks && task.subtasks.length > 0) {
            const total = task.subtasks.length;
            const done  = task.subtasks.filter(s => s.is_completed).length;
            const pct   = total > 0 ? Math.round((done / total) * 100) : 0;

            const progressBar  = document.getElementById('main-progress-bar');
            const progressText = document.getElementById('progress-text');
            const subtaskCount = document.getElementById('subtask-count');
            if (progressBar)  progressBar.style.width = pct + '%';
            if (progressText) progressText.textContent = pct + '% Complete';
            if (subtaskCount) subtaskCount.textContent = done + '/' + total + ' Subtasks';

            task.subtasks.forEach(s => {
                const item  = document.getElementById('subtask-' + s.id);
                const check = document.getElementById('subtask-check-' + s.id);
                if (!item || !check) return;
                if (s.is_completed) {
                    item.classList.add('completed');
                    item.style.opacity = '0.7';
                    check.classList.add('checked');
                    check.style.background = 'var(--completed-color)';
                    check.style.borderColor = 'var(--completed-color)';
                    check.innerHTML = '<i class="fa-solid fa-check" style="color:#fff;font-size:0.7rem;"></i>';
                } else {
                    item.classList.remove('completed');
                    item.style.opacity = '1';
                    check.classList.remove('checked');
                    check.style.background = 'transparent';
                    check.style.borderColor = 'var(--text-muted)';
                    check.innerHTML = '';
                }
            });
        }

        showLiveToast('Task updated by owner');
    }

    if (channelName) {
        window.Echo.channel(channelName)
            .listen('.task.updated', function(e) {
                applyTaskUpdate(e.task);
            })
            .listen('.task.deleted', function(e) {
                if (String(e.task_id) === String(taskId)) {
                    showLiveToast('This task was deleted by its owner');
                    setTimeout(() => { history.back(); }, 2500);
                }
            })
            .listen('.subtask.toggled', function(e) {
                if (String(e.subtask.task_id) !== String(taskId)) return;
                const subtask = e.subtask;
                const isDone = subtask.is_completed;
                const item = document.getElementById('subtask-' + subtask.id);
                const check = document.getElementById('subtask-check-' + subtask.id);
                if (item && check) {
                    item.classList.toggle('completed', isDone);
                    item.style.opacity = isDone ? '0.7' : '1';
                    const span = item.querySelector('span');
                    if (span) span.style.textDecoration = isDone ? 'line-through' : 'none';
                    check.style.background = isDone ? 'var(--completed-color)' : 'transparent';
                    check.style.borderColor = isDone ? 'var(--completed-color)' : 'var(--text-muted)';
                    check.innerHTML = isDone ? '<i class="fa-solid fa-check" style="color:#fff;font-size:0.8rem;"></i>' : '';
                }

                // Recalculate progress bar
                const allItems  = document.querySelectorAll('#subtasks-list .subtask-item');
                const doneItems = document.querySelectorAll('#subtasks-list .subtask-item.completed');
                const total     = allItems.length;
                const done      = doneItems.length;
                const pct       = total > 0 ? Math.round((done / total) * 100) : 0;

                const bar  = document.getElementById('main-progress-bar');
                const prog = document.getElementById('progress-text');
                const cnt  = document.getElementById('subtask-count');
                if (bar)  bar.style.width = pct + '%';
                if (prog) prog.textContent = pct + '% Complete';
                if (cnt)  cnt.textContent  = done + '/' + total + ' Subtasks';

                showLiveToast('Subtask status updated');
            })
            .listen('.attachment.uploaded', function(e) {
                if (String(e.attachment.task_id) !== String(taskId)) return;
                const att = e.attachment;
                const downloadUrl = `/shared/attachment/${att.id}/${shareToken}`;
                
                if (att.is_image) {
                    const grid = document.getElementById('attachments-grid');
                    if (grid) {
                        grid.style.display = 'grid';
                        const html = `
                        <div class="attachment-card image-card" id="attachment-${att.id}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.3s ease;" onclick="openLightbox('${att.file_url}', 'image')">
                            <img src="${att.file_url}" style="width: 100%; height: 100%; object-fit: cover;">
                            <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                            </div>
                            <a href="${downloadUrl}" onclick="event.stopPropagation();" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(59, 130, 246, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15); text-decoration: none;" title="Download file">
                                <i class="fa-solid fa-cloud-arrow-down" style="font-size: 0.8rem;"></i>
                            </a>
                        </div>`;
                        grid.insertAdjacentHTML('beforeend', html);
                    }
                } else if (att.is_video) {
                    const videos = document.getElementById('attachments-videos');
                    if (videos) {
                        videos.style.display = 'grid';
                        const html = `
                        <div class="attachment-card video-card" id="attachment-${att.id}" style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); transition: all 0.3s ease; background: #000;">
                            <video style="width: 100%; display: block; border-radius: 12px 12px 0 0; max-height: 220px; object-fit: cover;" preload="metadata" muted>
                                <source src="${att.file_url}" type="${att.file_type}">
                            </video>
                            <div class="video-play-overlay" onclick="openLightbox('${att.file_url}', 'video')" style="position: absolute; top: 0; left: 0; right: 0; bottom: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: rgba(0,0,0,0.25); transition: all 0.3s ease;">
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.3s ease;">
                                    <i class="fa-solid fa-play" style="color: var(--accent-color); font-size: 1.2rem; margin-left: 3px;"></i>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--subtask-bg);">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">${att.file_name}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-accent);">${att.formatted_size}</div>
                                </div>
                                <div style="display: flex; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                                    <a href="${downloadUrl}" class="btn-aqua" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; display: flex; align-items: center; gap: 4px; text-decoration: none; font-weight: 600;" title="Download">
                                        <i class="fa-solid fa-cloud-arrow-down"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>`;
                        videos.insertAdjacentHTML('beforeend', html);
                    }
                } else {
                    const list = document.getElementById('attachments-list');
                    if (list) {
                        list.style.display = 'flex';
                        let icon = 'fa-file';
                        if(att.file_type.includes('pdf')) icon = 'fa-file-pdf';
                        else if(att.file_type.includes('word') || att.file_type.includes('document')) icon = 'fa-file-word';
                        else if(att.file_type.includes('excel') || att.file_type.includes('sheet')) icon = 'fa-file-excel';
                        const html = `
                        <div class="attachment-row" id="attachment-${att.id}" style="display: flex; align-items: center; justify-content: space-between; background: var(--subtask-bg); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex-grow: 1;">
                                <i class="fa-solid ${icon}" style="font-size: 1.6rem; color: var(--accent-color);"></i>
                                <div style="min-width: 0;">
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">${att.file_name}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-accent);">${att.formatted_size}</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; flex-shrink: 0; margin-left: 12px;">
                                <a href="${downloadUrl}" class="btn-aqua" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
                                    <i class="fa-solid fa-cloud-arrow-down"></i> Download
                                </a>
                            </div>
                        </div>`;
                        list.insertAdjacentHTML('beforeend', html);
                    }
                }
                showLiveToast('Attachment uploaded: ' + att.file_name);
            })
            .listen('.attachment.deleted', function(e) {
                if (String(e.task_id) !== String(taskId)) return;
                const el = document.getElementById('attachment-' + e.attachment_id);
                if (el) {
                    el.style.opacity = '0';
                    setTimeout(() => {
                        el.remove();
                        // Hide grids if empty
                        const grid = document.getElementById('attachments-grid');
                        const videos = document.getElementById('attachments-videos');
                        const list = document.getElementById('attachments-list');
                        if (grid && grid.querySelectorAll('.attachment-card').length === 0) grid.style.display = 'none';
                        if (videos && videos.querySelectorAll('.attachment-card').length === 0) videos.style.display = 'none';
                        if (list && list.querySelectorAll('.attachment-row').length === 0) list.style.display = 'none';
                    }, 300);
                    showLiveToast('Attachment removed');
                }
            });
    }
})();
</script>
@endsection
