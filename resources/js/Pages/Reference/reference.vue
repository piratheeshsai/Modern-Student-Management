<script setup lang="ts">
import MainLayout from "@/Layouts/MainLayout.vue";
import { reactive, ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const form = reactive({
    name: "",
    type: "",
    contactInfo: "",
});

const isLoading = ref(false);
const toasts = ref([]);
const references = ref([]);
const isLoadingReferences = ref(false);
const editingRefSource = ref(null);
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

// Fetch references
const fetchRefSource = async () => {
    isLoadingReferences.value = true;
    try {
        const response = await axios.get("/api/reference");
        if (response.data.success) {
            references.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching references:", error);
        showToast("Failed to load references", "error");
    } finally {
        isLoadingReferences.value = false;
    }
};

// Open modal for add
const openAddModal = () => {
    resetForm();
    showModal.value = true;
};

// Open modal for edit
const editBranch = (reference: any) => {
    editingRefSource.value = reference;
    form.name = reference.name;
    form.type = reference.type;
    form.contactInfo = reference.contact_info;
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
            await axios.delete(`/api/reference/${branchId}`);

            Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: "Referral source has been deleted successfully.",
                confirmButtonColor: "#28a745",
                timer: 2000,
                showConfirmButton: false,
            });

            showToast("Referral source deleted successfully!", "success");
            await fetchRefSource();
        } catch (error) {
            console.error("Error deleting Referral source:", error);

            let errorMessage = "An error occurred while deleting the Referral source.";
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
    form.type = "";
    form.contactInfo = "";
    editingRefSource.value = null;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

// Modal form submit
async function handleSubmit() {
    if (!form.name.trim() || !form.type.trim()) {
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
            type: form.type.trim(),
            contact_info: form.contactInfo.trim(),
        };

        if (editingRefSource.value) {
            // Update existing branch
            response = await axios.put(
                `/api/reference/${editingRefSource.value.id}`,
                data
            );
        } else {
            // Create new branch
            response = await axios.post("/api/reference", data);
        }

        // Success SweetAlert
        Swal.fire({
            icon: "success",
            title: "Success!",
            text: editingRefSource.value
                ? "Referral source has been updated successfully."
                : "Referral source has been created successfully.",
            confirmButtonColor: "#28a745",
            timer: 3000,
            showConfirmButton: false,
        });

        // Success Toaster
        showToast(
            editingRefSource.value
                ? "Referral source updated successfully!"
                : "Referral source created successfully!",
            "success"
        );

        closeModal();
        await fetchRefSource();
    } catch (error) {
        console.error("Error with Referral source operation:", error);

        let errorMessage = editingRefSource.value
            ? "An error occurred while updating the Referral source."
            : "An error occurred while creating the Referral source.";

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
    fetchRefSource();
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
            aria-labelledby="refModalLabel"
            :aria-hidden="!showModal"
            :style="{ backgroundColor: showModal ? 'rgba(0,0,0,0.5)' : '' }"
            @click.self="closeModal"
        >
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-dark text-white border-0">
                        <h5 class="modal-title text-white" id="refModalLabel">
                            {{ editingRefSource ? 'Edit Referral source' : 'Add Referral source' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="handleSubmit">
                        <div class="modal-body p-4 bg-light">
                            <div class="mb-3">
                                <label for="refName" class="form-label fw-semibold text-dark">Referral Source Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg shadow-sm"
                                    id="refName" v-model="form.name" placeholder="Enter Reference Source Name  " :disabled="isLoading" required>
                            </div>
                            <div class="mb-3">
                                <label for="refSourceType" class="form-label fw-semibold text-dark">Referral Source Type<span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg shadow-sm"
                                    id="refSourceType" v-model="form.type" placeholder="Enter Type" :disabled="isLoading" required>
                            </div>

                            <div class="mb-3">
                                <label for="ContactInfo" class="form-label fw-semibold text-dark">Referral Source Contact <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg shadow-sm"
                                    id="ContactInfo" v-model="form.contactInfo" placeholder="Enter Type" :disabled="isLoading" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-light px-4 me-2 rounded-3 shadow-sm" @click="closeModal"
                                :disabled="isLoading" style="border: 1px solid #dee2e6;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-dark px-4 rounded-3 shadow-sm" :disabled="isLoading">
                                <span v-if="isLoading">{{ editingRefSource ? 'Updating...' : 'Submitting...' }}</span>
                                <span v-else>{{ editingRefSource ? 'Update' : 'Submit' }}</span>
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
                                Referral source List
                                </h6>
                                <button class="btn btn-md btn-light text-dark" @click="openAddModal">
                                    <i class="fas fa-plus me-1"></i> Add New
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
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Type</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Total Reference</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Contact</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="isLoadingReferences">
                                            <td colspan="5" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-else-if="references.length === 0">
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-muted mb-0">No references found</p>
                                            </td>
                                        </tr>
                                        <tr v-else v-for="(reference, index) in references" :key="reference.id">
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ index + 1 }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ reference.name }}</p>
                                            </td>

                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">{{ reference.type }}</p>
                                            </td>

                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">23</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">{{ reference.contact_info }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border shadow-sm rounded-2"
                                                        type="button" :id="`branchActionsDropdown${reference.id}`"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2"
                                                        :aria-labelledby="`branchActionsDropdown${reference.id}`">
                                                        <li>
                                                            <a class="dropdown-item rounded-2 mx-2 py-2" href="#"
                                                                @click.prevent="editBranch(reference)">
                                                                <i class="fas fa-edit text-primary me-2"></i>
                                                                <span class="fw-medium">Edit</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item rounded-2 mx-2 py-2" href="#"
                                                                @click.prevent="deleteBranch(reference.id)">
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
