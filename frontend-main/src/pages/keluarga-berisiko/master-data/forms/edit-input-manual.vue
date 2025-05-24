<template>
    <VDialog :modelValue="visible" @update:modelValue="$emit('update:visible', $event)" persistent max-width="800px">
        <VCard class="manual-form">
            <VCardTitle class="text-h5 pa-4 d-flex align-center">
                <span class="ml-4">Edit Data Keluarga Berisiko</span>
                <VSpacer />
                <VBtn icon variant="text" @click="closeAllDialogs">
                    <i class="bx bx-x"></i>
                </VBtn>
            </VCardTitle>

            <VCardText>
                <VBtn variant="text" class="mb-1 mt-n2" @click="closeDialog">
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
                                    ]" required maxlength="16" @input="limitInputDigits('no_kartu_keluarga')"
                                    type="text" inputmode="numeric" pattern="[0-9]*" />
                            </VCol>
                        </VRow>

                        <VRow>
                            <VCol cols="12" md="6">
                                <VTextField persistent-placeholder v-model="form.nik_kepala_keluarga"
                                    label="NIK Kepala Keluarga" :rules="[
                                        v => !!v || 'NIK harus diisi',
                                        v => v.length === 16 || 'NIK harus 16 digit',
                                        v => /^\d+$/.test(v) || 'Hanya boleh berisi angka'
                                    ]" required maxlength="16" @input="limitInputDigits('nik_kepala_keluarga')"
                                    type="text" inputmode="numeric" pattern="[0-9]*" />
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
                                            <tr v-for="(item, index) in sasaranData" :key="item.id">
                                                <td class="text-center">{{ index + 1 }}</td>
                                                <td>{{ item.name }}</td>
                                                <td class="text-center">
                                                    <VRadio v-model="selectedOptions[item.id]" :value="1"
                                                        :name="`sasaran_${item.field}`"
                                                        :rules="[v => v !== null || 'Harus dipilih']"
                                                        @change="handleRadioChange(item.id, 1)" />
                                                </td>
                                                <td class="text-center">
                                                    <VRadio v-model="selectedOptions[item.id]" :value="2"
                                                        :name="`sasaran_${item.field}`"
                                                        @change="handleRadioChange(item.id, 2)" />
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
                                    <tr v-for="(item, index) in penapisanData" :key="item.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td>{{ item.name }}</td>
                                        <td class="text-center">
                                            <VRadio v-model="selectedOptions[item.id]" :value="1"
                                                :name="`penapisan_${item.field}`"
                                                :rules="[v => v !== null || 'Harus dipilih']"
                                                @change="handleRadioChange(item.id, 1)" />
                                        </td>
                                        <td class="text-center">
                                            <VRadio v-model="selectedOptions[item.id]" :value="2"
                                                :name="`penapisan_${item.field}`"
                                                @change="handleRadioChange(item.id, 2)" />
                                        </td>
                                        <td class="text-center">
                                            <VRadio v-model="selectedOptions[item.id]" :value="3"
                                                :name="`penapisan_${item.field}`"
                                                @change="handleRadioChange(item.id, 3)" />
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
                                                :disabled="shouldDisablePendampinganOption(option.value)"
                                                @change="handlePendampinganChange(option.value)"
                                                class="checkbox-item" />
                                        </div>
                                        <div class="checkbox-column">
                                            <VCheckbox v-for="option in jenisPendampinganOptions.slice(4)"
                                                :key="option.value" v-model="form.jenis_pendampingan_diterima"
                                                :label="option.title" :value="option.value"
                                                :disabled="shouldDisablePendampinganOption(option.value)"
                                                @change="handlePendampinganChange(option.value)"
                                                class="checkbox-item" />
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
    </VDialog>
</template>

<script setup>
import { ref, reactive, onMounted, watch, defineProps, defineEmits, computed } from 'vue';
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
    VDialog,
    VIcon,
    VAutocomplete,
    VListItem,
    VListItemSubtitle,
    VListItemTitle
} from 'vuetify/components';
import apiClient from '@/services/api';

const props = defineProps({
    visible: Boolean,
    item: Object
});

const emit = defineEmits(['update:visible', 'save', 'cancel', 'refresh-data', 'edit-item', 'close-all']);

const formRef = ref(null);
const loading = ref(false);
const loadingDesa = ref(false);
const desaList = ref([]);
const desaSearch = ref('');
const originalData = ref(null);
const isDesaFocused = ref(false);

const selectedOptions = reactive({});

const sasaranData = [
    { id: 1, name: 'Punya Anak 0-23 Bulan', field: 'punya_anak_0_23_bulan' },
    { id: 2, name: 'Punya Anak 24-59 Bulan', field: 'punya_anak_24_59_bulan' },
    { id: 3, name: 'PUS', field: 'pus' },
    { id: 4, name: 'PUS Hamil', field: 'pus_hamil' }
];

const penapisanData = [
    { id: 5, name: 'Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak', field: 'sumber_air_minum_tidak_layak' },
    { id: 6, name: 'Keluarga Tidak Mempunyai Jamban yang Layak', field: 'jamban_tidak_layak' },
    { id: 7, name: 'Bukan Peserta KB Modern', field: 'bukan_peserta_kb_modern' },
    { id: 8, name: 'PUS 4 Terlalu Muda (Umur Istri < 20 Tahun)', field: 'pus_4_terlalu_muda' },
    { id: 9, name: 'PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)', field: 'pus_4_terlalu_tua' },
    { id: 10, name: 'PUS 4 Terlalu Dekat (< 2 Tahun)', field: 'pus_4_terlalu_dekat' },
    { id: 11, name: 'PUS 4 Terlalu Banyak (≥ 3 Anak)', field: 'pus_4_terlalu_banyak' },
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
];

const pus4TerlaluStatus = computed(() => {
    const terlalu_muda = selectedOptions[8];
    const terlalu_tua = selectedOptions[9]; 
    const terlalu_dekat = selectedOptions[10]; 
    const terlalu_banyak = selectedOptions[11]; 
    
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

watch(pus4TerlaluStatus, (newVal) => {
    form.pus_4_terlalu = newVal;
});

const form = reactive({
    id_keluarga: null,
    no_kartu_keluarga: '',
    nik_kepala_keluarga: '',
    nama_kepala_keluarga: '',
    nik_istri: '',
    nama_istri: '',
    id_desa: null,
    kecamatan: '',
    desa: '',

    punya_anak_0_23_bulan: null,
    punya_anak_24_59_bulan: null,
    pus: null,
    pus_hamil: null,

    sumber_air_minum_tidak_layak: null,
    jamban_tidak_layak: null,
    pus_4_terlalu_muda: null,
    pus_4_terlalu_tua: null,
    pus_4_terlalu_dekat: null,
    pus_4_terlalu_banyak: null,
    bukan_peserta_kb_modern: null,
    pus_4_terlalu: null,

    jenis_pendampingan_diterima: []
});

const filteredDesaList = computed(() => {
    if (!desaSearch.value || desaSearch.value.length < 2) {
        return desaList.value;
    }

    const searchTerm = desaSearch.value.toLowerCase();
    return desaList.value.filter(desa => {
        const searchString = `${desa.nama_desa} ${desa.kecamatan}`.toLowerCase();
        return searchString.includes(searchTerm);
    });
});

const handleSearchInput = (val) => {
    desaSearch.value = val;
    if (!val || val.length < 2) {
        desaList.value = [...desaList.value];
    }
};

const handleRadioChange = (id, value) => {
    selectedOptions[id] = value;

    const allData = [...sasaranData, ...penapisanData];
    const item = allData.find(item => item.id === id);
    if (item) {
        form[item.field] = value;
    }
};

const handleSubmit = async () => {
    const { valid } = await formRef.value.validate();

    if (!valid) {
        await window.Swal.fire({
            title: 'Validasi Gagal',
            html: 'Mohon lengkapi semua field yang wajib diisi',
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

    loading.value = true;

    try {
        const formData = {
            ...form,
            jenis_pendampingan_diterima: form.jenis_pendampingan_diterima,
            pus_4_terlalu: pus4TerlaluStatus.value || null
        };

        let response;
        if (form.id_keluarga) {
            response = await apiClient.put(`/keluarga-berisiko/${form.id_keluarga}`, formData);

            await window.Swal.fire({
                // title: 'Berhasil!',
                html: 'Data <strong>keluarga berisiko</strong> berhasil diperbarui',
                icon: 'success',
                confirmButtonColor: '#5b8cff',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-xl',
                    title: 'text-lg font-medium'
                },
                timer: 2000,
                showConfirmButton: false
            });

            const updatedData = response.data.data;
            Object.assign(props.item, updatedData);

            if (!props.item.desa) props.item.desa = {};
            const selectedDesa = desaList.value.find(d => d.id_desa === form.id_desa);
            if (selectedDesa) {
                props.item.desa.nama_desa = selectedDesa.nama_desa;
                props.item.desa.id_desa = selectedDesa.id_desa;
                if (!props.item.desa.kecamatan) props.item.desa.kecamatan = {};
                props.item.desa.kecamatan.nama_kecamatan = selectedDesa.kecamatan;
            }

            emit('save', updatedData);
            emit('refresh-data');
            closeDialog();

        } else {
            response = await apiClient.post('/keluarga-berisiko', formData);

            await window.Swal.fire({
                // title: 'Berhasil!',
                html: 'Data <strong>keluarga berisiko</strong> berhasil ditambahkan',
                icon: 'success',
                confirmButtonColor: '#5b8cff',
                background: '#f8fafc',
                customClass: {
                    popup: 'rounded-lg shadow-xl',
                    title: 'text-lg font-medium'
                },
                timer: 2000,
                showConfirmButton: false
            });

            emit('save', response.data.data);
            emit('refresh-data');
            closeDialog();
        }

    } catch (error) {
        console.error('Error submitting form:', error);

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
                    ${error.response?.data ? `
                    <a href="#" id="show-details" class="text-blue-500 hover:underline mt-2 inline-block">
                        Lihat Detail Error
                    </a>
                    ` : ''}
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

        if (error.response?.data) {
            document.getElementById('show-details')?.addEventListener('click', (e) => {
                e.preventDefault();
                window.Swal.fire({
                    title: 'Detail Error',
                    html: `
                        <div class="text-left overflow-auto max-h-96">
                            <pre class="text-sm">${JSON.stringify(error.response.data, null, 2)}</pre>
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
        }
    } finally {
        loading.value = false;
    }
};

const limitInputDigits = (fieldName) => {
    form[fieldName] = form[fieldName].replace(/\D/g, '');

    if (form[fieldName].length > 16) {
        form[fieldName] = form[fieldName].slice(0, 16);
    }
};

const loadDesaData = async () => {
    try {
        loadingDesa.value = true;
        const response = await apiClient.get('/desa');

        if (!response.data || !Array.isArray(response.data.data)) {
            throw new Error('Invalid data format from API');
        }

        desaList.value = response.data.data.map(desa => ({
            id_desa: desa.id_desa || desa.id,
            nama_desa: desa.nama_desa || desa.desa || '',
            kecamatan: desa.kecamatan?.nama_kecamatan || desa.nama_kecamatan || desa.kecamatan || 'N/A',
        }));

        if (props.item && props.item.id_desa) {
            await fillFormWithItemData();
        }
    } catch (error) {
        console.error('Error loading desa data:', error);
    } finally {
        loadingDesa.value = false;
    }
};

const handleDesaChange = (desaId) => {
    if (!desaId) {
        form.kecamatan = '';
        return;
    }

    const selectedDesa = desaList.value.find(desa => desa.id_desa === desaId);
    if (selectedDesa) {
        form.kecamatan = selectedDesa.kecamatan;
        form.id_desa = selectedDesa.id_desa;
    } else {
        apiClient.get(`/desa/${desaId}`)
            .then(response => {
                const desa = response.data.data;
                form.kecamatan = desa.kecamatan?.nama_kecamatan || desa.nama_kecamatan || desa.kecamatan || 'N/A';

                desaList.value.push({
                    id_desa: desa.id_desa || desa.id,
                    nama_desa: desa.nama_desa || desa.desa || '',
                    kecamatan: desa.kecamatan?.nama_kecamatan || desa.nama_kecamatan || desa.kecamatan || 'N/A',
                });
            })
            .catch(error => {
                console.error('Error fetching desa details:', error);
            });
    }
};

const convertApiValueToFormValue = (value, allowThirdOption = false) => {
    if (value === true || value === 1 || value === '1') return 1;
    if (value === false || value === 0 || value === 2 || value === '2') return 2;
    return allowThirdOption ? 3 : null;
};

const fillFormWithItemData = async () => {
    if (!props.item) return;

    if (desaList.value.length === 0) {
        await loadDesaData();
    }

    Object.assign(form, {
        id_keluarga: null,
        no_kartu_keluarga: '',
        nik_kepala_keluarga: '',
        nama_kepala_keluarga: '',
        nik_istri: '',
        nama_istri: '',
        id_desa: null,
        kecamatan: '',
        desa: '',

        punya_anak_0_23_bulan: null,
        punya_anak_24_59_bulan: null,
        pus: null,
        pus_hamil: null,

        sumber_air_minum_tidak_layak: null,
        jamban_tidak_layak: null,
        pus_4_terlalu_muda: null,
        pus_4_terlalu_tua: null,
        pus_4_terlalu_dekat: null,
        pus_4_terlalu_banyak: null,
        bukan_peserta_kb_modern: null,
        pus_4_terlalu: null,

        jenis_pendampingan_diterima: []
    });

    for (const id in selectedOptions) {
        selectedOptions[id] = null;
    }

    Object.assign(form, {
        id_keluarga: props.item.id_keluarga || props.item.id,
        no_kartu_keluarga: props.item.no_kartu_keluarga || '',
        nik_kepala_keluarga: props.item.nik_kepala_keluarga || '',
        nama_kepala_keluarga: props.item.nama_kepala_keluarga || '',
        nik_istri: props.item.nik_istri || '',
        nama_istri: props.item.nama_istri || '',
    });

    if (props.item.id_desa) {
        const desaId = Number(props.item.id_desa);

        const existingDesa = desaList.value.find(d => Number(d.id_desa) === desaId);

        if (existingDesa) {
            form.id_desa = existingDesa.id_desa;
            form.kecamatan = existingDesa.kecamatan;
        } else {
            try {
                const response = await apiClient.get(`/desa/${desaId}`);
                const desaData = response.data?.data || {};

                const newDesa = {
                    id_desa: desaId,
                    nama_desa: desaData.nama_desa || desaData.desa || '',
                    kecamatan: desaData.kecamatan?.nama_kecamatan ||
                        desaData.nama_kecamatan ||
                        desaData.kecamatan || 'N/A'
                };

                desaList.value.push(newDesa);
                form.id_desa = newDesa.id_desa;
                form.kecamatan = newDesa.kecamatan;
            } catch (error) {
                console.error('Error fetching desa:', error);
                form.id_desa = desaId;
                form.kecamatan = props.item.kecamatan || '';
            }
        }
    }

    const determinanData = props.item.determinan_k_b || props.item.determinanKB || {};

    sasaranData.forEach(item => {
        const apiValue = determinanData[item.field] ?? props.item[item.field];
        const value = convertApiValueToFormValue(apiValue);
        form[item.field] = value;
        selectedOptions[item.id] = value;
    });

    penapisanData.forEach(item => {
        const apiValue = determinanData[item.field] ?? props.item[item.field];
        const value = convertApiValueToFormValue(apiValue, true);
        form[item.field] = value;
        selectedOptions[item.id] = value;
    });

    form.pus_4_terlalu = props.item.pus_4_terlalu || determinanData.pus_4_terlalu || null;

    if (props.item.jenis_pendampingan_diterima) {
        let pendampinganValues = [];

        if (Array.isArray(props.item.jenis_pendampingan_diterima)) {
            pendampinganValues = [...props.item.jenis_pendampingan_diterima];
        } else if (typeof props.item.jenis_pendampingan_diterima === 'string') {
            pendampinganValues = props.item.jenis_pendampingan_diterima
                .split(',')
                .map(v => parseInt(v.trim()))
                .filter(v => !isNaN(v));
        }

        if (pendampinganValues.includes(8)) {
            form.jenis_pendampingan_diterima = [8];
        } else {
            form.jenis_pendampingan_diterima = pendampinganValues;
        }
    } else {
        form.jenis_pendampingan_diterima = [];
    }

    originalData.value = JSON.parse(JSON.stringify(form));
};

const handlePendampinganChange = (value) => {
    if (value === 8 && form.jenis_pendampingan_diterima.includes(8)) {
        form.jenis_pendampingan_diterima = [8];
    } else if (value !== 8 && form.jenis_pendampingan_diterima.includes(8)) {
        form.jenis_pendampingan_diterima = form.jenis_pendampingan_diterima.filter(v => v !== 8);
    }
};

const shouldDisablePendampinganOption = (value) => {
    if (value === 8) return false;
    return form.jenis_pendampingan_diterima.includes(8);
};

const resetForm = () => {
    if (originalData.value) {
        Object.assign(form, JSON.parse(JSON.stringify(originalData.value)));

        [...sasaranData, ...penapisanData].forEach(item => {
            selectedOptions[item.id] = form[item.field];
        });
    } else {
        [...sasaranData, ...penapisanData].forEach(item => {
            form[item.field] = null;
            selectedOptions[item.id] = null;
        });

        form.no_kartu_keluarga = '';
        form.nik_kepala_keluarga = '';
        form.nama_kepala_keluarga = '';
        form.nik_istri = '';
        form.nama_istri = '';
        form.id_desa = null;
        form.kecamatan = '';
        form.desa = '';
        form.pus_4_terlalu = null;
        form.jenis_pendampingan_diterima = [];

        fillFormWithItemData();
    }

    if (formRef.value) {
        formRef.value.resetValidation();
    }
};

const closeDialog = () => {
    emit('cancel');
    emit('update:visible', false);
};

const closeAllDialogs = () => {
    emit('close-all');
    emit('update:visible', false);
};

onMounted(() => {
    loadDesaData();
});

watch(() => props.visible, async (isVisible) => {
    if (isVisible && props.item) {
        if (desaList.value.length === 0) {
            await loadDesaData();
        }
        await fillFormWithItemData();
    }
}, { immediate: true });

watch(() => props.item, (newVal) => {
    if (newVal) {
        fillFormWithItemData();
    }
}, { deep: true });
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
    -webkit-overflow-scrolling: touch;
}

.custom-table {
    width: 100%;
    min-width: 600px;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 8px;
    background-color: rgb(var(--v-theme-surface));
}

.custom-table th,
.custom-table td {
    border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding: 12px 16px;
    vertical-align: middle;
}

.custom-table th {
    background-color: #f5f5f5;
    /* Light mode header color */
    font-weight: 600;
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

.highlight-row {
    background-color: rgba(var(--v-theme-primary), 0.05);
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
    background-color: rgba(var(--v-theme-primary), 0.08);
}

.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 50px;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 60%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3),
.custom-table th:nth-child(4),
.custom-table td:nth-child(4),
.custom-table th:nth-child(5),
.custom-table td:nth-child(5) {
    width: 20%;
}

:deep(.v-radio) {
    margin: 0 auto;
    display: flex;
    justify-content: center;
}

.jenis-pendampingan-container {
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 8px;
    padding: 16px;
    background-color: #f9f9f9;
    /* Light mode background */
}

.checkbox-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.checkbox-column {
    flex: 1;
    min-width: 200px;
}

.checkbox-item {
    margin-bottom: 8px;
}

.checkbox-item :deep(.v-label) {
    font-size: 0.875rem;
    line-height: 1.2;
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

/* Dark mode specific styles */
.v-theme--dark {
    .custom-table {
        border-color: #444;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .custom-table th {
        background-color: #1e1e1e;
        /* Dark mode header color */
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

    .jenis-pendampingan-container {
        background-color: #2d2d2d;
        /* Dark mode background */
        border-color: #444;
    }

    .highlight-row {
        background-color: rgba(var(--v-theme-primary), 0.1);
    }

    .custom-table tbody tr:hover {
        background-color: rgba(var(--v-theme-primary), 0.12);
    }
}

@media (max-width: 960px) {
    .checkbox-grid {
        flex-direction: column;
        gap: 8px;
    }

    .checkbox-column {
        min-width: 100%;
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

    :deep(.v-col) {
        padding-top: 4px;
        padding-bottom: 4px;
    }
}

@media (max-width: 375px) {

    .custom-table th,
    .custom-table td {
        padding: 6px 4px;
        font-size: 0.8rem;
    }
}
</style>