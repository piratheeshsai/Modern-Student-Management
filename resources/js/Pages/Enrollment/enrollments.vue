<template>
    <MainLayout>
        <ToastContainer :toasts="toasts" :removeToast="removeToast" />

        <div class="student-management">
            <!-- Header with Actions -->
            <div class="card mb-4">
                <div
                    class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex flex-wrap justify-content-between align-items-center px-3">
                    <h5 class="text-white mb-2 mb-md-0">Enrollments Management</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <Button icon="pi pi-plus" label="Add Enrollment" @click="navigateToEnrollmentCreate"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 140px;" />
                        <Button icon="pi pi-download" label="Export" @click="exportCSV()"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 120px;" />
                    </div>
                </div>
            </div>

            <!-- DataTable with TableSpinner -->
            <div class="datatable-wrapper">
                <TableSpinner :loading="loading" loading-text="Loading Enrollments..." />

                <DataTable
                    ref="dt"
                    v-model:filters="filters"
                    v-model:selection="selectedEnrollments"
                    :value="enrollments"
                    :lazy="true"
                    :paginator="true"
                    :rows="10"
                    :totalRecords="totalRecords"
                    :loading="false"
                    :rowsPerPageOptions="[5, 10, 25, 50]"
                    filterDisplay="menu"
                    responsiveLayout="scroll"
                    selectionMode="multiple"
                    dataKey="id"
                    sortMode="single"
                    :removableSort="true"
                    @page="onPage"
                    @sort="onSort"
                    @filter="onFilter"
                    class="p-datatable-sm">

                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="m-0">Enrollments ({{ totalRecords }})</h6>
                            <span class="p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="globalSearchValue" placeholder="Global Search" />
                            </span>
                        </div>
                    </template>

                    <!-- Enrollment ID -->
                    <Column field="id" header="Enrollment ID" sortField="id" :sortable="true" style="min-width: 100px">
                        <template #body="slotProps">
                            {{ slotProps.data.id }}
                        </template>
                    </Column>

                    <!-- Student Name -->
                    <Column field="student.full_name" header="Student Name" sortField="student.full_name" :sortable="false"
                        style="min-width: 180px">
                        <template #body="slotProps">
                            {{ slotProps.data.student?.full_name || 'N/A' }}
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="Search Student" class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Course Name -->
                    <Column field="course.name" header="Course Name" sortField="course.name" :sortable="false"
                        style="min-width: 150px">
                        <template #body="slotProps">
                            {{ slotProps.data.course?.name || 'N/A' }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="courseOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Course" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Status -->
                    <Column field="status" header="Status" sortField="status" :sortable="true" style="min-width: 100px">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status"
                                :severity="slotProps.data.status === 'active' ? 'success' : 'danger'" />
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="statusOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Status" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Enrollment Date -->
                    <Column field="enrollment_date" header="Enrollment Date" sortField="enrollment_date" :sortable="true"
                        style="min-width: 120px">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.enrollment_date) }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Calendar v-model="filterModel.value" dateFormat="mm/dd/yy" placeholder="Select Date"
                                class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Branch -->
                    <Column field="branch.name" header="Branch" sortField="branch.name" :sortable="false"
                        style="min-width: 120px">
                        <template #body="slotProps">
                            {{ slotProps.data.branch?.name || 'N/A' }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="branchOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Branch" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column header="Actions" :exportable="false" style="min-width: 80px">
                        <template #body="slotProps">
                            <Menu
                                :model="getActionMenu(slotProps.data)"
                                popup
                                :ref="el => setMenuRef(el, slotProps.data.id)"
                            />
                            <Button
                                icon="pi pi-ellipsis-v"
                                class="p-button-rounded p-button-text p-button-sm"
                                @click="openMenu($event, slotProps.data.id)"
                                v-tooltip.top="'Actions'"
                            />
                        </template>
                    </Column>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="pi pi-graduation-cap"></i>
                            </div>
                            <h6 class="mt-3 mb-2">No Enrollments Found</h6>
                            <p class="text-muted">Try adjusting your filters or add a new enrollment</p>
                            <Button
                                label="Add Enrollment"
                                icon="pi pi-plus"
                                @click="navigateToEnrollmentCreate"
                                class="p-button-outlined"
                            />
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Toast Notification -->
            <div class="toast-container" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;">
                <div v-for="toast in toasts" :key="toast.id" :class="[
                    'toast-item',
                    toast.show ? 'toast-show' : '',
                    toast.type === 'success' ? 'toast-success' : 'toast-error'
                ]" style="margin-bottom: 0.5rem; min-width: 220px; padding: 1rem; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); color: #fff; font-weight: 500;">
                    <span v-if="toast.type === 'success'" style="margin-right: 0.5rem;">✅</span>
                    <span v-else style="margin-right: 0.5rem;">❌</span>
                    {{ toast.message }}
                    <button @click="removeToast(toast.id)"
                        style="background: none; border: none; color: #fff; float: right; font-size: 1.1rem; cursor: pointer;">×</button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import ToastContainer from "@/Components/ToastContainer.vue";
import TableSpinner from "@/Components/TableSpinner.vue";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { router } from "@inertiajs/vue3";
import Menu from 'primevue/menu';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';

const toasts = ref([]);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);

const showToast = (message: string, type: "success" | "error" = "success") => {
    const id = Date.now();
    const toast = {
        id,
        message,
        type,
        show: false,
    };

    toasts.value.push(toast);

    setTimeout(() => {
        const toastElement = toasts.value.find((t) => t.id === id);
        if (toastElement) toastElement.show = true;
    }, 100);

    setTimeout(() => {
        removeToast(id);
    }, 10000);
};

const removeToast = (id: number) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
        toasts.value[index].show = false;
        setTimeout(() => {
            toasts.value.splice(index, 1);
        }, 300);
    }
};

// Define FilterMatchMode and FilterOperator
const FilterMatchMode = {
    STARTS_WITH: 'startsWith',
    CONTAINS: 'contains',
    NOT_CONTAINS: 'notContains',
    ENDS_WITH: 'endsWith',
    EQUALS: 'equals',
    NOT_EQUALS: 'notEquals',
    LESS_THAN: 'lt',
    LESS_THAN_OR_EQUAL_TO: 'lte',
    GREATER_THAN: 'gt',
    GREATER_THAN_OR_EQUAL_TO: 'gte',
    BETWEEN: 'between',
    IN: 'in',
    DATE_IS: 'dateIs',
    DATE_IS_NOT: 'dateIsNot',
    DATE_BEFORE: 'dateBefore',
    DATE_AFTER: 'dateAfter'
}

const FilterOperator = {
    AND: 'and',
    OR: 'or'
}

// Data
const dt = ref()
const enrollments = ref([])
const selectedEnrollments = ref([])
const totalRecords = ref(0)
const loading = ref(false)
const showAddDialog = ref(false)

// Filters
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    student_no: { value: null, matchMode: FilterMatchMode.CONTAINS },
    full_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    'course.name': { value: null, matchMode: FilterMatchMode.EQUALS },
    'branch.name': { value: null, matchMode: FilterMatchMode.EQUALS },
    is_approved: { value: null, matchMode: FilterMatchMode.EQUALS },
    created_at: { value: null, matchMode: FilterMatchMode.DATE_IS }
})

// Pagination & Sorting
const lazyParams = ref({
    first: 0,
    rows: 10,
    sortField: null,
    sortOrder: null,
    filters: filters.value
})

// Options for dropdowns
const courseOptions = ref([])
const branchOptions = ref([])
const statusOptions = ref([
    { label: 'All', value: null },
    { label: 'Approved', value: true },
    { label: 'Pending', value: false }
])

// Form data
const enrollmentForm = ref({
    id: null,
    student_id: '',
    course_id: '',
    branch_id: '',
    enrollment_date: '',
    payment_type: '',
    discount: 0,
    status: 'active'
})

// FIXED: Properly initialize arrays
const verifiedStudents = ref([])
const courses = ref([])
const branches = ref([])

interface EnrollmentErrors {
    student_id?: string;
    course_id?: string;
    branch_id?: string;
    enrollment_date?: string;
    payment_type?: string;
    discount?: string;
}

const errors = ref<EnrollmentErrors>({})

// Methods
const loadEnrollments = async () => {
    loading.value = true
    try {
        let sortOrder = 'asc'
        if (lazyParams.value.sortOrder === -1) {
            sortOrder = 'desc'
        } else if (lazyParams.value.sortOrder === 1) {
            sortOrder = 'asc'
        }

        const params: Record<string, any> = {
            page: Math.floor(lazyParams.value.first / lazyParams.value.rows) + 1,
            per_page: lazyParams.value.rows,
            filters: JSON.stringify(lazyParams.value.filters)
        }

        if (lazyParams.value.sortField) {
            params.sort_field = lazyParams.value.sortField
            params.sort_order = sortOrder
        }

        const response = await axios.get('/api/enrollments', { params })
        if (response.data.success) {
            enrollments.value = response.data.data || []
            totalRecords.value = response.data.total || 0
        } else {
            enrollments.value = []
            totalRecords.value = 0
        }
    } catch (error) {
        console.error('Error loading enrollments:', error)
        enrollments.value = []
        totalRecords.value = 0
    } finally {
        loading.value = false
    }
}

const onPage = (event) => {
    lazyParams.value.first = event.first
    lazyParams.value.rows = event.rows
    loadEnrollments()
}

const onSort = (event) => {
    lazyParams.value.sortField = event.sortField
    lazyParams.value.sortOrder = event.sortOrder
    loadEnrollments()
}

const onFilter = (event) => {
    lazyParams.value.filters = event.filters
    lazyParams.value.first = 0
    loadEnrollments()
}

const globalSearchValue = ref('')

let searchTimeout: NodeJS.Timeout
watch(globalSearchValue, (newValue) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.global.value = newValue
        lazyParams.value.filters = filters.value
        lazyParams.value.first = 0
        loadEnrollments()
    }, 300)
})

const exportCSV = () => {
    dt.value.exportCSV()
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const editEnrollment = (enrollment) => {
    enrollmentForm.value = { ...enrollment }
    isEditMode.value = true
    isModalOpen.value = true
}

const confirmDelete = async (enrollment) => {
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!",
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/enrollments/${enrollment.id}`);
            showToast("Enrollment deleted successfully!", "success");
            loadEnrollments();
        } catch (error) {
            console.error("Error deleting enrollment:", error);
            showToast("Error deleting enrollment", "error");
        }
    }
};




onMounted(async () => {
    try {
        // Load all required data
        const [studentsRes, coursesRes, branchesRes] = await Promise.all([
            axios.get('/api/students'),
            axios.get('/api/courses'),
            axios.get('/api/branches')
        ]);

        // Filter for verified students and set display names
        verifiedStudents.value = (studentsRes.data.data || [])
            .filter(student => student.is_verified === true)
            .map(student => ({
                ...student,
                full_name: `${student.first_name} ${student.last_name}`,
                display_name: `${student.student_no} - ${student.first_name} ${student.last_name}`
            }));

        // Set courses and branches
        courses.value = coursesRes.data.data || [];
        branches.value = branchesRes.data.data || [];

        // Set table filter options
        courseOptions.value = [
            { label: 'All Courses', value: null },
            ...courses.value.map(course => ({
                label: course.name,
                value: course.name
            }))
        ];

        branchOptions.value = [
            { label: 'All Branches', value: null },
            ...branches.value.map(branch => ({
                label: branch.name,
                value: branch.name
            }))
        ];

    } catch (error) {
        console.error('Error loading data:', error);
        showToast('Error loading data', 'error');
    }

    // Load enrollments
    loadEnrollments();
});

const menuRefs = ref({});

const setMenuRef = (el, id) => {
    if (el) {
        menuRefs.value[id] = el;
    }
};

const openMenu = (event, id) => {
    if (menuRefs.value[id]) {
        menuRefs.value[id].toggle(event);
    }
};

const getActionMenu = (row) => [
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        command: () => router.visit(`/enrollments/${row.id}/edit`)
    },
    {
        label: 'View',
        icon: 'pi pi-eye',
        command: () => router.visit(`/enrollments/${row.id}/view`)
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () => confirmDelete(row)
    }
];

const navigateToEnrollmentCreate = () => {
    router.visit('/enrollments/create')
}

</script>

<style scoped>
.student-management {
    padding: 1rem;
}

/* DataTable Wrapper for relative positioning */
.datatable-wrapper {
    position: relative;
    min-height: 400px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state h6 {
    color: #495057;
    font-weight: 600;
}

.empty-state p {
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.p-datatable {
    border-radius: 13px !important;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.10), 0 1.5px 4px rgba(0, 0, 0, 0.08);
}

.p-datatable .p-datatable-thead>tr>th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    font-weight: 600;
}

.p-datatable .p-datatable-tbody>tr {
    transition: background-color 0.2s;
}

.p-datatable .p-datatable-tbody>tr:hover {
    background-color: #f8f9fa;
}

:deep(.custom-outline-btn:hover) {
    background-color: #6c757d !important;
    color: white !important;
}

.gradient-white-btn {
    background: linear-gradient(90deg, #fff 0%, #f8f9fa 100%);
    border: 1px solid #dee2e6 !important;
    color: #212529 !important;
    transition: background 0.2s, color 0.2s;
}

.gradient-white-btn:hover,
.gradient-white-btn:focus {
    background: linear-gradient(90deg, #f1f3f4 0%, #e9ecef 100%) !important;
    color: #212529 !important;
    border-color: #bdbdbd !important;
    box-shadow: none !important;
}

.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

.toast-show {
    opacity: 1;
    transform: translateY(0);
}

.toast-item {
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.3s, transform 0.3s;
}

/* Ensure DataTable doesn't show its own loading */
:deep(.p-datatable-loading-overlay) {
    display: none !important;
}
</style>
