@extends('layout.layout')
@section('title', 'Categories')

@section($activeNav ?? 'Categories_nav', 'active')

@section('body-class', 'page-categories')

@section('content')
  <div style="margin: 24px 36px 0;">
      <h1 style="font-family: var(--font-accent); font-size: 1.8rem; margin: 0; color: var(--text-main);">
          Category Management
      </h1>
  </div>

  <div class="category-container">
      {{-- Categories List --}}
      <div class="category-panel">
           <div id="categories-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
              @foreach ($categories as $category)
                   @php
                       $share = $shares->get($category->id);
                       $shareToken = $share ? $share->share_token : null;
                       $shareUrl = $share ? $share->share_url : '';
                   @endphp
                   <div class="category-card" id="category-{{ $category->id }}" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 20px; background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.01);">
                       <div style="display: flex; justify-content: space-between; align-items: center;">
                           <span style="font-weight: 600; color: var(--text-main); font-size: 1.05rem;">{{ $category->name }}</span>
                           <button class="task-action delete" onclick="deleteCategory({{ $category->id }})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none; padding: 0;" title="Delete Category">
                               <i class="fa-regular fa-trash-can"></i>
                           </button>
                       </div>
                       
                       <div style="border-top: 1px solid var(--border-color); padding-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                           <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                               <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                                   <i class="fa-solid fa-share-nodes"></i> Public Share
                               </span>
                               <button onclick="toggleCategoryShare({{ $category->id }}, this)" class="btn-aqua" style="padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; background: {{ $shareToken ? 'var(--overdue-color)' : 'var(--accent-color)' }}; color: #fff; border: none; cursor: pointer; transition: all 0.2s;" data-shared="{{ $shareToken ? 'true' : 'false' }}">
                                   {{ $shareToken ? 'Disable' : 'Enable' }}
                               </button>
                           </div>
                           
                           <div class="share-url-container" style="display: {{ $shareToken ? 'flex' : 'none' }}; align-items: center; gap: 6px; background: var(--subtask-bg); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 8px; font-size: 0.8rem; width: 100%; box-sizing: border-box;">
                               <input type="text" class="category-share-input" value="{{ $shareUrl }}" readonly style="background: none; border: none; color: var(--text-main); width: 100%; outline: none; font-size: 0.75rem;" onclick="this.select()">
                               <button onclick="copyCategoryLink(this)" style="background: none; border: none; color: var(--accent-color); cursor: pointer; padding: 2px;" title="Copy Link">
                                   <i class="fa-regular fa-copy"></i>
                               </button>
                           </div>

                           {{-- Category Share Permissions --}}
                           <div class="category-permissions-container" style="display: {{ $shareToken ? 'flex' : 'none' }}; flex-direction: column; gap: 6px; margin-top: 4px; border-top: 1px dashed var(--border-color); padding-top: 6px;">
                               <label style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-main); cursor: pointer;">
                                   <input type="checkbox" class="cat-can-complete-checkbox" onchange="toggleCategoryPermission({{ $category->id }}, 'can_complete', this)" {{ $share && $share->can_complete ? 'checked' : '' }} style="width: 12px; height: 12px; accent-color: var(--accent-color);">
                                   Allow Completion (Complete Subtasks & Tasks)
                               </label>
                               <label style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-main); cursor: pointer;">
                                   <input type="checkbox" class="cat-can-edit-checkbox" onchange="toggleCategoryPermission({{ $category->id }}, 'can_edit', this)" {{ $share && $share->can_edit ? 'checked' : '' }} style="width: 12px; height: 12px; accent-color: var(--accent-color);">
                                   Allow Editing (Edit Titles, Descriptions, etc.)
                               </label>
                           </div>
                       </div>
                   </div>
              @endforeach
          </div>
      </div>

      {{-- Add Category Form --}}
      <div class="category-panel" style="height: fit-content;">
          <h2 style="font-family: var(--font-accent); font-size: 1.2rem; margin: 0 0 20px 0; color: var(--text-main);">Add New Category</h2>
          <form onsubmit="event.preventDefault(); addCategory();" style="display: flex; flex-direction: column; gap: 16px;">
              @csrf
              <div>
                  <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: var(--text-main);">Category Name</label>
                  <input type="text" id="category-name" class="category-input" placeholder="e.g. Work, Personal" required>
              </div>
              <button type="submit" class="btn-aqua" style="justify-content: center; width: 100%;">
                  <i class="fa-solid fa-plus"></i> Create Category
              </button>
          </form>
      </div>
  </div>

  <script>
    function addCategory() {
      const nameInput = document.getElementById("category-name");
      const name = nameInput.value;

      axios.post("/categories", {
          name: name,
          _token: document.querySelector('input[name="_token"]').value,
        })
        .then((response) => {
          const category = response.data.data;
          const html = `
            <div class="category-card" id="category-${category.id}" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 20px; background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.01);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: var(--text-main); font-size: 1.05rem;">${category.name}</span>
                    <button class="task-action delete" onclick="deleteCategory(${category.id})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none; padding: 0;" title="Delete Category">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
                
                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                            <i class="fa-solid fa-share-nodes"></i> Public Share
                        </span>
                        <button onclick="toggleCategoryShare(${category.id}, this)" class="btn-aqua" style="padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; background: var(--accent-color); color: #fff; border: none; cursor: pointer; transition: all 0.2s;" data-shared="false">
                            Enable
                        </button>
                    </div>
                    
                    <div class="share-url-container" style="display: none; align-items: center; gap: 6px; background: var(--subtask-bg); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 8px; font-size: 0.8rem; width: 100%; box-sizing: border-box;">
                        <input type="text" class="category-share-input" value="" readonly style="background: none; border: none; color: var(--text-main); width: 100%; outline: none; font-size: 0.75rem;" onclick="this.select()">
                        <button onclick="copyCategoryLink(this)" style="background: none; border: none; color: var(--accent-color); cursor: pointer; padding: 2px;" title="Copy Link">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>

                    {{-- Category Share Permissions --}}
                    <div class="category-permissions-container" style="display: none; flex-direction: column; gap: 6px; margin-top: 4px; border-top: 1px dashed var(--border-color); padding-top: 6px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-main); cursor: pointer;">
                            <input type="checkbox" class="cat-can-complete-checkbox" onchange="toggleCategoryPermission(${category.id}, 'can_complete', this)" style="width: 12px; height: 12px; accent-color: var(--accent-color);">
                            Allow Completion (Complete Subtasks & Tasks)
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: var(--text-main); cursor: pointer;">
                            <input type="checkbox" class="cat-can-edit-checkbox" onchange="toggleCategoryPermission(${category.id}, 'can_edit', this)" style="width: 12px; height: 12px; accent-color: var(--accent-color);">
                            Allow Editing (Edit Titles, Descriptions, etc.)
                        </label>
                    </div>
                </div>
            </div>`;
          document.getElementById("categories-list").insertAdjacentHTML("beforeend", html);
          nameInput.value = '';
        })
        .catch((error) => {
          alert("Error adding category");
        });
    }

    function deleteCategory(id) {
        if(!confirm('Are you sure? This will un-categorize tasks in this category.')) return;

        axios.delete(`/categories/${id}`, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
        })
        .then(() => {
            document.getElementById(`category-${id}`).remove();
        })
        .catch((error) => {
            alert("Error deleting category");
        });
    }

    function toggleCategoryShare(id, button) {
        axios.post(`/categories/${id}/share`, {
            _token: document.querySelector('input[name="_token"]').value
        })
        .then(response => {
            const data = response.data;
            const card = document.getElementById(`category-${id}`);
            const container = card.querySelector('.share-url-container');
            const permissionsContainer = card.querySelector('.category-permissions-container');
            const input = card.querySelector('.category-share-input');
            
            if (data.shared) {
                input.value = data.share_url;
                container.style.display = 'flex';
                permissionsContainer.style.display = 'flex';
                button.innerText = 'Disable';
                button.style.background = 'var(--overdue-color)';
                button.dataset.shared = 'true';
                card.querySelector('.cat-can-complete-checkbox').checked = false;
                card.querySelector('.cat-can-edit-checkbox').checked = false;
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
            alert('Error toggling category share settings');
        });
    }

    function toggleCategoryPermission(id, permissionType, checkbox) {
        checkbox.disabled = true;
        
        const params = {
            _token: document.querySelector('input[name="_token"]').value
        };
        params[permissionType] = checkbox.checked ? 1 : 0;

        axios.post(`/categories/${id}/share`, params)
        .then(response => {
            // Updated successfully
        })
        .catch(error => {
            console.error(error);
            alert('Error updating category share permissions');
            checkbox.checked = !checkbox.checked; // revert
        })
        .finally(() => {
            checkbox.disabled = false;
        });
    }

    function copyCategoryLink(button) {
        const container = button.closest('.share-url-container');
        const input = container.querySelector('.category-share-input');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value)
            .then(() => {
                alert('Copied category share link to clipboard!');
            })
            .catch(err => {
                alert('Failed to copy: ' + err);
            });
    }
  </script>
@endsection
