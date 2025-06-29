<template>
  <nav class="navbar navbar-main  navbar-expand-lg px-0 mx-3 mt-3 shadow border-radius-xl
  "
  >
    <div class="container-fluid py-1 px-3">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
          <li class="breadcrumb-item text-sm">
            <a class="opacity-5 text-dark" href="#">Pages</a>
          </li>
          <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
            {{ currentPageName }}
          </li>
        </ol>
      </nav>

      <!-- Navbar Content -->
      <!-- <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar"> -->
        <!-- Search Bar -->
        <!-- <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          <div class="input-group input-group-outline">
            <label class="form-label">Type here...</label>
            <input type="text" class="form-control" v-model="searchQuery" @input="handleSearch">
          </div>
        </div> -->

        <!-- Right Side Navigation -->
       <ul class="navbar-nav d-flex align-items-center justify-content-end">
    <!-- Online Builder Button -->
    <li class="nav-item d-flex align-items-center">
        <a class="btn btn-outline-primary btn-sm mb-0 me-3" target="_blank" href="#">
            Online Builder
        </a>
    </li>

    <!-- Mobile Menu Toggle -->
    <li class="nav-item d-xl-none d-flex align-items-center me-3">
        <a href="#" class="nav-link text-body p-2 d-flex align-items-center justify-content-center" @click="toggleSidebar">
            <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
            </div>
        </a>
    </li>

    <!-- Notifications Dropdown -->
    <li class="nav-item dropdown d-flex align-items-center me-3 position-relative">
        <a href="#" class="nav-link text-body p-2 d-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="material-symbols-rounded">notifications</i>
            <span v-if="notificationCount > 0" class="badge badge-sm bg-gradient-danger position-absolute top-0 start-100 translate-middle">
                {{ notificationCount }}
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" style="position: absolute; top: 100%; right: 0; z-index: 1050;">
            <li class="mb-2" v-for="notification in notifications" :key="notification.id">
                <a class="dropdown-item border-radius-md" href="#" @click="handleNotificationClick(notification)">
                    <div class="d-flex py-1">
                        <div class="my-auto">
                            <img :src="notification.avatar" class="avatar avatar-sm me-3" v-if="notification.avatar">
                            <div v-else class="avatar avatar-sm bg-gradient-secondary me-3 my-auto">
                                <i :class="notification.icon"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="text-sm font-weight-normal mb-1">
                                <span class="font-weight-bold">{{ notification.title }}</span>
                                {{ notification.message }}
                            </h6>
                            <p class="text-xs text-secondary mb-0">
                                <i class="fa fa-clock me-1"></i>
                                {{ notification.time }}
                            </p>
                        </div>
                    </div>
                </a>
            </li>
            <li v-if="notifications.length === 0">
                <span class="dropdown-item text-center">No notifications</span>
            </li>
        </ul>
    </li>

    <!-- User Profile -->
    <li class="nav-item d-flex align-items-center position-relative">
        <div class="dropdown">
            <a href="#" class="nav-link text-body p-2 d-flex align-items-center justify-content-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="material-symbols-rounded">account_circle</i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" style="position: absolute; top: 100%; right: 0; z-index: 1050;">
                <li><a class="dropdown-item" href="#" @click="goToProfile">Profile</a></li>
                <li><a class="dropdown-item" href="#" @click="goToSettings">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" @click="logout">Logout</a></li>
            </ul>
        </div>
    </li>
</ul>
      </div>
    <!-- </div> -->
  </nav>
</template>

<script>
export default {
  name: 'Navbar',
  data() {
    return {
      searchQuery: '',
      notifications: [
        {
          id: 1,
          title: 'New message',
          message: 'from John Doe',
          time: '13 minutes ago',
          avatar: '/assets/img/team-2.jpg'
        },
        {
          id: 2,
          title: 'New album',
          message: 'by Travis Scott',
          time: '1 day',
          avatar: '/assets/img/small-logos/logo-spotify.svg'
        },
        {
          id: 3,
          title: 'Payment successfully completed',
          message: '',
          time: '2 days',
          icon: 'fas fa-credit-card'
        }
      ]
    }
  },
  computed: {
    currentPageName() {
      // Get current page name from Inertia page component
      return this.$page.component.replace(/.*\//, '') || 'Dashboard';
    },
    notificationCount() {
      return this.notifications.length;
    }
  },
  methods: {

    toggleSidebar() {
      this.$emit('toggle-sidebar');
    },

    handleNotificationClick(notification) {
      // Handle notification click
      console.log('Notification clicked:', notification);
      this.$emit('notification-click', notification);
    },
    goToProfile() {
      this.$inertia.visit('/profile');
    },

    logout() {
      // Implement logout functionality with Inertia
      this.$inertia.post('/logout');
    }
  }
}
</script>

<style>
/* Hide the default Bootstrap dropdown arrow */
.dropdown-toggle::after {
  display: none !important;
}
</style>
