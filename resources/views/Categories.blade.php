@extends('layout.layout')
@section('title', 'Categories')

@section($activeNav ?? 'Categories_nav', 'active')

@section('CustomCss')
  #create-task-btn { display: none !important; }
@endsection

@section('content')
  <div style="margin: 24px 36px 0;">
      <h1 style="font-family: var(--font-accent); font-size: 1.8rem; margin: 0; color: var(--text-main);">
          Category Management
      </h1>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 350px; gap: 36px; margin: 36px;">
      {{-- Categories List --}}
      <div style="background: #fff; padding: 32px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
          <div id="categories-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
              @foreach ($categories as $category)
                  <div class="category-card" id="category-{{ $category->id }}" style="background: #ececec; padding: 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e5e7eb;">
                      <span style="font-weight: 600; color: var(--text-main);">{{ $category->name }}</span>
                      <button class="task-action delete" onclick="deleteCategory({{ $category->id }})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none;">
                          <i class="fa-regular fa-trash-can"></i>
                      </button>
                  </div>
              @endforeach
          </div>
      </div>

      {{-- Add Category Form --}}
      <div style="background: #fff; padding: 32px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: fit-content;">
          <h2 style="font-family: var(--font-accent); font-size: 1.2rem; margin: 0 0 20px 0;">Add New Category</h2>
          <form onsubmit="event.preventDefault(); addCategory();" style="display: flex; flex-direction: column; gap: 16px;">
              @csrf
              <div>
                  <label style="display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Category Name</label>
                  <input type="text" id="category-name" placeholder="e.g. Work, Personal" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;">
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
            <div class="category-card" id="category-${category.id}" style="background: #ececec; padding: 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e5e7eb;">
                <span style="font-weight: 600; color: var(--text-main);">${category.name}</span>
                <button class="task-action delete" onclick="deleteCategory(${category.id})" style="color: var(--overdue-color); cursor: pointer; background: none; border: none;">
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
