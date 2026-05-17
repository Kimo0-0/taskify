<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="/css/MCT.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/all.min.css">
    <style>
      @yield('CustomCss');
    </style>
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
<body>
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
                <option disabled selected>Select Category</option>
                @php $global_categories = \App\Models\Category::all(); @endphp
                @foreach ($global_categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

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

      function storeTask() {
          const taskData = {
              title: document.getElementById("task-title").value,
              description: document.getElementById("task-description").value,
              due_date: document.getElementById("task-due").value,
              priority: document.getElementById("task-priority").value,
              category_id: document.getElementById("category_id").value,
              subtasks: getSubtasks("subtask-list"),
              _token: '{{ csrf_token() }}',
          };

          axios.post("/tasks", taskData)
            .then((response) => {
                const task = response.data.data;
                // If we are on a page that has a task list, add it
                const taskList = document.querySelector(".tasks-list");
                if (taskList) {
                    if (typeof buildTaskHtml === "function") {
                        taskList.insertAdjacentHTML("afterbegin", buildTaskHtml(task));
                    } else {
                        location.reload(); // Fallback if buildTaskHtml isn't defined
                    }
                }

                document.getElementById("addTaskForm").reset();
                document.getElementById("subtask-list").innerHTML = '';

                // Update Total Counter if exists
                const totalCount = document.getElementById("total-tasks-count");
                if (totalCount) {
                    let current = parseInt(totalCount.innerText) || 0;
                    totalCount.innerText = current + 1;
                }

                toggleAddForm();
            })
            .catch((error) => {
                alert("Error adding task: " + (error.response.data.message || 'Unknown error'));
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
    </script>
</body>
</html>
