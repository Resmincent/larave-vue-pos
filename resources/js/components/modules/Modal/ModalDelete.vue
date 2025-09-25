<script setup lang="ts">
import { ref } from 'vue';

const isOpen = ref(false);
const message = ref('Are you sure you want to delete this item?');
const onConfirm = ref<(() => void) | null>(null);

function open(confirmMessage: string, confirmAction: () => void) {
    message.value = confirmMessage;
    onConfirm.value = confirmAction;
    isOpen.value = true;
}

function close() {
    isOpen.value = false;
}

function confirm() {
    if (onConfirm.value) {
        onConfirm.value();
    }
    close();
}

defineExpose({ open, close });
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex h-screen w-screen items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white shadow-md dark:bg-gray-700">
            <div class="p-4 text-center md:p-5">
                <svg
                    class="mx-auto mb-4 h-12 w-12 text-gray-400 dark:text-gray-200"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 20"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                    {{ message }}
                </h3>
                <div class="flex justify-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300"
                        @click="confirm"
                    >
                        Yes, delete
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                        @click="close"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
