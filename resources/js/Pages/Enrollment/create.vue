<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, onMounted, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import RadioButton from 'primevue/radiobutton';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Swal from 'sweetalert2';

const props = defineProps({
    enrollment: Object,
    mode: String
});





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











// --- TYPE DEFINITIONS ---
interface EnrollmentErrors {
    student_id?: string;
    course_id?: string;
    discount_type?: string;
    discount_value?: string;
    payment_plan?: string;
    advance_amount?: string;
    enrollment_date?: string;
    status?: string;
    notes?: string;
    payments?: string;
}

// --- STATE MANAGEMENT ---
const form = ref({
    student_id: null,
    course_id: null,
    discount_type: 'fixed', // 'fixed' or 'percentage'
    discount_value: 0,
    payment_plan: 'full', // 'full', '2-instalments', '3-instalments'
    instalment_1_percentage: 60, // Default percentage
    instalment_2_percentage: 20, // Default percentage for 4-instalment plan
    advance_amount: 0,
    full_payment_paid_now: false, // New state for full payment
    enrollment_date: new Date(),
    status: 'Active', // 'Active', 'Completed', 'Cancelled'
    notes: '',
    payments: []
});

const students = ref([]);
const courses = ref([]);
const selectedCourse = ref(null);
const isLoading = ref(false);
const errors = ref<EnrollmentErrors>({});

// --- DATA FETCHING ---
onMounted(async () => {
    try {
        const [studentsRes, coursesRes] = await Promise.all([
            axios.get('/api/students?verified=true'),
            axios.get('/api/courses')
        ]);

        students.value = (studentsRes.data.data || []).map(s => ({
            ...s,
            display_name: `${s.student_no} - ${s.first_name} ${s.last_name} (${s.id_no})`
        }));
        courses.value = coursesRes.data.data || [];
    } catch (error) {
        console.error("Failed to load initial data:", error);
        // Handle error display to user
    }
});

watch(
    [() => props.enrollment, courses, students],
    ([enrollment, coursesList, studentsList]) => {
        if (enrollment && coursesList.length && studentsList.length) {
            let advance = 0;
            if (enrollment.payments && Array.isArray(enrollment.payments)) {
                const adv = enrollment.payments.find(
                    p => p.payment_type === 'Advance' || p.type === 'Advance'
                );
                if (adv) advance = adv.amount;
            }
            // Normalize status to match Select options
            let normalizedStatus = 'Active';
            if (enrollment.status) {
                const status = enrollment.status.charAt(0).toUpperCase() + enrollment.status.slice(1).toLowerCase();
                if (['Active', 'Completed', 'Cancelled'].includes(status)) {
                    normalizedStatus = status;
                }
            }
            form.value = {
                ...form.value,
                ...enrollment,
                advance_amount: advance,
                enrollment_date: enrollment.enrollment_date ? new Date(enrollment.enrollment_date) : new Date(),
                status: normalizedStatus,
                payments: enrollment.payments
                    ? enrollment.payments.map(p => ({
                        ...p,
                        due_date: p.due_date ? new Date(p.due_date) : null
                    }))
                    : []
            };
            selectedCourse.value = coursesList.find(c => c.id === form.value.course_id) || null;
        }
    },
    { immediate: true }
);

// --- COMPUTED PROPERTIES ---
const courseFee = computed(() => {
    // Use 'fees' to match your database schema
    return selectedCourse.value?.fees || 0;
});

const discountAmount = computed(() => {
    if (!courseFee.value || !form.value.discount_value) return 0;
    if (form.value.discount_type === 'percentage') {
        return (courseFee.value * form.value.discount_value) / 100;
    }
    return form.value.discount_value;
});

const finalPayableAmount = computed(() => {
    const final = courseFee.value - discountAmount.value;
    return final > 0 ? final : 0;
});

const remainingForInstalments = computed(() => {
    const remaining = finalPayableAmount.value - form.value.advance_amount;
    return remaining > 0 ? remaining : 0;
});

const isReadOnly = computed(() => props.mode === 'view');

// --- WATCHERS to react to form changes ---
watch(() => form.value.course_id, (newId) => {
    selectedCourse.value = courses.value.find(c => c.id === newId) || null;
});

watch([() => form.value.payment_plan, finalPayableAmount, () => form.value.advance_amount, () => form.value.instalment_1_percentage, () => form.value.instalment_2_percentage], () => {
    generateInstalments();
}, { deep: true });


// --- METHODS ---
const generateInstalments = () => {
    const instalments = [];
    const enrolmentDate = new Date(form.value.enrollment_date);

    if (form.value.payment_plan === 'full') {
        instalments.push({
            type: 'Full Payment',
            amount: finalPayableAmount.value,
            due_date: enrolmentDate,
            status: 'Pending'
        });
    } else if (form.value.payment_plan.includes('instalment')) {
        // Add Advance Payment
        if (form.value.advance_amount > 0) {
            instalments.push({
                type: 'Advance',
                amount: form.value.advance_amount,
                due_date: enrolmentDate,
                status: 'Paid'
            });
        }

        // Handle the remaining instalments based on the plan
        if (form.value.payment_plan === '2-instalments') {
            // This plan is: Advance + 1 final instalment
            const dueDate = new Date(enrolmentDate);
            dueDate.setMonth(dueDate.getMonth() + 1);
            instalments.push({
                type: 'Instalment 1',
                amount: remainingForInstalments.value,
                due_date: dueDate,
                status: 'Pending'
            });
        } else if (form.value.payment_plan === '3-instalments') {
            // This plan is: Advance + 2 instalments with percentage split
            const firstInstalmentAmount = remainingForInstalments.value * (form.value.instalment_1_percentage / 100);
            const secondInstalmentAmount = remainingForInstalments.value - firstInstalmentAmount;

            // Instalment 1
            const dueDate1 = new Date(enrolmentDate);
            dueDate1.setMonth(dueDate1.getMonth() + 1);
            instalments.push({
                type: 'Instalment 1',
                amount: firstInstalmentAmount,
                due_date: dueDate1,
                status: 'Pending'
            });

            // Instalment 2
            const dueDate2 = new Date(enrolmentDate);
            dueDate2.setMonth(dueDate2.getMonth() + 2);
            instalments.push({
                type: 'Instalment 2',
                amount: secondInstalmentAmount,
                due_date: dueDate2,
                status: 'Pending'
            });
        } else if (form.value.payment_plan === '4-instalments') {
            // CORRECTED LOGIC: This plan is: Advance + 3 instalments with two percentage inputs
            const firstInstalmentAmount = remainingForInstalments.value * (form.value.instalment_1_percentage / 100);
            const secondInstalmentAmount = remainingForInstalments.value * (form.value.instalment_2_percentage / 100);
            const thirdInstalmentAmount = remainingForInstalments.value - firstInstalmentAmount - secondInstalmentAmount;

            // Instalment 1
            const dueDate1 = new Date(enrolmentDate);
            dueDate1.setMonth(dueDate1.getMonth() + 1);
            instalments.push({
                type: `Instalment 1`,
                amount: firstInstalmentAmount,
                due_date: dueDate1,
                status: 'Pending'
            });

            // Instalment 2
            const dueDate2 = new Date(enrolmentDate);
            dueDate2.setMonth(dueDate2.getMonth() + 2);
            instalments.push({
                type: `Instalment 2`,
                amount: secondInstalmentAmount,
                due_date: dueDate2,
                status: 'Pending'
            });

            // Instalment 3
            const dueDate3 = new Date(enrolmentDate);
            dueDate3.setMonth(dueDate3.getMonth() + 3);
            instalments.push({
                type: `Instalment 3`,
                amount: thirdInstalmentAmount > 0 ? thirdInstalmentAmount : 0, // Ensure it's not negative
                due_date: dueDate3,
                status: 'Pending'
            });
        }
    }
    form.value.payments = instalments;
};

const submitForm = async () => {
    // Required field validation
    if (
        !form.value.student_id ||
        !form.value.course_id ||
        !form.value.payment_plan ||
        !form.value.enrollment_date ||
        !form.value.status
    ) {
        Swal.fire({
            icon: "warning",
            title: "Validation Error",
            text: "Please fill in all required fields.",
            confirmButtonColor: "#28a745",
        });
        return;
    }

    // Final check on payments before submitting
    if (form.value.payment_plan === 'full') {
        form.value.payments[0].status = form.value.full_payment_paid_now ? 'Paid' : 'Pending';
    }

    // Prepare payload with formatted dates
    const payload = {
        ...form.value,
        enrollment_date: form.value.enrollment_date
            ? new Date(form.value.enrollment_date).toISOString().slice(0, 10)
            : null,
        payments: form.value.payments.map(p => ({
            ...p,
            due_date: p.due_date
                ? new Date(p.due_date).toISOString().slice(0, 10)
                : null,
        })),
    };

    isLoading.value = true;
    errors.value = {};

    try {
        if (props.mode === 'edit' && props.enrollment?.id) {
            // UPDATE existing enrollment
            await axios.put(`/api/enrollments/${props.enrollment.id}`, payload);
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Enrollment has been updated successfully.",
                confirmButtonColor: "#28a745",
                timer: 4000,
                showConfirmButton: true,
            });
        } else {
            // CREATE new enrollment
            await axios.post('/api/enrollments', payload);
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Enrollment has been created successfully.",
                confirmButtonColor: "#28a745",
                timer: 4000,
                showConfirmButton: true,
            });
        }
        // Optionally redirect or reset
        // router.visit('/enrollments');
    } catch (error) {
        let errorMessage = "An error occurred while submitting the enrollment.";

        if (error.response?.data?.errors) {
            const errorList = Object.values(error.response.data.errors).flat();
            errorMessage = errorList.join(", ");
            errors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }

        // Suggested code change incorporated here
        if (error.response?.data?.error) {
            errorMessage += " (" + error.response.data.error + ")";
        }

        Swal.fire({
            icon: "error",
            title: "Error!",
            text: errorMessage,
            confirmButtonColor: "#dc3545",
        });
    } finally {
        isLoading.value = false;
    }
};

</script>

<template>
    <MainLayout>
        <ToastContainer :toasts="toasts" :removeToast="removeToast" />
        <div class="container-fluid py-2">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div
                                class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                                <h6 class="text-white text-capitalize m-0">
                                    {{ props.mode === 'edit' ? 'Edit Enrollment' : 'Create New Enrollment' }}
                                </h6>

                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">


                    <form @submit.prevent="submitForm" class="card-body p-4 p-md-5">
                        <div class="vstack gap-5">

                            <!-- Section 1: Student Info -->
                            <div class="form-section">
                                <h6 class="section-title">1. Student Information</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="student" class="form-label">Student (ID / NIC / Name)</label>
                                        <Select v-model="form.student_id" :options="students" filter
                                            optionLabel="display_name" optionValue="id"
                                            placeholder="Search for a verified student..." class="w-100"
                                            :class="{ 'p-invalid': errors.student_id }" :disabled="isReadOnly" />
                                        <small v-if="errors.student_id" class="text-danger mt-1">{{ errors.student_id
                                            }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2 & 3: Course and Fee -->
                            <div class="form-section">
                                <h6 class="section-title">2. Course & Fee Details</h6>
                                <div class="row g-4">
                                    <!-- Course Info -->
                                    <div class="col-md-6">
                                        <label for="course" class="form-label">Course</label>
                                        <Select v-model="form.course_id" :options="courses" optionLabel="name"
                                            optionValue="id" placeholder="Select a course" class="w-100"
                                            :class="{ 'p-invalid': errors.course_id }" :disabled="isReadOnly" />
                                        <small v-if="errors.course_id" class="text-danger mt-1">{{ errors.course_id
                                            }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="info-display">
                                                    <label class="info-label">Course Fee</label>
                                                    <p class="info-value">LKR {{ courseFee.toLocaleString() }}</p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="info-display">
                                                    <label class="info-label">Duration</label>
                                                    <p class="info-value">{{ selectedCourse?.duration || 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Discount -->
                                    <div class="col-md-6">
                                        <label class="form-label">Discount Type</label>
                                        <div class="d-flex gap-4 pt-2">
                                            <div class="form-check">
                                                <RadioButton v-model="form.discount_type" inputId="d_fixed"
                                                    name="discountType" value="fixed" :disabled="isReadOnly" />
                                                <label for="d_fixed" class="ms-2">Fixed (LKR)</label>
                                            </div>
                                            <div class="form-check">
                                                <RadioButton v-model="form.discount_type" inputId="d_percent"
                                                    name="discountType" value="percentage" :disabled="isReadOnly" />
                                                <label for="d_percent" class="ms-2">Percentage (%)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="discount_value" class="form-label">Discount Value</label>
                                        <InputNumber v-model="form.discount_value" inputId="discount_value"
                                            class="w-100" :min="0"
                                            :max="form.discount_type === 'percentage' ? 100 : courseFee" :disabled="isReadOnly" />
                                        <small v-if="errors.discount_value" class="text-danger mt-1">{{
                                            errors.discount_value }}</small>
                                    </div>
                                    <!-- Final Amount -->
                                    <div class="col-12">
                                        <div class="info-display bg-success-subtle border-success-subtle">
                                            <label class="info-label text-success-emphasis">Final Payable Amount</label>
                                            <p class="info-value h4 fw-bold text-success">LKR {{
                                                finalPayableAmount.toLocaleString() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4 & 5: Payment Plan -->
                            <div class="form-section">
                                <h6 class="section-title">3. Payment Plan</h6>
                                <div class="vstack gap-4">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div class="form-check">
                                            <RadioButton v-model="form.payment_plan" inputId="p_full" name="paymentPlan"
                                                value="full" :disabled="isReadOnly" />
                                            <label for="p_full" class="ms-2">Full Payment</label>
                                        </div>
                                        <div class="form-check">
                                            <RadioButton v-model="form.payment_plan" inputId="p_2_inst"
                                                name="paymentPlan" value="2-instalments" :disabled="isReadOnly" />
                                            <label for="p_2_inst" class="ms-2">2 Instalments</label>
                                        </div>
                                        <div class="form-check">
                                            <RadioButton v-model="form.payment_plan" inputId="p_3_inst"
                                                name="paymentPlan" value="3-instalments" :disabled="isReadOnly" />
                                            <label for="p_3_inst" class="ms-2">3 Instalments</label>
                                        </div>
                                        <div class="form-check">
                                            <RadioButton v-model="form.payment_plan" inputId="p_4_inst"
                                                name="paymentPlan" value="4-instalments" :disabled="isReadOnly" />
                                            <label for="p_4_inst" class="ms-2">4 Instalments</label>
                                        </div>
                                    </div>

                                    <div v-if="form.payment_plan.includes('instalment')"
                                        class="row g-3 pt-4 border-top">
                                        <div class="col-md-4">
                                            <label for="advance_amount" class="form-label">Advance Amount</label>
                                            <InputNumber v-model="form.advance_amount" inputId="advance_amount"
                                                class="w-100" mode="currency" currency="LKR" locale="en-US"
                                                :disabled="isReadOnly" />
                                            <small v-if="!form.advance_amount" class="text-warning fw-semibold mt-1">
                                                An advance payment is required for instalment plans.
                                            </small>
                                            <small v-if="errors.advance_amount" class="text-danger mt-1">{{
                                                errors.advance_amount }}</small>
                                        </div>
                                        <div class="col-md-4"
                                            v-if="form.payment_plan === '3-instalments' || form.payment_plan === '4-instalments'">
                                            <label for="instalment_1_percentage" class="form-label">Instalment 1
                                                (%)</label>
                                            <InputNumber v-model="form.instalment_1_percentage"
                                                inputId="instalment_1_percentage" class="w-100" suffix="%" :min="0"
                                                :max="100" :disabled="isReadOnly" />
                                        </div>
                                        <div class="col-md-4" v-if="form.payment_plan === '4-instalments'">
                                            <label for="instalment_2_percentage" class="form-label">Instalment 2
                                                (%)</label>
                                            <InputNumber v-model="form.instalment_2_percentage"
                                                inputId="instalment_2_percentage" class="w-100" suffix="%" :min="0"
                                                :max="100" :disabled="isReadOnly" />
                                        </div>
                                    </div>
                                    <div v-if="form.payment_plan === 'full'" class="pt-4 border-top">
                                        <div class="form-check">
                                            <Checkbox v-model="form.full_payment_paid_now" inputId="full_paid"
                                                :binary="true" :disabled="isReadOnly" />
                                            <label for="full_paid" class="ms-2">Paid in Full Now?</label>
                                        </div>
                                    </div>

                                    <!-- Instalment Preview Table -->
                                    <div v-if="form.payments.length > 0" class="pt-2">
                                        <h6 class="fw-semibold text-body-secondary mb-2">Payment Schedule Preview</h6>
                                        <div class="table-responsive border rounded-3">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Amount (LKR)</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(payment, index) in form.payments" :key="index">
                                                        <td>{{ payment.type }}</td>
                                                        <td>{{ payment.amount.toLocaleString('en-US', {
                                                            minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                                        </td>
                                                        <td>
                                                            <Calendar v-model="payment.due_date" dateFormat="yy-mm-dd"
                                                                class="p-inputtext-sm" :disabled="isReadOnly" />
                                                        </td>
                                                        <td>
                                                            <span class="badge"
                                                                :class="payment.status === 'Paid' ? 'text-bg-success' : 'text-bg-warning'">
                                                                {{ payment.status }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 6: Enrolment Info -->
                            <div class="form-section">
                                <h6 class="section-title">4. Enrollment Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="enrollment_date" class="form-label">Enrollment Date</label>
                                        <Calendar v-model="form.enrollment_date" inputId="enrollment_date"
                                            dateFormat="yy-mm-dd" class="w-100" :disabled="isReadOnly" />
                                        <small v-if="errors.enrollment_date" class="text-danger mt-1">{{
                                            errors.enrollment_date }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Enrollment Status</label>
                                        <Select v-model="form.status" :options="['Active', 'Completed', 'Cancelled']"
                                            placeholder="Select status" class="w-100" :disabled="isReadOnly" />
                                        <small v-if="errors.status" class="text-danger mt-1">{{ errors.status }}</small>
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">Notes / Comments</label>
                                        <Textarea v-model="form.notes" id="notes" rows="3" class="w-100" autoResize
                                            :readonly="isReadOnly" />
                                        <small v-if="errors.notes" class="text-danger mt-1">{{ errors.notes }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 7: Submit -->
                            <div class="pt-4 d-flex justify-content-end">
                                <Button
  v-if="!isReadOnly"
  :label="props.mode === 'edit' ? 'Update Enrollment' : 'Create Enrollment'"
  icon="pi pi-check"
  type="submit"
  :loading="isLoading"
  class="p-button-success p-button-lg"
/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    </MainLayout>
</template>

<style scoped>


.info-display {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
}

.info-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #6c757d;
}

.info-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* PrimeVue overrides to better match Bootstrap */
:deep(.p-inputtext),
:deep(.p-select-label) {
    border-color: #ced4da;
    border-radius: 0.375rem !important;
}

:deep(.p-inputtext:focus),
:deep(.p-select:has(.p-select-label:focus)) {
    box-shadow: 0 0 0 0.25rem rgba(0, 110, 253, 0.25) !important;
    border-color: #86b7fe !important;
}

:deep(.p-invalid) {
    border-color: #dc3545 !important;
}
</style>
