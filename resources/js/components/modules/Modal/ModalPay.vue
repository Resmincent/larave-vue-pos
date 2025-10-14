<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

type PaymentMethod = { id: number; name: string; code: string };
type SaleLite = { id: number; code: string; grandTotal: number | string; paidTotal: number | string };

const props = defineProps<{
    show: boolean;
    methods: PaymentMethod[];
    sale: SaleLite | null;
}>();

const emit = defineEmits(['close', 'saved']);

/* --- angka aman dari props --- */
const totalDue = computed(() => Number(props.sale?.grandTotal ?? 0)); // grand_total
const totalPaid = computed(() => Number(props.sale?.paidTotal ?? 0)); // paid_total

/* --- sisa sebelum input & sisa setelah input --- */
const stillDueBefore = computed(() => Math.max(0, totalDue.value - totalPaid.value)); // sisa tagihan saat modal dibuka

const form = useForm({
    payments: [{ payment_method_id: null as number | null, amount: 0, note: '' }],
});

const inputAmount = computed({
    get: () => Number(form.payments[0].amount ?? 0),
    set: (v: number) => (form.payments[0].amount = Number(v) || 0),
});

const remainingAfterInput = computed(() => Math.max(0, stillDueBefore.value - inputAmount.value));

/* --- isi default saat modal dibuka & sale ready --- */
watch(
    () => props.show && !!props.sale,
    (open) => {
        if (open) {
            form.reset();
            form.clearErrors();
            form.payments[0].payment_method_id = props.methods?.[0]?.id ?? null;
            form.payments[0].amount = stillDueBefore.value; // default = sisa tagihan
            form.payments[0].note = '';
        }
    },
    { immediate: true },
);

function close() {
    if (!form.processing) emit('close');
}
function submit() {
    if (!props.sale) return;
    form.patch(`/sales/${props.sale.id}/pay`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
    });
}

/* --- formatter --- */
const fmtIDR = (n: number) => `Rp${(n ?? 0).toLocaleString('id-ID')}`;
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="close" />
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 text-black shadow-xl">
            <div class="mb-4">
                <h3 class="text-sm font-semibold">Pay Sale</h3>
                <p v-if="sale" class="text-sm text-gray-600">
                    <span class="font-medium">{{ sale.code }}</span>
                    <br />
                    Total: <strong>{{ fmtIDR(totalDue) }}</strong> - Paid: <strong>{{ fmtIDR(totalPaid) }}</strong> = Remaining:
                    <strong>{{ fmtIDR(stillDueBefore) }}</strong>
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Payment Method</label>
                    <select v-model.number="form.payments[0].payment_method_id" class="w-full rounded border px-3 py-2 text-sm">
                        <option :value="null" disabled>-- Select method --</option>
                        <option v-for="m in methods" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                    <p v-if="form.errors['payments.0.payment_method_id']" class="mt-1 text-xs text-red-600">
                        {{ form.errors['payments.0.payment_method_id'] }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Amount</label>
                    <input v-model.number="inputAmount" type="number" min="0" step="1" class="w-full rounded border px-3 py-2 text-sm" />
                    <p class="mt-1 text-xs text-gray-600">
                        Remaining after this payment:
                        <strong>{{ fmtIDR(remainingAfterInput) }}</strong>
                    </p>
                    <p v-if="form.errors['payments.0.amount']" class="mt-1 text-xs text-red-600">
                        {{ form.errors['payments.0.amount'] }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Note (optional)</label>
                    <textarea
                        v-model="form.payments[0].note"
                        rows="2"
                        class="w-full rounded border px-3 py-2 text-sm"
                        placeholder="e.g. Cash / Transfer"
                    />
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" class="rounded border px-4 py-2 text-sm" @click="close" :disabled="form.processing">Cancel</button>
                <button
                    type="button"
                    class="rounded bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600 disabled:opacity-60"
                    @click="submit"
                    :disabled="form.processing || !form.payments[0].payment_method_id"
                >
                    <span v-if="!form.processing">Pay</span>
                    <span v-else>Processing...</span>
                </button>
            </div>
        </div>
    </div>
</template>
