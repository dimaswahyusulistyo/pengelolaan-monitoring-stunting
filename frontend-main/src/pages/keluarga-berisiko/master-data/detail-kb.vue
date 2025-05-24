<template>
    <VDialog :modelValue="visible" @update:modelValue="$emit('update:visible', $event)" max-width="800px">
        <VCard class="detail-form">
            <VCardTitle class="text-h5 pa-4 d-flex align-center">
                <span class="ml-4">Detail Data Keluarga Berisiko</span>
                <VSpacer />
                <VBtn icon variant="text" @click="closeDialog">
                    <i class="bx bx-x"></i>
                </VBtn>
            </VCardTitle>

            <VCardText>
                <div class="d-flex justify-end mb-4" v-if="isAllowedToEdit">
                    <VBtn color="warning" @click="editItem(item)" class="rounded-pill px-5">
                        <i class="bx bx-edit-alt mr-2"></i> Edit
                    </VBtn>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Data Keluarga</h3>
                    </div>

                    <VRow>
                        <VCol cols="12" md="12">
                            <VTextField v-if="isAllowedToEdit" label="No Kartu Keluarga" :value="item.no_kartu_keluarga"
                                readonly persistent-placeholder />
                        </VCol>
                    </VRow>

                    <VRow>
                        <template v-if="isAllowedToEdit">
                            <VCol cols="12" md="6">
                                <VTextField label="NIK Kepala Keluarga" :value="item.nik_kepala_keluarga" readonly
                                    persistent-placeholder />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="Nama Kepala Keluarga" :value="item.nama_kepala_keluarga" readonly
                                    persistent-placeholder />
                            </VCol>
                        </template>
                        <template v-else>
                            <VCol cols="12" md="12">
                                <VTextField label="Nama Kepala Keluarga" :value="item.nama_kepala_keluarga" readonly
                                    persistent-placeholder />
                            </VCol>
                        </template>
                    </VRow>

                    <VRow>
                        <template v-if="isAllowedToEdit">
                            <VCol cols="12" md="6">
                                <VTextField label="NIK Istri" :value="item.nik_istri" readonly persistent-placeholder />
                            </VCol>
                            <VCol cols="12" md="6">
                                <VTextField label="Nama Istri" :value="item.nama_istri" readonly
                                    persistent-placeholder />
                            </VCol>
                        </template>
                        <template v-else>
                            <VCol cols="12" md="12">
                                <VTextField label="Nama Istri" :value="item.nama_istri" readonly
                                    persistent-placeholder />
                            </VCol>
                        </template>
                    </VRow>

                    <VRow>
                        <VCol cols="12" md="12">
                            <VTextField label="Alamat"
                                :value="`${item.desa?.nama_desa || ''}, ${item.desa?.kecamatan?.nama_kecamatan || ''}`"
                                readonly persistent-placeholder />
                        </VCol>
                    </VRow>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Sasaran</h3>
                    </div>
                    <div class="table-responsive">
                        <VTable class="custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center header-cell-top-left no-column">No</th>
                                    <th class="text-center name-column">Sasaran</th>
                                    <th class="text-center header-cell-top-right status-column">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(sasaran, index) in sasaranData" :key="sasaran.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td>{{ sasaran.name }}</td>
                                    <td class="text-center">
                                        <VChip :color="getSasaranStatusColor(getSasaranValue(sasaran.field))"
                                            size="small" :variant="getSasaranValue(sasaran.field) ? 'outlined' : 'flat'"
                                            :class="`status-chip ${getSasaranValue(sasaran.field) ? 'active' : 'inactive'}`">
                                            {{ getSasaranStatusLabel(getSasaranValue(sasaran.field)) }}
                                        </VChip>
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </div>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Penapisan</h3>
                    </div>
                    <div class="table-responsive">
                        <VTable class="custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center header-cell-top-left no-column">No</th>
                                    <th class="text-center name-column">Penapisan</th>
                                    <th class="text-center header-cell-top-right status-column">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(penapisan, index) in penapisanData" :key="penapisan.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td>{{ penapisan.name }}</td>
                                    <td class="text-center">
                                        <VChip :color="getPenapisanStatusColor(getPenapisanValue(penapisan.field))"
                                            size="small"
                                            :variant="getPenapisanValue(penapisan.field) ? 'outlined' : 'flat'"
                                            :class="`status-chip ${getPenapisanValue(penapisan.field) ? 'active' : 'inactive'}`">
                                            {{ getPenapisanStatusLabel(getPenapisanValue(penapisan.field)) }}
                                        </VChip>
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </div>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Jenis Pendampingan yang Diterima</h3>
                    </div>
                    <VRow>
                        <VCol cols="12" md="12" class="mt-2">
                            <div class="jenis-pendampingan-container">
                                <div class="pendampingan-list">
                                    <VChip v-for="pendampingan in getPendampinganLabels()" :key="pendampingan"
                                        color="primary" variant="outlined" class="mr-2 mb-2">
                                        {{ pendampingan }}
                                    </VChip>
                                </div>
                            </div>
                        </VCol>
                    </VRow>
                </div>
            </VCardText>

            <VCardActions class="pa-4">
                <VSpacer />
                <VBtn color="grey-darken-1" variant="text" @click="closeDialog">
                    Tutup
                </VBtn>
            </VCardActions>
        </VCard>
    </VDialog>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue';
import {
    VCard,
    VCardTitle,
    VCardText,
    VCardActions,
    VSpacer,
    VBtn,
    VRow,
    VCol,
    VTextField,
    VTable,
    VChip,
    VDialog
} from 'vuetify/components';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();

const props = defineProps({
    visible: Boolean,
    item: Object
});

const emit = defineEmits(['update:visible', 'edit-item']);

const currentUser = computed(() => authStore.user);
const isAllowedToEdit = computed(() => {
    return ['Dinas KB'].includes(currentUser.value?.role);
});

const sasaranData = [
    { id: 1, name: 'Punya Anak 0-23 Bulan', field: 'punya_anak_0_23_bulan' },
    { id: 2, name: 'Punya Anak 24-59 Bulan', field: 'punya_anak_24_59_bulan' },
    { id: 3, name: 'PUS', field: 'pus' },
    { id: 4, name: 'PUS Hamil', field: 'pus_hamil' }
];

const penapisanData = [
    { id: 5, name: 'Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak', field: 'sumber_air_minum_tidak_layak' },
    { id: 6, name: 'Keluarga Tidak Mempunyai Jamban yang Layak', field: 'jamban_tidak_layak' },
    { id: 7, name: 'PUS 4 Terlalu Muda (Umur Istri < 20 Tahun)', field: 'pus_4_terlalu_muda' },
    { id: 8, name: 'PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)', field: 'pus_4_terlalu_tua' },
    { id: 9, name: 'PUS 4 Terlalu Dekat (< 2 Tahun)', field: 'pus_4_terlalu_dekat' },
    { id: 10, name: 'PUS 4 Terlalu Banyak (≥ 3 Anak)', field: 'pus_4_terlalu_banyak' },
    { id: 11, name: 'PUS 4 Terlalu', field: 'pus_4_terlalu' },
    { id: 12, name: 'Bukan Peserta KB Modern', field: 'bukan_peserta_kb_modern' }
];

const jenisPendampinganOptions = [
    { value: 1, title: 'Ya, fasilitas rujukan' },
    { value: 2, title: 'Ya, fasilitas bansos' },
    { value: 3, title: 'Ya, fasilitas KIE' },
    { value: 4, title: 'Ya, surveilans melalui elsimil' },
    { value: 5, title: 'Ya, surveilans melalui EPPGBM' },
    { value: 6, title: 'Ya, Bapak Asuh Anak Stunting' },
    { value: 7, title: 'Ya, fasilitas Pemberian Makanan Tambahan (PMT)' },
    { value: 8, title: 'Tidak ada' },
].map(option => ({
    ...option,
    value: Number(option.value)
}));

const getSasaranValue = (field) => {
    if (!props.item) return null;

    if (props.item.determinan_k_b && props.item.determinan_k_b[field] !== undefined) {
        return props.item.determinan_k_b[field];
    }

    if (props.item.determinanKB && props.item.determinanKB[field] !== undefined) {
        return props.item.determinanKB[field];
    }

    if (props.item.determinan_kb && props.item.determinan_kb[field] !== undefined) {
        return props.item.determinan_kb[field];
    }

    return props.item[field];
};

const getPenapisanValue = (field) => {
    if (!props.item) return null;

    if (props.item.determinan_k_b && props.item.determinan_k_b[field] !== undefined) {
        return props.item.determinan_k_b[field];
    }

    if (props.item.determinanKB && props.item.determinanKB[field] !== undefined) {
        return props.item.determinanKB[field];
    }

    if (props.item.determinan_kb && props.item.determinan_kb[field] !== undefined) {
        return props.item.determinan_kb[field];
    }

    return props.item[field];
};

const getSasaranStatusLabel = (value) => {
    if (value === 1 || value === true || value === '1') return 'Terpenuhi';
    return 'Tidak Terpenuhi';
};

const getSasaranStatusColor = (value) => {
    if (value === 1 || value === true || value === '1') return 'success';
    return 'error';
};

const getPenapisanStatusLabel = (value) => {
    if (value === 1 || value === true || value === '1') return 'Terpenuhi';
    if (value === 0 || value === false || value === '0' || value === 2 || value === '2') return 'Tidak Terpenuhi';
    return 'Tidak Berlaku';
};

const getPenapisanStatusColor = (value) => {
    if (value === 1 || value === true || value === '1') return 'success';
    if (value === 0 || value === false || value === '0' || value === 2 || value === '2') return 'error';
    return 'secondary';
};

const getPendampinganLabels = () => {
    if (!props.item) return ['Tidak ada'];

    let jenisPendampinganDiterima = [];

    if (props.item.jenis_pendampingan_diterima) {
        if (Array.isArray(props.item.jenis_pendampingan_diterima)) {
            jenisPendampinganDiterima = props.item.jenis_pendampingan_diterima.map(Number);
        } else if (typeof props.item.jenis_pendampingan_diterima === 'string') {
            jenisPendampinganDiterima = props.item.jenis_pendampingan_diterima
                .split(',')
                .map(s => Number(s.trim()))
                .filter(s => s);
        } else if (typeof props.item.jenis_pendampingan_diterima === 'number') {
            jenisPendampinganDiterima = [props.item.jenis_pendampingan_diterima];
        }
    }

    if (jenisPendampinganDiterima.length === 0) {
        return ['Tidak ada'];
    }

    return jenisPendampinganOptions
        .filter(option => jenisPendampinganDiterima.includes(option.value))
        .map(option => option.title);
};

const editItem = (item) => {
    emit('edit-item', item);
};

const closeDialog = () => {
    emit('update:visible', false);
};
</script>

<style scoped>
.detail-form {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

.section-header {
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding-bottom: 8px;
    margin-bottom: 16px;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

.table-responsive {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin: 8px 0 16px;
    -webkit-overflow-scrolling: touch;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: rgb(var(--v-theme-surface));
}

.custom-table .no-column {
    width: 40px;
}

.custom-table .status-column {
    width: 110px;
}

.custom-table .name-column {
    width: auto;
}

.custom-table th,
.custom-table td {
    border-right: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    padding: 12px 16px;
    vertical-align: middle;
}

.custom-table th {
    background-color: #f5f5f5;
    font-weight: 600;
    color: #333;
}

.custom-table tbody tr:hover {
    background-color: #f8f9fa;
}

.header-cell-top-left {
    border-top-left-radius: 8px;
}

.header-cell-top-right {
    border-top-right-radius: 8px;
}

.custom-table tr:last-child td:first-child {
    border-bottom-left-radius: 8px;
}

.custom-table tr:last-child td:last-child {
    border-bottom-right-radius: 8px;
}

.status-chip {
    font-weight: 500;
    letter-spacing: 0.5px;
}

.jenis-pendampingan-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    background-color: #f9f9f9;
}

.pendampingan-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.v-theme--dark {
    .custom-table {
        border-color: #444;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .custom-table th {
        background-color: #1e1e1e;
        color: #ffffff;
    }

    .custom-table td {
        background-color: #2d2d2d;
        color: #ffffff;
    }

    .custom-table th,
    .custom-table td {
        border-color: #444;
    }

    .custom-table tbody tr:hover {
        background-color: #3d3d3d;
    }

    .jenis-pendampingan-container {
        background-color: #2d2d2d;
        border-color: #444;
    }

    .status-chip.active {
        background-color: rgba(var(--v-theme-primary), 0.1);
    }
}

@media (max-width: 960px) {
    .section-title {
        font-size: 1.1rem;
    }
}

@media (max-width: 600px) {

    .custom-table th,
    .custom-table td {
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .jenis-pendampingan-container {
        padding: 12px;
    }

    :deep(.v-col) {
        padding-top: 4px;
        padding-bottom: 4px;
    }
}

@media (max-width: 600px) {

    .custom-table th,
    .custom-table td {
        padding: 8px 6px;
        font-size: 0.85rem;
    }

    .status-chip {
        font-size: 0.75rem !important;
        height: 24px !important;
        padding: 0 6px !important;
    }

    .custom-table .no-column {
        width: 30px;
    }

    .custom-table .status-column {
        width: 90px;
    }
}

@media (max-width: 375px) {

    .custom-table th,
    .custom-table td {
        padding: 6px 4px;
        font-size: 0.8rem;
    }

    .status-chip {
        font-size: 0.7rem !important;
        height: 22px !important;
        padding: 0 4px !important;
    }
}
</style>