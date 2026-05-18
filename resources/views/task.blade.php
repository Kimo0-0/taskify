@extends('layout.layout')
@section('title', $task['title'])
@section('Dashboard_nav', 'active')

@section('CustomCss')
  #create-task-btn { display: none !important; }
  #Back-to-Dashboard { display: flex !important; }
  
  .task-details-card {
      max-width: 800px;
      margin: 40px auto;
      background: var(--card-bg);
      padding: 40px;
      border-radius: 24px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  @media (max-width: 768px) {
      .task-details-card {
          margin: 16px;
          padding: 24px;
      }
  }
  
  .description-box {
      color: var(--text-muted);
      line-height: 1.6;
      background: var(--subtask-bg);
      padding: 20px;
      border-radius: 12px;
      border-left: 4px solid var(--border-color);
      transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .subtask-detail-item {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      padding: 16px;
      border-radius: 12px;
      transition: background-color 0.3s ease, border-color 0.3s ease;
  }
  
  .subtask-item.completed span {
      text-decoration: line-through;
      color: var(--text-muted);
  }
  
  .custom-checkbox.checked {
      background: var(--accent-color);
      border-color: var(--accent-color);
  }
@endsection

@section('content')
  <div class="task-details-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
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
        <div class="progress-container" style="height: 12px;">
            <div id="main-progress-bar" class="progress-bar" style="width: {{ $progress }}%; height: 100%; border-radius: 6px;"></div>
        </div>
    </div>

    <div style="margin-bottom: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-align-left" style="color: var(--accent-color);"></i> Description
        </h3>
        <p class="description-box">
            {{ $task['description'] ?: 'No description provided.' }}
        </p>
    </div>

    <div class="subtasks-section">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-list-check" style="color: var(--accent-color);"></i> Sub-tasks
        </h3>
        <div id="subtasks-list" style="display: flex; flex-direction: column; gap: 12px;">
            @forelse ($task->subtasks as $subtask)
                <div class="subtask-item subtask-detail-item {{ $subtask->is_completed ? 'completed' : '' }}" id="subtask-{{ $subtask->id }}">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; width: 100%; color: var(--text-main);" onclick="toggleSubtask({{ $subtask->id }})">
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

    {{-- File Attachments Section --}}
    <div class="attachments-section" style="margin-top: 40px; border-top: 1px solid var(--border-color); padding-top: 32px;">
        <h3 style="font-family: var(--font-accent); font-size: 1.2rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--text-main);">
            <i class="fa-solid fa-paperclip" style="color: var(--accent-color);"></i> Attachments
        </h3>

        {{-- Premium Upload Dropzone --}}
        <div id="dropzone" class="upload-dropzone" style="border: 2px dashed var(--border-color); padding: 30px; border-radius: 16px; text-align: center; background: var(--subtask-bg); transition: all 0.3s ease; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px;" onclick="document.getElementById('file-input').click()">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--accent-color); transition: transform 0.3s ease;"></i>
            <div style="font-weight: 600; color: var(--text-main); font-family: var(--font-main);">Drag & Drop your files here or <span style="color: var(--accent-color);">Browse</span></div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Supports Images, PDFs, and Documents (Max 10MB)</div>
            <input type="file" id="file-input" style="display: none;" onchange="uploadAttachment(this.files[0])">
        </div>

        {{-- Dynamic Attachments Grid for images (Lightbox Gallery) --}}
        <div id="attachments-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 16px; margin-bottom: 20px;">
            @foreach($task->attachments as $attachment)
                @if($attachment->is_image)
                    <div class="attachment-card image-card" id="attachment-{{ $attachment->id }}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.3s ease;" onclick="openLightbox('{{ $attachment->file_url }}')">
                        <img src="{{ $attachment->file_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                            <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                        </div>
                        <button onclick="event.stopPropagation(); deleteAttachment({{ $attachment->id }})" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(239, 68, 68, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Delete file">
                            <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- List for non-images (PDFs, Docs, etc) --}}
        <div id="attachments-list" style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($task->attachments as $attachment)
                @if(!$attachment->is_image)
                    @php
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
                            <a href="/attachments/{{ $attachment->id }}/download" class="btn-aqua" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Download
                            </a>
                            <button onclick="deleteAttachment({{ $attachment->id }})" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; background: var(--overdue-color); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 600;">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
  </div>

  {{-- Fullscreen Lightbox Overlay --}}
  <div id="lightbox" onclick="closeLightbox()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 20000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; cursor: pointer;">
      <button onclick="closeLightbox()" style="position: absolute; top: 24px; right: 24px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; transition: all 0.3s ease; z-index: 20001;" title="Close Gallery">
          <i class="fa-solid fa-xmark"></i>
      </button>
      <img id="lightbox-img" src="" style="max-width: 90%; max-height: 85%; object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.3s ease;" onclick="event.stopPropagation()">
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

    // ================== Task Attachment Handlers ==================
    const dropzone = document.getElementById('dropzone');

    // Drag and Drop interactive effects
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = 'var(--accent-color)';
                dropzone.style.background = 'var(--bg-hover)';
                dropzone.querySelector('i').style.transform = 'scale(1.15)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = 'var(--border-color)';
                dropzone.style.background = 'var(--subtask-bg)';
                dropzone.querySelector('i').style.transform = 'scale(1)';
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const file = dt.files[0];
            if (file) uploadAttachment(file);
        }, false);
    }

    function uploadAttachment(file) {
        if (!file) return;

        // Size check (10MB maximum)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds the 10MB limit!');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Show uploading feedback state
        const dropzoneText = dropzone.querySelector('div');
        const originalText = dropzoneText.innerHTML;
        dropzoneText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading file... Please wait.';
        dropzone.style.pointerEvents = 'none';

        axios.post('/tasks/{{ $task->id }}/attachments', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            const attachment = response.data.data;
            
            // Build dynamic markup based on file type
            if (attachment.is_image) {
                const grid = document.getElementById('attachments-grid');
                const imageCard = `
                    <div class="attachment-card image-card" id="attachment-${attachment.id}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; opacity: 0; transform: translateY(10px); transition: all 0.4s ease;" onclick="openLightbox('${attachment.file_url}')">
                        <img src="${attachment.file_url}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                            <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                        </div>
                        <button onclick="event.stopPropagation(); deleteAttachment(${attachment.id})" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(239, 68, 68, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Delete file">
                            <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>`;
                grid.insertAdjacentHTML('beforeend', imageCard);
                // Trigger smooth fade in entry
                setTimeout(() => {
                    const el = document.getElementById(`attachment-${attachment.id}`);
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 50);
            } else {
                const list = document.getElementById('attachments-list');
                let icon = 'fa-file';
                if (attachment.file_type.includes('pdf')) icon = 'fa-file-pdf';
                else if (attachment.file_type.includes('word') || attachment.file_type.includes('document')) icon = 'fa-file-word';
                else if (attachment.file_type.includes('excel') || attachment.file_type.includes('sheet')) icon = 'fa-file-excel';

                const docRow = `
                    <div class="attachment-row" id="attachment-${attachment.id}" style="display: flex; align-items: center; justify-content: space-between; background: var(--subtask-bg); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border-color); opacity: 0; transform: translateY(10px); transition: all 0.4s ease;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex-grow: 1;">
                            <i class="fa-solid ${icon}" style="font-size: 1.6rem; color: var(--accent-color);"></i>
                            <div style="min-width: 0;">
                                <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">${attachment.file_name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-accent);">${attachment.formatted_size}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-shrink: 0; margin-left: 12px;">
                            <a href="/attachments/${attachment.id}/download" class="btn-aqua" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 600;">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Download
                            </a>
                            <button onclick="deleteAttachment(${attachment.id})" style="padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; background: var(--overdue-color); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: 600;">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>
                        </div>
                    </div>`;
                list.insertAdjacentHTML('beforeend', docRow);
                // Trigger smooth fade in entry
                setTimeout(() => {
                    const el = document.getElementById(`attachment-${attachment.id}`);
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 50);
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error uploading attachment: ' + (error.response?.data?.message || 'Unknown error'));
        })
        .finally(() => {
            // Restore dropzone state
            dropzoneText.innerHTML = originalText;
            dropzone.style.pointerEvents = 'auto';
            document.getElementById('file-input').value = '';
        });
    }

    function deleteAttachment(id) {
        if (!confirm('Are you sure you want to delete this attachment permanently?')) return;

        axios.delete(`/attachments/${id}`, {
            data: { _token: '{{ csrf_token() }}' }
        })
        .then(() => {
            const el = document.getElementById(`attachment-${id}`);
            if (el) {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.9)';
                setTimeout(() => el.remove(), 300);
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error deleting attachment: ' + (error.response?.data?.message || 'Unknown error'));
        });
    }

    // ================== Lightbox Gallery Handlers ==================
    function openLightbox(src) {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        if (lightbox && img) {
            img.src = src;
            lightbox.style.display = 'flex';
            setTimeout(() => {
                lightbox.style.opacity = '1';
                img.style.transform = 'scale(1)';
            }, 50);
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        if (lightbox && img) {
            lightbox.style.opacity = '0';
            img.style.transform = 'scale(0.95)';
            setTimeout(() => {
                lightbox.style.display = 'none';
                img.src = '';
            }, 300);
        }
    }
  </script>
@endsection
