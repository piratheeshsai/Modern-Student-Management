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
                        <Button icon="pi pi-plus" label="Add Enrollment" @click="newEnrollment"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 140px;" />
                        <Button icon="pi pi-download" label="Export" @click="exportCSV()"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 120px;" />
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <DataTable ref="dt" v-model:filters="filters" v-model:selection="selectedEnrollments" :value="enrollments"
                :lazy="true" :paginator="true" :rows="10" :totalRecords="totalRecords" :loading="loading"
                :rowsPerPageOptions="[5, 10, 25, 50]" filterDisplay="menu" responsiveLayout="scroll"
                selectionMode="multiple" dataKey="id" sortMode="single" :removableSort="true" @page="onPage"
                @sort="onSort" @filter="onFilter" class="p-datatable-sm">
                <template #header>
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Enrollments ({{ totalRecords }})</h6>
                        <span class="p-input-icon-left">
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
                <Column field="student.full_name" header="Student Name" sortField="student.full_name" :sortable="true"
                    style="min-width: 180px">
                    <template #body="slotProps">
                        {{ slotProps.data.student?.full_name || 'N/A' }}
                    </template>
                </Column>

                <!-- Course Name -->
                <Column field="course.name" header="Course Name" sortField="course.name" :sortable="true"
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
                <Column field="branch.name" header="Branch" sortField="branch.name" :sortable="true"
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
                <Column header="Actions" :exportable="false" style="min-width: 120px">
                    <template #body="slotProps">
                        <div class="d-flex gap-1">
                            <Button icon="pi pi-pencil"
                                class="p-button-rounded p-button-text p-button-sm p-button-warning"
                                @click="editEnrollment(slotProps.data)" v-tooltip.top="'Edit'" />
                            <Button icon="pi pi-trash"
                                class="p-button-rounded p-button-text p-button-sm p-button-danger"
                                @click="confirmDelete(slotProps.data)" v-tooltip.top="'Delete'" />
                        </div>
                    </template>
                </Column>
            </DataTable>

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

        <!-- Modal -->
        <div class="modal fade" :class="{ show: isModalOpen, 'd-block': isModalOpen }" tabindex="-1" role="dialog"
            aria-labelledby="courseModalLabel" :aria-hidden="!isModalOpen"
            :style="{ backgroundColor: isModalOpen ? 'rgba(0,0,0,0.5)' : '' }">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-dark text-white border-0">
                        <h5 class="modal-title text-white" id="EnrollmentModalLabel">
                            <i class="fas fa-graduation-cap me-2"></i>
                            {{ isEditMode ? 'Edit Enrollment' : 'Create New Enrollment' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"
                            aria-label="Close"></button>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="modal-body p-5" style="background-color: #f8f9fa;">
                            <div class="row g-4">
                                <!-- Student Dropdown -->
                                <div class="col-md-6">
                                    <label for="studentSelect" class="form-label fw-semibold text-dark mb-2">
                                        Student <span class="text-danger">*</span>
                                    </label>
                                    <Select
                                        v-model="enrollmentForm.student_id"
                                        :options="verifiedStudents"
                                        filter
                                        optionLabel="display_name"
                                        optionValue="id"
                                        placeholder="Select a Student"
                                        class="w-full"
                                        :class="{ 'p-invalid': errors.student_id }"
                                        style="min-height: 50px;"
                                        appendTo="self">
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ getSelectedStudentName(slotProps.value) }}</span>
                                                    <span class="text-sm text-gray-500">{{ getSelectedStudentNo(slotProps.value) }}</span>
                                                </div>
                                            </div>
                                            <span v-else>
                                                {{ slotProps.placeholder }}
                                            </span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center p-2">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ slotProps.option.full_name }}</span>
                                                    <span class="text-sm text-gray-500">{{ slotProps.option.student_no }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </Select>
                                    <div v-if="errors.student_id" class="text-red-500 text-sm mt-1">
                                        {{ errors.student_id }}
                                    </div>
                                </div>

                                <!-- Course Dropdown -->
                                <div class="col-md-6">
                                    <label for="courseSelect" class="form-label fw-semibold text-dark mb-2">
                                        Course <span class="text-danger">*</span>
                                    </label>
                                    <Select
                                        v-model="enrollmentForm.course_id"
                                        :options="courses"
                                        filter
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Select a Course"
                                        class="w-full"
                                        :class="{ 'p-invalid': errors.course_id }"
                                        style="min-height: 50px;"
                                        appendTo="self">
                                        <template #value="slotProps">
                                            <div v-if="slotProps.value" class="flex items-center">
                                                <span>{{ getSelectedCourseName(slotProps.value) }}</span>
                                            </div>
                                            <span v-else>
                                                {{ slotProps.placeholder }}
                                            </span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center p-2">
                                                <span>{{ slotProps.option.name }}</span>
                                            </div>
                                        </template>
                                    </Select>
                                    <div v-if="errors.course_id" class="text-red-500 text-sm mt-1">
                                        {{ errors.course_id }}
                                    </div>
                                </div>

                                <!-- Branch Dropdown -->
                                <div class="col-md-6">
                                    <label for="branchSelect" class="form-label fw-semibold text-dark mb-2">
                                        Branch <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.branch_id }" id="branchSelect" v-model="enrollmentForm.branch_id"
                                        style="background-color: white; padding: 15px;">
                                        <option value="">Select Branch</option>
                                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                            {{ branch.name }}
                                        </option>
                                    </select>
                                    <div v-if="errors.branch_id" class="invalid-feedback mt-1">
                                        {{ errors.branch_id }}
                                    </div>
                                </div>

                                <!-- Enrollment Date -->
                                <div class="col-md-6">
                                    <label for="enrollmentDate" class="form-label fw-semibold text-dark mb-2">
                                        Enrollment Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.enrollment_date }" id="enrollmentDate"
                                        v-model="enrollmentForm.enrollment_date"
                                        style="background-color: white; padding: 15px;">
                                    <div v-if="errors.enrollment_date" class="invalid-feedback mt-1">
                                        {{ errors.enrollment_date }}
                                    </div>
                                </div>

                                <!-- Payment Type -->
                                <div class="col-md-6">
                                    <label for="paymentType" class="form-label fw-semibold text-dark mb-2">
                                        Payment Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.payment_type }" id="paymentType" v-model="enrollmentForm.payment_type"
                                        style="background-color: white; padding: 15px;">
                                        <option value="">Select Payment Type</option>
                                        <option value="full">Full Payment</option>
                                        <option value="installment">Installment</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                    <div v-if="errors.payment_type" class="invalid-feedback mt-1">
                                        {{ errors.payment_type }}
                                    </div>
                                </div>

                                <!-- Optional Discount -->
                                <div class="col-md-6">
                                    <label for="discount" class="form-label fw-semibold text-dark mb-2">
                                        Discount (Optional)
                                    </label>
                                    <input type="number" class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.discount }" id="discount" v-model="enrollmentForm.discount"
                                        placeholder="Enter discount amount" min="0" step="0.01"
                                        style="background-color: white; padding: 15px;">
                                    <div v-if="errors.discount" class="invalid-feedback mt-1">
                                        {{ errors.discount }}
                                    </div>


                                </div>



                            </div>
                        </div>

                        <div class="modal-footer border-0 p-4" style="background-color: #f8f9fa;">
                            <button type="button" class="btn btn-light btn-lg px-4 me-3 rounded-3 shadow-sm"
                                @click="closeModal" :disabled="isLoading" style="border: 1px solid #dee2e6;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-secondary btn-lg px-4 rounded-3 shadow-sm"
                                :disabled="isLoading" style="background-color: #212529; border: none;">
                                <span v-if="isLoading">Processing...</span>
                                <span v-else>{{ isEditMode ? 'Update Enrollment' : 'Create Enrollment' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import ToastContainer from "@/Components/ToastContainer.vue";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";


import Select from 'primevue/select';


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

const resetForm = () => {
    enrollmentForm.value = {
        id: null,
        student_id: '',
        course_id: '',
        branch_id: '',
        enrollment_date: '',
        payment_type: '',
        discount: 0,
        status: 'active'
    }
    errors.value = {}
}

const newEnrollment = () => {
    resetForm();
    isEditMode.value = false;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

const getSelectedStudentName = (studentId) => {
    const student = verifiedStudents.value.find(s => s.id === studentId);
    return student ? student.full_name : '';
};

const getSelectedStudentNo = (studentId) => {
    const student = verifiedStudents.value.find(s => s.id === studentId);
    return student ? student.student_no : '';
};

const getSelectedCourseName = (courseId) => {
    const course = courses.value.find(c => c.id === courseId);
    return course ? course.name : '';
};

const submitForm = async () => {
    // Add your form submission logic here
    console.log('Form submitted:', enrollmentForm.value);
};


onMounted(async () => {
    try {
        // Load all required data
        const [studentsRes, coursesRes, branchesRes] = await Promise.all([
            axios.get('/api/students'), // Fetch all students
            axios.get('/api/courses'),
            axios.get('/api/branches')
        ]);

        console.log('Students response:', studentsRes.data);
        console.log('Courses response:', coursesRes.data);
        console.log('Branches response:', branchesRes.data);

        // Filter for verified students on the frontend and set display names
        verifiedStudents.value = (studentsRes.data.data || [])
            .filter(student => student.is_verified === true) // Filter here
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

        console.log('Verified students:', verifiedStudents.value);
        console.log('Courses:', courses.value);
        console.log('Branches:', branches.value);

    } catch (error) {
        console.error('Error loading data:', error);
        showToast('Error loading data', 'error');
    }

    // Load enrollments
    loadEnrollments();
});
</script>

<style scoped>
.student-management {
    padding: 1rem;
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



</style>
