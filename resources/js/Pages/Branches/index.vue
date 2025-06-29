<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const form = reactive({
    name: "",
    location: "",
});

const isLoading = ref(false);
const toasts = ref([]);
const branches = ref([]);
const isLoadingBranches = ref(false);
const editingBranch = ref(null);
const showModal = ref(false);

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

// Fetch branches
const fetchBranches = async () => {
    isLoadingBranches.value = true;
    try {
        const response = await axios.get("/api/branches");
        if (response.data.success) {
            branches.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching branches:", error);
        showToast("Failed to load branches", "error");
    } finally {
        isLoadingBranches.value = false;
    }
};

// Open modal for add
const openAddModal = () => {
    resetForm();
    showModal.value = true;
};

// Open modal for edit
const editBranch = (branch: any) => {
    editingBranch.value = branch;
    form.name = branch.name;
    form.location = branch.location;
    showModal.value = true;
};

// Delete branch function
const deleteBranch = async (branchId: number) => {
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
            await axios.delete(`/api/branches/${branchId}`);

            Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: "Branch has been deleted successfully.",
                confirmButtonColor: "#28a745",
                timer: 2000,
                showConfirmButton: false,
            });

            showToast("Branch deleted successfully!", "success");
            await fetchBranches();
        } catch (error) {
            console.error("Error deleting branch:", error);

            let errorMessage = "An error occurred while deleting the branch.";
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            Swal.fire({
                icon: "error",
                title: "Error!",
                text: errorMessage,
                confirmButtonColor: "#dc3545",
            });

            showToast(errorMessage, "error");
        }
    }
};

const resetForm = () => {
    form.name = "";
    form.location = "";
    editingBranch.value = null;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

// Modal form submit
async function handleSubmit() {
    if (!form.name.trim() || !form.location.trim()) {
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
        const data = {
            name: form.name.trim(),
            location: form.location.trim(),
        };

        if (editingBranch.value) {
            // Update existing branch
            response = await axios.put(
                `/api/branches/${editingBranch.value.id}`,
                data
            );
        } else {
            // Create new branch
            response = await axios.post("/api/branches", data);
        }

        // Success SweetAlert
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: editingBranch.value
                ? "Branch has been updated successfully."
                : "Branch has been created successfully.",
            confirmButtonColor: "#28a745",
            timer: 3000,
            showConfirmButton: false,
        });

        // Success Toaster
        showToast(
            editingBranch.value
                ? "Branch updated successfully!"
                : "Branch created successfully!",
            "success"
        );

        closeModal();
        await fetchBranches();
    } catch (error) {
        console.error("Error with branch operation:", error);

        let errorMessage = editingBranch.value
            ? "An error occurred while updating the branch."
            : "An error occurred while creating the branch.";

        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(", ");
        }

        // Error SweetAlert
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: errorMessage,
            confirmButtonColor: "#dc3545",
        });

        // Error Toaster
        showToast(errorMessage, "error");
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    fetchBranches();
});
</script>

<template>
    <MainLayout>
        <!-- Toast Container -->
        <div class="toast-container">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'toast-item',
                    toast.show ? 'toast-show' : '',
                    toast.type === 'success' ? 'toast-success' : 'toast-error',
                ]"
            >
                <div class="toast-content">
                    <div class="toast-icon">
                        <i
                            v-if="toast.type === 'success'"
                            class="fas fa-check-circle"
                        ></i>
                        <i v-else class="fas fa-exclamation-circle"></i>
                    </div>
                    <span class="toast-message">{{ toast.message }}</span>
                    <button
                        class="toast-close"
                        @click="removeToast(toast.id)"
                        aria-label="Close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Modal -->
        <div
            class="modal fade"
            :class="{ show: showModal, 'd-block': showModal }"
            tabindex="-1"
            role="dialog"
            aria-labelledby="branchModalLabel"
            :aria-hidden="!showModal"
            :style="{ backgroundColor: showModal ? 'rgba(0,0,0,0.5)' : '' }"
            @click.self="closeModal"
        >
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-dark text-white border-0">
                        <h5 class="modal-title text-white" id="branchModalLabel">
                            {{ editingBranch ? 'Edit Branch' : 'Add Branch' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="handleSubmit">
                        <div class="modal-body p-4 bg-light">
                            <div class="mb-3">
                                <label for="branchName" class="form-label fw-semibold text-dark">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg shadow-sm"
                                    id="branchName" v-model="form.name" placeholder="Enter branch name" :disabled="isLoading" required>
                            </div>
                            <div class="mb-3">
                                <label for="branchLocation" class="form-label fw-semibold text-dark">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg shadow-sm"
                                    id="branchLocation" v-model="form.location" placeholder="Enter location" :disabled="isLoading" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-light px-4 me-2 rounded-3 shadow-sm" @click="closeModal"
                                :disabled="isLoading" style="border: 1px solid #dee2e6;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-dark px-4 rounded-3 shadow-sm" :disabled="isLoading">
                                <span v-if="isLoading">{{ editingBranch ? 'Updating...' : 'Submitting...' }}</span>
                                <span v-else>{{ editingBranch ? 'Update' : 'Submit' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="container-fluid py-2">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                                <h6 class="text-white text-capitalize m-0">
                                    Branches List
                                </h6>
                                <button class="btn btn-md btn-light text-dark" @click="openAddModal">
                                    <i class="fas fa-plus me-1"></i> Add Branch
                                </button>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0" style="overflow-x: visible">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">#</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-2">Name</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Location</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="isLoadingBranches">
                                            <td colspan="5" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else-if="branches.length === 0">
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-muted mb-0">No branches found</p>
                                            </td>
                                        </tr>
                                        <tr v-else v-for="(branch, index) in branches" :key="branch.id">
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ index + 1 }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ branch.name }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">{{ branch.location }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border shadow-sm rounded-2"
                                                        type="button" :id="`branchActionsDropdown${branch.id}`"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2"
                                                        :aria-labelledby="`branchActionsDropdown${branch.id}`">
                                                        <li>
                                                            <a class="dropdown-item rounded-2 mx-2 py-2" href="#"
                                                                @click.prevent="editBranch(branch)">
                                                                <i class="fas fa-edit text-primary me-2"></i>
                                                                <span class="fw-medium">Edit</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item rounded-2 mx-2 py-2" href="#"
                                                                @click.prevent="deleteBranch(branch.id)">
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
    </MainLayout>
</template>
