<template>
    <div v-if="loading" class="table-loading-overlay" :class="overlayClass">
        <div class="table-spinner-container">
            <div class="trending-spinner" :class="spinnerSize">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-text">{{ loadingText }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TableSpinner',
    props: {
        loading: {
            type: Boolean,
            default: false
        },
        loadingText: {
            type: String,
            default: 'Loading...'
        },
        size: {
            type: String,
            default: 'medium', // small, medium, large
            validator: (value) => ['small', 'medium', 'large'].includes(value)
        },
        theme: {
            type: String,
            default: 'light', // light, dark
            validator: (value) => ['light', 'dark'].includes(value)
        }
    },
    computed: {
        spinnerSize() {
            return `size-${this.size}`
        },
        overlayClass() {
            return `theme-${this.theme}`
        }
    }
}
</script>

<style scoped>
/* Base overlay styles */
.table-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    border-radius: 8px;
    animation: fadeIn 0.3s ease-in;
}

.table-loading-overlay.theme-light {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(3px);
}

.table-loading-overlay.theme-dark {
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(3px);
}

.table-spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

/* Base spinner styles */
.trending-spinner {
    position: relative;
    margin-bottom: 20px;
}

.trending-spinner.size-small {
    width: 50px;
    height: 50px;
}

.trending-spinner.size-medium {
    width: 80px;
    height: 80px;
}

.trending-spinner.size-large {
    width: 100px;
    height: 100px;
}

.spinner-ring {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 3px solid transparent;
    border-radius: 50%;
    animation: spin 2s linear infinite;
}

.spinner-ring:nth-child(1) {
    border-top-color: #667eea;
    animation-duration: 1.5s;
}

.spinner-ring:nth-child(2) {
    border-right-color: #764ba2;
    animation-duration: 2s;
    animation-direction: reverse;
    transform: scale(0.8);
}

.spinner-ring:nth-child(3) {
    border-bottom-color: #f093fb;
    animation-duration: 2.5s;
    transform: scale(0.6);
}

.spinner-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: 600;
    color: #667eea;
    letter-spacing: 0.5px;
    animation: pulse 1.5s ease-in-out infinite;
    white-space: nowrap;
}

.trending-spinner.size-small .spinner-text {
    font-size: 9px;
}

.trending-spinner.size-medium .spinner-text {
    font-size: 11px;
}

.trending-spinner.size-large .spinner-text {
    font-size: 13px;
}

/* Dark theme text color */
.theme-dark .spinner-text {
    color: #ffffff;
}

/* Animations */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
