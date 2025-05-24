<template>
  <VCard class="manual-form" max-width="600px">
    <VCardTitle class="text-h5 pa-4 d-flex align-center">
      <span class="ml-4">Input Manual Data Stunting</span>
      <VSpacer />
      <VBtn icon variant="text" @click="closeDialog">
        <i class="bx bx-x"></i>
      </VBtn>
    </VCardTitle>

    <VCardText>
      <VBtn variant="text" class="mb-2" @click="$emit('cancel')">
        <i class="bx bx-arrow-back mr-2"></i> Kembali
      </VBtn>
      <VForm ref="formRef">
        <div class="form-section mt-6">
          <div class="section-header">
            <h3 class="section-title">Data Anak</h3>
          </div>
          <br>
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                persistent-placeholder
                v-model="form.nama_anak"
                label="Nama Anak"
                :rules="[v => !!v || 'Nama anak harus diisi']"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSelect
                persistent-placeholder
                v-model="form.jenis_kelamin"
                :items="jenisKelaminOptions"
                label="Jenis Kelamin"
                item-title="title"
                item-value="value"
                :rules="[v => !!v || 'Jenis kelamin harus dipilih']"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
              persistent-placeholder
              v-model="form.tanggal_lahir"
              label="Tanggal Lahir"
              type="date"
              :rules="[v => !!v || 'Tanggal lahir harus diisi']"
              required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                persistent-placeholder
                v-model="form.nik_anak"
                label="NIK"
                :rules="[
                  v => !!v || 'NIK harus diisi',
                  v => (v && v.toString().length === 16) || 'NIK harus 16 digit',
                  v => /^\d{16}$/.test(v) || 'Hanya 16 digit angka yang diperbolehkan'
                ]"
                required
                type="number"
                counter
                maxlength="16"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)"
              />
            </VCol>

            <VCol cols="12" md="4">
              <VAutocomplete
                v-model="form.id_desa"
                :items="filteredDesaList"
                label="Desa/Kelurahan"
                item-title="nama_desa"
                item-value="id_desa"
                :rules="[v => !!v || 'Desa harus dipilih']"
                @update:model-value="handleDesaChange"
                v-model:search="desaSearch"
                :loading="loadingDesa"
                clearable
                no-filter
                placeholder="Cari desa/kelurahan..."
                @update:search="handleSearchInput"
              >
                <template #item="{ props, item }">
                  <VListItem v-bind="props">
                    <VListItemSubtitle>
                      Kec. {{ item.raw.kecamatan }} - {{ item.raw.puskesmas }}
                    </VListItemSubtitle>
                  </VListItem>
                </template>
                <template #no-data>
                  <VListItem>
                    <VListItemTitle>
                      {{ desaSearch.length < 2 ? 'Ketik minimal 2 karakter' : 'Data tidak ditemukan' }}
                    </VListItemTitle>
                  </VListItem>
                </template>
              </VAutocomplete>
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                persistent-placeholder
                v-model="form.kecamatan"
                label="Kecamatan"
                :rules="[v => !!v || 'Kecamatan harus diisi']"
                required
                readonly
              />
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                persistent-placeholder
                v-model="form.puskesmas"
                label="Puskesmas"
                :rules="[v => !!v || 'Puskesmas harus diisi']"
                required
                readonly
              />
            </VCol>

            <VCol cols="12" md="12">
              <VTextField
                persistent-placeholder
                v-model="form.posyandu"
                label="Posyandu"
                :rules="[v => !!v || 'Posyandu harus diisi']"
                required
              />
            </VCol>
          </VRow>
        </div>
        <VDivider class="my-4" />

        <div class="form-section mt-6">
          <div class="section-header">
            <h3 class="section-title">Faktor Determinan</h3>
          </div>
          <br>
          <VRow>
            <VCol cols="12" md="5">
              <VSelect
                persistent-placeholder
                v-model="form.jkn"
                :items="jknOptions"
                label="JKN"
                item-title="title"
                item-value="value"
                :rules="[v => !!v || 'JKN harus dipilih']"
                required
              />
            </VCol>

            <VCol cols="12" md="7">
              <VTextField
                persistent-placeholder
                v-model="form.penyakit_penyerta"
                label="Penyakit Penyerta"
              />
            </VCol>

              <div class="table-container">
                <VAlert
                  v-if="tableValidationError"
                  color="error"
                  variant="tonal"
                  class="mb-4"
                >
                  Mohon pilih opsi untuk semua faktor determinan dalam tabel!
                </VAlert>
                <VTable class="custom-table">
                  <thead>
                    <tr>
                      <th rowspan="1" class="text-center header-cell-top-left" style="width: 50px">No</th>
                      <th rowspan="1" class="text-center" style="width: 60%" >Faktor Determinan</th>
                      <th rowspan="1" class="text-center" style="width: 20%">Opsi 1</th>
                      <th rowspan="1" class="text-center" style="width: 20%">Opsi 2</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in paginatedData" :key="item.id_faktor">
                      <td class="text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                      <td>
                        <b>{{ item.nama_faktor }}</b> {{ item.keterangan }}
                        <div 
                          v-if="validationAttempted && selectedOptions[item.id_faktor] !== 1 && selectedOptions[item.id_faktor] !== 2" 
                          class="text-error"
                          style="color: red; font-size: 0.75rem;"
                        >
                          Harus dipilih
                        </div>
                      </td>

                      <td class="text-center">
                        <VRadio
                          v-model="selectedOptions[item.id_faktor]"
                          :value="1"
                          @change="handleRadioChange(item.id_faktor, 1)"
                          :error="validationAttempted && !selectedOptions[item.id_faktor]"
                        />
                      </td>

                      <td class="text-center">
                        <VRadio
                          v-model="selectedOptions[item.id_faktor]"
                          :value="2"
                          @change="handleRadioChange(item.id_faktor, 2)"
                          :error="validationAttempted && !selectedOptions[item.id_faktor]"
                        />
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </div>
          </VRow>
        </div>
      </VForm>
    </VCardText>

    <VCardActions class="pa-4">
      <VSpacer />
      <VBtn
        color="primary"
        @click="handleSubmit"
      >
        Simpan
      </VBtn>
      <VBtn
        color="grey-darken-1"
        variant="text"
        @click="resetForm"
      >
        Reset
      </VBtn>
    </VCardActions>
  </VCard>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
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
  VSnackbar,
  VRadio,
  VTable,
  VAutocomplete,
} from 'vuetify/components';

import apiClient from '@/services/api';

const emit = defineEmits(['save', 'cancel', 'goBackToOptions', 'closeDialog', 'refresh-data', 'close-modal']);

const formRef = ref(null);

const form = reactive({
  nama_anak: '',
  jenis_kelamin: '',
  tanggal_lahir: '',
  nik_anak: '',
  id_kecamatan: '',
  id_puskesmas: '',
  id_desa: null,
  posyandu: '',
  jkn: null,
  status_ekonomi: null,
  imunisasi: null,
  bpnt: null,
  pkh: null,
  jamban_sehat: null,
  kebiasaan_merokok: null,
  penyakit_penyerta: '',
  riwayat_ibu_hamil: null,
  sumber_air_bersih: null,
  kecacingan_menderita: null,
  kecacingan_obat: null
});

const faktorDeterminan = ref([
  { id_faktor: 1, nama_faktor: 'Status Ekonomi', keterangan: '(Gakin:1; Non Gakin:2)' },
  { id_faktor: 2, nama_faktor: 'Imunisasi', keterangan: '(Lengkap:1; Tidak:2)' },
  { id_faktor: 3, nama_faktor: 'BPNT', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 4, nama_faktor: 'PKH', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 5, nama_faktor: 'Jamban Sehat', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 6, nama_faktor: 'Sumber Air Bersih', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 7, nama_faktor: 'Kebiasaan Merokok', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 8, nama_faktor: 'Riwayat Ibu Hamil', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 9, nama_faktor: 'Menderita Cacingan', keterangan: '(Ya:1; Tidak:2)' },
  { id_faktor: 10, nama_faktor: 'Mendapat Obat', keterangan: '(Ya:1; Tidak:2)' },
]);

const selectedOptions = reactive({});

const loading = ref(false);
const showError = ref(false);
const errorMessage = ref('');
const desaSearch = ref('');
const loadingDesa = ref(false);
const desaList = ref([]);
const allDesas = ref([]);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const validationAttempted = ref(false);
const tableValidationError = ref(false);

const jenisKelaminOptions = [
  { value: 'L', title: 'Laki-laki' },
  { value: 'P', title: 'Perempuan' }
];

const statusEkonomiOptions = [
  { value: 1, title: 'Gakin' },
  { value: 2, title: 'Non Gakin' }
];

const imunisasiOptions = [
  { value: 1, title: 'Lengkap' },
  { value: 2, title: 'Tidak Lengkap' }
];

const jknOptions = [
  { value: 1, title: 'BPJS Mandiri' },
  { value: 2, title: 'BPJS Pemerintah' },
  { value: 3, title: 'Tidak Punya' },
  { value: 4, title: 'Asuransi Swasta' },
];

const binaryOptions = [
  { value: 1, title: 'Ya' },
  { value: 2, title: 'Tidak' }
];

const yesNoOptions = [
  { value: true, title: 'Ya' },
  { value: false, title: 'Tidak' }
];

const filteredDesaList = computed(() => {
  if (!desaSearch.value || desaSearch.value.length < 2) {
    return desaList.value;
  }
  
  const searchTerm = desaSearch.value.toLowerCase();
  return allDesas.value.filter(desa => {
    const searchString = `${desa.nama_desa} ${desa.kecamatan} ${desa.puskesmas}`.toLowerCase();
    return searchString.includes(searchTerm);
  });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return faktorDeterminan.value.slice(start, end);
});

const debounce = (func, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func(...args), delay);
  };
};

const handleSearchInput = (val) => {
  desaSearch.value = val;
  if (!val || val.length < 2) {
    desaList.value = [...allDesas.value];
  }
};

const searchDesa = async (searchTerm) => {
  if (!searchTerm || searchTerm.length < 2) {
    desaList.value = [];
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
      nama_desa: desa.desa || desa.nama_desa || '',
      kecamatan: desa.kecamatan || '',
      puskesmas: desa.puskesmas || '',
      fullInfo: `${desa.desa || desa.nama_desa} - ${desa.kecamatan} (${desa.puskesmas})`
    }));
  } catch (error) {
    console.error('Error searching desa:', error);
    desaList.value = [];
  } finally {
    loadingDesa.value = false;
  }
};

const debouncedSearchDesa = debounce(searchDesa, 500);

const customFilter = (item, queryText, itemText) => {
  const searchText = queryText.toLowerCase();
  const textFields = [
    itemText.title.toLowerCase(),
    item.raw.kecamatan.toLowerCase(),
    item.raw.puskesmas.toLowerCase()
  ];
  
  return textFields.some(text => 
    text.includes(searchText) || 
    searchText.split(' ').some(term => text.includes(term))
  );
};

const loadAllDesas = async () => {
  try {
    loadingDesa.value = true;
    const response = await apiClient.get('/desa');

    if (!response.data || !Array.isArray(response.data.data)) {
      throw new Error('Invalid data format from API');
    }
    
    allDesas.value = response.data.data.map(desa => ({
      id_desa: desa.id,
      nama_desa: desa.nama_desa || desa.desa || '',
      kecamatan: desa.kecamatan || '',
      puskesmas: desa.puskesmas || '',
      fullInfo: `${desa.nama_desa || desa.desa} - ${desa.kecamatan} (${desa.puskesmas})`
    }));
    
    desaList.value = [...allDesas.value];
  } catch (err) {
    console.error('Error loading desa data:', err);
    showError.value = true;
    errorMessage.value = 'Gagal memuat data desa: ' + err.message;
  } finally {
    loadingDesa.value = false;
  }
};

const handleDesaChange = async (desaId) => {
  if (!desaId) return;

  const selectedDesa = desaList.value.find(desa => desa.id_desa === desaId);
  if (selectedDesa) {
    form.kecamatan = selectedDesa.kecamatan;
    form.puskesmas = selectedDesa.puskesmas;
    form.id_desa = selectedDesa.id_desa;
  }
};

const handleRadioChange = (idFaktor, value) => {
  selectedOptions[idFaktor] = value;
  if (validationAttempted.value && tableValidationError.value) {
    validateTableOptions();
  }
};

const validateTableOptions = () => {
  let isValid = true;
  tableValidationError.value = false;
  
  faktorDeterminan.value.forEach(item => {
    if (selectedOptions[item.id_faktor] !== 1 && selectedOptions[item.id_faktor] !== 2) {
      isValid = false;
    }
  });
  
  tableValidationError.value = !isValid;
  return isValid;
};

const handleSubmit = async () => {
  validationAttempted.value = true;
  const formValid = await formRef.value.validate();

  const tableValid = validateTableOptions();

  if (!formValid.valid || !tableValid) {
    await window.Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Mohon lengkapi semua field yang wajib diisi',
    });
    return;
  }

  loading.value = true;

  try {
    const nikCheckResponse = await apiClient.post('/anak-stunting/check-nik', {
      nik_anak: form.nik_anak
    });

    if (nikCheckResponse.data.exists) {
      const result = await window.Swal.fire({
        title: 'Konfirmasi',
        text: 'NIK telah ada sebelumnya. Lanjutkan untuk mengupdate data dengan NIK tersebut atau batalkan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Lanjutkan',
        cancelButtonText: 'Batalkan',
        reverseButtons: true
      });

      if (!result.isConfirmed) {
        loading.value = false;
        return;
      }

      const payload = {
        nik_anak: form.nik_anak,
        nama_anak: form.nama_anak,
        jenis_kelamin: form.jenis_kelamin,
        tanggal_lahir: form.tanggal_lahir,
        id_desa: form.id_desa,
        posyandu: form.posyandu,
        jkn: form.jkn,
        penyakit_penyerta: form.penyakit_penyerta,
        status_ekonomi: selectedOptions[1] === 1 ? '1' : '2',
        imunisasi: selectedOptions[2] === 1 ? '1' : '2',
        bpnt: selectedOptions[3] === 1 ? '1' : '2',
        pkh: selectedOptions[4] === 1 ? '1' : '2',
        jamban_sehat: selectedOptions[5] === 1 ? '1' : '2',
        sumber_air_bersih: selectedOptions[6] === 1 ? '1' : '2',
        kebiasaan_merokok: selectedOptions[7] === 1 ? '1' : '2',
        riwayat_ibu_hamil: selectedOptions[8] === 1 ? '1' : '2',
        kecacingan_menderita: selectedOptions[9] === 1 ? '1' : '2',
        kecacingan_obat: selectedOptions[10] === 1 ? '1' : '2',
      };

      const idToUpdate = nikCheckResponse.data.id_anak || form.id;
      
      const updateResponse = await apiClient.put(`/anak-stunting/${idToUpdate}`, payload);
      emit('save', updateResponse.data);
      emit('refresh-data');
      await window.Swal.fire({
        icon: 'success',
        title: 'Data berhasil diupdate!',
        showConfirmButton: false,
        timer: 2000
      });
    } else {
      const payload = {
        nik_anak: form.nik_anak,
        nama_anak: form.nama_anak,
        jenis_kelamin: form.jenis_kelamin,
        tanggal_lahir: form.tanggal_lahir,
        id_desa: form.id_desa,
        posyandu: form.posyandu,
        jkn: form.jkn,
        penyakit_penyerta: form.penyakit_penyerta,
        status_ekonomi: selectedOptions[1] === 1 ? '1' : '2',
        imunisasi: selectedOptions[2] === 1 ? '1' : '2',
        bpnt: selectedOptions[3] === 1 ? '1' : '2',
        pkh: selectedOptions[4] === 1 ? '1' : '2',
        jamban_sehat: selectedOptions[5] === 1 ? '1' : '2',
        sumber_air_bersih: selectedOptions[6] === 1 ? '1' : '2',
        kebiasaan_merokok: selectedOptions[7] === 1 ? '1' : '2',
        riwayat_ibu_hamil: selectedOptions[8] === 1 ? '1' : '2',
        kecacingan_menderita: selectedOptions[9] === 1 ? '1' : '2',
        kecacingan_obat: selectedOptions[10] === 1 ? '1' : '2',
      };

      const response = await apiClient.post('/anak-stunting', payload);
      emit('save', response.data);
      
      emit('refresh-data');

      await window.Swal.fire({
        icon: 'success',
        title: 'Data berhasil ditambahkan!',
        showConfirmButton: false,
        timer: 2000
      });
    }
  } catch (error) {
    showError.value = true;
    console.error('Error during submission:', error);

    let errorMessage = 'Terjadi kesalahan saat menyimpan data';

    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage = errors.join(', ');
      console.error('Validation errors:', error.response.data.errors);
    }

    await window.Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: errorMessage,
    });
  } finally {
    loading.value = false;
  }
};

const validate = async () => {
  const { valid } = await formRef.value.validate();
  if (valid) {
    emit('save', form);
  }
};

const resetForm = () => {
  if (formRef.value) {
    formRef.value.reset();
    Object.assign(form, {
      nama_anak: '',
      jenis_kelamin: '',
      tanggal_lahir: '',
      nik_anak: '',
      id_desa: null,
      kecamatan: '',
      puskesmas: '',
      posyandu: '',
      jkn: null,
      status_ekonomi: null,
      imunisasi: null,
      bpnt: null,
      pkh: null,
      jamban_sehat: null,
      kebiasaan_merokok: null,
      penyakit_penyerta: '',
      riwayat_ibu_hamil: null,
      sumber_air_bersih: null,
      kecacingan_menderita: null,
      kecacingan_obat: null
    });

    faktorDeterminan.value.forEach(item => {
      selectedOptions[item.id_faktor] = null;
    });
  }
};

const closeDialog = () => {
  emit('closeDialog');
};

onMounted(async () => {
  loadAllDesas();
  faktorDeterminan.value.forEach(item => {
    selectedOptions[item.id_faktor] = null;
  });
});

watch(desaSearch, (newVal) => {
  if (newVal && newVal.length >= 2) {
    debouncedSearchDesa(newVal);
  } else {
    desaList.value = [];
  }
});

defineExpose({
  desaSearch,
  desaList,
  loadingDesa,
  filteredDesaList,
  customFilter,
  searchDesa
});
</script>

<style scoped>
.manual-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 0;
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

:deep(.v-input) {
    margin-bottom: 12px;
}

.table-container {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-top: 8px;
    margin-bottom: 16px;
    width: 100%;
}

.custom-table {
    width: 100%;
    table-layout: fixed;
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
    text-transform: none;
    color: #333;
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

.v-theme--dark .custom-table th {
    background-color: #1e1e1e;
    color: #ffffff;
}

.v-theme--dark .custom-table td {
    background-color: #2d2d2d;
    color: #ffffff;
}

.v-theme--dark .custom-table tbody tr:hover {
    background-color: #3d3d3d;
}

.v-theme--dark .custom-table,
.v-theme--dark .custom-table td,
.v-theme--dark .custom-table th {
    border-color: #444;
}

:deep(.v-radio) {
    margin: 0 auto;
    display: flex;
    justify-content: center;
}

:deep(.v-selection-control) {
    margin: 0 auto;
}
</style>