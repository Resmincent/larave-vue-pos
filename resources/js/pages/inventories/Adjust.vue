<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import inventories from '@/routes/inventories';
import { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    inventory: { id: number; qty: number; product_id: number };
    product: { id: number; name: string; sku: string; unit: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventories',
        href: inventories.index().url,
    },
    {
        title: `Adjust Stock - ${props.product.name}`,
        href: inventories.adjust(props.product.id).url,
    },
];

const form = useForm({
    qty_change: 0,
    note: '',
});

function submit() {
    form.post(inventories.adjust(props.product.id).url);
}
</script>

<template>
    <Head title="Adjust Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <div class="w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Adjust Inventory" description="Update stock quantity for product" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Product Information -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Product Information" description="Detail produk terkait inventory" class="text-gray-800" />

                    <div class="py-3">
                        <p class="text-sm text-gray-600"><span class="font-semibold">Name:</span> {{ props.product.name }}</p>
                        <p class="text-sm text-gray-600"><span class="font-semibold">SKU:</span> {{ props.product.sku }}</p>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">Current Stock:</span> {{ props.inventory.qty }} {{ props.product.unit }}
                        </p>
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Adjust Form -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Stock Adjustment" description="Masukkan jumlah perubahan stock" class="text-gray-800" />

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Quantity Change</label>
                        <input
                            v-model="form.qty_change"
                            type="number"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="contoh: +10 atau -5"
                        />
                        <div v-if="form.errors.qty_change" class="mt-1 text-sm text-red-600">
                            {{ form.errors.qty_change }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Gunakan angka positif untuk menambah stok, negatif untuk mengurangi.</p>
                    </div>

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Note</label>
                        <textarea
                            v-model="form.note"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Catatan penyesuaian stok (opsional)"
                        ></textarea>
                        <div v-if="form.errors.note" class="mt-1 text-sm text-red-600">
                            {{ form.errors.note }}
                        </div>
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        @click="$inertia.visit(inventories.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        :disabled="form.processing"
                    >
                        Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
