<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import sales from '@/routes/sales';
import type { BreadcrumbItem } from '@/types';
import type { Customer } from '@/types/customers';
import type { Product } from '@/types/products';
import type { CreateSalePayload, PaymentMethod } from '@/types/sales';
import { Tax } from '@/types/tax';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

// kalkulasi (berguna untuk UX / validasi ringan)
const subtotal = computed(() => form.items.reduce((s, it) => s + Number(it.sell_price) * Number(it.qty), 0));
const discountTotal = computed(() => form.items.reduce((s, it) => s + Number(it.discount || 0), 0));
const taxTotal = computed(() =>
    props.taxes.reduce((s, t) => {
        const taxAmount = form.items
            .filter((it) => it.tax_id === t.id)
            .reduce((ts, it) => ts + ((Number(it.sell_price) * Number(it.qty) - Number(it.discount || 0)) * t.rate) / 100, 0);
        return s + taxAmount;
    }, 0),
);
const grandTotal = computed(() => subtotal.value - discountTotal.value + taxTotal.value);
const paidTotal = computed(() => form.payments.reduce((s, p) => s + Number(p.amount || 0), 0));

const activeProducts = computed(() => props.products.filter((p) => (p as any).is_active));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sales', href: sales.index().url },
    { title: 'Create Sale', href: sales.create().url },
];

const props = defineProps<{
    customers: Customer[];
    products: Product[];
    methods: PaymentMethod[];
    code: string;
    taxes: Tax[];
}>();

const form = useForm<CreateSalePayload>({
    customer_id: null,
    code: props.code,
    status: 'OPEN',
    note: null,
    items: [{ product_id: null, qty: 1, sell_price: 0, discount: 0, tax_id: null, is_active: true }],
    payments: [{ payment_method_id: null, amount: 0, note: null }],
});

// ===== Helpers =====
const addItem = () => {
    form.items.push({ product_id: null, qty: 1, sell_price: 0, discount: 0, tax_id: null, is_active: true });
};
const removeItem = (idx: number) => {
    if (form.items.length > 1) form.items.splice(idx, 1);
};

const addPayment = () => {
    form.payments.push({ payment_method_id: null, amount: 0, note: null });
};
const removePayment = (idx: number) => {
    if (form.payments.length > 1) {
        form.payments.splice(idx, 1);
    }
};

// auto set harga saat pilih product (kalau sell_price masih 0)
const onPickProduct = (idx: number) => {
    const row = form.items[idx];
    const prod = props.products.find((p) => p.id === row.product_id);

    if (!prod) return;

    //  Tidak boleh pilih non aktif
    if (!prod.is_active) {
        row.product_id = null;
        return;
    }

    // isi harga default saat dipilih
    if (prod && (!row.sell_price || row.sell_price === 0)) {
        row.sell_price = Number((prod as any).sell_price ?? 0);
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
                    // set selalu, atau pakai kondisi jika hanya saat 0
                    row.sell_price = Number((prod as any).sell_price ?? 0);
                }
            }
        });
    },
    { deep: false },
);

watch(
    () => form.items.map((it) => it.product_id),
    () => {
        form.items.forEach((row) => {
            const prod = props.products.find((p) => p.id === row.product_id);
            if (prod && !(prod as any).is_active) {
                row.product_id = null; // reset karena tidak boleh pilih produk non-aktif
            }
        });
    },
    { deep: false },
);

// kalau status PAID tapi bayar kurang dari grandTotal, bantu isi payment pertama
watch(
    () => form.status,
    (st) => {
        if (st === 'PAID' && paidTotal.value < grandTotal.value && form.payments.length) {
            const delta = grandTotal.value - paidTotal.value;
            form.payments[0].amount = Number(form.payments[0].amount || 0) + delta;
        }
    },
);

// submit
const submit = () => {
    form.post(sales.store().url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Sale" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Sale" description="Create a new sale" class="text-gray-800" />
                    </div>
                </div>

                <!-- Sale Information -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Sale Information" description="Insert sale information below" class="text-gray-800" />

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <!-- Customer -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Customer</label>
                            <select
                                v-model="form.customer_id"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            >
                                <option :value="null">— Select Customer —</option>
                                <option v-for="c in props.customers" :key="c.id" :value="c.id" class="text-sm text-black">
                                    {{ c.user?.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</div>
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
                                <option value="OPEN">Open</option>
                                <option value="PAID">Paid</option>
                                <option value="VOID">Void</option>
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
                                            <option v-for="c in activeProducts" :key="c.id" :value="c.id" class="text-sm text-black">
                                                {{ c.sku }} — {{ c.name }}
                                            </option>
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
                                            v-model.number="it.sell_price"
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="w-28 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div v-if="form.errors[`items.${idx}.sell_price`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`items.${idx}.sell_price`] }}
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
                                                Number(it.sell_price) * Number(it.qty) -
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

                <!-- Payments -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <div class="flex items-center justify-between">
                        <HeadingSmall title="Payments" description="Detail pembayaran" class="text-gray-800" />
                        <button type="button" @click="addPayment" class="rounded-md border px-3 py-1.5 text-sm text-black hover:bg-gray-50">
                            + Add Payment
                        </button>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-lg border">
                        <table class="min-w-full">
                            <thead class="bg-gray-100 text-sm text-black">
                                <tr>
                                    <th class="px-3 py-2 text-center">Method</th>
                                    <th class="px-3 py-2 text-center">Note</th>
                                    <th class="px-3 py-2 text-center">Amount</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(p, idx) in form.payments" :key="idx" class="border-t text-sm">
                                    <td class="px-3 py-2">
                                        <select
                                            v-model="p.payment_method_id"
                                            class="w-56 rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        >
                                            <option :value="null">— Select —</option>
                                            <option v-for="m in props.methods" :key="m.id" :value="m.id">{{ m.name }} ({{ m.code }})</option>
                                        </select>
                                        <div v-if="form.errors[`payments.${idx}.payment_method_id`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`payments.${idx}.payment_method_id`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input
                                            v-model="p.note"
                                            type="text"
                                            placeholder="Catatan (opsional)"
                                            class="w-full rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <input
                                            v-model.number="p.amount"
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="w-40 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div v-if="form.errors[`payments.${idx}.amount`]" class="mt-1 text-xs text-red-600">
                                            {{ form.errors[`payments.${idx}.amount`] }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <button
                                            type="button"
                                            class="text-red-600 hover:underline disabled:opacity-40"
                                            :disabled="form.payments.length === 1"
                                            @click="removePayment(idx)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="text-sm">
                                <tr class="border-t bg-gray-50">
                                    <td class="px-3 py-2 font-medium">Paid Total</td>
                                    <td></td>
                                    <td class="px-3 py-2 text-right font-medium">
                                        {{ paidTotal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bg-green-50 text-green-800">
                                    <td class="px-3 py-2 font-semibold">Hint</td>
                                    <td colspan="2" class="px-3 py-2">
                                        Untuk status <b>PAID</b>, pastikan <b>Paid Total ≥ Grand Total</b> agar kembalian (change due) terhitung
                                        benar.
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div v-if="form.errors.payments" class="mt-2 text-sm text-red-600">{{ form.errors.payments }}</div>
                </div>

                <!-- Actions -->
                <div class="mb-5 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        @click="$inertia.visit(sales.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Sale' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
