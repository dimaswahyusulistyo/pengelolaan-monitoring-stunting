<template>
    <VCard class="manual-form" max-width="800px">
        <VCardTitle class="text-h5 pa-4 d-flex align-center">
            <span class="ml-4">Input Manual Data Keluarga Berisiko</span>
            <VSpacer />
            <VBtn icon variant="text" @click="closeDialog">
                <i class="bx bx-x"></i>
            </VBtn>
        </VCardTitle>

        <VCardText>
            <VBtn variant="text" class="mb-1 mt-n2" @click="$emit('cancel')">
                <i class="bx bx-arrow-back mr-2"></i> Kembali
            </VBtn>
            <VForm ref="formRef">
                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Data Keluarga</h3>
                    </div>

                    <VRow>
                        <VCol cols="12" md="12">
                            <VTextField persistent-placeholder v-model="form.no_kartu_keluarga"
                                label="Nomor Kartu Keluarga" :rules="[
                                    v => !!v || 'No. KK harus diisi',
                                    v => v.length === 16 || 'No KK harus 16 digit',
                                    v => /^\d+$/.test(v) || 'Hanya boleh berisi angka'
                                ]" required maxlength="16" @input="limitInputDigits('no_kartu_keluarga')" type="text"
                                inputmode="numeric" pattern="[0-9]*" />
                        </VCol>
                    </VRow>

                    <VRow>
                        <VCol cols="12" md="6">
                            <VTextField persistent-placeholder v-model="form.nik_kepala_keluarga"
                                label="NIK Kepala Keluarga" :rules="[
                                    v => !!v || 'NIK harus diisi',
                                    v => v.length === 16 || 'NIK harus 16 digit',
                                    v => /^\d+$/.test(v) || 'Hanya boleh berisi angka'
                                ]" required maxlength="16" @input="limitInputDigits('nik_kepala_keluarga')" type="text"
                                inputmode="numeric" pattern="[0-9]*" />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField persistent-placeholder v-model="form.nama_kepala_keluarga"
                                label="Nama Kepala Keluarga" :rules="[v => !!v || 'Nama harus diisi']" required />
                        </VCol>
                    </VRow>

                    <VRow>
                        <VCol cols="12" md="6">
                            <VTextField persistent-placeholder v-model="form.nik_istri" label="NIK Istri" :rules="[
                                v => !!v || 'NIK harus diisi',
                                v => v.length === 16 || 'NIK harus 16 digit',
                                v => /^\d+$/.test(v) || 'Hanya boleh berisi angka'
                            ]" required maxlength="16" @input="limitInputDigits('nik_istri')" type="text"
                                inputmode="numeric" pattern="[0-9]*" />
                        </VCol>
                        <VCol cols="12" md="6">
                            <VTextField persistent-placeholder v-model="form.nama_istri" label="Nama Istri"
                                :rules="[v => !!v || 'Nama harus diisi']" required />
                        </VCol>
                    </VRow>

                    <VRow>
                        <VCol cols="12" md="6">
                            <VAutocomplete persistent-placeholder v-model="form.id_desa" :items="filteredDesaList"
                                label="Desa/Kelurahan" item-title="nama_desa" item-value="id_desa"
                                :rules="[v => !!v || 'Desa/Kelurahan harus diisi']"
                                @update:model-value="handleDesaChange" v-model:search="desaSearch"
                                :loading="loadingDesa" clearable no-filter
                                :placeholder="isDesaFocused ? 'Cari desa/kelurahan...' : ''"
                                @focus="isDesaFocused = true" @blur="isDesaFocused = false" required>
                                <template #item="{ props, item }">
                                    <VListItem v-bind="props">
                                        <VListItemSubtitle>
                                            Kec. {{ item.raw.kecamatan }}
                                        </VListItemSubtitle>
                                    </VListItem>
                                </template>
                                <template #no-data>
                                    <VListItem>
                                        <VListItemTitle>
                                            {{ desaSearch.length < 2 ? 'Ketik minimal 2 karakter'
                                                : 'Data tidak ditemukan' }} </VListItemTitle>
                                    </VListItem>
                                </template>
                            </VAutocomplete>
                        </VCol>

                        <VCol cols="12" md="6">
                            <VTextField persistent-placeholder v-model="form.kecamatan" label="Kecamatan"
                                :rules="[v => !!v || 'Kecamatan harus diisi']" required readonly />
                        </VCol>
                    </VRow>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Sasaran</h3>
                    </div>
                    <VRow>
                        <VCol cols="12">
                            <div class="table-container">
                                <VTable class="custom-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center header-cell-top-left" style="width: 50px">No</th>
                                            <th class="text-center" style="width: 60%">Sasaran</th>
                                            <th class="text-center" style="width: 20%">Terpenuhi</th>
                                            <th class="text-center header-cell-top-right" style="width: 20%">Tidak
                                                Terpenuhi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in sasaranData" :key="item.id_faktor">
                                            <td class="text-center">{{ index + 1 }}</td>
                                            <td>{{ item.nama_faktor }}</td>
                                            <td class="text-center">
                                                <VRadio v-model="selectedOptions[item.field]" :value="1"
                                                    :name="`sasaran_${item.field}`"
                                                    :rules="[v => v !== null || 'Harus dipilih']"
                                                    @change="handleRadioChange(item.field, 1)" />
                                            </td>
                                            <td class="text-center">
                                                <VRadio v-model="selectedOptions[item.field]" :value="2"
                                                    :name="`sasaran_${item.field}`"
                                                    @change="handleRadioChange(item.field, 2)" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </div>
                        </VCol>
                    </VRow>
                </div>

                <div class="form-section mt-6">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Penapisan</h3>
                    </div>

                    <div class="table-container">
                        <VTable class="custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center header-cell-top-left" style="width: 50px">No</th>
                                    <th class="text-center" style="width: 60%">Penapisan</th>
                                    <th class="text-center">Terpenuhi</th>
                                    <th class="text-center">Tidak Terpenuhi</th>
                                    <th class="text-center header-cell-top-right">Tidak Berlaku</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in penapisanData" :key="item.id_faktor">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td>{{ item.nama_faktor }}</td>
                                    <td class="text-center">
                                        <VRadio v-model="selectedOptions[item.field]" :value="1"
                                            :name="`penapisan_${item.field}`"
                                            :rules="[v => v !== null || 'Harus dipilih']"
                                            @change="handleRadioChange(item.field, 1)" />
                                    </td>
                                    <td class="text-center">
                                        <VRadio v-model="selectedOptions[item.field]" :value="2"
                                            :name="`penapisan_${item.field}`"
                                            @change="handleRadioChange(item.field, 2)" />
                                    </td>
                                    <td class="text-center">
                                        <VRadio v-model="selectedOptions[item.field]" :value="3"
                                            :name="`penapisan_${item.field}`"
                                            @change="handleRadioChange(item.field, 3)" />
                                    </td>
                                </tr>
                                <tr class="highlight-row">
                                    <td class="text-center">{{ penapisanData.length + 1 }}</td>
                                    <td><strong>PUS 4 Terlalu (Hasil Kalkulasi)</strong></td>
                                    <td class="text-center">
                                        <VIcon v-if="pus4TerlaluStatus === 1" color="success" size="20">bx-check
                                        </VIcon>
                                    </td>
                                    <td class="text-center">
                                        <VIcon v-if="pus4TerlaluStatus === 2" color="error" size="20">bx-x</VIcon>
                                    </td>
                                    <td class="text-center">
                                        <VIcon v-if="pus4TerlaluStatus === 3" color="warning" size="20">bx-minus
                                        </VIcon>
                                    </td>
                                </tr>
                            </tbody>
                        </VTable>
                    </div>
                </div>

                <div class="form-section mt-10">
                    <div class="section-header mb-4">
                        <h3 class="section-title">Jenis Pendampingan yang Diterima</h3>
                    </div>
                    <VRow>
                        <VCol cols="12" md="12" class="mt-2">
                            <div class="jenis-pendampingan-container">
                                <div class="checkbox-grid">
                                    <div class="checkbox-column">
                                        <VCheckbox v-for="option in jenisPendampinganOptions.slice(0, 4)"
                                            :key="option.value" v-model="form.jenis_pendampingan_diterima"
                                            :label="option.title" :value="option.value"
                                            :disabled="shouldDisableOption(option.value)"
                                            @change="handleCheckboxChange(option.value)" class="checkbox-item" />
                                    </div>
                                    <div class="checkbox-column">
                                        <VCheckbox v-for="option in jenisPendampinganOptions.slice(4)"
                                            :key="option.value" v-model="form.jenis_pendampingan_diterima"
                                            :label="option.title" :value="option.value"
                                            :disabled="shouldDisableOption(option.value)"
                                            @change="handleCheckboxChange(option.value)" class="checkbox-item" />
                                    </div>
                                </div>
                            </div>
                        </VCol>
                    </VRow>
                </div>
            </VForm>
        </VCardText>

        <VCardActions class="pa-4">
            <VSpacer />
            <VBtn color="primary" @click="handleSubmit" :loading="loading">
                Simpan
            </VBtn>
            <VBtn color="grey-darken-1" variant="text" @click="resetForm">
                Reset
            </VBtn>
        </VCardActions>
    </VCard>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, computed, watch } from 'vue';
import {
    VForm,
    VTextField,
    VSelect,
    VBtn,
    VCard,
    VCardTitle,
    VCardText,
    VCardActions,
    VSpacer,
    VRow,
    VCol,
    VRadio,
    VCheckbox,
    VTable,
    VAutocomplete,
    VListItem,
    VListItemSubtitle,
    VListItemTitle
} from 'vuetify/components';
import apiClient from '@/services/api';

const emit = defineEmits(['save', 'cancel', 'closeDialog', 'refresh-data', 'close-modal']);

const formRef = ref(null);
const loading = ref(false);
const showError = ref(false);
const errorMessage = ref('');
const desaSearch = ref('');
const loadingDesa = ref(false);
const allDesa = ref([]);
const desaList = ref([]);
const isDesaFocused = ref(false);

const sasaranData = [
    { id_faktor: 1, nama_faktor: 'Punya Anak 0-23 Bulan', field: 'punya_anak_0_23_bulan' },
    { id_faktor: 2, nama_faktor: 'Punya Anak 24-59 Bulan', field: 'punya_anak_24_59_bulan' },
    { id_faktor: 3, nama_faktor: 'PUS', field: 'pus' },
    { id_faktor: 4, nama_faktor: 'PUS Hamil', field: 'pus_hamil' }
];

const penapisanData = [
    { id_faktor: 5, nama_faktor: 'Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak', field: 'sumber_air_minum_tidak_layak' },
    { id_faktor: 6, nama_faktor: 'Keluarga Tidak Mempunyai Jamban yang Layak', field: 'jamban_tidak_layak' },
    { id_faktor: 7, nama_faktor: 'Bukan Peserta KB Modern', field: 'bukan_peserta_kb_modern' },
    { id_faktor: 8, nama_faktor: 'PUS 4 Terlalu Muda (Umur Istri < 20 Tahun)', field: 'pus_4_terlalu_muda' },
    { id_faktor: 9, nama_faktor: 'PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)', field: 'pus_4_terlalu_tua' },
    { id_faktor: 10, nama_faktor: 'PUS 4 Terlalu Dekat (< 2 Tahun)', field: 'pus_4_terlalu_dekat' },
    { id_faktor: 11, nama_faktor: 'PUS 4 Terlalu Banyak (≥ 3 Anak)', field: 'pus_4_terlalu_banyak' },
];

const jenisPendampinganOptions = [
    { value: 1, title: 'Ya, fasilitas rujukan' },
    { value: 2, title: 'Ya, fasilitas bansos' },
    { value: 3, title: 'Ya, fasilitas KIE' },
    { value: 4, title: 'Ya, surveilans melalui elsimil' },
    { value: 5, title: 'Ya, surveilans melalui EPPGBM' },
    { value: 6, title: 'Ya, Bapak Asuh Anak Stunting' },
    { value: 7, title: 'Ya, fasilitas Pemberian Makanan Tambahan (PMT)' },
    { value: 8, title: 'Tidak ada' }
];

const form = reactive({
    no_kartu_keluarga: '',
    nik_kepala_keluarga: '',
    nama_kepala_keluarga: '',
    nik_istri: '',
    nama_istri: '',
    id_desa: null,
    kecamatan: '',
    jenis_pendampingan_diterima: []
});

const selectedOptions = reactive({});

const debounce = (func, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
};

const limitInputDigits = (fieldName) => {
    form[fieldName] = form[fieldName].replace(/\D/g, '');

    if (form[fieldName].length > 16) {
        form[fieldName] = form[fieldName].slice(0, 16);
    }
};

const filteredDesaList = computed(() => {
    if (!desaSearch.value || desaSearch.value.length < 2) {
        return desaList.value;
    }

    const searchTerm = desaSearch.value.toLowerCase();
    return allDesa.value.filter(desa => {
        const searchString = `${desa.nama_desa} ${desa.kecamatan}`.toLowerCase();
        return searchString.includes(searchTerm);
    });
});

const pus4TerlaluStatus = computed(() => {
    const terlalu_muda = selectedOptions.pus_4_terlalu_muda;
    const terlalu_tua = selectedOptions.pus_4_terlalu_tua;
    const terlalu_dekat = selectedOptions.pus_4_terlalu_dekat;
    const terlalu_banyak = selectedOptions.pus_4_terlalu_banyak;

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].some(val => val === null || val === undefined)) {
        return null;
    }

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].some(val => val === 1)) {
        return 1;
    }

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].every(val => val === 2)) {
        return 2;
    }

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].every(val => val === 2 || val === 3) &&
        [terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].some(val => val === 3)) {
        return 3;
    }

    return null;
});

const searchDesa = async (searchTerm) => {
    if (!searchTerm || searchTerm.length < 2) {
        desaList.value = [...allDesa.value];
        return;
    }

    try {
        loadingDesa.value = true;
        const response = await apiClient.get('/desa', {
            params: {
                search: searchTerm,
                limit: 20
            }
        });

        desaList.value = response.data.data.map(desa => ({
            id_desa: desa.id,
            nama_desa: desa.desa,
            kecamatan: desa.kecamatan,
            fullInfo: `${desa.desa} - ${desa.kecamatan}`
        }));
    } catch (error) {
        console.error('Error searching desa:', error);
        desaList.value = [];
    } finally {
        loadingDesa.value = false;
    }
};

const debouncedSearchDesa = debounce(searchDesa, 500);

const loadDesaData = async () => {
    try {
        loadingDesa.value = true;
        const response = await apiClient.get('/desa');
        const data = response.data.data;

        allDesa.value = data.map(desa => ({
            id_desa: desa.id,
            nama_desa: desa.desa,
            kecamatan: desa.kecamatan,
            fullInfo: `${desa.desa} - ${desa.kecamatan}`
        }));

        desaList.value = [...allDesa.value];

    } catch (error) {
        showError.value = true;
        errorMessage.value = 'Gagal memuat data desa';
        console.error('Error loading desa data:', error);
    } finally {
        loadingDesa.value = false;
    }
};

const handleDesaChange = async (desaId) => {
    if (!desaId) return;

    const selectedDesa = desaList.value.find(desa => desa.id_desa === desaId);
    if (selectedDesa) {
        form.kecamatan = selectedDesa.kecamatan;
        form.id_desa = selectedDesa.id_desa;
    }
};

const handleRadioChange = (field, value) => {
    selectedOptions[field] = value;
};

const handleCheckboxChange = (value) => {
    if (value === 8) {
        if (form.jenis_pendampingan_diterima.includes(8)) {
            form.jenis_pendampingan_diterima = [8];
        } else {
            form.jenis_pendampingan_diterima = [];
        }
    } else {
        form.jenis_pendampingan_diterima = form.jenis_pendampingan_diterima.filter(v => v !== 8);
    }
};

const shouldDisableOption = (value) => {
    return value !== 8 && form.jenis_pendampingan_diterima.includes(8);
};

const validateRequiredRadioOptions = () => {
    const missingFields = [];

    sasaranData.forEach(item => {
        if (selectedOptions[item.field] === null || selectedOptions[item.field] === undefined) {
            missingFields.push(item.nama_faktor);
        }
    });

    penapisanData.forEach(item => {
        if (selectedOptions[item.field] === null || selectedOptions[item.field] === undefined) {
            missingFields.push(item.nama_faktor);
        }
    });

    return missingFields;
};

const handleSubmit = async () => {
    try {
        loading.value = true;

        const { valid } = await formRef.value.validate();
        const missingFields = validateRequiredRadioOptions();

        if (!valid || missingFields.length > 0) {
            let errorText = 'Mohon lengkapi semua field yang wajib diisi';
            if (missingFields.length > 0) {
                errorText += `: <strong>${missingFields.join(', ')}</strong>`;
            }

            await window.Swal.fire({
                title: 'Validasi Gagal',
                html: errorText,
                icon: 'error',
                confirmButtonColor: '#ff6b6b',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-xl',
                    title: 'text-lg font-medium'
                }
            });
            return;
        }

        let checkKKResponse;
        try {
            checkKKResponse = await apiClient.post('/keluarga-berisiko/check-no-kk', {
                no_kartu_keluarga: form.no_kartu_keluarga
            });
        } catch (postError) {
            if (postError.response?.status === 405) {
                checkKKResponse = await apiClient.get('/keluarga-berisiko/check-no-kk', {
                    params: {
                        no_kartu_keluarga: form.no_kartu_keluarga
                    }
                });
            } else {
                throw postError;
            }
        }

        let checkNikKepalaResponse;
        try {
            checkNikKepalaResponse = await apiClient.post('/keluarga-berisiko/check-nik-kepala', {
                nik_kepala_keluarga: form.nik_kepala_keluarga
            });
        } catch (postError) {
            if (postError.response?.status === 405) {
                checkNikKepalaResponse = await apiClient.get('/keluarga-berisiko/check-nik-kepala', {
                    params: {
                        nik_kepala_keluarga: form.nik_kepala_keluarga
                    }
                });
            } else {
                throw postError;
            }
        }

        let checkNikIstriResponse;
        try {
            checkNikIstriResponse = await apiClient.post('/keluarga-berisiko/check-nik-istri', {
                nik_istri: form.nik_istri
            });
        } catch (postError) {
            if (postError.response?.status === 405) {
                checkNikIstriResponse = await apiClient.get('/keluarga-berisiko/check-nik-istri', {
                    params: {
                        nik_istri: form.nik_istri
                    }
                });
            } else {
                throw postError;
            }
        }

        if (checkKKResponse.data.exists || checkNikKepalaResponse.data.exists || checkNikIstriResponse.data.exists) {
            let duplicateMessage = 'Data berikut sudah terdaftar:<br><ul>';

            if (checkKKResponse.data.exists) {
                duplicateMessage += `<li>No. KK: ${form.no_kartu_keluarga} (${checkKKResponse.data.nama_kepala_keluarga})</li>`;
            }
            if (checkNikKepalaResponse.data.exists) {
                duplicateMessage += `<li>NIK Kepala Keluarga: ${form.nik_kepala_keluarga} (${checkNikKepalaResponse.data.nama_kepala_keluarga})</li>`;
            }
            if (checkNikIstriResponse.data.exists) {
                duplicateMessage += `<li>NIK Istri: ${form.nik_istri} (${checkNikIstriResponse.data.nama_istri})</li>`;
            }

            duplicateMessage += '</ul>Lanjutkan untuk memperbarui data?';

            const result = await window.Swal.fire({
                title: 'Data Duplikat Ditemukan',
                html: duplicateMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#5b8cff',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-lg shadow-xl',
                    title: 'text-lg font-medium'
                }
            });

            if (!result.isConfirmed) {
                return;
            }
        }

        const payload = {
            no_kartu_keluarga: form.no_kartu_keluarga,
            nik_kepala_keluarga: form.nik_kepala_keluarga,
            nama_kepala_keluarga: form.nama_kepala_keluarga,
            nik_istri: form.nik_istri,
            nama_istri: form.nama_istri,
            id_desa: Number(form.id_desa),
            jenis_pendampingan_diterima: form.jenis_pendampingan_diterima,

            punya_anak_0_23_bulan: selectedOptions.punya_anak_0_23_bulan,
            punya_anak_24_59_bulan: selectedOptions.punya_anak_24_59_bulan,
            pus: selectedOptions.pus,
            pus_hamil: selectedOptions.pus_hamil,

            sumber_air_minum_tidak_layak: selectedOptions.sumber_air_minum_tidak_layak,
            jamban_tidak_layak: selectedOptions.jamban_tidak_layak,
            pus_4_terlalu_muda: selectedOptions.pus_4_terlalu_muda,
            pus_4_terlalu_tua: selectedOptions.pus_4_terlalu_tua,
            pus_4_terlalu_dekat: selectedOptions.pus_4_terlalu_dekat,
            pus_4_terlalu_banyak: selectedOptions.pus_4_terlalu_banyak,
            bukan_peserta_kb_modern: selectedOptions.bukan_peserta_kb_modern,

            pus_4_terlalu: pus4TerlaluStatus.value
        };

        let response;
        if (checkKKResponse.data.exists) {
            response = await apiClient.put(`/keluarga-berisiko/${checkKKResponse.data.id_keluarga}`, payload);
        } else {
            response = await apiClient.post('/keluarga-berisiko', payload);
        }

        await window.Swal.fire({
            html: `Data <strong>keluarga berisiko</strong> berhasil ${checkKKResponse.data.exists ? 'diperbarui' : 'ditambahkan'}`,
            icon: 'success',
            confirmButtonColor: '#5b8cff',
            background: '#f8fafc',
            customClass: {
                popup: 'rounded-lg shadow-xl',
                title: 'text-lg font-medium'
            },
            timer: 3000,
            showConfirmButton: false
        });

        emit('save');
        emit('close-modal');
        emit('refresh-data');

    } catch (error) {
        let errorMessage = 'Terjadi kesalahan saat menyimpan data';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            errorMessage = Object.values(error.response.data.errors)
                .flat()
                .join(', ');
        }

        await window.Swal.fire({
            title: 'Gagal!',
            html: `
        <div class="text-left">
          <p>${errorMessage}</p>
          <a href="#" id="show-details" class="text-blue-500 hover:underline mt-2 inline-block">
            Lihat Detail Error
          </a>
        </div>
      `,
            icon: 'error',
            confirmButtonColor: '#ff6b6b',
            background: '#f8fafc',
            customClass: {
                popup: 'rounded-lg shadow-xl',
                title: 'text-lg font-medium',
                htmlContainer: 'text-left'
            }
        });

        document.getElementById('show-details')?.addEventListener('click', (e) => {
            e.preventDefault();
            window.Swal.fire({
                title: 'Detail Error',
                html: `
          <div class="text-left overflow-auto max-h-96">
            <pre class="text-sm">${JSON.stringify(error.response?.data || error.message, null, 2)}</pre>
          </div>
        `,
                width: '80%',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-xl',
                    title: 'text-lg font-medium',
                    htmlContainer: 'text-left'
                },
                confirmButtonColor: '#5b8cff'
            });
        });
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    if (formRef.value) {
        formRef.value.reset();

        Object.assign(form, {
            no_kartu_keluarga: '',
            nik_kepala_keluarga: '',
            nama_kepala_keluarga: '',
            nik_istri: '',
            nama_istri: '',
            id_desa: null,
            kecamatan: '',
            jenis_pendampingan_diterima: [],
        });

        sasaranData.forEach(item => {
            selectedOptions[item.field] = null;
        });

        penapisanData.forEach(item => {
            selectedOptions[item.field] = null;
        });

        nextTick(() => {
            formRef.value.resetValidation();
        });
    }
};

const closeDialog = () => {
    emit('closeDialog');
};

watch(desaSearch, (newVal) => {
    if (newVal && newVal.length >= 2) {
        debouncedSearchDesa(newVal);
    } else {
        desaList.value = [...allDesa.value];
    }
});

onMounted(() => {
    sasaranData.forEach(item => {
        selectedOptions[item.field] = null;
    });

    penapisanData.forEach(item => {
        selectedOptions[item.field] = null;
    });

    loadDesaData();
});
</script>

<style scoped>
.manual-form {
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

.table-container {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin: 8px 0 16px;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 8px;
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

.highlight-row {
    background-color: rgba(0, 0, 0, 0.03);
}

.highlight-row td {
    font-weight: 500;
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

.custom-table tbody tr:hover {
    background-color: #f8f9fa;
}

.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 50px;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 65%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3),
.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 15%;
}

.jenis-pendampingan-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    background-color: #f9f9f9;
}

.checkbox-grid {
    display: flex;
    justify-content: space-between;
}

.checkbox-column {
    width: 48%;
}

.checkbox-item {
    margin-bottom: 8px;
}

.checkbox-item :deep(.v-label) {
    font-size: 0.875rem;
    line-height: 1.2;
}

.v-theme--dark {
    .custom-table th {
        background-color: #1e1e1e;
        color: #ffffff;
    }

    .custom-table td {
        background-color: #2d2d2d;
        color: #ffffff;
    }

    .custom-table tbody tr:hover {
        background-color: #3d3d3d;
    }

    .custom-table,
    .custom-table td,
    .custom-table th {
        border-color: #444;
    }

    .jenis-pendampingan-container {
        background-color: #2d2d2d;
        border-color: #444;
    }

    .section-title,
    .jenis-pendampingan-container h4 {
        color: rgba(var(--v-theme-on-dark), var(--v-high-emphasis-opacity));
    }
}

@media (max-width: 960px) {
    .checkbox-grid {
        flex-direction: column;
    }

    .checkbox-column {
        width: 100%;
    }
}

@media (max-width: 600px) {
    .section-title {
        font-size: 1.1rem;
    }

    .custom-table th,
    .custom-table td {
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .jenis-pendampingan-container {
        padding: 12px;
    }
}
</style>