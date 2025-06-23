<template>
    <aside
        class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2"
        :class="{
            show: visible,
            hide: !visible,
        }"
        id="sidenav-main"
    >
        <!-- Sidebar Header -->
        <div class="sidenav-header">
            <i
                class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-xl-none"
                aria-hidden="true"
                id="iconSidenav"
                @click="closeSidebar"
            >
            </i>

            <Link class="navbar-brand px-4 py-3 m-0" href="/dashboard">
                <img
                    src="/assets/img/logo-ct-dark.png"
                    class="navbar-brand-img"
                    width="26"
                    height="26"
                    alt="main_logo"
                />
                <span class="ms-1 text-sm text-dark">{{ appName }}</span>
            </Link>
        </div>

        <hr class="horizontal dark mt-0 mb-2" />

        <!-- Navigation Menu -->
        <ul class="navbar-nav">
            <!-- Main Navigation Items -->
            <li class="nav-item" v-for="item in mainMenuItems" :key="item.name">
                <Link
                    class="nav-link"
                    :class="{
                        'active bg-gradient-dark text-white': isActiveRoute(
                            item.route
                        ),
                        'text-dark': !isActiveRoute(item.route),
                    }"
                    :href="item.route"
                    @click="handleLinkClick"
                >
                    <i class="material-symbols-rounded opacity-5">{{
                        item.icon
                    }}</i>
                    <span class="nav-link-text ms-1">{{ item.name }}</span>
                </Link>
            </li>

            <!-- Account Pages Section -->
            <li class="nav-item mt-3">
                <h6
                    class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5"
                >
                    Account pages
                </h6>
            </li>

            <li
                class="nav-item"
                v-for="item in accountMenuItems"
                :key="item.name"
            >
                <Link
                    class="nav-link text-dark"
                    :href="item.route"
                    @click="handleLinkClick"
                >
                    <i class="material-symbols-rounded opacity-5">{{
                        item.icon
                    }}</i>
                    <span class="nav-link-text ms-1">{{ item.name }}</span>
                </Link>
            </li>
        </ul>
    </aside>
</template>

<script>
import { Link } from "@inertiajs/vue3";

export default {
    name: "Sidebar",
    components: {
        Link,
    },
    props: {
        currentRoute: {
            type: String,
            default: "",
        },
        visible: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            appName: "Your App Name",
            mainMenuItems: [
                { name: "Dashboard", route: "/dashboard", icon: "dashboard" },
                { name: "Tables", route: "/tables", icon: "table_view" },
                { name: "Billing", route: "/billing", icon: "receipt_long" },
                {
                    name: "Virtual Reality",
                    route: "/virtual-reality",
                    icon: "view_in_ar",
                },
                {
                    name: "RTL",
                    route: "/rtl",
                    icon: "format_textdirection_r_to_l",
                },
                {
                    name: "Notifications",
                    route: "/notifications",
                    icon: "notifications",
                },
            ],
            accountMenuItems: [
                { name: "Profile", route: "/profile", icon: "person" },
                { name: "Sign In", route: "/sign-in", icon: "login" },
                { name: "Sign Up", route: "/sign-up", icon: "assignment" },
            ],
        };
    },
    methods: {
        isActiveRoute(route) {
            return this.currentRoute === route || this.$page.url === route;
        },
        closeSidebar() {
            this.$emit("close");
        },
        handleLinkClick() {
            // Close sidebar on mobile when link is clicked
            if (window.innerWidth < 1200) {
                this.$emit("close");
            }
        },
    },
};
</script>

<style scoped>
.sidenav {
    transition: transform 0.3s ease-in-out;
    z-index: 1050;
}

/* Desktop - always visible */
@media (min-width: 1200px) {
    .sidenav {
        transform: translateX(0) !important;
    }
}

/* Mobile/Tablet - hidden by default */
@media (max-width: 1199.98px) {
    .sidenav {
        transform: translateX(-100%);
        width: 270px;
    }

    .sidenav.show {
        transform: translateX(0);
    }

    .sidenav.hide {
        transform: translateX(-100%);
    }
}
</style>
