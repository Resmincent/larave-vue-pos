<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import sales from '@/routes/sales';
import { BreadcrumbItem } from '@/types';
import { Sale } from '@/types/sales';
import { computed } from 'vue';

const props = defineProps<{
    sale: Sale;
}>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Sales', href: sales.index().url },
    { title: 'Detail Sale', href: sales.show(props.sale).url },
];

const formatIDR = (n?: number | string | null) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(n ?? 0));

const subtotal = computed(() => {
    if (props.sale?.subtotal != null) return Number(props.sale.subtotal);
    const items = props.sale?.sale_items ?? [];
    return items.reduce((s, it) => s + Number(it.price ?? 0) * Number(it.qty ?? 0) - Number(it.discount ?? 0), 0);
});

const discountTotal = computed(() => Number(props.sale?.discount_total ?? 0));
const taxTotal = computed(() => Number(props.sale?.tax_total ?? 0));
const grandTotal = computed(() =>
    props.sale?.grand_total != null ? Number(props.sale.grand_total) : subtotal.value - discountTotal.value + taxTotal.value,
);

const paidTotal = computed(() => Number(props.sale?.paid_total ?? 0));
const changeDue = computed(() => (props.sale?.change_due != null ? Number(props.sale.change_due) : Math.max(paidTotal.value - grandTotal.value, 0)));
console.log('Sale: ', props.sale);

console.log('sale items', props.sale.sale_items);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <div class="grid gap-y-3 md:grid-rows-2">
                    <div class="flex justify-between">
                        <div class="text-sm text-black">Sale Code</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.code }}</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-sm text-black">Casier</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.user?.name }}</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-sm text-black">Status</div>
                        <div v-if="props.sale.status === 'OPEN'">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800"
                                >{{ props.sale.status }}</span
                            >
                        </div>
                        <div v-else-if="props.sale.status === 'PAID'">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                >{{ props.sale.status }}</span
                            >
                        </div>
                        <div v-else>
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-red-300 bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
                                >{{ props.sale.status }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <HeadingSmall title="Customer Information" class="my-2 border-b text-gray-800" />

                <div class="grid gap-x-6 gap-y-3 py-2 md:grid-cols-1">
                    <div class="grid grid-cols-[140px_1fr] items-baseline">
                        <div class="text-sm text-black">Customer Name</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.customer?.user.name }}</div>
                    </div>

                    <div class="grid grid-cols-[140px_1fr] items-baseline">
                        <div class="text-sm text-black">Email</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.customer?.user.email }}</div>
                    </div>

                    <div class="grid grid-cols-[140px_1fr] items-baseline">
                        <div class="text-sm text-black">Phone Number</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.customer?.phone }}</div>
                    </div>

                    <div class="grid grid-cols-[140px_1fr] items-baseline">
                        <div class="text-sm text-black">Address</div>
                        <div class="text-sm font-bold text-black">{{ props.sale.customer?.address }}</div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <HeadingSmall title="Product Information" class="my-2 border-b text-gray-800" />

                <!-- Tabel Items -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-600">
                            <tr class="border-b">
                                <th class="py-2 pr-4">SKU</th>
                                <th class="py-2 pr-4">Product</th>
                                <th class="py-2 pr-4">Unit</th>
                                <th class="py-2 pr-4 text-right">Qty</th>
                                <th class="py-2 pr-4 text-right">Price</th>
                                <th class="py-2 pr-4 text-right">Discount</th>
                                <th class="py-2 pr-0 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="it in props.sale.sale_items" :key="it.id" class="border-b last:border-0">
                                <td class="py-2 pr-4 text-black">{{ it.product.sku }}</td>
                                <td class="py-2 pr-4 text-black">{{ it.product.name }}</td>
                                <td class="py-2 pr-4 text-black">{{ it.product.unit }}</td>
                                <td class="py-2 pr-4 text-right text-black">{{ Number(it.qty ?? 0) }}</td>
                                <td class="py-2 pr-4 text-right text-black">{{ formatIDR(it.price) }}</td>
                                <td class="py-2 pr-4 text-right text-black">{{ formatIDR(it.discount) }}</td>
                                <td class="py-2 pr-0 text-right text-black">
                                    {{ formatIDR(Number(it.price ?? 0) * Number(it.qty ?? 0) - Number(it.discount ?? 0)) }}
                                </td>
                            </tr>
                            <tr v-if="!props.sale.sale_items?.length">
                                <td class="py-4 text-center text-gray-500" colspan="7">No items</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Total -->
                <div class="mt-4 grid gap-2 md:ml-auto md:w-1/2">
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Subtotal</div>
                        <div class="text-sm font-semibold text-black">{{ formatIDR(subtotal) }}</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Discount Total</div>
                        <div class="text-sm font-semibold text-black">- {{ formatIDR(discountTotal) }}</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Tax Total</div>
                        <div class="text-sm font-semibold text-black">{{ formatIDR(taxTotal) }}</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] border-t pt-2">
                        <div class="text-sm text-black">Grand Total</div>
                        <div class="text-sm font-bold text-black">{{ formatIDR(grandTotal) }}</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Paid</div>
                        <div class="text-sm font-semibold text-black">{{ formatIDR(paidTotal) }}</div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Change</div>
                        <div class="text-sm font-semibold text-black">{{ formatIDR(changeDue) }}</div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <div v-if="props.sale.note !== null">
                <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                    <div class="text-sm text-black">
                        {{ props.sale.note }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
