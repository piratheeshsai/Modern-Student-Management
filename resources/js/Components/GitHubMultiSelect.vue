<template>
  <div class="github-multiselect">
    <div
      class="github-multiselect-input"
      @click="toggleDropdown"
      :class="{ 'is-open': isOpen }"
    >
      <!-- Selected items as badges -->
      <div
        v-for="item in selectedItems"
        :key="item"
        class="github-badge"
      >
        <span class="badge-text">{{ item }}</span>
        <button
          type="button"
          class="badge-close"
          @click.stop="removeItem(item)"
          aria-label="Remove"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Placeholder or search input -->
      <input
        v-if="selectedItems.length === 0 || isOpen"
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        class="search-input"
        :placeholder="selectedItems.length === 0 ? placeholder : 'Search...'"
        @focus="openDropdown"
        @blur="closeDropdown"
        @keydown.enter.prevent="selectFirstMatch"
        @keydown.escape="closeDropdown"
        @keydown.backspace="handleBackspace"
      />

      <!-- Dropdown arrow -->
      <div class="dropdown-arrow">
        <i class="fas fa-chevron-down" :class="{ 'rotated': isOpen }"></i>
      </div>
    </div>

    <!-- Dropdown options -->
    <div
      v-if="isOpen"
      class="github-dropdown"
      @mousedown.prevent
    >
      <div
        v-for="option in filteredOptions"
        :key="option"
        class="dropdown-option"
        :class="{ 'selected': selectedItems.includes(option) }"
        @click="toggleOption(option)"
      >
        <div class="option-content">
          <i
            v-if="selectedItems.includes(option)"
            class="fas fa-check option-check"
          ></i>
          <span>{{ option }}</span>
        </div>
      </div>

      <div v-if="filteredOptions.length === 0" class="no-options">
        No options found
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue'

interface Props {
  modelValue: string[]
  options: string[]
  placeholder?: string
  searchable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select options...',
  searchable: true
})

const emit = defineEmits<{
  'update:modelValue': [value: string[]]
}>()

const isOpen = ref(false)
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement>()

const selectedItems = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options
  }

  return props.options.filter(option =>
    option.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
}

const openDropdown = () => {
  isOpen.value = true
}

const closeDropdown = () => {
  setTimeout(() => {
    isOpen.value = false
    searchQuery.value = ''
  }, 150)
}

const toggleOption = (option: string) => {
  const newSelected = [...selectedItems.value]
  const index = newSelected.indexOf(option)

  if (index > -1) {
    newSelected.splice(index, 1)
  } else {
    newSelected.push(option)
  }

  selectedItems.value = newSelected
  searchQuery.value = ''
}

const removeItem = (item: string) => {
  const newSelected = selectedItems.value.filter(selected => selected !== item)
  selectedItems.value = newSelected
}

const selectFirstMatch = () => {
  if (filteredOptions.value.length > 0) {
    toggleOption(filteredOptions.value[0])
  }
}

const handleBackspace = () => {
  if (searchQuery.value === '' && selectedItems.value.length > 0) {
    removeItem(selectedItems.value[selectedItems.value.length - 1])
  }
}

// Close dropdown when clicking outside
watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', closeDropdown)
  } else {
    document.removeEventListener('click', closeDropdown)
  }
})
</script>

<style scoped>
.github-multiselect {
  position: relative;
  width: 100%;
}

.github-multiselect-input {
  width: 100%;
  min-height: 44px;
  padding: 6px 32px 6px 8px;
  border: 1px solid #d0d7de;
  border-radius: 6px;
  background-color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-size: 14px;
}

.github-multiselect-input:hover {
  border-color: #b1b9c2;
}

.github-multiselect-input.is-open,
.github-multiselect-input:focus-within {
  border-color: #0969da;
  box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.12);
}

.github-badge {
  display: inline-flex;
  align-items: center;
  background-color: #ddf4ff;
  border: 1px solid #54aeff;
  border-radius: 12px;
  padding: 2px 6px 2px 8px;
  font-size: 12px;
  font-weight: 500;
  color: #0969da;
  gap: 4px;
  max-width: 200px;
}

.badge-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.badge-close {
  background: none;
  border: none;
  color: #0969da;
  cursor: pointer;
  padding: 0;
  width: 14px;
  height: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
  font-size: 10px;
}

.badge-close:hover {
  background-color: rgba(9, 105, 218, 0.15);
}

.search-input {
  border: none;
  outline: none;
  flex: 1;
  min-width: 60px;
  font-size: 14px;
  padding: 4px 0;
  background: transparent;
}

.search-input::placeholder {
  color: #656d76;
}

.dropdown-arrow {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  color: #656d76;
  pointer-events: none;
  transition: transform 0.2s;
}

.dropdown-arrow .fa-chevron-down.rotated {
  transform: rotate(180deg);
}

.github-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #ffffff;
  border: 1px solid #d0d7de;
  border-radius: 6px;
  box-shadow: 0 8px 24px rgba(140, 149, 159, 0.2);
  z-index: 1000;
  max-height: 200px;
  overflow-y: auto;
  margin-top: 4px;
}

.dropdown-option {
  padding: 8px 12px;
  cursor: pointer;
  font-size: 14px;
  border-bottom: 1px solid #f6f8fa;
  transition: background-color 0.1s;
}

.dropdown-option:last-child {
  border-bottom: none;
}

.dropdown-option:hover {
  background-color: #f6f8fa;
}

.dropdown-option.selected {
  background-color: #ddf4ff;
  color: #0969da;
}

.option-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.option-check {
  color: #0969da;
  font-size: 12px;
  width: 12px;
}

.no-options {
  padding: 12px;
  text-align: center;
  color: #656d76;
  font-style: italic;
  font-size: 14px;
}

/* Success/Error badge variants */
.github-badge.success {
  background-color: #dcfce7;
  border-color: #22c55e;
  color: #15803d;
}

.github-badge.success .badge-close {
  color: #15803d;
}

.github-badge.success .badge-close:hover {
  background-color: rgba(21, 128, 61, 0.15);
}

.github-badge.error {
  background-color: #fef2f2;
  border-color: #ef4444;
  color: #dc2626;
}

.github-badge.error .badge-close {
  color: #dc2626;
}

.github-badge.error .badge-close:hover {
  background-color: rgba(220, 38, 38, 0.15);
}

/* Green badge like "none" in your screenshot */
.github-badge.none-style {
  background-color: #dcfce7;
  border-color: #16a34a;
  color: #15803d;
  font-weight: 600;
}

.github-badge.none-style .badge-close {
  color: #15803d;
}

.github-badge.none-style .badge-close:hover {
  background-color: rgba(21, 128, 61, 0.15);
}
</style>
