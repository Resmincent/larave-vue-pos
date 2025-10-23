<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import purchases from '@/routes/purchases';
import { BreadcrumbItem } from '@/types';
import { Product } from '@/types/products';
import { CreatePurchasePayload } from '@/types/purchase';
import { Supplier } from '@/types/suppliers';
import { Tax } from '@/types/tax';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Purchase', href: purchases.index().url },
    { title: 'Create Purchase', href: purchases.create().url },
];

const props = defineProps<{
    products: Product[];
    code: string;
    taxes: Tax[];
    suppliers: Supplier[];
}>();

const form = useForm<CreatePurchasePayload>({
    supplier_id: null,
    code: props.code,
    status: 'DRAFT',
    note: null,
    items: [{ product_id: null, qty: 1, cost_price: 0, discount: 0, tax_id: null }],
});

const subtotal = computed(() => form.items.reduce((s, it) => s + Number(it.cost_price) * it.qty, 0));
const discountTotal = computed(() => form.items.reduce((s, it) => s + Number(it.discount || 0), 0));
const taxTotal = computed(() =>
    props.taxes.reduce((s, t) => {
        const taxAmount = form.items
            .filter((it) => it.tax_id === t.id)
            .reduce((ts, it) => ts + ((Number(it.cost_price) * Number(it.qty) - Number(it.discount || 0)) * t.rate) / 100, 0);
        return s + taxAmount;
    }, 0),
);
const grandTotal = computed(() => subtotal.value - discountTotal.value + taxTotal.value);

const addItem = () => {
    form.items.push({ product_id: null, qty: 1, cost_price: 0, discount: 0, tax_id: null });
};
const removeItem = (idx: number) => {
    if (form.items.length > 1) form.items.splice(idx, 1);
};

// auto set harga saat pilih product (kalau cost_price masih 0)
const onPickProduct = (idx: number) => {
    const row = form.items[idx];
    const prod = props.products.find((p) => p.id === row.product_id);
    if (prod && (!row.cost_price || row.cost_price === 0)) {
        row.cost_price = Number((prod as any).cost_price ?? 0);
    }
};

watch(
    () => form.items.map((it) => it.product_id),
    (newVals, oldVals) => {
        newVals.forEach((pid, idx) => {
            if (pid !== oldVals?.[idx]) {
                const row = form.items[idx];
                const prod = props.products.find((p) => p.id === pid);
                if (prod) {
                    row.cost_price = Number((prod as any).cost_price ?? 0);
                }
            }
        });
    },
    { deep: false },
);

const submit = () => {
    form.post(purchases.store().url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Purchase" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Purchase" description="Create a new purchase" class="text-gray-800" />
                    </div>
                </div>

                <!-- Purchase Information -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Purchase Information" description="Insert purchase information below" class="text-gray-800" />

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <!-- Customer -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Customer</label>
                            <select
                                v-model="form.supplier_id"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            >
                                <option :value="null">— Select Supplier —</option>
                                <option v-for="c in props.suppliers" :key="c.id" :value="c.id" class="text-sm text-black">
                                    {{ c.user?.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.supplier_id" class="mt-1 text-sm text-red-600">{{ form.errors.supplier_id }}</div>
                        </div>

                        <!-- Code -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Sale Code</label>
                            <input
                                v-model="form.code"
                                disabled
                                type="text"
                                class="w-full rounded-md border bg-gray-400 px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                placeholder="e.g. S-2025-0001"
                            />
                            <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            >
                                <option value="DRAFT">Draft</option>
                                <option value="RECEIVED">Received</option>
                                <option value="CANCELLED">Cancel</option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mt-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Note</label>
                        <textarea
                            v-model="form.note"
                            rows="2"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Catatan transaksi (opsional)"
                        />
                        <div v-if="form.errors.note" class="mt-1 text-sm text-red-600">{{ form.errors.note }}</div>
                    </div>
                </div>

                <!-- Items -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <div class="flex items-center justify-between">
                        <HeadingSmall title="Items" description="Tambah produk yang dijual" class="text-gray-800" />
                        <button type="button" @click="addItem" class="rounded-md border px-3 py-1.5 text-sm text-black hover:bg-gray-50">
                            + Add Item
                        </button>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-lg border">
                        <table class="min-w-full">
                            <thead class="bg-gray-100 text-sm text-black">
                                <tr>
                                    <th class="px-3 py-2 text-center">Product</th>
                                    <th class="px-3 py-2 text-center">Qty</th>
                                    <th class="px-3 py-2 text-center">Price</th>
                                    <th class="px-3 py-2 text-center">Discount</th>
                                    <th class="px-3 py-2 text-center">Tax</th>
                                    <th class="px-3 py-2 text-center">Line Total</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(it, idx) in form.items" :key="idx" class="border-t text-sm text-black">
                                    <td class="px-3 py-2">
                                        <select
                                            v-model="it.product_id"
                                            @change="onPickProduct(idx)"
                                            class="w-72 rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        >
                                            <option :value="null">— Select —</option>
                                            <option v-for="p in props.products" :key="p.id" :value="p.id">{{ p.sku }} — {{ p.name }}</option>
                                        </select>
                                        <div v-if="form.errors[`items.${idx}.product_id`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.product_id`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <input
                                            v-model.number="it.qty"
                                            type="number"
                                            min="1"
                                            class="w-24 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div v-if="form.errors[`items.${idx}.qty`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.qty`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <input
                                            v-model.number="it.cost_price"
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="w-28 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div v-if="form.errors[`items.${idx}.cost_price`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.cost_price`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <input
                                            v-model.number="it.discount"
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="w-24 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div v-if="form.errors[`items.${idx}.discount`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.discount`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2">
                                        <select
                                            v-model="it.tax_id"
                                            class="w-24 rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        >
                                            <option :value="null">— Select —</option>
                                            <option v-for="p in props.taxes" :key="p.id" :value="p.id">{{ p.name }}</option>
                                        </select>
                                        <div v-if="form.errors[`items.${idx}.tax_id`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.tax_id`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right tabular-nums">
                                        {{
                                            (
                                                Number(it.cost_price) * Number(it.qty) -
                                                Number(it.discount || 0) +
                                                Number(it.tax_id || 0)
                                            ).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })
                                        }}
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <button
                                            type="button"
                                            class="text-red-600 hover:underline disabled:opacity-40"
                                            :disabled="form.items.length === 1"
                                            @click="removeItem(idx)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="m-2 text-sm text-black">
                                <tr class="border-t bg-gray-50">
                                    <td colspan="5" class="px-3 py-2 text-right font-medium">Sub Total</td>
                                    <td class="px-3 py-2 text-right font-medium">
                                        {{ subtotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-3 py-2 text-right">Discount Total</td>
                                    <td class="px-3 py-2 text-right">
                                        {{ discountTotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-3 py-2 text-right">Tax Total</td>
                                    <td class="px-3 py-2 text-right">
                                        {{ taxTotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="border-t bg-gray-100">
                                    <td colspan="5" class="px-3 py-2 text-right text-base font-semibold">Grand Total</td>
                                    <td class="px-3 py-2 text-right text-base font-semibold">
                                        {{ grandTotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div v-if="form.errors.items" class="mt-2 text-sm text-red-600">{{ form.errors.items }}</div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
