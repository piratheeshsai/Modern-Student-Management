<template>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2" @click="togglePanel">
      <i class="material-symbols-rounded py-2">settings</i>
    </a>

    <div class="card shadow-lg" v-show="isOpen">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Material UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" @click="closePanel">
            <i class="material-symbols-rounded">clear</i>
          </button>
        </div>
      </div>

      <hr class="horizontal dark my-1">

      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Colors -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <div class="badge-colors my-2 text-start">
          <span
            v-for="color in sidebarColors"
            :key="color.name"
            class="badge filter"
            :class="[`bg-gradient-${color.name}`, { 'active': selectedSidebarColor === color.name }]"
            :data-color="color.name"
            @click="setSidebarColor(color.name)"
          ></span>
        </div>

        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button
            v-for="type in sidenavTypes"
            :key="type.name"
            class="btn px-3 mb-2"
            :class="[
              selectedSidenavType === type.name ? 'bg-gradient-dark active' : 'bg-gradient-dark',
              type.name !== 'dark' ? 'ms-2' : ''
            ]"
            @click="setSidenavType(type.name)"
          >
            {{ type.label }}
          </button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">
          You can change the sidenav type just on desktop view.
        </p>

        <!-- Navbar Fixed -->
        <div class="mt-3 d-flex">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input
              class="form-check-input mt-1 ms-auto"
              type="checkbox"
              id="navbarFixed"
              v-model="navbarFixed"
              @change="toggleNavbarFixed"
            >
          </div>
        </div>

        <hr class="horizontal dark my-3">

        <!-- Light / Dark Mode -->
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input
              class="form-check-input mt-1 ms-auto"
              type="checkbox"
              id="dark-version"
              v-model="darkMode"
              @change="toggleDarkMode"
            >
          </div>
        </div>

        <hr class="horizontal dark my-sm-4">

        <!-- Action Buttons -->
        <a class="btn bg-gradient-info w-100 mb-2" href="#" @click="downloadTheme">
          Free Download
        </a>
        <a class="btn btn-outline-dark w-100 mb-3" href="#" @click="viewDocumentation">
          View documentation
        </a>

        <!-- Social Sharing -->
        <div class="w-100 text-center">
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="#" class="btn btn-dark mb-0 me-2" @click="shareOnTwitter">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="#" class="btn btn-dark mb-0 me-2" @click="shareOnFacebook">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SettingsPanel',
  data() {
    return {
      isOpen: false,
      selectedSidebarColor: 'dark',
      selectedSidenavType: 'white',
      navbarFixed: false,
      darkMode: false,
      sidebarColors: [
        { name: 'primary' },
        { name: 'dark' },
        { name: 'info' },
        { name: 'success' },
        { name: 'warning' },
        { name: 'danger' }
      ],
      sidenavTypes: [
        { name: 'dark', label: 'Dark' },
        { name: 'transparent', label: 'Transparent' },
        { name: 'white', label: 'White' }
      ]
    }
  },
  methods: {
    togglePanel() {
      this.isOpen = !this.isOpen;
    },
    closePanel() {
      this.isOpen = false;
      this.$emit('close');
    },
    setSidebarColor(color) {
      this.selectedSidebarColor = color;
      // Implement sidebar color change logic
      document.documentElement.style.setProperty('--sidebar-color', color);
    },
    setSidenavType(type) {
      this.selectedSidenavType = type;
      // Implement sidenav type change logic
      const sidebar = document.getElementById('sidenav-main');
      if (sidebar) {
        sidebar.className = sidebar.className.replace(/bg-\w+/g, '');
        switch(type) {
          case 'dark':
            sidebar.classList.add('bg-gradient-dark');
            break;
          case 'transparent':
            sidebar.classList.add('bg-transparent');
            break;
          case 'white':
            sidebar.classList.add('bg-white');
            break;
        }
      }
    },
    toggleNavbarFixed() {
      // Implement navbar fixed toggle logic
      const navbar = document.getElementById('navbarBlur');
      if (navbar) {
        if (this.navbarFixed) {
          navbar.classList.add('position-sticky', 'top-1');
        } else {
          navbar.classList.remove('position-sticky', 'top-1');
        }
      }
    },
    toggleDarkMode() {
      // Implement dark mode toggle logic
      if (this.darkMode) {
        document.body.classList.add('dark-version');
      } else {
        document.body.classList.remove('dark-version');
      }
    },
    downloadTheme() {
      console.log('Download theme');
    },
    viewDocumentation() {
      console.log('View documentation');
    },
    shareOnTwitter() {
      console.log('Share on Twitter');
    },
    shareOnFacebook() {
      console.log('Share on Facebook');
    }
  }
}
</script>
