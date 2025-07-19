<template>
    <MainLayout>
        <ToastContainer :toasts="toasts" :removeToast="removeToast" />

        <div class="payment-management">
            <!-- Header with Actions -->
            <div class="card mb-4">
                <div
                    class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex flex-wrap justify-content-between align-items-center px-3">
                    <h5 class="text-white mb-2 mb-md-0">Payment Management</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <Button icon="pi pi-plus" label="Add Payment" @click="navigateToPaymentCreate"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 140px;" />
                        <Button icon="pi pi-download" label="Export" @click="exportCSV()"
                            class="gradient-white-btn text-dark px-3 py-2 fw-bold" style="min-width: 120px;" />
                    </div>
                </div>
            </div>

            <!-- Date Range Filter Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Date Filter:</label>
                        </div>
                        <div class="col-md-2">
                            <Dropdown v-model="dateFilterType" :options="dateFilterOptions" optionLabel="label"
                                optionValue="value" placeholder="Filter By" class="w-100" @change="applyDateRangeFilter" />
                        </div>
                        <div class="col-md-3">
                            <Calendar v-model="dateFrom" dateFormat="mm/dd/yy" placeholder="From Date"
                                class="w-100" @date-select="applyDateRangeFilter" showIcon />
                        </div>
                        <div class="col-md-3">
                            <Calendar v-model="dateTo" dateFormat="mm/dd/yy" placeholder="To Date"
                                class="w-100" @date-select="applyDateRangeFilter" showIcon />
                        </div>
                        <div class="col-md-2">
                            <Button label="Clear" @click="clearDateRange"
                                class="p-button-outlined p-button-secondary w-100" />
                        </div>
                    </div>
                    <div class="row mt-2" v-if="dateFrom || dateTo">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="pi pi-info-circle me-1"></i>
                                Filtering {{ dateFilterType || 'dates' }} from {{ dateFrom ? formatDate(dateFrom) : 'earliest' }}
                                to {{ dateTo ? formatDate(dateTo) : 'latest' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable with Reusable Loading Spinner -->
            <div class="datatable-wrapper">
                <TableSpinner :loading="loading" loading-text="Loading Payments..." />

                <DataTable
                    ref="dt"
                    v-model:filters="filters"
                    v-model:selection="selectedPayments"
                    :value="payments"
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
                            <h6 class="m-0">Payments ({{ totalRecords }})</h6>
                            <span class="p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="globalSearchValue" placeholder="Global Search" @input="onGlobalSearch" />
                            </span>
                        </div>
                    </template>

                    <!-- Payment ID -->
                    <Column field="id" header="Payment ID" :sortable="true" style="min-width: 100px">
                        <template #body="slotProps">
                            {{ slotProps.data.id }}
                        </template>
                    </Column>

                    <!-- Student Name -->
                    <Column field="enrollment.student.full_name" header="Student Name" :sortable="false" style="min-width: 180px">
                        <template #body="slotProps">
                            {{ slotProps.data.enrollment?.student?.full_name || 'N/A' }}
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="Search Student" class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Amount -->
                    <Column field="amount" header="Amount" :sortable="true" style="min-width: 120px">
                        <template #body="slotProps">
                            LKR {{ parseFloat(slotProps.data.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="Search Amount" class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Payment Type -->
                    <Column field="payment_type" header="Payment Type" :sortable="true" style="min-width: 130px">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.payment_type"
                                 :severity="getPaymentTypeSeverity(slotProps.data.payment_type)" />
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="paymentTypeOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Type" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Due Date -->
                    <Column field="due_date" header="Due Date" :sortable="true" style="min-width: 130px">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.due_date) }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Calendar v-model="filterModel.value" dateFormat="mm/dd/yy" placeholder="Select Date"
                                class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Paid Date -->
                    <Column field="paid_date" header="Paid Date" :sortable="true" style="min-width: 130px">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.paid_date) }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Calendar v-model="filterModel.value" dateFormat="mm/dd/yy" placeholder="Select Date"
                                class="p-column-filter" />
                        </template>
                    </Column>

                    <!-- Status -->
                    <Column field="status" header="Status" :sortable="true" style="min-width: 100px">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status"
                                :severity="getStatusSeverity(slotProps.data.status)" />
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="statusOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Status" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Course Name -->
                    <Column field="enrollment.course.name" header="Course" :sortable="false" style="min-width: 150px">
                        <template #body="slotProps">
                            {{ slotProps.data.enrollment?.course?.name || 'N/A' }}
                        </template>
                        <template #filter="{ filterModel }">
                            <Dropdown v-model="filterModel.value" :options="courseOptions" optionLabel="label"
                                optionValue="value" placeholder="Select Course" class="p-column-filter" showClear />
                        </template>
                    </Column>

                    <!-- Branch -->
                    <Column field="enrollment.branch.name" header="Branch" :sortable="false" style="min-width: 120px">
                        <template #body="slotProps">
                            {{ slotProps.data.enrollment?.branch?.name || 'N/A' }}
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

                    <!-- Empty State when no data -->
                    <template #empty>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="pi pi-inbox"></i>
                            </div>
                            <h6 class="mt-3 mb-2">No Payments Found</h6>
                            <p class="text-muted">Try adjusting your filters or add a new payment</p>
                            <Button
                                label="Add Payment"
                                icon="pi pi-plus"
                                @click="navigateToPaymentCreate"
                                class="p-button-outlined"
                            />
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Toast notifications -->
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

<script>
import MainLayout from '@/Layouts/MainLayout.vue'
import TableSpinner from '@/Components/TableSpinner.vue'
import ToastContainer from '@/Components/ToastContainer.vue'
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import Tag from 'primevue/tag'
import Menu from 'primevue/menu'

export default {
    name: 'Payments',
    components: {
        MainLayout,
        TableSpinner,
        ToastContainer,
        DataTable,
        Column,
        InputText,
        Button,
        Dropdown,
        Calendar,
        Tag,
        Menu
    },
    props: {
        user: Object,
    },
    setup() {
        const payments = ref([])
        const selectedPayments = ref([])
        const filters = ref({})
        const loading = ref(false)
        const totalRecords = ref(0)
        const globalSearchValue = ref('')
        const dateFrom = ref(null)
        const dateTo = ref(null)
        const dt = ref()
        const menuRefs = ref({})
        const toasts = ref([])

        // Filter options
        const statusOptions = ref([
            { label: 'Pending', value: 'pending' },
            { label: 'Paid', value: 'paid' },
            { label: 'Overdue', value: 'overdue' },
            { label: 'Refunded', value: 'refunded' }
        ])

        const paymentTypeOptions = ref([
            { label: 'Advance', value: 'Advance' },
            { label: '1st Installment', value: '1st Installment' },
            { label: '2nd Installment', value: '2nd Installment' },
            { label: '3rd Installment', value: '3rd Installment' },
            { label: 'Final Payment', value: 'Final Payment' },
            { label: 'Full Payment', value: 'Full Payment' }
        ])

        const courseOptions = ref([])
        const branchOptions = ref([])

        const dateFilterType = ref('due_date')

        const dateFilterOptions = ref([
            { label: 'Due Date', value: 'due_date' },
            { label: 'Paid Date', value: 'paid_date' },
            { label: 'Both Dates', value: 'both' }
        ])

        // Initialize filters
        const initFilters = () => {
            filters.value = {
                'global': { value: null, matchMode: 'contains' },
                'enrollment.student.full_name': { value: null, matchMode: 'contains' },
                'amount': { value: null, matchMode: 'contains' },
                'payment_type': { value: null, matchMode: 'equals' },
                'due_date': { value: null, matchMode: 'dateIs' },
                'paid_date': { value: null, matchMode: 'dateIs' },
                'status': { value: null, matchMode: 'equals' },
                'enrollment.course.name': { value: null, matchMode: 'equals' },
                'enrollment.branch.name': { value: null, matchMode: 'equals' }
            }
        }

        // Load data
        const loadLazyData = async (event = {}) => {
            loading.value = true
            try {
                const params = {
                    page: (event.first || 0) / (event.rows || 10) + 1,
                    per_page: event.rows || 10,
                    sort_field: event.sortField || 'created_at',
                    sort_order: event.sortOrder === 1 ? 'asc' : 'desc',
                    filters: JSON.stringify(buildFilters())
                }

                console.log('API Request params:', params)
                console.log('Built filters:', buildFilters())

                const response = await axios.get('/api/payments', { params })

                console.log('API Response:', response.data)

                payments.value = response.data.data
                totalRecords.value = response.data.total
            } catch (error) {
                console.error('Error loading payments:', error)
                showToast('Failed to load payments: ' + (error.response?.data?.message || error.message), 'error')
            } finally {
                loading.value = false
            }
        }

        // Build filters for API
        const buildFilters = () => {
            const apiFilters = {}

            if (globalSearchValue.value) {
                apiFilters.global = { value: globalSearchValue.value }
            }

            Object.keys(filters.value).forEach(key => {
                if (filters.value[key].value !== null && filters.value[key].value !== '') {
                    if (key === 'enrollment.student.full_name') {
                        apiFilters.student = { value: filters.value[key].value }
                    } else if (key === 'enrollment.course.name') {
                        apiFilters.course = { value: filters.value[key].value }
                    } else if (key === 'enrollment.branch.name') {
                        apiFilters.branch = { value: filters.value[key].value }
                    } else if (key === 'due_date' || key === 'paid_date') {
                        // Format date for API
                        apiFilters[key] = { value: formatDateForAPI(filters.value[key].value) }
                    } else {
                        apiFilters[key] = { value: filters.value[key].value }
                    }
                }
            })

            // Add date range filters with type
            if (dateFrom.value) {
                apiFilters.date_from = { value: formatDateForAPI(dateFrom.value) }
            }
            if (dateTo.value) {
                apiFilters.date_to = { value: formatDateForAPI(dateTo.value) }
            }
            if (dateFilterType.value) {
                apiFilters.date_filter_type = { value: dateFilterType.value }
            }

            return apiFilters
        }

        // Event handlers
        const onPage = (event) => {
            console.log('Page event:', event)
            loadLazyData(event)
        }

        const onSort = (event) => {
            console.log('Sort event:', event)
            loadLazyData(event)
        }

        const onFilter = (event) => {
            console.log('Filter event:', event)
            loadLazyData(event)
        }

        const onGlobalSearch = () => {
            loadLazyData({
                first: 0,
                rows: 10,
                sortField: 'created_at',
                sortOrder: -1
            })
        }

        const applyDateRangeFilter = () => {
            loadLazyData({
                first: 0,
                rows: 10,
                sortField: 'created_at',
                sortOrder: -1
            })
        }

        const clearDateRange = () => {
            dateFrom.value = null
            dateTo.value = null
            dateFilterType.value = 'due_date'
            applyDateRangeFilter()
        }

        // Utility functions
        const formatDate = (date) => {
            if (!date) return 'N/A'
            return new Date(date).toLocaleDateString('en-US')
        }

        const formatDateForAPI = (date) => {
            if (!date) return null
            return new Date(date).toISOString().split('T')[0]
        }

        const getPaymentTypeSeverity = (type) => {
            const severityMap = {
                'Advance': 'info',
                '1st Installment': 'primary',
                '2nd Installment': 'primary',
                '3rd Installment': 'primary',
                'Final Payment': 'success',
                'Full Payment': 'success'
            }
            return severityMap[type] || 'secondary'
        }

        const getStatusSeverity = (status) => {
            const severityMap = {
                'pending': 'warning',
                'paid': 'success',
                'overdue': 'danger',
                'refunded': 'info'
            }
            return severityMap[status] || 'secondary'
        }

        // Navigation
        const navigateToPaymentCreate = () => {
            router.visit('/payments/create')
        }

        // Actions menu
        const getActionMenu = (payment) => [
            {
                label: 'View',
                icon: 'pi pi-eye',
                command: () => viewPayment(payment)
            },
            {
                label: 'Edit',
                icon: 'pi pi-pencil',
                command: () => editPayment(payment)
            },
            {
                label: 'Delete',
                icon: 'pi pi-trash',
                command: () => deletePayment(payment)
            }
        ]

        const setMenuRef = (el, id) => {
            if (el) {
                menuRefs.value[id] = el
            }
        }

        const openMenu = (event, id) => {
            menuRefs.value[id]?.toggle(event)
        }

        const viewPayment = (payment) => {
            router.visit(`/payments/${payment.id}`)
        }

        const editPayment = (payment) => {
            router.visit(`/payments/${payment.id}/edit`)
        }

        const deletePayment = async (payment) => {
            if (confirm('Are you sure you want to delete this payment?')) {
                try {
                    await axios.delete(`/api/payments/${payment.id}`)
                    showToast('Payment deleted successfully', 'success')
                    loadLazyData()
                } catch (error) {
                    showToast('Failed to delete payment', 'error')
                }
            }
        }

        const exportCSV = () => {
            dt.value.exportCSV()
        }

        // Toast notifications
        const showToast = (message, type = 'success') => {
            const toast = {
                id: Date.now(),
                message,
                type,
                show: false
            }
            toasts.value.push(toast)
            setTimeout(() => toast.show = true, 100)
            setTimeout(() => removeToast(toast.id), 5000)
        }

        const removeToast = (id) => {
            const index = toasts.value.findIndex(toast => toast.id === id)
            if (index > -1) {
                toasts.value[index].show = false
                setTimeout(() => {
                    toasts.value.splice(index, 1)
                }, 300)
            }
        }

        // Load dropdown options
        const loadDropdownOptions = async () => {
            try {
                const coursesResponse = await axios.get('/api/courses')
                courseOptions.value = coursesResponse.data.map(course => ({
                    label: course.name,
                    value: course.name
                }))

                const branchesResponse = await axios.get('/api/branches')
                branchOptions.value = branchesResponse.data.map(branch => ({
                    label: branch.name,
                    value: branch.name
                }))
            } catch (error) {
                console.error('Error loading dropdown options:', error)
            }
        }

        // Initialize
        onMounted(() => {
            initFilters()
            loadLazyData()
            loadDropdownOptions()
        })

        return {
            payments,
            selectedPayments,
            filters,
            loading,
            totalRecords,
            globalSearchValue,
            dateFrom,
            dateTo,
            dt,
            statusOptions,
            paymentTypeOptions,
            courseOptions,
            branchOptions,
            dateFilterType,
            dateFilterOptions,
            onPage,
            onSort,
            onFilter,
            onGlobalSearch,
            applyDateRangeFilter,
            clearDateRange,
            formatDate,
            getPaymentTypeSeverity,
            getStatusSeverity,
            navigateToPaymentCreate,
            getActionMenu,
            setMenuRef,
            openMenu,
            exportCSV,
            toasts,
            removeToast
        }
    }
}
</script>

<style scoped>
.payment-management {
    padding: 1rem;
}

/* DataTable Wrapper for relative positioning */
.datatable-wrapper {
    position: relative;
    min-height: 400px;
}

/* Ensure DataTable doesn't show its own loading */
:deep(.p-datatable-loading-overlay) {
    display: none !important;
}
</style>
