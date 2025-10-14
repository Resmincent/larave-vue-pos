<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';

type SaleLite = {
    id: number;
    code: string;
    status?: string; // optional, hanya untuk info
};

const props = defineProps<{
    show: boolean;
    sale: SaleLite | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const form = useForm({});

function close() {
    if (!form.processing) emit('close');
}

function submit() {
    if (!props.sale) return;

    // Jika route kamu PATCH:
    form.patch(`/sales/${props.sale.id}/void`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
    });

    // Jika route kamu POST + spoofing:
    // form.post(`/sales/${props.sale.id}/void`, {
    //   preserveScroll: true,
    //   onSuccess: () => { emit('saved'); emit('close'); },
    //   data: { _method: 'patch' },
    // });
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center" role="dialog" aria-modal="true">
        <!-- overlay -->
        <div class="absolute inset-0 bg-black/50" @click="close" />

        <!-- panel -->
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 text-black shadow-xl">
            <div class="mb-4">
                <h3 class="text-lg font-semibold">Void Sale</h3>
                <p class="text-sm text-gray-600" v-if="sale">
                    Apakah kamu yakin ingin VOID transaksi <strong>{{ sale.code }}</strong
                    >?
                </p>
                <p class="mt-1 text-xs text-amber-700" v-if="(sale?.status || '').toLowerCase() === 'paid'">
                    *Stok akan dikembalikan karena status saat ini PAID.
                </p>

                <!-- tampilkan error umum jika ada -->
                <p v-if="Object.keys(form.errors).length" class="mt-2 text-xs text-red-600">
                    <!-- tampilkan error pertama -->
                    {{ Object.values(form.errors)[0] as string }}
                </p>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" class="rounded border px-4 py-2 text-sm" @click="close" :disabled="form.processing">Cancel</button>
                <button
                    type="button"
                    class="rounded bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-60"
                    @click="submit"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Void</span>
                    <span v-else>Processing...</span>
                </button>
            </div>
        </div>
    </div>
</template>
