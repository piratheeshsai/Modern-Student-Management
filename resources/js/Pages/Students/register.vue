<template>
    <div class="toast-container">
        <div v-for="toast in toasts" :key="toast.id" :class="[
            'toast-item',
            toast.show ? 'toast-show' : '',
            toast.type === 'success' ? 'toast-success' : 'toast-error',
        ]">
            <div class="toast-content">
                <div class="toast-icon">
                    <i v-if="toast.type === 'success'" class="fas fa-check-circle"></i>
                    <i v-else class="fas fa-exclamation-circle"></i>
                </div>
                <span class="toast-message">{{ toast.message }}</span>
                <button class="toast-close" @click="removeToast(toast.id)" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="registration-page">
        <!-- Enhanced Header -->
        <div class="header-section">
            <div class="container">
                <div class="row">
                    <div class="col-auto">
                        <div class="logo-container">
                            <div class="logo-circle">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="logo-text">
                                <h3>OCBTE</h3>
                                <div class="logo-subtitle">CAMPUS</div>
                            </div>
                        </div>
                    </div>
                    <div class="col title-section">
                        <h1 class="page-title">Student Registration Portal</h1>
                        <p class="page-subtitle">Join Our Academic Excellence Journey</p>
                    </div>
                    <div class="col-auto">
                        <div class="header-actions">
                            <button
                                class="menu-toggle"
                                @click="toggleMobileMenu"
                                :class="{ 'active': mobileMenuOpen }"
                                aria-label="Toggle Menu"
                                :aria-expanded="mobileMenuOpen"
                            >
                                <i class="fas" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                            </button>
                            <div class="header-info d-none d-md-flex">
                                <i class="fas fa-phone-alt"></i>
                                <span>1-800-OCBTE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            class="mobile-menu-overlay"
            :class="{ 'show': mobileMenuOpen }"
            @click="closeMobileMenu"
        ></div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" :class="{ 'open': mobileMenuOpen }">
            <div class="mobile-menu-content">
                <div class="mobile-menu-header">
                    <h4>Navigation</h4>
                    <button class="close-menu" @click="closeMobileMenu" aria-label="Close Menu">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mobile-menu-body">
                    <!-- Title Info for Mobile -->
                    <div class="mobile-title-info">
                        <h3 class="mobile-page-title">Student Registration Portal</h3>
                        <p class="mobile-page-subtitle">Join Our Academic Excellence Journey</p>
                    </div>

                    <!-- Contact Info -->
                    <div class="mobile-contact-info">
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <strong>Support Hotline</strong>
                                <p>1-800-OCBTE</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email Support</strong>
                                <p>support@ocbte.edu</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Office Hours</strong>
                                <p>Mon-Fri: 9AM - 5PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="registration-card">
                        <div class="card-header-custom">
                            <div class="header-content">
                                <div class="header-text">
                                    <h4 class="mb-1 text-white">STUDENT REGISTRATION FORM</h4>
                                    <p class="mb-0">Please fill in all required information in block capitals</p>
                                </div>
                            </div>

                        </div>

                        <div class="card-body-custom">
                            <form @submit.prevent="submitForm">
                                <!-- Personal Information Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-user-circle"></i>
                                        <h5>Personal Information</h5>
                                    </div>

                                    <!-- Title -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Title</label>
                                        <div class="radio-group">
                                            <div class="radio-item" v-for="option in titleOptions" :key="option">
                                                <input class="radio-input" type="radio" :id="`title-${option}`"
                                                    name="title" :value="option" v-model="form.title" />
                                                <label class="radio-label" :for="`title-${option}`">{{ option }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Name -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Full Name</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="input-wrapper">
                                                    <input type="text" class="form-input" v-model="form.surname"
                                                        placeholder="Surname" required />
                                                    <i class="input-icon fas fa-user"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-wrapper">
                                                    <input type="text" class="form-input" v-model="form.firstName"
                                                        placeholder="First Name" required />
                                                    <i class="input-icon fas fa-user"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ID Information -->
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">ID Number</label>
                                                <div class="input-wrapper">
                                                    <input type="text" class="form-input" v-model="form.idNo"
                                                        placeholder="Enter your ID number" required />
                                                    <i class="input-icon fas fa-id-card"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">ID Type</label>
                                                <div class="radio-group-compact">
                                                    <div class="radio-item" v-for="type in idTypes" :key="type">
                                                        <input class="radio-input" type="radio" :id="`id-${type}`"
                                                            name="idType" :value="type" v-model="form.idType" />
                                                        <label class="radio-label" :for="`id-${type}`">{{ type }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date of Birth -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Date of Birth</label>
                                        <div class="input-wrapper">
                                            <input type="date" class="form-input" v-model="form.dob" required />
                                            <i class="input-icon fas fa-calendar-alt"></i>
                                        </div>
                                    </div>

                                    <!-- Home Address -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Home Address</label>
                                        <div class="input-wrapper">
                                            <textarea class="form-input" rows="3" v-model="form.address"
                                                    placeholder="Enter your complete home address" required></textarea>
                                            <i class="input-icon fas fa-home"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Information Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-graduation-cap"></i>
                                        <h5>Academic Information</h5>
                                    </div>

                                    <!-- School Name -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">School Name</label>
                                        <div class="input-wrapper">
                                            <input type="text" class="form-input" v-model="form.schoolName"
                                                   placeholder="Enter your school name" />
                                            <i class="input-icon fas fa-school"></i>
                                        </div>
                                    </div>

                                    <!-- Qualification -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Qualification</label>
                                        <div class="input-wrapper">
                                            <multiselect
                                                v-model="form.qualification"
                                                :options="QualificationOptions.map(q => q.label)"
                                                placeholder="Select your qualification"
                                                :searchable="true"
                                                :multiple="false"
                                                :close-on-select="true"
                                                :allow-empty="true"
                                                :show-labels="false"
                                                class="custom-multiselect"
                                            />
                                        </div>
                                    </div>




                                    <!-- Course and Branch -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">Course Name</label>
                                                <div class="input-wrapper">
                                                    <multiselect
                                                        v-model="form.courseName"
                                                        :options="course.map(c => c.name)"
                                                        placeholder="Select a course"
                                                        :searchable="true"
                                                        :multiple="false"
                                                        :close-on-select="true"
                                                        :allow-empty="true"
                                                        :show-labels="false"
                                                        class="custom-multiselect"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">Branch Name</label>
                                                <div class="input-wrapper">
                                                    <multiselect
                                                        v-model="form.branchName"
                                                        :options="branch.map(b => b.name)"
                                                        placeholder="Select a branch"
                                                        :searchable="true"
                                                        :multiple="false"
                                                        :close-on-select="true"
                                                        :allow-empty="true"
                                                        :show-labels="false"
                                                        class="custom-multiselect"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-address-book"></i>
                                        <h5>Contact Information</h5>
                                    </div>

                                    <!-- Email -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Personal Email</label>
                                        <div class="input-wrapper">
                                            <input type="email" class="form-input" v-model="form.email"
                                                   placeholder="Enter your email address" required />
                                            <i class="input-icon fas fa-envelope"></i>
                                        </div>
                                    </div>

                                    <!-- Contact Numbers -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">Residence Phone</label>
                                                <div class="input-wrapper">
                                                    <input type="tel" class="form-input" v-model="form.resPhone"
                                                           placeholder="Home phone" />
                                                    <i class="input-icon fas fa-phone"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">Mobile Number</label>
                                                <div class="input-wrapper">
                                                    <input type="tel" class="form-input" v-model="form.mobile"
                                                           placeholder="Mobile number" required />
                                                    <i class="input-icon fas fa-mobile-alt"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group-custom">
                                                <label class="form-label-custom">WhatsApp Number</label>
                                                <div class="input-wrapper">
                                                    <input type="tel" class="form-input" v-model="form.whatsapp"
                                                           placeholder="WhatsApp number" />
                                                    <i class="input-icon fab fa-whatsapp"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preferred Contact Methods -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Preferred Contact Methods</label>
                                        <div class="checkbox-grid">
                                            <div class="checkbox-item" v-for="method in contactOptions" :key="method.value">
                                                <input class="checkbox-input" type="checkbox" :id="method.value"
                                                    v-model="form.preferredContacts" :value="method.value" />
                                                <label class="checkbox-label" :for="method.value">{{ method.label }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information Section -->
                                <div class="form-section">
                                    <div class="section-header">
                                        <i class="fas fa-info-circle"></i>
                                        <h5>Additional Information</h5>
                                    </div>

                                    <!-- Employment Details -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Company Name (If Employed)</label>
                                        <div class="input-wrapper">
                                            <input type="text" class="form-input" v-model="form.company"
                                                   placeholder="Enter company name (optional)" />
                                            <i class="input-icon fas fa-building"></i>
                                        </div>
                                    </div>

                                    <!-- Reference Source -->
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">How did you hear about us?</label>
                                        <div class="input-wrapper">
                                            <multiselect
                                                v-model="form.refSource"
                                                :options="referralSources.map(r => r.name)"
                                                placeholder="Select how you heard about us"
                                                :searchable="true"
                                                :multiple="false"
                                                :close-on-select="true"
                                                :allow-empty="true"
                                                :show-labels="false"
                                                class="custom-multiselect"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Declaration -->
                                <div class="declaration-section">
                                    <div class="declaration-content">
                                        <div class="declaration-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div class="declaration-text">
                                            <div class="custom-checkbox">
                                                <input type="checkbox" v-model="form.agree" id="agree" required />
                                                <label for="agree">
                                                    <strong>Declaration:</strong> I confirm that the information provided above is true and correct.
                                                    I have read and understood the rules and regulations of the program and agree to abide by them.
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="submit-section">
                                    <button type="submit" class="submit-btn" :disabled="!form.agree">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Submit Registration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import Swal from 'sweetalert2'

const referralSources = ref([])
const course =ref([]);
const branch = ref([]);
const toasts = ref([]);
const student = ref([]);



const editingStudent = ref(null);
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

const form = reactive({
    title: '',
    surname: '',
    firstName: '',
    idNo: '',
    idType: '',
    dob: '',
    address: '',
    schoolName: '',
    qualification: '',
    courseName: '',
    branchName: '',
    email: '',
    company: '',
    refSource: '',
    resPhone: '',
    mobile: '',
    whatsapp: '',
    preferredContacts: [],
    agree: false,


})

const titleOptions = ['Mr', 'Ms', 'Mrs', 'Miss', 'Rev', 'Dr']

const idTypes = ['NIC', 'P/P', 'Postal']

const contactOptions = [

    { label: 'E-Mail: Personal', value: 'email_personal' },
    { label: 'Phone: Residence', value: 'phone_res' },
    { label: 'Phone: Mobile', value: 'phone_mobile' },
    { label: 'WhatsApp', value: 'whatsapp' },

]

const QualificationOptions = [
    { label: 'GCE O/L', value: 'gce_ol' },
    { label: 'GCE A/L', value: 'gce_al' },
    { label: 'Degree', value: 'degree' },
    { label: 'Diploma', value: 'diploma' },
    { label: 'Other', value: 'other' },
]



onMounted(async () => {
    try {

        // const studentRes = await axios.get('/api/students');
        // student.value = studentRes.data.data;


        const refRes = await axios.get('/api/reference');
        referralSources.value = refRes.data.data;


        const courseRes = await axios.get('/api/courses');
        course.value = courseRes.data.data;

        const branchRes = await axios.get('/api/branches');
        branch.value = branchRes.data.data;

        showToast('Data loaded successfully', 'success');
    } catch (error) {
        console.error('Error loading data:', error);
        showToast('Failed to load data', 'error');
    }
})


const submitForm = async () => {
    // Validation
    if (!form.agree) {
        Swal.fire({
            icon: "warning",
            title: "Declaration Required",
            text: "Please accept the declaration to continue.",
            confirmButtonColor: "#28a745",
        });
        return;
    }

    // Check required fields
    if (!form.title || !form.surname || !form.firstName || !form.idNo || !form.idType ||
        !form.dob || !form.address || !form.courseName || !form.branchName ||
        !form.email || !form.mobile) {
        Swal.fire({
            icon: "warning",
            title: "Validation Error",
            text: "Please fill in all required fields.",
            confirmButtonColor: "#28a745",
        });
        return;
    }

    isLoading.value = true;

    try {
        // Find course, branch, and referral source IDs
        const selectedCourse = course.value.find(c => c.name === form.courseName);
        const selectedBranch = branch.value.find(b => b.name === form.branchName);
        const selectedRefSource = referralSources.value.find(r => r.name === form.refSource);

        const data = {
            title: form.title.trim(),
            first_name: form.firstName.trim(),
            last_name: form.surname.trim(),
            id_type: form.idType.trim(),
            id_no: form.idNo.trim(),
            dob: form.dob,
            address: form.address.trim(),
            school_name: form.schoolName?.trim() || null,
            company_name: form.company?.trim() || null,
            course_id: selectedCourse?.id,
            branch_id: selectedBranch?.id,
            referral_source_id: selectedRefSource?.id || null,
            email: form.email.trim(),
            mobile: form.mobile.trim(),
            phone_residence: form.resPhone?.trim() || null,
            phone_whatsapp: form.whatsapp?.trim() || null,
            qualification: form.qualification?.trim() || null,
            preferred_contacts: form.preferredContacts,
        };

        let response;

        if (editingStudent.value) {
            // Update existing student
            response = await axios.put(`/api/students/${editingStudent.value.id}`, data);
        } else {
            // Create new student
            response = await axios.post("/api/students", data);
        }

        // Success SweetAlert
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: editingStudent.value
                ? "Student information has been updated successfully."
                : "Registration has been submitted successfully. You will receive a confirmation email shortly.",
            confirmButtonColor: "#28a745",
            timer: 5000,
            showConfirmButton: true,
        });

        // Success Toast
        showToast(
            editingStudent.value
                ? "Student updated successfully!"
                : "Registration submitted successfully!",
            "success"
        );

        // Reset form after successful submission
        resetForm();

    } catch (error) {
    console.error("Error submitting form:", error);

    let errorMessage = editingStudent.value
        ? "An error occurred while updating the student information."
        : "An error occurred while submitting your registration.";

    // Check for validation errors first (more specific)
    if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat();
        errorMessage = errors.join(", ");
    } else if (error.response?.data?.message && error.response.data.message !== "Validation failed") {
        // Only use the message if it's not the generic "Validation failed"
        errorMessage = error.response.data.message;
    }

    // Error SweetAlert
    Swal.fire({
        icon: "error",
        title: "Error!",
        text: errorMessage,
        confirmButtonColor: "#dc3545",
    });

    // Error Toast
    showToast(errorMessage, "error");
} finally {
    isLoading.value = false;
}
};



const resetForm = () => {
    Object.assign(form, {
        title: '',
        surname: '',
        firstName: '',
        idNo: '',
        idType: '',
        dob: '',
        address: '',
        schoolName: '',
        qualification: '',
        courseName: '',
        branchName: '',
        email: '',
        company: '',
        refSource: '',
        resPhone: '',
        mobile: '',
        whatsapp: '',
        preferredContacts: [],
        agree: false,
    });
    editingStudent.value = null;
};





const mobileMenuOpen = ref(false);

// Add method to toggle mobile menu
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;

    // Prevent body scroll when menu is open
    if (mobileMenuOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

// Close menu when clicking outside or on links
const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
    document.body.style.overflow = '';
};

// Clean up on component unmount
onUnmounted(() => {
    document.body.style.overflow = '';
});






</script>


