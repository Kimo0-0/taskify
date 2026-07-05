@extends('layout.layout')
@section('title', $task['title'])
@section('Dashboard_nav', 'active')

@section('body-class', 'page-task-details')

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

    {{-- Share Settings Section --}}
    <div style="background: var(--subtask-bg); padding: 20px 24px; border-radius: 20px; border: 1px solid var(--border-color); margin-bottom: 24px; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; width: 100%;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-share-nodes" style="color: var(--accent-color); font-size: 1.4rem;"></i>
                <div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 1rem;">Public Sharing</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Allow anyone with the link to view this task without login.</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div id="share-link-container" style="display: {{ $task->share_token ? 'flex' : 'none' }}; align-items: center; gap: 8px; background: var(--input-bg); padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border-color); max-width: 320px;">
                    <input type="text" id="share-url-input" value="{{ $task->share_url }}" readonly style="background: none; border: none; color: var(--text-main); font-size: 0.85rem; width: 220px; outline: none;">
                    <button onclick="copyShareUrl()" class="btn-aqua" style="padding: 6px 10px; border-radius: 6px; font-size: 0.75rem; border: none; font-weight: 600;">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
                <button id="toggle-share-btn" onclick="toggleTaskShare({{ $task->id }})" class="btn-aqua" style="background: {{ $task->share_token ? 'var(--overdue-color)' : 'var(--accent-color)' }}; color: #fff; padding: 10px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s;">
                    {{ $task->share_token ? 'Disable Link' : 'Enable Link' }}
                </button>
            </div>
        </div>

        {{-- Share Permissions Toggles --}}
        <div id="share-permissions-container" style="display: {{ $task->share_token ? 'flex' : 'none' }}; align-items: center; gap: 24px; border-top: 1px solid var(--border-color); padding-top: 12px; flex-wrap: wrap;">
            <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Link Permissions:</div>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-main); cursor: pointer;">
                <input type="checkbox" id="share-can-complete-checkbox" onchange="toggleTaskPermission({{ $task->id }}, 'share_can_complete', this)" {{ $task->share_can_complete ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--accent-color);">
                Allow Completion (Complete Subtasks & Task)
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-main); cursor: pointer;">
                <input type="checkbox" id="share-can-edit-checkbox" onchange="toggleTaskPermission({{ $task->id }}, 'share_can_edit', this)" {{ $task->share_can_edit ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--accent-color);">
                Allow Editing (Edit Title, Description, etc.)
            </label>
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

        {{-- Premium Upload Dropzone (Multi-file) --}}
        <div id="dropzone" class="upload-dropzone" style="border: 2px dashed var(--border-color); padding: 30px; border-radius: 16px; text-align: center; background: var(--subtask-bg); transition: all 0.3s ease; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px;" onclick="document.getElementById('file-input').click()">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--accent-color); transition: transform 0.3s ease;"></i>
            <div id="dropzone-text" style="font-weight: 600; color: var(--text-main); font-family: var(--font-main);">Drag & Drop your files here or <span style="color: var(--accent-color);">Browse</span></div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Supports Images, Videos, PDFs, and Documents (Max 100MB per file, up to 10 files)</div>
            <input type="file" id="file-input" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" style="display: none;" onchange="uploadAttachments(this.files)">
        </div>

        {{-- Upload Progress Container --}}
        <div id="upload-progress-container" style="display: none; margin-bottom: 24px; display: none; flex-direction: column; gap: 8px;"></div>

        {{-- Dynamic Attachments Grid for images (Lightbox Gallery) --}}
        <div id="attachments-grid" style="display: {{ $task->attachments->where('is_image', true)->count() > 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 16px; margin-bottom: 20px;">
            @foreach($task->attachments->where('is_image', true) as $attachment)
                <div class="attachment-card image-card" id="attachment-{{ $attachment->id }}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; transition: all 0.3s ease;" onclick="openLightbox('{{ $attachment->file_url }}', 'image')">
                    <img src="{{ $attachment->file_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                    </div>
                    <button onclick="event.stopPropagation(); deleteAttachment({{ $attachment->id }})" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(239, 68, 68, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Delete file">
                        <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Dynamic Attachments Grid for videos --}}
        <div id="attachments-videos" style="display: {{ $task->attachments->where('is_video', true)->count() > 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px;">
            @foreach($task->attachments->where('is_video', true) as $attachment)
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
                            <a href="/attachments/{{ $attachment->id }}/download" class="btn-aqua" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; display: flex; align-items: center; gap: 4px; text-decoration: none; font-weight: 600;" title="Download">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                            </a>
                            <button onclick="deleteAttachment({{ $attachment->id }})" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; background: var(--overdue-color); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600;" title="Delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Other Files List --}}
        <div id="attachments-list" style="display: {{ $task->attachments->where('is_image', false)->where('is_video', false)->count() > 0 ? 'flex' : 'none' }}; flex-direction: column; gap: 12px;">
            @foreach($task->attachments->where('is_image', false)->where('is_video', false) as $attachment)
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
            @endforeach
        </div>
    </div>
  </div>

  {{-- Fullscreen Lightbox Overlay (supports images and videos) --}}
  <div id="lightbox" onclick="closeLightbox()" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 20000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; cursor: pointer;">
      <button onclick="closeLightbox()" style="position: absolute; top: 24px; right: 24px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.5rem; transition: all 0.3s ease; z-index: 20001;" title="Close">
          <i class="fa-solid fa-xmark"></i>
      </button>
      <img id="lightbox-img" src="" style="max-width: 90%; max-height: 85%; object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.3s ease; display: none;" onclick="event.stopPropagation()">
      <video id="lightbox-video" controls autoplay style="max-width: 90%; max-height: 85%; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95); transition: transform 0.3s ease; display: none;" onclick="event.stopPropagation()"></video>
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
            const files = dt.files;
            if (files && files.length > 0) uploadAttachments(files);
        }, false);
    }

    function uploadAttachments(files) {
        if (!files || files.length === 0) return;
        
        if (files.length > 10) {
            alert('You can only upload a maximum of 10 files at a time.');
            return;
        }

        const validFiles = [];
        const progressContainer = document.getElementById('upload-progress-container');
        progressContainer.innerHTML = '';
        progressContainer.style.display = 'flex';

        // Size check (100MB maximum per file)
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > 100 * 1024 * 1024) {
                alert(`File ${files[i].name} exceeds the 100MB limit and will be skipped.`);
            } else {
                validFiles.push(files[i]);
            }
        }

        if (validFiles.length === 0) {
            progressContainer.style.display = 'none';
            return;
        }

        const formData = new FormData();
        validFiles.forEach((file, index) => {
            formData.append('files[]', file);
            
            // Add progress bar for each file
            progressContainer.insertAdjacentHTML('beforeend', `
                <div class="upload-progress-item" id="upload-progress-${index}" style="background: var(--card-bg); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-file-arrow-up" style="color: var(--accent-color);"></i>
                    <div style="flex-grow: 1; min-width: 0;">
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;">${file.name}</div>
                        <div class="progress-bar-container" style="height: 6px; background: var(--bg-hover); border-radius: 3px; overflow: hidden;">
                            <div class="progress-bar-fill" style="width: 0%; height: 100%; background: var(--accent-color); transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                    <div class="progress-percentage" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); width: 40px; text-align: right;">0%</div>
                </div>
            `);
        });

        formData.append('_token', '{{ csrf_token() }}');

        const dropzone = document.getElementById('dropzone');
        const dropzoneText = dropzone.querySelector('#dropzone-text');
        const originalText = dropzoneText.innerHTML;
        dropzoneText.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Uploading ${validFiles.length} file(s)...`;
        dropzone.style.pointerEvents = 'none';

        axios.post('/tasks/{{ $task->id }}/attachments', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: function(progressEvent) {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                // Animate all progress bars (simplified, as we upload all together)
                validFiles.forEach((_, index) => {
                    const item = document.getElementById(`upload-progress-${index}`);
                    if (item) {
                        item.querySelector('.progress-bar-fill').style.width = `${percentCompleted}%`;
                        item.querySelector('.progress-percentage').textContent = `${percentCompleted}%`;
                    }
                });
            }
        })
        .then(response => {
            const attachments = response.data.data;
            attachments.forEach(attachment => {
                addAttachmentToUI(attachment);
            });
            
            // Hide progress container after a short delay
            setTimeout(() => {
                progressContainer.style.display = 'none';
                progressContainer.innerHTML = '';
            }, 2000);
        })
        .catch(error => {
            console.error(error);
            alert('Error uploading attachment: ' + (error.response?.data?.message || 'Unknown error'));
            progressContainer.style.display = 'none';
        })
        .finally(() => {
            // Restore dropzone state
            dropzoneText.innerHTML = originalText;
            dropzone.style.pointerEvents = 'auto';
            document.getElementById('file-input').value = '';
        });
    }

    function addAttachmentToUI(attachment) {
        // Prevent duplicate rendering
        if (document.getElementById(`attachment-${attachment.id}`)) return;

        if (attachment.is_image || (attachment.file_type && attachment.file_type.startsWith('image/'))) {
            const grid = document.getElementById('attachments-grid');
            if (grid) grid.style.display = 'grid';
            const imageCard = `
                <div class="attachment-card image-card" id="attachment-${attachment.id}" style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--border-color); cursor: pointer; opacity: 0; transform: translateY(10px); transition: all 0.4s ease;" onclick="openLightbox('${attachment.file_url}', 'image')">
                    <img src="${attachment.file_url}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="card-overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.45); opacity: 0; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                        <i class="fa-solid fa-magnifying-glass-plus" style="color: #fff; font-size: 1.4rem;"></i>
                    </div>
                    <button onclick="event.stopPropagation(); deleteAttachment(${attachment.id})" class="delete-attachment-btn" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(239, 68, 68, 0.95); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Delete file">
                        <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                    </button>
                </div>`;
            if (grid) grid.insertAdjacentHTML('beforeend', imageCard);
            setTimeout(() => {
                const el = document.getElementById(`attachment-${attachment.id}`);
                if(el) {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }
            }, 50);
        } else if (attachment.is_video || (attachment.file_type && attachment.file_type.startsWith('video/'))) {
            const grid = document.getElementById('attachments-videos');
            if (grid) grid.style.display = 'grid';
            const videoCard = `
                <div class="attachment-card video-card" id="attachment-${attachment.id}" style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); opacity: 0; transform: translateY(10px); transition: all 0.4s ease; background: #000;">
                    <video style="width: 100%; display: block; border-radius: 12px 12px 0 0; max-height: 220px; object-fit: cover;" preload="metadata" muted>
                        <source src="${attachment.file_url}" type="${attachment.file_type}">
                    </video>
                    <div class="video-play-overlay" onclick="openLightbox('${attachment.file_url}', 'video')" style="position: absolute; top: 0; left: 0; right: 0; bottom: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: rgba(0,0,0,0.25); transition: all 0.3s ease;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.3s ease;">
                            <i class="fa-solid fa-play" style="color: var(--accent-color); font-size: 1.2rem; margin-left: 3px;"></i>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--subtask-bg);">
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 600; color: var(--text-main); font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: var(--font-main);">${attachment.file_name}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-accent);">${attachment.formatted_size}</div>
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                            <a href="/attachments/${attachment.id}/download" class="btn-aqua" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; display: flex; align-items: center; gap: 4px; text-decoration: none; font-weight: 600;" title="Download">
                                <i class="fa-solid fa-cloud-arrow-down"></i>
                            </a>
                            <button onclick="deleteAttachment(${attachment.id})" style="padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; background: var(--overdue-color); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; gap: 4px; font-weight: 600;" title="Delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            if (grid) grid.insertAdjacentHTML('beforeend', videoCard);
            setTimeout(() => {
                const el = document.getElementById(`attachment-${attachment.id}`);
                if(el) {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }
            }, 50);
        } else {
            const list = document.getElementById('attachments-list');
            if (list) list.style.display = 'flex';
            let icon = 'fa-file';
            if (attachment.file_type && attachment.file_type.includes('pdf')) icon = 'fa-file-pdf';
            else if (attachment.file_type && (attachment.file_type.includes('word') || attachment.file_type.includes('document'))) icon = 'fa-file-word';
            else if (attachment.file_type && (attachment.file_type.includes('excel') || attachment.file_type.includes('sheet'))) icon = 'fa-file-excel';

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
            if (list) list.insertAdjacentHTML('beforeend', docRow);
            setTimeout(() => {
                const el = document.getElementById(`attachment-${attachment.id}`);
                if(el) {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }
            }, 50);
        }
    }

    function deleteAttachment(id) {
        if (!confirm('Are you sure you want to delete this attachment permanently?')) return;

        axios.delete(`/attachments/${id}`, {
            data: { _token: '{{ csrf_token() }}' }
        })
        .then(() => {
            removeAttachmentFromUI(id);
        })
        .catch(error => {
            console.error(error);
            alert('Error deleting attachment: ' + (error.response?.data?.message || 'Unknown error'));
        });
    }

    function removeAttachmentFromUI(id) {
        const el = document.getElementById(`attachment-${id}`);
        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'scale(0.9)';
            setTimeout(() => {
                el.remove();
                
                // Hide section grids/lists if empty
                const grid = document.getElementById('attachments-grid');
                if (grid && grid.querySelectorAll('.attachment-card').length === 0) grid.style.display = 'none';
                
                const vgrid = document.getElementById('attachments-videos');
                if (vgrid && vgrid.querySelectorAll('.attachment-card').length === 0) vgrid.style.display = 'none';
                
                const list = document.getElementById('attachments-list');
                if (list && list.querySelectorAll('.attachment-row').length === 0) list.style.display = 'none';
            }, 300);
        }
    }

    // Echo event receivers
    function handleSubtaskToggleFromEvent(subtask) {
        if (parseInt(subtask.task_id) !== parseInt('{{ $task->id }}')) return;
        const item = document.getElementById(`subtask-${subtask.id}`);
        if (!item) return;
        const checkbox = item.querySelector('.custom-checkbox');
        
        if (subtask.is_completed) {
            item.classList.add('completed');
            if (checkbox) checkbox.classList.add('checked');
        } else {
            item.classList.remove('completed');
            if (checkbox) checkbox.classList.remove('checked');
        }
        
        updateProgressBar();
    }

    function handleAttachmentUploadFromEvent(attachment) {
        if (parseInt(attachment.task_id) !== parseInt('{{ $task->id }}')) return;
        addAttachmentToUI(attachment);
    }

    function handleAttachmentDeleteFromEvent(attachmentId, taskId) {
        if (parseInt(taskId) !== parseInt('{{ $task->id }}')) return;
        removeAttachmentFromUI(attachmentId);
    }

    function updateTaskDetailsFromEvent(task) {
        if (parseInt(task.id) !== parseInt('{{ $task->id }}')) return;
        // Update Title
        const titleEl = document.querySelector('h1');
        if (titleEl) titleEl.innerText = task.title;

        // Update Category
        const catEl = document.querySelector('.task-catigory span');
        if (catEl) catEl.innerText = task.category_name || '';

        // Update Description
        const descEl = document.querySelector('.description-box');
        if (descEl) descEl.innerText = task.description || 'No description provided.';

        // Update Due Date
        const dateEl = document.querySelector('.fa-calendar').parentElement;
        if (dateEl) {
            dateEl.innerHTML = `<i class="fa-regular fa-calendar"></i> ${task.formatted_date}`;
        }
    }

    // ================== Lightbox Gallery Handlers ==================
    function openLightbox(src, type = 'image') {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        const video = document.getElementById('lightbox-video');
        
        if (lightbox) {
            if (type === 'image' && img) {
                img.src = src;
                img.style.display = 'block';
                video.style.display = 'none';
                video.src = '';
            } else if (type === 'video' && video) {
                video.src = src;
                video.style.display = 'block';
                img.style.display = 'none';
                img.src = '';
            }
            
            lightbox.style.display = 'flex';
            setTimeout(() => {
                lightbox.style.opacity = '1';
                if (type === 'image' && img) img.style.transform = 'scale(1)';
                if (type === 'video' && video) video.style.transform = 'scale(1)';
            }, 50);
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        const img = document.getElementById('lightbox-img');
        const video = document.getElementById('lightbox-video');
        
        if (lightbox) {
            lightbox.style.opacity = '0';
            if (img) img.style.transform = 'scale(0.95)';
            if (video) {
                video.style.transform = 'scale(0.95)';
                video.pause();
            }
            
            setTimeout(() => {
                lightbox.style.display = 'none';
                if (img) img.src = '';
                if (video) video.src = '';
            }, 300);
        }
    }

    function toggleTaskShare(id) {
        axios.post(`/tasks/${id}/share`, {
            _token: '{{ csrf_token() }}'
        })
        .then(response => {
            const data = response.data;
            const container = document.getElementById('share-link-container');
            const permissionsContainer = document.getElementById('share-permissions-container');
            const input = document.getElementById('share-url-input');
            const btn = document.getElementById('toggle-share-btn');
            
            if (data.shared) {
                input.value = data.share_url;
                container.style.display = 'flex';
                permissionsContainer.style.display = 'flex';
                btn.innerText = 'Disable Link';
                btn.style.background = 'var(--overdue-color)';
                document.getElementById('share-can-complete-checkbox').checked = false;
                document.getElementById('share-can-edit-checkbox').checked = false;
            } else {
                container.style.display = 'none';
                permissionsContainer.style.display = 'none';
                input.value = '';
                btn.innerText = 'Enable Link';
                btn.style.background = 'var(--accent-color)';
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error toggling task share settings');
        });
    }

    function toggleTaskPermission(id, permissionType, checkbox) {
        checkbox.disabled = true;
        
        const params = {
            _token: '{{ csrf_token() }}'
        };
        params[permissionType] = checkbox.checked ? 1 : 0;

        axios.post(`/tasks/${id}/share`, params)
        .then(response => {
            // Updated successfully
        })
        .catch(error => {
            console.error(error);
            alert('Error updating share permissions');
            checkbox.checked = !checkbox.checked; // revert
        })
        .finally(() => {
            checkbox.disabled = false;
        });
    }

    function copyShareUrl() {
        const input = document.getElementById('share-url-input');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value)
            .then(() => {
                alert('Copied share link to clipboard!');
            })
            .catch(err => {
                alert('Failed to copy: ' + err);
            });
    }
  </script>
@endsection
