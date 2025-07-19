<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import { ref, onMounted, reactive } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import ToastContainer from '@/Components/ToastContainer.vue'

const courses = ref([]);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);
const toasts = ref([]);

// Form data
const courseForm = reactive({
    id: null,
    name: '',
    duration: '',
    fees: '',
    description: ''
});

// Toaster functionality
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




// Form validation errors
const errors = ref<{
    name?: string;
    duration?: string;
    fees?: string;
    description?: string;
}>({});

const fetchCourses = async () => {
    try {
        const response = await axios.get("/api/courses");
        console.log("API Response:", response.data);
        if (response.data.success) {
            courses.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching courses:", error);
    }
};

const openAddCourseModal = () => {
    resetForm();
    isEditMode.value = false;
    isModalOpen.value = true;
};

const editCourse = (course) => {
    resetForm();
    isEditMode.value = true;

    // Populate form with course data
    courseForm.id = course.id;
    courseForm.name = course.name;
    courseForm.duration = course.duration;
    courseForm.fees = course.fees;
    courseForm.description = course.description;

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

const resetForm = () => {
    courseForm.id = null;
    courseForm.name = '';
    courseForm.duration = '';
    courseForm.fees = '';
    courseForm.description = '';
    errors.value = {};
};

const validateForm = () => {
    errors.value = {};

    if (!courseForm.name.trim()) {
        errors.value.name = 'Course name is required';
    }

    if (!courseForm.duration.trim()) {
        errors.value.duration = 'Duration is required';
    }

    if (!courseForm.fees.trim()) {
        errors.value.fees = 'Course fees is required';
    }

    if (!courseForm.description.trim()) {
        errors.value.description = 'Description is required';
    }

    return Object.keys(errors.value).length === 0;
};

const submitForm = async () => {
    if (!validateForm()) {
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
        let response;

        if (isEditMode.value) {
            // Update existing course
            response = await axios.put(`/api/courses/${courseForm.id}`, {
                name: courseForm.name,
                duration: courseForm.duration,
                fees: courseForm.fees,
                description: courseForm.description
            });
        } else {
            // Create new course
            response = await axios.post('/api/courses', {
                name: courseForm.name,
                duration: courseForm.duration,
                fees: courseForm.fees,
                description: courseForm.description
            });
        }
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: isEditMode.value
                ? "Course has been updated successfully."
                : "Course has been created successfully.",
            confirmButtonColor: "#28a745",
            timer: 2000,
            showConfirmButton: false,
        });

        showToast(
            isEditMode.value
                ? "Course updated successfully!"
                : "Course created successfully!",
            "success"
        );

          resetForm();

        if (response.data.success) {
            // Refresh courses list
            await fetchCourses();
            closeModal();

            // Show success message (you can implement toast notification here)
            console.log(isEditMode.value ? 'Course updated successfully' : 'Course created successfully');
        }
    } catch (error) {
        console.error('Error saving course:', error);

         let errorMessage = isEditMode.value
            ? "An error occurred while updating the course."
            : "An error occurred while creating the course.";
        // Handle validation errors from server
        if (error.response && error.response.data && error.response.data.errors) {
            errors.value = error.response.data.errors;
        }
         Swal.fire({
                icon: "error",
                title: "Error!",
                text: errorMessage,
                confirmButtonColor: "#dc3545",
            });

            showToast(errorMessage, "error");
    } finally {
        isLoading.value = false;
    }
};

const deleteCourse = async (courseId: number) => {
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!",
    })
    if(result.isConfirmed) {
        try {

            await axios.delete(`/api/courses/${courseId}`);
            showToast("Course deleted successfully!", "success");
            await fetchCourses();
        }catch (error) {
            console.error("Error deleting course:", error);
            let errorMessage = "An error occurred while deleting the Course.";
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            showToast(errorMessage, "error");

        }
    }
};

onMounted(() => {
    fetchCourses();
});
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
                                    Courses List
                                </h6>
                                <button class="btn btn-md btn-light text-dark" @click="openAddCourseModal">
                                    <i class="fas fa-plus me-1"></i> Create new Course
                                </button>
                            </div>
                        </div>

                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 fw-bold">
                                                #</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 fw-bold">
                                                Course Name</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 fw-bold">
                                                Duration</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 fw-bold">
                                                Course Fees</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 fw-bold">
                                                Description</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 fw-bold">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="courses.length === 0">
                                            <td colspan="6" class="text-center py-4">
                                                <p class="text-muted">No courses available</p>
                                            </td>
                                        </tr>
                                        <tr v-else v-for="(course, index) in courses" :key="course.id">
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ index + 1 }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ course.name }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">{{ course.duration }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">{{ course.fees }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0 text-truncate description-cell"
                                                    :title="course.description">
                                                    {{ course.description }}
                                                </p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border shadow-sm rounded-2"
                                                        type="button" :id="`courseActionsDropdown${course.id}`"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2"
                                                        :aria-labelledby="`courseActionsDropdown${course.id}`">
                                                        <li>
                                                            <a href="#" class="dropdown-item rounded-2 mx-2 py-2"
                                                                @click.prevent="editCourse(course)">
                                                                <i class="fas fa-edit text-primary me-2"></i>
                                                                <span class="fw-medium">Edit</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="dropdown-item rounded-2 mx-2 py-2"
                                                                @click.prevent="deleteCourse(course.id)">
                                                                <i class="fas fa-trash-alt text-danger me-2"></i>
                                                                <span class="fw-medium">Delete</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Modal -->
        <div class="modal fade" :class="{ show: isModalOpen, 'd-block': isModalOpen }" tabindex="-1" role="dialog"
            aria-labelledby="courseModalLabel" :aria-hidden="!isModalOpen"
            :style="{ backgroundColor: isModalOpen ? 'rgba(0,0,0,0.5)' : '' }">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-dark text-white border-0">
                        <h5 class="modal-title text-white" id="courseModalLabel">
                            <i class="fas fa-graduation-cap me-2"></i>
                            {{ isEditMode ? 'Edit Course' : 'Create New Course' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"
                            aria-label="Close"></button>
                    </div>

                    <form @submit.prevent="submitForm">
                        <div class="modal-body p-5" style="background-color: #f8f9fa;">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="courseName" class="form-label fw-semibold text-dark mb-2">
                                        Course Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.name }" id="courseName" v-model="courseForm.name"
                                        placeholder="Enter course name" style="background-color: white; padding: 15px;">
                                    <div v-if="errors.name" class="invalid-feedback mt-1">
                                        {{ errors.name }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="courseDuration" class="form-label fw-semibold text-dark mb-2">
                                        Duration <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.duration }" id="courseDuration"
                                        v-model="courseForm.duration" placeholder="e.g., 6 months, 2 years"
                                        style="background-color: white; padding: 15px;">
                                    <div v-if="errors.duration" class="invalid-feedback mt-1">
                                        {{ errors.duration }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="courseFees" class="form-label fw-semibold text-dark mb-2">
                                        Course Fees <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.fees }" id="courseFees" v-model="courseForm.fees"
                                        placeholder="Enter course fees" style="background-color: white; padding: 15px;">
                                    <div v-if="errors.fees" class="invalid-feedback mt-1">
                                        {{ errors.fees }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="courseDescription" class="form-label fw-semibold text-dark mb-2">
                                        Description
                                    </label>
                                    <textarea class="form-control form-control-lg border-0 shadow-sm rounded-3"
                                        :class="{ 'is-invalid': errors.description }" id="courseDescription"
                                        v-model="courseForm.description" rows="3" placeholder="Enter course description"
                                        style="background-color: white; padding: 15px; resize: none;"></textarea>
                                    <div v-if="errors.description" class="invalid-feedback mt-1">
                                        {{ errors.description }}
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
                                <span v-else>{{ isEditMode ? 'Update Course' : 'Create Course' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.description-cell {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0 auto;
    display: block;
}

@media (max-width: 768px) {
    .description-cell {
        max-width: 80px;
    }
}

.modal.show {
    display: block !important;
}

.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    padding: 1.5rem 2rem;
}

.form-control:focus {
    border-color: #f13376 !important;
    box-shadow: 0 0 0 0.2rem rgba(226, 30, 161, 0.1) !important;
    background-color: white !important;
}

.form-control {
    transition: all 0.2s ease-in-out;
}

.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
    opacity: 1;
    background-size: 1.2em;
}

.btn-close-white:hover {
    filter: invert(1) grayscale(100%) brightness(150%);
    opacity: 0.8;
}

.btn-close-white:focus {
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
}
</style>
