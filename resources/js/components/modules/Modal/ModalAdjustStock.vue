<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
    show: boolean;
    product: {
        id: number;
        name: string;
        sku: string;
        unit: string;
        qty: number;
    } | null;
}>();

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    qty_change: 0,
    note: '',
});

watch(
    () => props.show,
    (val) => {
        if (val && props.product) {
            // reset form setiap kali modal dibuka
            form.reset();
        }
    },
);

function submit() {
    if (!props.product) return;
    form.post(`/inventories/${props.product.id}/adjust`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
    });
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Adjust Stock</h2>
            <div v-if="props.product" class="mb-4 text-sm text-gray-600">
                <p><strong>Product:</strong> {{ props.product.name }} ({{ props.product.sku }})</p>
                <p><strong>Current Stock:</strong> {{ props.product.qty }} {{ props.product.unit }}</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Quantity Change</label>
                    <input
                        v-model="form.qty_change"
                        type="number"
                        placeholder="+10 atau -5"
                        class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                    />
                    <div v-if="form.errors.qty_change" class="mt-1 text-sm text-red-600">
                        {{ form.errors.qty_change }}
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Note</label>
                    <textarea
                        v-model="form.note"
                        placeholder="Catatan penyesuaian stok (opsional)"
                        class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                    />
                    <div v-if="form.errors.note" class="mt-1 text-sm text-red-600">
                        {{ form.errors.note }}
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                        :disabled="form.processing"
                    >
                        Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
