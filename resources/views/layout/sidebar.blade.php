<div class="sidebar">
  <button class="close-sidebar lt-768" onclick="toggleSidebar()"><i class="fa-solid fa-xmark"></i></button>
  <div class="profile">
    <div class="profile-avatar">
      <img src="/images/profile.png" alt="Profile" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=3b82f6&color=fff'">
    </div>
    <span class="profile-name">{{ Auth::user()->name }}</span>
  </div>
  
  <div class="links">
    <ul>
      <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}">
          <span class="icon"><i class="fa-solid fa-house"></i></span>
          <span class="title">Dashboard</span>
        </a>
      </li>
      <li class="{{ Route::is('today') ? 'active' : '' }}">
        <a href="{{ route('today') }}">
          <span class="icon"><i class="fa-solid fa-calendar-day"></i></span>
          <span class="title">Today</span>
        </a>
      </li>
      <li class="{{ Route::is('upcoming') ? 'active' : '' }}">
        <a href="{{ route('upcoming') }}">
          <span class="icon"><i class="fa-solid fa-calendar-days"></i></span>
          <span class="title">Upcoming</span>
        </a>
      </li>
      <li class="{{ Route::is('important') ? 'active' : '' }}">
        <a href="{{ route('important') }}">
          <span class="icon"><i class="fa-solid fa-star"></i></span>
          <span class="title">Important</span>
        </a>
      </li>
      <li class="{{ Route::is('completed') ? 'active' : '' }}">
        <a href="{{ route('completed') }}">
          <span class="icon"><i class="fa-solid fa-circle-check"></i></span>
          <span class="title">Completed</span>
        </a>
      </li>
      <li class="{{ Route::is('categories.index') ? 'active' : '' }}">
        <a href="{{ route('categories.index') }}">
          <span class="icon"><i class="fa-solid fa-folder"></i></span>
          <span class="title">Categories</span>
        </a>
      </li>
    </ul>
  </div>
</div>
