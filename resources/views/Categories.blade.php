@extends('layout.layout')
@section('title', 'Categories')

@section($activeNav ?? 'Categories_nav', 'active')

@section('CustomCss')
  #create-task-btn { display: none !important; }
  .category-container {
      display: grid;
      grid-template-columns: 1fr 350px;
      gap: 36px;
      margin: 36px;
  }
  @media (max-width: 768px) {
      .category-container {
          grid-template-columns: 1fr;
          margin: 16px;
          gap: 20px;
      }
  }
  .category-panel {
      background: var(--card-bg);
      padding: 32px;
      border-radius: 20px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  .category-card {
      background: var(--border-color);
      padding: 16px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: 1px solid var(--border-color);
      transition: background-color 0.3s ease, border-color 0.3s ease;
  }
  .category-input {
      width: 100%;
      padding: 12px;
      border: 1px solid var(--border-color);
      background: var(--input-bg);
      color: var(--text-main);
      border-radius: 8px;
      font-family: var(--font-main);
      font-size: 1rem;
      box-sizing: border-box;
      transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
  }
@endsection

@section('content')
  <div style="margin: 24px 36px 0;">
      <h1 style="font-family: var(--font-accent); font-size: 1.8rem; margin: 0; color: var(--text-main);">
          Category Management
      </h1>
  </div>

  <div class="category-container">
      {{-- Categories List --}}
      <div class="category-panel">
          <div id="categories-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
              @foreach ($categories as $category)
                  <div class="category-card" id="category-{{ $category->id }}">
                      <span style="font-weight: 600; color: var(--text-main);">{{ $category->name }}</span>
                      <button class="task-action delete" onclick="deleteCategory({{ $category->id }})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none; padding: 0;">
                          <i class="fa-regular fa-trash-can"></i>
                      </button>
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
            <div class="category-card" id="category-${category.id}">
                <span style="font-weight: 600; color: var(--text-main);">${category.name}</span>
                <button class="task-action delete" onclick="deleteCategory(${category.id})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none; padding: 0;">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
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
  </script>
@endsection
