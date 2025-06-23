<!-- Main Layout -->
<template>
    <div class="g-sidenav-show bg-gray-100">
        <!-- Sidebar -->
        <Sidebar
            :current-route="$page.url"
            :visible="sidebarOpen"
            @close="closeSidebar"
        />

        <!-- Main Content -->
        <main
            class="main-content position-relative max-height-vh-100 h-100 border-radius-lg"
            :class="{ 'g-sidenav-pinned': sidebarOpen }"
        >
            <!-- Navbar -->
            <Navbar
                @search="handleSearch"
                @toggle-sidebar="toggleSidebar"
                @open-settings="openSettings"
                @notification-click="handleNotificationClick"
            />

            <!-- Page Content -->
            <div class="container-fluid py-2">
                <slot />
            </div>
        </main>

        <!-- Settings Panel -->
        <SettingsPanel v-if="showSettings" @close="closeSettings" />

        <!-- Mobile Overlay -->
        <div
            v-if="sidebarOpen && isMobile"
            class="sidebar-overlay"
            @click="closeSidebar"
        ></div>
    </div>
</template>

<script>
import Sidebar from "@/Components/Sidebar.vue";
import Navbar from "@/Components/Navbar.vue";
import SettingsPanel from "@/Components/SettingsPanel.vue";

export default {
    name: "MainLayout",
    components: {
        Sidebar,
        Navbar,
        SettingsPanel,
    },
    data() {
        return {
            showSettings: false,
            sidebarOpen: false, // Start closed on mobile
            isMobile: false,
        };
    },
    mounted() {
        this.checkScreenSize();
        window.addEventListener('resize', this.checkScreenSize);
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.checkScreenSize);
    },
    methods: {
        checkScreenSize() {
            this.isMobile = window.innerWidth < 1200; // Bootstrap xl breakpoint
            // Auto-open sidebar on desktop, auto-close on mobile
            if (!this.isMobile) {
                this.sidebarOpen = true;
            } else {
                this.sidebarOpen = false;
            }
        },
        handleSearch(query) {
            console.log("Search query:", query);
            // Implement global search functionality
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        closeSidebar() {
            this.sidebarOpen = false;
        },
        openSettings() {
            this.showSettings = true;
        },
        closeSettings() {
            this.showSettings = false;
        },
        handleNotificationClick(notification) {
            console.log("Notification clicked:", notification);
            // Handle notification click
        },
    },
};
</script>

<style scoped>
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    display: block;
}

@media (max-width: 1199.98px) {
    .main-content {
        margin-left: 0 !important;
    }
}
</style>
