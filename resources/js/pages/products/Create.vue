<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/products';
import { BreadcrumbItem } from '@/types';
import { Category } from '@/types/categories';
import { Tax } from '@/types/tax';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: products.index().url,
    },
    {
        title: 'Create Product',
        href: products.create().url,
    },
];

const props = defineProps<{
    taxes: Tax[];
    categories: Category[];
    units: string[];
}>();

const form = useForm({
    sku: '',
    name: '',
    category_id: null as number | null,
    tax_id: null as number | null,
    sell_price: '',
    cost_price: '',
    unit: '',
    is_active: false,
});

function submit() {
    form.post(products.store().url);
}
</script>
<template>
    <Head title="Create Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <div class="w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Product" description="Create a new product" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Product Information" description="Inser product infformation below" class="text-gray-800" />

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Product name"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="flex flex-row">
                        <div class="basis-192">
                            <label class="mb-1 block text-sm font-medium text-gray-700">SKU</label>
                            <input
                                v-model="form.sku"
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                                placeholder="SKU"
                            />
                            <div v-if="form.errors.sku" class="mt-1 text-sm text-red-600">
                                {{ form.errors.sku }}
                            </div>
                        </div>
                        <div class="flex basis-64 items-center justify-center">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.is_active" class="text-blue-600 focus:ring focus:ring-blue-200" />
                                <span class="text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Unit</label>
                        <select
                            v-model="form.unit"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                        >
                            <option disabled value="">-- Pilih Unit --</option>
                            <option v-for="unit in props.units" :key="unit" :value="unit">
                                {{ unit }}
                            </option>
                        </select>

                        <div v-if="form.errors.unit" class="mt-1 text-sm text-red-600">
                            {{ form.errors.unit }}
                        </div>
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Category" description="Assign a category to the product" class="text-gray-800" />

                    <!-- Container kategori -->
                    <div class="mt-4 grid max-h-72 grid-cols-2 gap-4 overflow-y-auto md:grid-cols-3 lg:grid-cols-4">
                        <label
                            v-for="category in props.categories"
                            :key="category.id"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-1 py-1 shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50"
                        >
                            <input
                                type="radio"
                                class="text-blue-600 focus:ring focus:ring-blue-200"
                                v-model="form.category_id"
                                :value="category.id"
                            />
                            <span class="text-sm font-medium text-gray-700">{{ category.name }}</span>
                        </label>
                    </div>

                    <!-- Error message -->
                    <div v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">
                        {{ form.errors.category_id }}
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Taxes -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Tax" description="Assign a tax to the product" class="text-gray-800" />
                    <div class="mt-3 space-y-2">
                        <label v-for="tax in props.taxes" :key="tax.rate" class="flex cursor-pointer items-center gap-2">
                            <input type="radio" class="text-blue-600 focus:ring focus:ring-blue-200" v-model="form.tax_id" :value="tax.id" />
                            <span class="text-gray-700">{{ tax.name }}</span>
                        </label>
                    </div>

                    <div v-if="form.errors.tax_id" class="mt-2 text-sm text-red-600">
                        {{ form.errors.tax_id }}
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Product Price" description="Insert product price below" class="text-gray-800" />

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Cost Price</label>
                        <input
                            v-model="form.cost_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Cost Price"
                        />
                        <div v-if="form.errors.cost_price" class="mt-1 text-sm text-red-600">
                            {{ form.errors.cost_price }}
                        </div>
                    </div>

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Sell Price</label>
                        <input
                            v-model="form.sell_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Sell Price"
                        />
                        <div v-if="form.errors.sell_price" class="mt-1 text-sm text-red-600">
                            {{ form.errors.sell_price }}
                        </div>
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        @click="$inertia.visit(products.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        :disabled="form.processing"
                    >
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
