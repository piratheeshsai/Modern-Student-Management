<template>
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2"
        :class="{
            show: visible,
            hide: !visible,
        }" id="sidenav-main">
        <!-- Sidebar Header -->
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-xl-none"
                aria-hidden="true" id="iconSidenav" @click="closeSidebar">
            </i>

            <Link class="navbar-brand px-4 py-3 m-0" href="/dashboard">
            <img src="/assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo" />
            <span class="ms-1 text-sm text-dark">{{ appName }}</span>
            </Link>
        </div>

        <hr class="horizontal dark mt-0 mb-2" />

        <!-- Navigation Menu -->
        <ul class="navbar-nav">
            <!-- Main Navigation Items -->
            <li class="nav-item" v-for="item in mainMenuItems" :key="item.name">
                <!-- Regular menu item without sub-items -->
                <Link v-if="!item.subItems" class="nav-link" :class="{
                    'active bg-gradient-dark text-white': isActiveRoute(
                        item.route
                    ),
                    'text-dark': !isActiveRoute(item.route),
                }" :href="item.route" @click="handleLinkClick">
                <i class="material-symbols-rounded opacity-5">{{
                    item.icon
                }}</i>
                <span class="nav-link-text ms-1">{{ item.name }}</span>
                </Link>

                <!-- Menu item with sub-items -->
                <div v-else>
                    <a class="nav-link text-dark cursor-pointer" @click="toggleSubMenu(item.name)" :class="{
                        'active bg-gradient-dark text-white': isActiveParentRoute(item),
                    }">
                        <i class="material-symbols-rounded opacity-5">{{ item.icon }}</i>
                        <span class="nav-link-text ms-1">{{ item.name }}</span>
                        <i class="material-symbols-rounded ms-auto text-xs" :class="{
                            'rotate-180': expandedMenus.includes(item.name)
                        }">expand_more</i>
                    </a>

                    <!-- Sub-menu items -->
                    <ul class="navbar-nav ms-3" v-show="expandedMenus.includes(item.name)">
                        <li class="nav-item" v-for="subItem in item.subItems" :key="subItem.name">
                            <Link class="nav-link py-2" :class="{
                                'active bg-gradient-primary text-white': isActiveRoute(subItem.route),
                                'text-dark': !isActiveRoute(subItem.route),
                            }" :href="subItem.route" @click="handleLinkClick">
                            <i class="material-symbols-rounded opacity-5 text-sm">{{ subItem.icon }}</i>
                            <span class="nav-link-text ms-1 text-sm">{{ subItem.name }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Account Pages Section -->
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">
                    Account pages
                </h6>
            </li>

            <li class="nav-item" v-for="item in accountMenuItems" :key="item.name">
                <Link class="nav-link text-dark" :href="item.route" @click="handleLinkClick">
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
            appName: "OCBTE",
            expandedMenus: [],
            mainMenuItems: [
                {
                    name: "Dashboard", route: "/dashboard",
                    icon: "dashboard"
                },
                {
                    name: "Students", route: "/students",
                    icon: "dashboard"
                },
                {
                    name: "Courses", route: "/courses",
                    icon: "table_view"
                },
                { name: "Enrollments", route: "/enrollments", icon: "table_view" },
                { name: "Make Enrollment", route: "/enrollments/create", icon: "table_view" },
                {
                    name: "Payment",
                    icon: "receipt_long",
                    subItems: [
                        {
                            name: "All Payments",
                            route: "/payments",
                            icon: "payments"
                        },
                        {
                            name: "Due Payments",
                            route: "/payments/due",
                            icon: "schedule"
                        }
                    ]
                },
                {
                    name: "Reports",
                    route: "/attendance",
                    icon: "assignment",
                },
                {
                    name: "Branches",
                    route: "/branches",
                    icon: "format_textdirection_r_to_l",
                },
                {
                    name: "Reference",
                    route: "/reference",
                    icon: "format_textdirection_r_to_l",
                },
                {
                    name: "Notifications",
                    route: "/notifications",
                    icon: "notifications",
                },
            ],
            accountMenuItems: [
                {
                    name: "Profile", route: "/profile",
                    icon: "person"
                },
                {
                    name: "Sign In", route: "/sign-in",
                    icon: "login"
                },
                {
                    name: "Sign Up", route: "/sign-up",
                    icon: "assignment"
                },
            ],
        };
    },
    methods: {
        isActiveRoute(route) {
            return this.currentRoute === route || this.$page.url === route;
        },
        isActiveParentRoute(item) {
            if (!item.subItems) return false;
            return item.subItems.some(subItem => this.isActiveRoute(subItem.route));
        },
        toggleSubMenu(menuName) {
            const index = this.expandedMenus.indexOf(menuName);
            if (index > -1) {
                this.expandedMenus.splice(index, 1);
            } else {
                this.expandedMenus.push(menuName);
            }
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

.cursor-pointer {
    cursor: pointer;
}

.rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.3s ease;
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
