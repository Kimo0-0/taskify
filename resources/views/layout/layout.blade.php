<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/MCT.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/all.min.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body class="@yield('body-class')">
    @include('layout.sidebar')
    <div class="page">
        @include('layout.navbar')
        @yield('content')
    </div>
    <button class="fab-add lt-768" onclick="toggleAddForm()"><i class="fa-solid fa-plus"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.16.0/dist/axios.min.js"></script>
    <script src="/js/MCT.js"></script>

    {{-- Global Add Task Modal --}}
    <div class="add_Task_Form">
        <form onsubmit="storeTask(); return false;" id="addTaskForm" class="add_Task">
            @csrf
            <h2 class="form-title">Create New Task</h2>
            <button type="button" onclick="toggleAddForm()" class="close-btn"><i class="fa-solid fa-xmark"></i></button>

            <label>Task Title</label>
            <input type="text" id="task-title" name="title" placeholder="What needs to be done?">

            <label>Description</label>
            <textarea id="task-description" name="description" rows="3" placeholder="Add details..."></textarea>

            <div class="subtasks-section">
                <label>Subtasks</label>
                <div class="add-subtask" style="display: flex; gap: 8px; margin-top: 8px;">
                    <input type="text" id="subtask-input" style="flex-grow: 1;" placeholder="Add a subtask">
                    <button type="button" id="add-subtask-btn" class="btn-aqua"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div id="subtask-list" style="margin-top: 12px;"></div>
            </div>

            <div class="task-form-unique-grid">
                <div>
                    <label>Due Date</label>
                    <input type="datetime-local" name="due_date" id="task-due" style="width: 100%;">
                </div>
                <div>
                    <label>Priority</label>
                    <select name="priority" id="task-priority" style="width: 100%;">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <label>Category</label>
            <select name="category_id" id="category_id">
                <option value="" selected>Select Category (No Category)</option>
                @php $global_categories = \App\Models\Category::all(); @endphp
                @foreach ($global_categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            {{-- File Attachments --}}
            <label style="margin-top: 8px;">Attachments <span style="color: var(--text-muted); font-weight: 400; font-size: 0.8rem;">(optional)</span></label>
            <div id="create-dropzone" onclick="document.getElementById('create-file-input').click()" style="border: 2px dashed var(--border-color); border-radius: 12px; padding: 18px 12px; text-align: center; cursor: pointer; background: var(--subtask-bg); transition: all 0.25s ease; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.6rem; color: var(--accent-color);"></i>
                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">Drag & Drop or <span style="color: var(--accent-color);">Browse</span></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Images, Videos, PDFs & more (max 100MB)</div>
                <input type="file" id="create-file-input" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" style="display: none;" onchange="previewCreateFiles(this.files)">
            </div>
            <div id="create-file-preview" style="display: none; flex-direction: column; gap: 6px; margin-top: 8px;"></div>

            <button type="button" onclick="storeTask()" class="btn-aqua" style="justify-content: center; margin-top: 16px;">Add Task</button>
        </form>
    </div>

    <script>
      function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
      }

      function toggleAddForm() {
          const form = document.querySelector('.add_Task_Form');
          if (form) form.classList.toggle('active');
      }

      // Paste Clipboard Images support (Ctrl+V)
      document.addEventListener('paste', function(e) {
          const items = (e.clipboardData || e.originalEvent.clipboardData).items;
          const files = [];
          for (let i = 0; i < items.length; i++) {
              if (items[i].kind === 'file') {
                  const blob = items[i].getAsFile();
                  if (blob) {
                      // Give it a default filename if it doesn't have one (e.g. pasted image)
                      const ext = blob.type.split('/')[1] || 'png';
                      const filename = `pasted-image-${Date.now()}.${ext}`;
                      const file = new File([blob], filename, { type: blob.type });
                      files.push(file);
                  }
              }
          }

          if (files.length > 0) {
              const addTaskModal = document.querySelector('.add_Task_Form:not([id])');
              const isAddTaskActive = addTaskModal && addTaskModal.classList.contains('active');

              if (isAddTaskActive) {
                  e.preventDefault();
                  if (typeof previewCreateFiles === 'function') {
                      previewCreateFiles(files);
                  }
              } else if (typeof uploadAttachments === 'function') {
                  // Ensure no other modal is open to avoid uploading when editing profile, etc.
                  const anyModalOpen = Array.from(document.querySelectorAll('.add_Task_Form, .update_Task_Form'))
                      .some(m => m.classList.contains('active'));
                  if (!anyModalOpen) {
                      e.preventDefault();
                      uploadAttachments(files);
                  }
              }
          }
      });

      // Subtasks Logic for Global Modal
      document.addEventListener('click', function(e) {
          if (e.target && e.target.id === 'add-subtask-btn' || e.target.closest('#add-subtask-btn')) {
              addSubtaskToList("subtask-input", "subtask-list");
          }
      });

      function addSubtaskToList(inputId, listId) {
          const input = document.getElementById(inputId);
          const list = document.getElementById(listId);
          if (!input || !list) return;
          const title = input.value.trim();
          if (!title) return;

          list.insertAdjacentHTML("beforeend", `
              <div class="subtask-item" data-title="${title}">
                  <span>${title}</span>
                  <button type="button" class="task-action delete" onclick="this.parentElement.remove()">
                      <i class="fa-regular fa-trash-can"></i>
                  </button>
              </div>
          `);
          input.value = '';
      }

      function getSubtasks(listId) {
          const subtasks = [];
          document.querySelectorAll(`#${listId} .subtask-item`).forEach(item => {
              subtasks.push(item.dataset.title);
          });
          return subtasks;
      }

      // ---- Create Task File Preview ----
      let createSelectedFiles = [];

      const createDropzone = document.getElementById('create-dropzone');
      if (createDropzone) {
          ['dragenter', 'dragover'].forEach(ev => {
              createDropzone.addEventListener(ev, e => {
                  e.preventDefault();
                  createDropzone.style.borderColor = 'var(--accent-color)';
                  createDropzone.style.background = 'var(--bg-hover)';
              });
          });
          ['dragleave', 'drop'].forEach(ev => {
              createDropzone.addEventListener(ev, e => {
                  e.preventDefault();
                  createDropzone.style.borderColor = 'var(--border-color)';
                  createDropzone.style.background = 'var(--subtask-bg)';
              });
          });
          createDropzone.addEventListener('drop', e => {
              if (e.dataTransfer.files.length > 0) previewCreateFiles(e.dataTransfer.files);
          });
      }

      function previewCreateFiles(files) {
          const preview = document.getElementById('create-file-preview');
          if (!files || !preview) return;

          Array.from(files).forEach(file => {
              if (createSelectedFiles.length >= 10) return;
              if (file.size > 100 * 1024 * 1024) {
                  alert(`${file.name} exceeds 100MB limit.`);
                  return;
              }
              createSelectedFiles.push(file);
          });

          renderCreateFilePreviews();
      }

      function renderCreateFilePreviews() {
          const preview = document.getElementById('create-file-preview');
          if (!preview) return;
          if (createSelectedFiles.length === 0) {
              preview.style.display = 'none';
              preview.innerHTML = '';
              return;
          }
          preview.style.display = 'flex';
          preview.innerHTML = createSelectedFiles.map((file, i) => {
              const isImg = file.type.startsWith('image/');
              const icon = file.type.includes('pdf') ? 'fa-file-pdf' :
                           file.type.includes('word') || file.type.includes('document') ? 'fa-file-word' :
                           file.type.includes('excel') || file.type.includes('sheet') ? 'fa-file-excel' :
                           file.type.startsWith('video/') ? 'fa-file-video' : 'fa-file';
              const size = file.size >= 1048576 ? (file.size/1048576).toFixed(1)+' MB' :
                           file.size >= 1024 ? (file.size/1024).toFixed(0)+' KB' : file.size+' B';
              return `
                  <div style="display:flex; align-items:center; gap:10px; background:var(--subtask-bg); border:1px solid var(--border-color); border-radius:8px; padding:8px 12px;">
                      <i class="fa-solid ${icon}" style="color:var(--accent-color); font-size:1.2rem; flex-shrink:0;"></i>
                      <div style="flex-grow:1; min-width:0;">
                          <div style="font-size:0.82rem; font-weight:600; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${file.name}</div>
                          <div style="font-size:0.72rem; color:var(--text-muted);">${size}</div>
                      </div>
                      <button type="button" onclick="removeCreateFile(${i})" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:2px 6px; border-radius:6px; transition:color 0.2s;" title="Remove">
                          <i class="fa-solid fa-xmark"></i>
                      </button>
                  </div>`;
          }).join('');
      }

      function removeCreateFile(index) {
          createSelectedFiles.splice(index, 1);
          renderCreateFilePreviews();
      }

      function storeTask() {
          const btn = document.querySelector('#addTaskForm .btn-aqua');
          const originalText = btn ? btn.innerHTML : '';
          if (btn) { btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...'; btn.disabled = true; }

          const taskData = {
              title: document.getElementById("task-title").value,
              description: document.getElementById("task-description").value,
              due_date: document.getElementById("task-due").value,
              priority: document.getElementById("task-priority").value,
              category_id: document.getElementById("category_id").value || null,
              subtasks: getSubtasks("subtask-list"),
              _token: '{{ csrf_token() }}',
          };

          axios.post("/tasks", taskData)
            .then((response) => {
                const taskId = response.data.data?.id || response.data.id;

                // Upload attachments if any
                const uploadPromise = (createSelectedFiles.length > 0 && taskId)
                    ? (() => {
                        const formData = new FormData();
                        createSelectedFiles.forEach(f => formData.append('files[]', f));
                        formData.append('_token', '{{ csrf_token() }}');
                        if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading files...';
                        return axios.post(`/tasks/${taskId}/attachments`, formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });
                    })()
                    : Promise.resolve();

                uploadPromise.finally(() => {
                    // Reset form
                    document.getElementById("addTaskForm").reset();
                    document.getElementById("subtask-list").innerHTML = '';
                    createSelectedFiles = [];
                    renderCreateFilePreviews();

                    if (btn) { btn.innerHTML = originalText; btn.disabled = false; }

                    // Update Total Counter if exists
                    const totalCount = document.getElementById("total-tasks-count");
                    if (totalCount) {
                        let current = parseInt(totalCount.innerText) || 0;
                        totalCount.innerText = current + 1;
                    }

                    toggleAddForm();

                    // Clear filters
                    const searchEl = document.getElementById('search-tasks');
                    const catEl = document.getElementById('filter-category');
                    const priEl = document.getElementById('filter-priority');
                    const statEl = document.getElementById('filter-status');
                    if (searchEl) searchEl.value = '';
                    if (catEl) catEl.value = '';
                    if (priEl) priEl.value = '';
                    if (statEl) statEl.value = '';

                    if (typeof refreshTaskList === 'function') {
                        refreshTaskList();
                    } else {
                        location.reload();
                    }
                });
            })
            .catch((error) => {
                if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
                alert("Error adding task: " + (error.response?.data?.message || 'Unknown error'));
            });
      }

      // Theme Toggle Logic
      function updateThemeUI() {
          const isDark = document.documentElement.classList.contains('dark-mode');
          const toggleIcon = document.getElementById('theme-toggle-icon');
          if (toggleIcon) {
              if (isDark) {
                  toggleIcon.className = 'fa-solid fa-sun';
                  toggleIcon.style.color = '#f59e0b';
              } else {
                  toggleIcon.className = 'fa-regular fa-moon';
                  toggleIcon.style.color = 'var(--text-muted)';
              }
          }
      }

      function toggleTheme() {
          const docEl = document.documentElement;
          docEl.classList.toggle('dark-mode');

          const isDark = docEl.classList.contains('dark-mode');
          localStorage.setItem('theme', isDark ? 'dark' : 'light');
          updateThemeUI();
      }

      // Initialize Theme UI on DOMContentLoaded
      document.addEventListener('DOMContentLoaded', updateThemeUI);

      // --- Profile Modal Logic ---
      function openProfileModal() {
          const form = document.getElementById('profileModal');
          if (form) form.classList.add('active');
      }

      function closeProfileModal() {
          const form = document.getElementById('profileModal');
          if (form) form.classList.remove('active');
      }

      function previewProfileImage(event) {
          const file = event.target.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  document.getElementById('profile-preview-img').src = e.target.result;
              }
              reader.readAsDataURL(file);
          }
      }

      function updateProfile() {
          const form = document.getElementById('updateProfileForm');
          const formData = new FormData(form);

          axios.post("/profile/update", formData)
            .then((response) => {
                if (response.data.success) {
                    // Update Sidebar instantly
                    const sidebarName = document.getElementById('sidebar-profile-name');
                    const sidebarImg = document.getElementById('sidebar-profile-img');
                    
                    if(sidebarName) sidebarName.textContent = response.data.name;
                    if(sidebarImg) sidebarImg.src = response.data.profile_image_url;

                    closeProfileModal();
                }
            })
            .catch((error) => {
                alert("Error updating profile: " + (error.response?.data?.message || 'Unknown error'));
            });
      }

      function toggleProfileShare(button) {
          button.disabled = true;
          button.innerText = 'Processing...';

          axios.post('/profile/share', {
              _token: '{{ csrf_token() }}'
          })
          .then(response => {
              const data = response.data;
              const container = document.getElementById('profile-share-url-container');
              const permissionsContainer = document.getElementById('profile-share-permissions');
              const input = document.getElementById('profile-share-url-input');
              
              if (data.shared) {
                  input.value = data.share_url;
                  container.style.display = 'flex';
                  permissionsContainer.style.display = 'flex';
                  button.innerText = 'Disable';
                  button.style.background = 'var(--overdue-color)';
                  button.dataset.shared = 'true';
                  document.getElementById('profile-can-complete-checkbox').checked = false;
                  document.getElementById('profile-can-edit-checkbox').checked = false;
              } else {
                  container.style.display = 'none';
                  permissionsContainer.style.display = 'none';
                  input.value = '';
                  button.innerText = 'Enable';
                  button.style.background = 'var(--accent-color)';
                  button.dataset.shared = 'false';
              }
          })
          .catch(error => {
              console.error(error);
              alert('Error toggling profile share settings');
          })
          .finally(() => {
              button.disabled = false;
          });
      }

      function toggleProfilePermission(permissionType, checkbox) {
          checkbox.disabled = true;
          
          const params = {
              _token: '{{ csrf_token() }}'
          };
          params[permissionType] = checkbox.checked ? 1 : 0;

          axios.post('/profile/share', params)
          .then(response => {
              // Updated successfully
          })
          .catch(error => {
              console.error(error);
              alert('Error updating profile share permissions');
              checkbox.checked = !checkbox.checked; // revert
          })
          .finally(() => {
              checkbox.disabled = false;
          });
      }

      function copyProfileShareLink(button) {
          const input = document.getElementById('profile-share-url-input');
          input.select();
          input.setSelectionRange(0, 99999);
          navigator.clipboard.writeText(input.value)
              .then(() => {
                  alert('Copied profile share link to clipboard!');
              })
              .catch(err => {
                  alert('Failed to copy: ' + err);
              });
      }
    </script>

    {{-- Profile Edit Modal --}}
    <div class="add_Task_Form" id="profileModal">
        <form onsubmit="updateProfile(); return false;" id="updateProfileForm" class="add_Task" enctype="multipart/form-data">
            @csrf
            <h2 class="form-title">Edit Profile</h2>
            <button type="button" onclick="closeProfileModal()" class="close-btn"><i class="fa-solid fa-xmark"></i></button>

            <div style="display: flex; flex-direction: column; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid var(--accent-color); position: relative; cursor: pointer;" onclick="document.getElementById('profile_image_input').click()">
                    <img id="profile-preview-img" src="{{ Auth::user()->profile_image_url }}" alt="Profile Preview" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5); padding: 4px; text-align: center; color: white;">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <input type="file" id="profile_image_input" name="profile_image" accept="image/*" style="display: none;" onchange="previewProfileImage(event)">
            </div>

            <label>Name</label>
            <input type="text" name="name" value="{{ Auth::user()->name }}" required>

            <div style="border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div>
                        <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">
                            <i class="fa-solid fa-share-nodes" style="color: var(--accent-color);"></i> Share All My Tasks
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Allow anyone with the link to view all your tasks.</div>
                    </div>
                    <button type="button" onclick="toggleProfileShare(this)" class="btn-aqua" style="padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; background: {{ Auth::user()->share_token ? 'var(--overdue-color)' : 'var(--accent-color)' }}; color: #fff; border: none; cursor: pointer; transition: all 0.2s;" data-shared="{{ Auth::user()->share_token ? 'true' : 'false' }}">
                        {{ Auth::user()->share_token ? 'Disable' : 'Enable' }}
                    </button>
                </div>
                
                <div id="profile-share-url-container" style="display: {{ Auth::user()->share_token ? 'flex' : 'none' }}; align-items: center; gap: 8px; background: var(--subtask-bg); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 10px; font-size: 0.8rem; width: 100%; box-sizing: border-box;">
                    <input type="text" id="profile-share-url-input" value="{{ Auth::user()->share_url }}" readonly style="background: none; border: none; color: var(--text-main); width: 100%; outline: none; font-size: 0.8rem;" onclick="this.select()">
                    <button type="button" onclick="copyProfileShareLink(this)" style="background: none; border: none; color: var(--accent-color); cursor: pointer; padding: 2px;" title="Copy Link">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>

                {{-- Profile Share Permissions --}}
                <div id="profile-share-permissions" style="display: {{ Auth::user()->share_token ? 'flex' : 'none' }}; flex-direction: column; gap: 8px; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Link Permissions:</div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                        <input type="checkbox" id="profile-can-complete-checkbox" onchange="toggleProfilePermission('share_can_complete', this)" {{ Auth::user()->share_can_complete ? 'checked' : '' }} style="width: 14px; height: 14px; accent-color: var(--accent-color);">
                        Allow Completion (Complete Subtasks & Tasks)
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                        <input type="checkbox" id="profile-can-edit-checkbox" onchange="toggleProfilePermission('share_can_edit', this)" {{ Auth::user()->share_can_edit ? 'checked' : '' }} style="width: 14px; height: 14px; accent-color: var(--accent-color);">
                        Allow Editing (Edit Titles, Descriptions, etc.)
                    </label>
                </div>
            </div>

            <button type="button" onclick="updateProfile()" class="btn-aqua" style="justify-content: center; margin-top: 16px;">Save Changes</button>
        </form>
    </div>

    @auth
    <!-- Live Sync WebSocket Client -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY', 'task-manager-key-secret') }}',
            wsHost: '{{ env('REVERB_HOST', 'localhost') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: {{ env('REVERB_SCHEME', 'http') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        });

        // Listen on the user's private channel
        window.Echo.private('user.{{ Auth::id() }}')
            .listen('.task.created', (e) => {
                console.log('Task created in another tab:', e.task);
                if (typeof refreshTaskList === 'function') {
                    refreshTaskList();
                }
            })
            .listen('.task.updated', (e) => {
                console.log('Task updated in another tab:', e.task);
                if (typeof refreshTaskList === 'function') {
                    refreshTaskList();
                }
                // Update active task details page if currently opened
                if (typeof updateTaskDetailsFromEvent === 'function') {
                    updateTaskDetailsFromEvent(e.task);
                }
            })
            .listen('.task.deleted', (e) => {
                console.log('Task deleted in another tab:', e.task_id);
                if (typeof refreshTaskList === 'function') {
                    refreshTaskList();
                }
                // Redirect to dashboard if viewing deleted task
                if (window.location.pathname === `/task/${e.task_id}`) {
                    alert('This task has been deleted in another tab.');
                    window.location.href = '/Dashboard';
                }
            })
            .listen('.subtask.toggled', (e) => {
                console.log('Subtask toggled in another tab:', e.subtask);
                if (typeof refreshTaskList === 'function') {
                    refreshTaskList();
                }
                if (typeof handleSubtaskToggleFromEvent === 'function') {
                    handleSubtaskToggleFromEvent(e.subtask);
                }
            })
            .listen('.attachment.uploaded', (e) => {
                console.log('Attachment uploaded in another tab:', e.attachment);
                if (typeof handleAttachmentUploadFromEvent === 'function') {
                    handleAttachmentUploadFromEvent(e.attachment);
                }
            })
            .listen('.attachment.deleted', (e) => {
                console.log('Attachment deleted in another tab:', e.attachment_id);
                if (typeof handleAttachmentDeleteFromEvent === 'function') {
                    handleAttachmentDeleteFromEvent(e.attachment_id, e.task_id);
                }
            });
    </script>
    @endauth

</body>
</html>
