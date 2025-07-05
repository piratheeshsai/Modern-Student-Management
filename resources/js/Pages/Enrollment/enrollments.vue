<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import ToastContainer from "@/Components/ToastContainer.vue";
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";



const toasts = ref([]);


const showToast = (message: string, type: "success" | "error" = "success") => {
    const id = Date.now();
    const toast = {
        id,
        message,
        type,
        show: false,
    };

    toasts.value.push(toast);

    // Trigger animation
    setTimeout(() => {
        const toastElement = toasts.value.find((t) => t.id === id);
        if (toastElement) toastElement.show = true;
    }, 100);

    // Auto remove after 5 seconds
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

// Define FilterMatchMode and FilterOperator manually since components are global
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
const enrollments = ref([]) // <-- change from students
const selectedEnrollments = ref([]) // <-- change from selectedStudents
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
  { label: 'All', value: null }, // Add an "All" option
  { label: 'Approved', value: true },
  { label: 'Pending', value: false }
])

// Add navigation method for the Add Student button
const navigateToRegister = () => {
    router.visit('/students-register')
}

// Methods
const loadEnrollments = async () => {
  loading.value = true
  try {
    // Fix the sort order logic
    let sortOrder = 'asc'
    if (lazyParams.value.sortOrder === -1) {
      sortOrder = 'desc'
    } else if (lazyParams.value.sortOrder === 1) {
      sortOrder = 'asc'
    }

    // Use a flexible object type that allows additional properties
    const params: Record<string, any> = {
      page: Math.floor(lazyParams.value.first / lazyParams.value.rows) + 1,
      per_page: lazyParams.value.rows,
      filters: JSON.stringify(lazyParams.value.filters)
    }

    // Only add sort params if sorting is active
    if (lazyParams.value.sortField) {
      params.sort_field = lazyParams.value.sortField
      params.sort_order = sortOrder
    }

    console.log('Sending API request with params:', params)

    const response = await axios.get('/api/enrollments', { params })
    if (response.data.success) {
      enrollments.value = response.data.data || []
      totalRecords.value = response.data.total || 0
    } else {
      enrollments.value = []
      totalRecords.value = 0
    }
  } catch (error) {
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
  console.log('Sort event triggered:', event)

  lazyParams.value.sortField = event.sortField
  lazyParams.value.sortOrder = event.sortOrder

  console.log('Updated lazyParams:', lazyParams.value)

  loadEnrollments()
}

const onFilter = (event) => {
  console.log('Filter event triggered:', event)

  lazyParams.value.filters = event.filters
  lazyParams.value.first = 0 // Reset to first page when filtering

  console.log('Updated filter params:', lazyParams.value)

  loadEnrollments()
}

// Add global search value ref
const globalSearchValue = ref('')

// Watch for changes in global search and debounce
let searchTimeout: NodeJS.Timeout
watch(globalSearchValue, (newValue) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.value.global.value = newValue
    lazyParams.value.filters = filters.value
    lazyParams.value.first = 0 // Reset to first page
    loadEnrollments()
  }, 300) // 300ms debounce
})

const exportCSV = () => {
  dt.value.exportCSV()
}

const getStatusLabel = (student) => {
  if (student.is_approved) return 'Approved'
  return 'Pending'
}

const getStatusSeverity = (student) => {
  if (student.is_approved) return 'success'
  return 'warning'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const viewStudent = (student) => {
  // Implement view logic
  console.log('View student:', student)
}

// const editStudent = (student) => {
//   // Navigate to register page with student data
//   router.visit('/students-register', {
//     method: 'get',
//     data: {
//       edit: true,
//       student_id: student.id
//     }
//   })
// }

const editStudent = (student) => {
  router.visit(`/students-register/${student.id}`);
};


const confirmDelete = async (student) => {
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
            await axios.delete(`/api/students/${student.id}`);
            showToast("Student deleted successfully!", "success");
            loadEnrollments(); // Reload the table data
        } catch (error) {
            console.error("Error deleting student:", error);
            let errorMessage = "An error occurred while deleting the student.";
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            showToast(errorMessage, "error");
        }
    }
};

// Load data on mount
onMounted(async () => {
  // Load dropdown options
  try {
    const [coursesRes, branchesRes] = await Promise.all([
      axios.get('/api/courses'),
      axios.get('/api/branches')
    ])

    // Fix course options format
    courseOptions.value = [
      { label: 'All Courses', value: null }, // Add "All" option
      ...coursesRes.data.data.map(course => ({
        label: course.name,
        value: course.name
      }))
    ]

    // Fix branch options format
    branchOptions.value = [
      { label: 'All Branches', value: null }, // Add "All" option
      ...branchesRes.data.data.map(branch => ({
        label: branch.name,
        value: branch.name
      }))
    ]
  } catch (error) {
    console.error('Error loading options:', error)
  }



  // Load initial data
  loadEnrollments()
})

const editEnrollment = (student) => {
  router.visit(`/students-register/${student.id}`);
};
</script>

<style scoped>
.student-management {
  padding: 1rem;
}

.p-datatable {
  border-radius: 13px !important;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,0.10), 0 1.5px 4px rgba(0,0,0,0.08); /* Add this line */
}

.p-datatable .p-datatable-thead > tr > th {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  font-weight: 600;
}

.p-datatable .p-datatable-tbody > tr {
  transition: background-color 0.2s;
}

.p-datatable .p-datatable-tbody > tr:hover {
  background-color: #f8f9fa;
}

:deep(.custom-outline-btn:hover) {
  background-color: #6c757d !important;
  color: white !important;
}

/* Toast styles */
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 9999;
}

.toast-item {
  margin-bottom: 0.5rem;
  min-width: 220px;
  padding: 1rem;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  color: #fff;
  font-weight: 500;
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
</style>



<template>
    <MainLayout>


        <ToastContainer :toasts="toasts" :removeToast="removeToast" />

          <div class="student-management">


    <!-- Header with Actions -->
    <div class="card mb-4">
      <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex flex-wrap justify-content-between align-items-center px-3">
        <h5 class="text-white mb-2 mb-md-0">Enrollments Management</h5>
        <div class="d-flex gap-2 flex-wrap">
          <Button
            icon="pi pi-plus"
            label="Add Enrollment"
            @click="navigateToRegister"
            class="gradient-white-btn text-dark px-3 py-2 fw-bold"
            style="min-width: 140px;"
          />
          <Button
            icon="pi pi-download"
            label="Export"
            @click="exportCSV()"
            class="gradient-white-btn text-dark px-3 py-2 fw-bold"
            style="min-width: 120px;"
          />
        </div>
      </div>
    </div>

    <!-- DataTable -->
    <DataTable
      ref="dt"
      v-model:filters="filters"
      v-model:selection="selectedEnrollments"
      :value="enrollments"
      :lazy="true"
      :paginator="true"
      :rows="10"
      :totalRecords="totalRecords"
      :loading="loading"
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
      class="p-datatable-sm"
    >
      <template #header>
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="m-0">Enrollments ({{ totalRecords }})</h6>
          <span class="p-input-icon-left">
            <InputText
              v-model="globalSearchValue"
              placeholder="Global Search"
            />
          </span>
        </div>
      </template>

      <!-- Enrollment ID -->
      <Column
        field="id"
        header="Enrollment ID"
        sortField="id"
        :sortable="true"
        style="min-width: 100px"
      >
        <template #body="slotProps">
          {{ slotProps.data.id }}
        </template>
      </Column>

      <!-- Student Name -->
      <Column
        field="student.full_name"
        header="Student Name"
        sortField="student.full_name"
        :sortable="true"
        style="min-width: 180px"
      >
        <template #body="slotProps">
          {{ slotProps.data.student?.full_name || 'N/A' }}
        </template>
      </Column>

      <!-- Course Name -->
      <Column
        field="course.name"
        header="Course Name"
        sortField="course.name"
        :sortable="true"
        style="min-width: 150px"
      >
        <template #body="slotProps">
          {{ slotProps.data.course?.name || 'N/A' }}
        </template>
        <template #filter="{ filterModel }">
          <Dropdown
            v-model="filterModel.value"
            :options="courseOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Select Course"
            class="p-column-filter"
            showClear
          />
        </template>
      </Column>

      <!-- Status -->
      <Column
        field="status"
        header="Status"
        sortField="status"
        :sortable="true"
        style="min-width: 100px"
      >
        <template #body="slotProps">
          <Tag :value="slotProps.data.status" :severity="slotProps.data.status === 'active' ? 'success' : 'danger'" />
        </template>
        <template #filter="{ filterModel }">
          <Dropdown
            v-model="filterModel.value"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Select Status"
            class="p-column-filter"
            showClear
          />
        </template>
      </Column>

      <!-- Enrollment Date -->
      <Column
        field="enrollment_date"
        header="Enrollment Date"
        sortField="enrollment_date"
        :sortable="true"
        style="min-width: 120px"
      >
        <template #body="slotProps">
          {{ formatDate(slotProps.data.enrollment_date) }}
        </template>
        <template #filter="{ filterModel }">
          <Calendar
            v-model="filterModel.value"
            dateFormat="mm/dd/yy"
            placeholder="Select Date"
            class="p-column-filter"
          />
        </template>
      </Column>

      <!-- Branch -->
      <Column
        field="branch.name"
        header="Branch"
        sortField="branch.name"
        :sortable="true"
        style="min-width: 120px"
      >
        <template #body="slotProps">
          {{ slotProps.data.branch?.name || 'N/A' }}
        </template>
        <template #filter="{ filterModel }">
          <Dropdown
            v-model="filterModel.value"
            :options="branchOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Select Branch"
            class="p-column-filter"
            showClear
          />
        </template>
      </Column>

      <!-- Actions -->
      <Column header="Actions" :exportable="false" style="min-width: 120px">
        <template #body="slotProps">
          <div class="d-flex gap-1">
            <Button
              icon="pi pi-pencil"
              class="p-button-rounded p-button-text p-button-sm p-button-warning"
              @click="editEnrollment(slotProps.data)"
              v-tooltip.top="'Edit'"
            />
            <Button
              icon="pi pi-trash"
              class="p-button-rounded p-button-text p-button-sm p-button-danger"
              @click="confirmDelete(slotProps.data)"
              v-tooltip.top="'Delete'"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- Toast Notification -->
    <div class="toast-container" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'toast-item',
          toast.show ? 'toast-show' : '',
          toast.type === 'success' ? 'toast-success' : 'toast-error'
        ]"
        style="margin-bottom: 0.5rem; min-width: 220px; padding: 1rem; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); color: #fff; font-weight: 500;"
      >
        <span v-if="toast.type === 'success'" style="margin-right: 0.5rem;">✅</span>
        <span v-else style="margin-right: 0.5rem;">❌</span>
        {{ toast.message }}
        <button @click="removeToast(toast.id)" style="background: none; border: none; color: #fff; float: right; font-size: 1.1rem; cursor: pointer;">×</button>
      </div>
    </div>
  </div>
    </MainLayout>
</template>
