<template>
  <VRow>
    <VCol cols="12">
      <VCard title="LAPORAN KEGIATAN TPPS" class="rounded-lg">
        <VRow class="align-center px-4 py-2">
          <VCol cols="12" md="7" lg="6" class="d-flex align-center pb-2">
            <VMenu offset-y>
              <template v-slot:activator="{ props }">
                <VBtn v-bind="props" color="primary" class="me-2 rounded-lg d-flex justify-center align-center"
                  style="min-width: 40px; min-height: 40px;">
                  <VIcon>bx-filter</VIcon>
                </VBtn>
              </template>
              <VList>
                <VListItem v-for="(item, index) in sortOptions" :key="index" :value="item.value"
                  @click="applySort(item.value)">
                  <VListItemTitle>{{ item.title }}</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
            <VTextField v-model="searchQuery" prepend-inner-icon="bx-search" placeholder="Search" class="flex-grow-1"
              clearable density="comfortable" @update:modelValue="fetchData" />
          </VCol>
          <VCol cols="12" md="5" lg="6" class="d-flex justify-md-end justify-start flex-wrap pb-2">
            <VBtn v-if="isAllowedToAdd" color="success" class="me-2 mb-2 rounded-lg" @click="openAddDialog">Tambah</VBtn>
            <VBtn color="warning" @click="exportData" class="mb-2 rounded-lg">Export</VBtn>
          </VCol>
        </VRow>

        <div class="table-container">
          <VTable class="custom-table elevation-1">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">Nama Kegiatan</th>
                <th class="text-center">Jumlah Anggaran</th>
                <th class="text-center">Kerangka Acuan</th>
                <th class="text-center">Dokumentasi</th>
                <th v-if="isAllowedToAdd" class="text-center header-cell-top-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in paginatedData" :key="index">
                <td class="text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="text-center">{{ item.nama_kegiatan }}</td>
                <td class="text-center">{{ formatCurrency(item.jumlah_anggaran) }}</td>
                <td class="text-center">
                  <div v-if="item.kerangka_acuan" class="d-flex justify-center align-center">
                    <VBtn variant="text" color="primary" @click="openFilePreview(item.kerangka_acuan)"
                      class="preview-link">
                      <i class="bx bx-file me-2"></i> Lihat File
                    </VBtn>
                  </div>
                </td>
                <td class="text-center">
                  <div v-if="item.dokumentasi_kegiatan_tpps" class="d-flex justify-center align-center">
                    <VBtn variant="text" color="primary" @click="openFilePreview(item.dokumentasi_kegiatan_tpps)"
                      class="preview-link">
                      <i class="bx bx-image me-2"></i> Lihat File
                    </VBtn>
                  </div>
                </td>
                <td v-if="isAllowedToAdd" class="text-center">
                  <div class="button-group">
                    <VBtn icon color="primary" @click="editItem(item)" class="square-button">
                      <i class="bx bxs-edit-alt"></i>
                    </VBtn>
                    <VBtn icon color="error" @click="deleteItem(item)" class="square-button">
                      <i class="bx bxs-trash-alt"></i>
                    </VBtn>
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </div>

        <div class="d-flex align-center justify-space-between pa-4">
          <div class="d-flex align-center">
            <span class="text-body-2 me-4">Items per page:</span>
            <VSelect v-model="itemsPerPage" :items="[5, 10, 15, 20]" variant="outlined" density="compact" hide-details
              class="items-per-page-select" style="width: 80px;" @update:modelValue="fetchData" />
          </div>
          <VPagination v-model="currentPage" :length="totalPages" :total-visible="7" rounded="lg"
            @update:modelValue="fetchData" />
          <div class="d-flex align-center">
            <span class="text-body-2">
              {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, totalItems) }} of {{
                totalItems
              }}
            </span>
          </div>
        </div>
      </VCard>
    </VCol>

    <VDialog v-model="previewDialog" max-width="800">
      <VCard>
        <VCardTitle class="d-flex align-center pa-4">
          <span>Pratinjau File</span>
          <VSpacer />
          <VBtn icon variant="text" @click="previewDialog = false">
            <VIcon>bx bx-x</VIcon>
          </VBtn>
        </VCardTitle>
        <VDivider />
        <VCardText class="pa-4">
          <div class="preview-carousel">
            <VBtn icon variant="text" :disabled="previewFiles.length <= 1 || currentPreviewIndex === 0"
              @click="currentPreviewIndex--">
              <VIcon size="32">bx bx-chevron-left</VIcon>
            </VBtn>
            <div class="preview-content">
              <div v-if="isPreviewPDF" class="preview-container" style="height: 70vh;">
                <embed :src="currentPreviewFileUrl" type="application/pdf" width="100%" height="100%"
                  style="border: none;" frameborder="0">
              </div>
              <div v-else-if="isPreviewImage" class="preview-container">
                <img :src="currentPreviewFileUrl" class="preview-image-full" alt="Preview" @error="handleImageError" />
              </div>
              <div v-else class="preview-container text-center">
                <VIcon size="64" color="grey" class="mb-4">bx bx-file</VIcon>
                <p class="text-body-1 mb-4">File tidak dapat ditampilkan secara langsung</p>
                <VBtn color="primary" :href="currentPreviewFileUrl" target="_blank" download>
                  Unduh File
                </VBtn>
              </div>
            </div>
            <VBtn icon variant="text"
              :disabled="previewFiles.length <= 1 || currentPreviewIndex === previewFiles.length - 1"
              @click="currentPreviewIndex++">
              <VIcon size="32">bx bx-chevron-right</VIcon>
            </VBtn>
          </div>
          <div class="text-center mt-4" v-if="previewFiles.length > 1">
            <span class="text-body-2">
              File {{ currentPreviewIndex + 1 }} dari {{ previewFiles.length }}
            </span>
          </div>
        </VCardText>
        <VDivider />
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn color="grey-darken-1" variant="text" @click="previewDialog = false">Tutup</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VDialog v-model="addDialog" max-width="600px" persistent>
    <VCard class="form">
      <VCardTitle class="text-h5 d-flex align-center mb-2">
        <span>Tambah Laporan Kegiatan TPPS</span>
        <VSpacer />
        <VBtn icon variant="text" @click="closeAddDialog">
          <i class="bx bx-x"></i>
        </VBtn>
      </VCardTitle>
      <VCardText class="pt-4">
        <VForm ref="addFormRef">
          <div class="form-section">
            <div class="mt-4">
              <VTextField v-model="newItem.nama_kegiatan" label="Nama Kegiatan" class="mb-6"
                :rules="[v => !!v || 'Nama Kegiatan harus diisi']" required />
              <VTextField v-model="newItem.jumlah_anggaran" label="Jumlah Anggaran" prefix="Rp" class="mb-2" :rules="[
                v => !!v || 'Jumlah Anggaran harus diisi',
                v => !v || (parseInt(v.replace(/\D/g, '')) <= 10000000000)
              ]" required @input="(e) => validateBudgetInput(e, 'new')" inputmode="numeric" pattern="[0-9]*" />
              <p class="text-caption text-disabled mt-2 mb-4">*Masukkan nominal tanpa titik (.)</p>

              <VFileInput v-model="newKerangkaAcuanFiles" label="Kerangka Acuan" accept=".pdf"
                placeholder="Pilih file PDF" class="mb-2" multiple :rules="[
                  v => !v || v.length <= (5 - kerangkaAcuanFiles.length) || 'Maksimal 5 file',
                  v => !v || Array.from(v).every(file => file.size <= 15 * 1024 * 1024) || 'File terlalu besar'
                ]" :error-messages="formErrors.add.kerangkaAcuan ? [formErrors.add.kerangkaAcuan] : []"
                @update:modelValue="handleNewKakFiles"
                messages="Format file: PDF (maksimal 15MB per file, maksimal 5 file total)" required />

              <div v-if="kerangkaAcuanFiles.length > 0" class="mb-6">
                <p class="text-subtitle-2 mb-2">File Kerangka Acuan:</p>
                <div class="file-carousel">
                  <VBtn icon variant="text" :disabled="currentKakPreviewIndex === 0"
                    @click="currentKakPreviewIndex--">
                    <i class="bx bx-chevron-left"></i>
                  </VBtn>
                  <div class="file-preview-box d-flex align-center p-2 rounded bg-grey-lighten-4" @click="previewAddKakFile(currentKakPreviewIndex)">
                    <VIcon size="24" color="red" class="me-2">bx-file-pdf</VIcon>
                    <span class="file-preview-name flex-grow-1">
                      {{ kerangkaAcuanFiles[currentKakPreviewIndex].name }}
                    </span>
                    <VBtn icon variant="text" color="error" class="ml-2"
                      @click.stop="removeKakFileInAdd(currentKakPreviewIndex)">
                      <i class="bx bx-trash"></i>
                    </VBtn>
                  </div>
                  <VBtn icon variant="text" :disabled="currentKakPreviewIndex === kerangkaAcuanFiles.length - 1"
                    @click="currentKakPreviewIndex++">
                    <i class="bx bx-chevron-right"></i>
                  </VBtn>
                </div>
              </div>

              <VFileInput v-model="newDokumentasiFiles" label="Dokumentasi Kegiatan" accept=".jpg,.jpeg,.png,.pdf"
                placeholder="Pilih file" class="mb-2" multiple :rules="[
                  v => !v || v.length <= (5 - dokumentasiFiles.length) || 'Maksimal 5 file',
                  v => !v || Array.from(v).every(file => file.size <= 15 * 1024 * 1024) || 'File terlalu besar'
                ]" :error-messages="formErrors.add.dokumentasi ? [formErrors.add.dokumentasi] : []"
                @update:modelValue="handleNewDokumentasiFiles"
                messages="Format file: JPG, JPEG, PNG, PDF (maksimal 15MB per file, maksimal 5 file total)"
                required />

              <div v-if="dokumentasiFiles.length > 0" class="mb-6">
                <p class="text-subtitle-2 mb-2">File Dokumentasi:</p>
                <div class="file-carousel">
                  <VBtn icon variant="text" :disabled="currentDokumentasiPreviewIndex === 0"
                    @click="currentDokumentasiPreviewIndex--">
                    <i class="bx bx-chevron-left"></i>
                  </VBtn>
                  <div class="file-preview-box d-flex align-center p-2 rounded bg-grey-lighten-4" @click="previewAddDokumentasiFile(currentDokumentasiPreviewIndex)">
                    <template v-if="isFileImage(dokumentasiFiles[currentDokumentasiPreviewIndex])">
                      <img :src="getObjectURL(dokumentasiFiles[currentDokumentasiPreviewIndex])" class="me-2"
                        width="32" height="32" alt="Preview" />
                    </template>
                    <VIcon v-else size="24" color="red" class="me-2">bx-file-pdf</VIcon>
                    <span class="file-preview-name flex-grow-1">
                      {{ dokumentasiFiles[currentDokumentasiPreviewIndex].name }}
                    </span>
                    <VBtn icon variant="text" color="error" class="ml-2"
                      @click.stop="removeDokumentasiFileInAdd(currentDokumentasiPreviewIndex)">
                      <i class="bx bx-trash"></i>
                    </VBtn>
                  </div>
                  <VBtn icon variant="text" :disabled="currentDokumentasiPreviewIndex === dokumentasiFiles.length - 1"
                    @click="currentDokumentasiPreviewIndex++">
                    <i class="bx bx-chevron-right"></i>
                  </VBtn>
                </div>
              </div>
            </div>
          </div>
        </VForm>
      </VCardText>
      <VCardActions class="pt-2 pb-4 px-4">
        <VSpacer />
        <VBtn color="primary" @click="saveNewItem" :loading="loading">Simpan</VBtn>
        <VBtn color="grey-darken-1" variant="text" @click="resetAddForm">Reset</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

    <VDialog v-model="editDialog" max-width="600px" persistent>
    <VCard class="form">
      <VCardTitle class="text-h5 d-flex align-center mb-2">
        <span>Edit Laporan Kegiatan TPPS</span>
        <VSpacer />
        <VBtn icon variant="text" @click="editDialog = false">
          <i class="bx bx-x"></i>
        </VBtn>
      </VCardTitle>
      <VCardText class="pt-4">
        <VForm ref="editFormRef">
          <div class="form-section">
            <div class="mt-4">
              <VTextField v-model="editedItem.nama_kegiatan" label="Nama Kegiatan" class="mb-6"
                :rules="[v => !!v || 'Nama Kegiatan harus diisi']" required />
              <VTextField v-model="editedItem.jumlah_anggaran" label="Jumlah Anggaran" prefix="Rp" class="mb-2"
                :rules="[
                  v => !!v && v.toString().trim() !== '' || 'Jumlah Anggaran harus diisi',
                  v => !v || /^\d+$/.test(v) || 'Hanya angka yang diperbolehkan',
                  v => !v || parseInt(v) <= 10000000000 || 'Maksimal Rp 10.000.000.000'
                ]" @input="(e) => validateBudgetInput(e, 'edit')" required />
              <p class="text-caption text-disabled mt-2 mb-4">*Masukkan nominal tanpa titik (.)</p>

              <VFileInput v-model="tempKerangkaAcuanFile" label="Kerangka Acuan" accept=".pdf"
                placeholder="Pilih file PDF" class="mb-2" multiple :rules="[
                  v => !v || v.length <= (5 - (editedItem.kerangka_acuan?.length || 0) - editKerangkaAcuanFiles.length) || `Maksimal ${5 - (editedItem.kerangka_acuan?.length || 0) - editKerangkaAcuanFiles.length} file tersisa`
                ]" :error-messages="formErrors.edit.kerangkaAcuan ? [formErrors.edit.kerangkaAcuan] : []"
                @update:modelValue="handleEditKakFiles"
                messages="Format file: PDF (maksimal 15MB per file, maksimal 5 file total)" required />

              <div v-if="getOrderedKakFiles.length > 0" class="mb-6">
                <p class="text-subtitle-2 mb-2">File Kerangka Acuan:</p>
                <div class="file-carousel">
                  <VBtn icon variant="text" :disabled="currentEditKakIndex === 0" @click="currentEditKakIndex--">
                    <i class="bx bx-chevron-left"></i>
                  </VBtn>
                  <div class="file-preview-box d-flex align-center p-2 rounded bg-grey-lighten-4" @click="previewEditKakFile(currentEditKakIndex)">
                    <VIcon size="24" color="red" class="me-2">bx-file-pdf</VIcon>
                    <span class="file-preview-name flex-grow-1">
                      {{ getEditKakFileName(currentEditKakIndex) }}
                    </span>
                    <VBtn icon variant="text" color="error" class="ml-2"
                      @click.stop="removeKakFile(currentEditKakIndex)" :disabled="getOrderedKakFiles.length <= 1">
                      <i class="bx bx-trash"></i>
                    </VBtn>
                  </div>
                  <VBtn icon variant="text" :disabled="currentEditKakIndex === getOrderedKakFiles.length - 1"
                    @click="currentEditKakIndex++">
                    <i class="bx bx-chevron-right"></i>
                  </VBtn>
                </div>
              </div>

              <VFileInput v-model="tempDokumentasiFile" label="Dokumentasi Kegiatan" accept=".jpg,.jpeg,.png,.pdf"
                placeholder="Pilih file" class="mb-2" multiple :rules="[
                  v => !v || v.length <= (5 - (editedItem.dokumentasi_kegiatan_tpps?.length || 0) - editDokumentasiFiles.length) || `Maksimal ${5 - (editedItem.dokumentasi_kegiatan_tpps?.length || 0) - editDokumentasiFiles.length} file tersisa`
                ]" :error-messages="formErrors.edit.dokumentasi ? [formErrors.edit.dokumentasi] : []"
                @update:modelValue="handleEditDokumentasiFiles"
                messages="Format file: JPG, JPEG, PNG, PDF (maksimal 15MB per file, maksimal 5 file total)"
                required />

              <div v-if="getOrderedDokumentasiFiles.length > 0" class="mb-6">
                <p class="text-subtitle-2 mb-2">File Dokumentasi:</p>
                <div class="file-carousel">
                  <VBtn icon variant="text" :disabled="currentEditDokumentasiIndex === 0"
                    @click="currentEditDokumentasiIndex--">
                    <i class="bx bx-chevron-left"></i>
                  </VBtn>
                  <div class="file-preview-box d-flex align-center p-2 rounded bg-grey-lighten-4" @click="previewEditDokumentasiFile(currentEditDokumentasiIndex)">
                    <template v-if="isEditDokumentasiImage(currentEditDokumentasiIndex)">
                      <img :src="getEditDokumentasiPreviewUrl(currentEditDokumentasiIndex)" class="me-2" width="32"
                        height="32" alt="Preview" />
                    </template>
                    <VIcon v-else size="24" color="red" class="me-2">bx-file-pdf</VIcon>
                    <span class="file-preview-name flex-grow-1">
                      {{ getEditDokumentasiFileName(currentEditDokumentasiIndex) }}
                    </span>
                    <VBtn icon variant="text" color="error" class="ml-2"
                      @click.stop="removeDokumentasiFile(currentEditDokumentasiIndex)" :disabled="getOrderedDokumentasiFiles.length <= 1">
                      <i class="bx bx-trash"></i>
                    </VBtn>
                  </div>
                  <VBtn icon variant="text"
                    :disabled="currentEditDokumentasiIndex === getOrderedDokumentasiFiles.length - 1"
                    @click="currentEditDokumentasiIndex++">
                    <i class="bx bx-chevron-right"></i>
                  </VBtn>
                </div>
              </div>
            </div>
          </div>
        </VForm>
      </VCardText>
      <VCardActions class="pt-2 pb-4 px-4">
        <VSpacer />
        <VBtn color="primary" @click="updateItem" :loading="loading">Simpan</VBtn>
        <VBtn color="grey-darken-1" variant="text" @click="resetEditForm">Reset</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  </VRow>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import apiClient from '@/services/api';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const tppsData = ref([]);
const addDialog = ref(false);
const editDialog = ref(false);
const addFormRef = ref(null);
const editFormRef = ref(null);
const previewDialog = ref(false);
const previewFiles = ref([]);
const currentPreviewIndex = ref(0);
const loading = ref(false);

const currentUser = computed(() => authStore.user);
const isAllowedToAdd = computed(() => {
  const allowedRoles = [
    'Dinas KB', 
    'Dinas Kesehatan', 
    'Dinas Sosial', 
    'Dinas Pertanian', 
    'Dinas PU', 
    'Dispermades', 
    'Kader Desa'
  ];
  return allowedRoles.includes(currentUser.value?.role);
});

const newItem = ref({
  nama_kegiatan: '',
  jumlah_anggaran: '',
});

const editedItem = ref({
  id_report_tpps: null,
  nama_kegiatan: '',
  jumlah_anggaran: '',
  kerangka_acuan: null,
  dokumentasi_kegiatan_tpps: null
});

const kerangkaAcuanFiles = ref([]);
const dokumentasiFiles = ref([]);
const newKerangkaAcuanFiles = ref([]);
const newDokumentasiFiles = ref([]);
const currentKakPreviewIndex = ref(0);
const currentDokumentasiPreviewIndex = ref(0);
const editKerangkaAcuanFiles = ref([]);
const editDokumentasiFiles = ref([]);
const tempKerangkaAcuanFile = ref([]);
const tempDokumentasiFile = ref([]);
const currentEditKakIndex = ref(0);
const currentEditDokumentasiIndex = ref(0);
const filesToDelete = ref({
  kerangka_acuan: [],
  dokumentasi: []
});

const sortOptions = ref([
  { title: 'A-Z (Nama Kegiatan)', value: 'name_asc' },
  { title: 'Z-A (Nama Kegiatan)', value: 'name_desc' },
  { title: 'Terbaru', value: 'latest' },
  { title: 'Terlama', value: 'oldest' },
]);

const currentSort = ref('latest');

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedData.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(tppsData.value.length / itemsPerPage.value);
});

const currentPreviewFileUrl = computed(() => {
  if (previewFiles.value.length === 0) return '';
  const file = previewFiles.value[currentPreviewIndex.value];
  return file instanceof File ? URL.createObjectURL(file) : file.url;
});

const isPreviewPDF = computed(() => {
  const url = currentPreviewFileUrl.value?.toLowerCase();
  return url?.endsWith('.pdf') || previewFiles.value[currentPreviewIndex.value]?.type === 'application/pdf';
});

const isPreviewImage = computed(() => {
  const url = currentPreviewFileUrl.value?.toLowerCase();
  return url?.endsWith('.jpg') || url?.endsWith('.jpeg') || url?.endsWith('.png') ||
    previewFiles.value[currentPreviewIndex.value]?.type?.startsWith('image/');
});

const applySort = (sortValue) => {
  currentSort.value = sortValue;
  fetchData();
};

const fetchData = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/report-tpps', {
      params: {
        search: searchQuery.value,
        page: currentPage.value,
        per_page: itemsPerPage.value,
        sort: currentSort.value
      }
    });

    tppsData.value = response.data.data || [];
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    loading.value = false;
  }
};

const sortedData = computed(() => {
  const data = [...tppsData.value];

  switch (currentSort.value) {
    case 'name_asc':
      return data.sort((a, b) => a.nama_kegiatan.localeCompare(b.nama_kegiatan));
    case 'name_desc':
      return data.sort((a, b) => b.nama_kegiatan.localeCompare(a.nama_kegiatan));
    case 'latest':
      return data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    case 'oldest':
      return data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    default:
      return data;
  }
});

const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0';
  if (typeof amount === 'string' && /\D/.test(amount)) return amount;
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return `Rp ${numericAmount.toLocaleString('id-ID')}`;
};

const formatAnggaran = (type) => {
  const target = type === 'edit' ? editedItem.value : newItem.value;

  if (target.jumlah_anggaran) {
    target.jumlah_anggaran = target.jumlah_anggaran.toString().replace(/\D/g, '');

    if (target.jumlah_anggaran === '') {
      target.jumlah_anggaran = null;
    }
  } else {
    target.jumlah_anggaran = null;
  }
};

const validateBudgetInput = (event, type) => {
  let input = event.target?.value || event;

  input = input.toString().replace(/\D/g, '');

  if (input.length > 10) {
    input = input.slice(0, 10);
  }

  if (type === 'new') {
    newItem.value.jumlah_anggaran = input;
  } else {
    editedItem.value.jumlah_anggaran = input;
  }

  nextTick(() => {
    if (type === 'new' && addFormRef.value) {
      addFormRef.value.validate();
    } else if (editFormRef.value) {
      editFormRef.value.validate();
    }
  });
};

const openFilePreview = (filepath) => {
  if (!filepath) {
    alert('File tidak tersedia');
    return;
  }

  const filesToPreview = Array.isArray(filepath) ? filepath : [filepath];
  previewFiles.value = filesToPreview.map(file => ({
    url: file.startsWith('http') ? file : `${apiClient.defaults.baseURL.replace('/api', '')}/${file}`,
    type: file.split('.').pop().toLowerCase()
  }));

  currentPreviewIndex.value = 0;
  previewDialog.value = true;
};

const formErrors = ref({
  add: {
    kerangkaAcuan: '',
    dokumentasi: ''
  },
  edit: {
    kerangkaAcuan: '',
    dokumentasi: ''
  }
});

const handleNewKakFiles = (files) => {
  if (!files) return;

  const totalFiles = files.length + kerangkaAcuanFiles.value.length;
  if (totalFiles > 5) {
    formErrors.value.add.kerangkaAcuan = 'Maksimal 5 file';
    newKerangkaAcuanFiles.value = [];
    return;
  }

  formErrors.value.add.kerangkaAcuan = '';
  kerangkaAcuanFiles.value = [...files, ...kerangkaAcuanFiles.value];
  newKerangkaAcuanFiles.value = [];
};

const handleNewDokumentasiFiles = (files) => {
  if (!files) return;

  const totalFiles = files.length + dokumentasiFiles.value.length;
  if (totalFiles > 5) {
    formErrors.value.add.dokumentasi = 'Maksimal 5 file';
    newDokumentasiFiles.value = [];
    return;
  }

  formErrors.value.add.dokumentasi = '';
  dokumentasiFiles.value = [...files, ...dokumentasiFiles.value];
  newDokumentasiFiles.value = [];
};

const handleEditKakFiles = (files) => {
  if (!files || files.length === 0) return;

  const totalFiles = files.length + editKerangkaAcuanFiles.value.length + (editedItem.value.kerangka_acuan?.length || 0);
  if (totalFiles > 5) {
    formErrors.value.edit.kerangkaAcuan = 'Maksimal 5 file';
    tempKerangkaAcuanFile.value = [];
    return;
  }

  formErrors.value.edit.kerangkaAcuan = '';
  editKerangkaAcuanFiles.value = [...files, ...editKerangkaAcuanFiles.value];
  tempKerangkaAcuanFile.value = [];
};

const handleEditDokumentasiFiles = (files) => {
  if (!files || files.length === 0) return;

  const totalFiles = files.length + editDokumentasiFiles.value.length + (editedItem.value.dokumentasi_kegiatan_tpps?.length || 0);
  if (totalFiles > 5) {
    formErrors.value.edit.dokumentasi = 'Maksimal 5 file';
    tempDokumentasiFile.value = [];
    return;
  }

  formErrors.value.edit.dokumentasi = '';
  editDokumentasiFiles.value = [...files, ...editDokumentasiFiles.value];
  tempDokumentasiFile.value = [];
};

const removeKakFile = (index) => {
  const orderedFiles = getOrderedKakFiles.value;
  
  if (orderedFiles.length <= 1) {
    showErrorAlert('Gagal', 'Minimal harus ada 1 file Kerangka Acuan');
    return;
  }
  
  if (index < orderedFiles.length) {
    if (index < editKerangkaAcuanFiles.value.length) {
      editKerangkaAcuanFiles.value.splice(index, 1);
    } else {
      const fileToDelete = orderedFiles[index];
      filesToDelete.value.kerangka_acuan.push(fileToDelete);
      editedItem.value.kerangka_acuan = editedItem.value.kerangka_acuan.filter(
        file => file !== fileToDelete
      );
    }

    if (currentEditKakIndex.value >= getOrderedKakFiles.value.length) {
      currentEditKakIndex.value = Math.max(0, getOrderedKakFiles.value.length - 1);
    }
  }
};

const removeKakFileInAdd = (index) => {
  kerangkaAcuanFiles.value.splice(index, 1);
  if (currentKakPreviewIndex.value >= kerangkaAcuanFiles.value.length) {
    currentKakPreviewIndex.value = Math.max(0, kerangkaAcuanFiles.value.length - 1);
  }
};

const removeDokumentasiFile = (index) => {
  const orderedFiles = getOrderedDokumentasiFiles.value;
  
  if (orderedFiles.length <= 1) {
    showErrorAlert('Gagal', 'Minimal harus ada 1 file Dokumentasi');
    return;
  }
  
  if (index < orderedFiles.length) {
    if (index < editDokumentasiFiles.value.length) {
      editDokumentasiFiles.value.splice(index, 1);
    } else {
      const fileToDelete = orderedFiles[index];
      filesToDelete.value.dokumentasi.push(fileToDelete);
      editedItem.value.dokumentasi_kegiatan_tpps = editedItem.value.dokumentasi_kegiatan_tpps.filter(
        file => file !== fileToDelete
      );
    }

    if (currentEditDokumentasiIndex.value >= getOrderedDokumentasiFiles.value.length) {
      currentEditDokumentasiIndex.value = Math.max(0, getOrderedDokumentasiFiles.value.length - 1);
    }
  }
};

const removeDokumentasiFileInAdd = (index) => {
  dokumentasiFiles.value.splice(index, 1);
  if (currentDokumentasiPreviewIndex.value >= dokumentasiFiles.value.length) {
    currentDokumentasiPreviewIndex.value = Math.max(0, dokumentasiFiles.value.length - 1);
  }
};

const getOrderedKakFiles = computed(() => {
  return [
    ...editKerangkaAcuanFiles.value,
    ...(editedItem.value.kerangka_acuan || [])
  ];
});

const getOrderedDokumentasiFiles = computed(() => {
  const newFiles = [...editDokumentasiFiles.value];

  const oldFiles = editedItem.value.dokumentasi_kegiatan_tpps || [];

  return [...newFiles, ...oldFiles];
});

const isFileImage = (file) => {
  if (!file) return false;
  if (file.type) return file.type.startsWith('image/');
  const fileName = typeof file === 'string' ? file : file.name;
  return fileName.toLowerCase().endsWith('.jpg') ||
    fileName.toLowerCase().endsWith('.jpeg') ||
    fileName.toLowerCase().endsWith('.png');
};

const getObjectURL = (file) => file ? URL.createObjectURL(file) : '';

const openAddDialog = () => {
  resetAddForm();
  addDialog.value = true;
};

const closeAddDialog = () => {
  resetAddForm();
  addDialog.value = false;
};

const resetAddForm = () => {
  newItem.value = {
    nama_kegiatan: '',
    jumlah_anggaran: ''
  };
  kerangkaAcuanFiles.value = [];
  dokumentasiFiles.value = [];
  newKerangkaAcuanFiles.value = [];
  newDokumentasiFiles.value = [];
  currentKakPreviewIndex.value = 0;
  currentDokumentasiPreviewIndex.value = 0;

  formErrors.value.add.kerangkaAcuan = '';
  formErrors.value.add.dokumentasi = '';

  nextTick(() => {
    if (addFormRef.value) {
      addFormRef.value.reset();
      addFormRef.value.resetValidation();
    }
  });
};

const resetEditForm = () => {
  if (originalEditedItem.value) {
    editedItem.value = JSON.parse(JSON.stringify(originalEditedItem.value));

    if (editedItem.value.jumlah_anggaran) {
      editedItem.value.jumlah_anggaran = editedItem.value.jumlah_anggaran.toString()
        .replace(/\D/g, '');
    }
  }

  editKerangkaAcuanFiles.value = [];
  editDokumentasiFiles.value = [];
  tempKerangkaAcuanFile.value = [];
  tempDokumentasiFile.value = [];
  filesToDelete.value = { kerangka_acuan: [], dokumentasi: [] };
  currentEditKakIndex.value = 0;
  currentEditDokumentasiIndex.value = 0;

  formErrors.value.edit.kerangkaAcuan = '';
  formErrors.value.edit.dokumentasi = '';

  nextTick(() => {
    if (editFormRef.value) {
      editFormRef.value.resetValidation();
    }
  });
};

const saveNewItem = async () => {
  if (kerangkaAcuanFiles.value.length > 5 || dokumentasiFiles.value.length > 5) {
    if (kerangkaAcuanFiles.value.length > 5) {
      formErrors.value.add.kerangkaAcuan = 'Maksimal 5 file';
    }
    if (dokumentasiFiles.value.length > 5) {
      formErrors.value.add.dokumentasi = 'Maksimal 5 file';
    }
    await Swal.fire({
      title: 'Jumlah File Melebihi Batas',
      text: 'Maksimal 5 file untuk setiap tipe file',
      icon: 'error'
    });
    return;
  }

  const { valid } = await addFormRef.value.validate();

  const missingFields = [];
  if (!newItem.value.nama_kegiatan) missingFields.push('Nama Kegiatan');
  if (!newItem.value.jumlah_anggaran) missingFields.push('Jumlah Anggaran');
  if (kerangkaAcuanFiles.value.length === 0) missingFields.push('Kerangka Acuan');
  if (dokumentasiFiles.value.length === 0) missingFields.push('Dokumentasi');

  if (!valid || missingFields.length > 0) {
    let errorText = 'Mohon lengkapi semua field yang wajib diisi';
    if (missingFields.length > 0) {
      errorText += `: <strong>${missingFields.join(', ')}</strong>`;
    }

    await Swal.fire({
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

  loading.value = true;

  try {
    const formData = new FormData();
    formData.append('nama_kegiatan', newItem.value.nama_kegiatan);
    formData.append('jumlah_anggaran', newItem.value.jumlah_anggaran);

    kerangkaAcuanFiles.value.forEach((file, index) => {
      formData.append(`kerangka_acuan[${index}]`, file);
    });

    dokumentasiFiles.value.forEach((file, index) => {
      formData.append(`dokumentasi_kegiatan_tpps[${index}]`, file);
    });

    await apiClient.post('/report-tpps', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    await showSuccessAlert('Data <strong>laporan kegiatan</strong> berhasil ditambahkan');
    await fetchData();
    addDialog.value = false;
    resetAddForm();
  } catch (error) {
    let errorMessage = 'Terjadi kesalahan saat menyimpan data';
    if (error.response?.data?.errors) {
      errorMessage = Object.values(error.response.data.errors).flat().join('\n');
    }
    await showErrorAlert('Gagal!', errorMessage);
  } finally {
    loading.value = false;
  }
};

const originalEditedItem = ref(null);

const editItem = (item) => {
  originalEditedItem.value = JSON.parse(JSON.stringify(item));
  editedItem.value = JSON.parse(JSON.stringify(item));

  editKerangkaAcuanFiles.value = [];
  editDokumentasiFiles.value = [];
  tempKerangkaAcuanFile.value = [];
  tempDokumentasiFile.value = [];
  filesToDelete.value = { kerangka_acuan: [], dokumentasi: [] };
  currentEditKakIndex.value = 0;
  currentEditDokumentasiIndex.value = 0;

  editDialog.value = true;
};

const getEditKakFileName = (index) => {
  const orderedFiles = getOrderedKakFiles.value;
  if (index >= orderedFiles.length) return '';
  
  const file = orderedFiles[index];
  if (file instanceof File) {
    return file.name;
  }
  return file.split('/').pop();
};

const getEditDokumentasiFileName = (index) => {
  const orderedFiles = getOrderedDokumentasiFiles.value;
  if (index >= orderedFiles.length) return '';

  const file = orderedFiles[index];
  if (file instanceof File) {
    return file.name;
  }
  return file.split('/').pop();
};

const isEditDokumentasiImage = (index) => {
  const orderedFiles = getOrderedDokumentasiFiles.value;
  if (index >= orderedFiles.length) return false;

  const file = orderedFiles[index];
  if (file instanceof File) {
    return file.type.startsWith('image/');
  }
  return file.toLowerCase().endsWith('.jpg') ||
    file.toLowerCase().endsWith('.jpeg') ||
    file.toLowerCase().endsWith('.png');
};

const getEditDokumentasiPreviewUrl = (index) => {
  const orderedFiles = getOrderedDokumentasiFiles.value;
  if (index >= orderedFiles.length) return '';

  const file = orderedFiles[index];
  if (file instanceof File) {
    return URL.createObjectURL(file);
  }
  return `${apiClient.defaults.baseURL.replace('/api', '')}/${file}`;
};

const previewAddKakFile = (index) => {
  if (index < kerangkaAcuanFiles.value.length) {
    const file = kerangkaAcuanFiles.value[index];
    previewFiles.value = [{
      url: URL.createObjectURL(file),
      type: 'application/pdf'
    }];
    currentPreviewIndex.value = 0;
    previewDialog.value = true;
  }
};

const previewAddDokumentasiFile = (index) => {
  if (index < dokumentasiFiles.value.length) {
    const file = dokumentasiFiles.value[index];
    previewFiles.value = [{
      url: URL.createObjectURL(file),
      type: file.type.startsWith('image/') ? file.type : 'application/pdf'
    }];
    currentPreviewIndex.value = 0;
    previewDialog.value = true;
  }
};

const previewEditKakFile = (index) => {
  const orderedFiles = getOrderedKakFiles.value;
  if (index < orderedFiles.length) {
    const file = orderedFiles[index];
    if (file instanceof File) {
      previewFiles.value = [{
        url: URL.createObjectURL(file),
        type: 'application/pdf'
      }];
    } else {
      openFilePreview(file);
    }
    currentPreviewIndex.value = 0;
    previewDialog.value = true;
  }
};

const previewEditDokumentasiFile = (index) => {
  const orderedFiles = getOrderedDokumentasiFiles.value;
  if (index < orderedFiles.length) {
    const file = orderedFiles[index];
    if (file instanceof File) {
      previewFiles.value = [{
        url: URL.createObjectURL(file),
        type: file.type.startsWith('image/') ? file.type : 'application/pdf'
      }];
    } else {
      openFilePreview(file);
    }
    currentPreviewIndex.value = 0;
    previewDialog.value = true;
  }
};

const updateItem = async () => {
  const totalKak = (editedItem.value.kerangka_acuan?.length || 0) + editKerangkaAcuanFiles.value.length;
  const totalDok = (editedItem.value.dokumentasi_kegiatan_tpps?.length || 0) + editDokumentasiFiles.value.length;

  if (totalKak > 5 || totalDok > 5) {
    if (totalKak > 5) {
      formErrors.value.edit.kerangkaAcuan = 'Maksimal 5 file';
    }
    if (totalDok > 5) {
      formErrors.value.edit.dokumentasi = 'Maksimal 5 file';
    }
    await Swal.fire({
      title: 'Jumlah File Melebihi Batas',
      text: 'Maksimal 5 file untuk setiap tipe file',
      icon: 'error'
    });
    return;
  }

  try {
    formatAnggaran('edit');

    const { valid } = await editFormRef.value.validate();
    if (!valid) {
      await Swal.fire({
        title: 'Validasi Gagal',
        text: 'Mohon lengkapi semua field yang wajib diisi',
        icon: 'error'
      });
      return;
    }

    loading.value = true;

    const formData = new FormData();
    formData.append('nama_kegiatan', editedItem.value.nama_kegiatan);
    formData.append('jumlah_anggaran', editedItem.value.jumlah_anggaran);
    formData.append('_method', 'PUT');

    filesToDelete.value.kerangka_acuan.forEach(file => {
      formData.append('delete_kerangka_acuan[]', file);
    });
    filesToDelete.value.dokumentasi.forEach(file => {
      formData.append('delete_dokumentasi[]', file);
    });

    editKerangkaAcuanFiles.value.forEach(file => {
      formData.append('kerangka_acuan[]', file);
    });
    editDokumentasiFiles.value.forEach(file => {
      formData.append('dokumentasi_kegiatan_tpps[]', file);
    });

    const response = await apiClient.post(`/report-tpps/${editedItem.value.id_report_tpps}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    await showSuccessAlert('Data <strong>laporan kegiatan</strong> berhasil diperbarui');
    await fetchData();
    editDialog.value = false;
  } catch (error) {
    console.error('Error updating data:', error);
    let errorMessage = 'Terjadi kesalahan saat memperbarui data';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      errorMessage = Object.values(error.response.data.errors).flat().join('\n');
    }
    await showErrorAlert('Gagal!', errorMessage);
  } finally {
    loading.value = false;
  }
};

const deleteItem = async (item) => {
  const result = await showConfirmDialog(
    'Apakah Anda yakin?',
    `Anda akan menghapus laporan <strong>${item.nama_kegiatan}</strong>. Data yang dihapus tidak dapat dikembalikan!`
  );

  if (result.isConfirmed) {
    try {
      loading.value = true;
      await apiClient.delete(`/report-tpps/${item.id_report_tpps}`);
      await fetchData();
      await showSuccessAlert('Data <strong>laporan kegiatan</strong> berhasil dihapus');
    } catch (error) {
      console.error('Error deleting report:', error);
      await showErrorAlert('Gagal!', 'Terjadi kesalahan saat menghapus laporan');
    } finally {
      loading.value = false;
    }
  }
};

const showSuccessAlert = (message) => {
  return Swal.fire({
    html: message,
    icon: 'success',
    confirmButtonColor: '#5b8cff',
    background: '#f8fafc',
    customClass: { popup: 'rounded-lg shadow-xl', title: 'text-lg font-medium' },
    timer: 3000,
    showConfirmButton: false
  });
};

const showErrorAlert = (title, message) => {
  return Swal.fire({
    title: title,
    html: message,
    icon: 'error',
    confirmButtonColor: '#ff6b6b',
    background: '#f8fafc',
    customClass: { popup: 'rounded-lg shadow-xl', title: 'text-lg font-medium' }
  });
};

const showConfirmDialog = (title, html) => {
  return Swal.fire({
    title: title,
    html: html,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ff6b6b',
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    background: '#f8fafc',
    customClass: { popup: 'rounded-lg shadow-xl', title: 'text-lg font-medium' }
  });
};

const handleImageError = (event) => {
  event.target.src = '';
  event.target.parentNode.innerHTML = `
    <div class="text-center">
      <p class="text-body-2 mt-2">Tidak dapat menampilkan gambar</p>
    </div>
  `;
};

const exportData = async () => {
  try {
    loading.value = true;
  
    const itemsWithDocs = await Promise.all(
      sortedData.value.map(async (item) => {
        if (!item.dokumentasi_kegiatan_tpps) return item;
        
        const docs = Array.isArray(item.dokumentasi_kegiatan_tpps) ? 
          item.dokumentasi_kegiatan_tpps : [item.dokumentasi_kegiatan_tpps];
        
        const limitedDocs = docs.slice(0, 5); 
        
        const docImages = [];
        
        for (const doc of limitedDocs) {
            try {
            let cleanDocPath = doc;
            
            cleanDocPath = cleanDocPath.replace(/^\/?uploads\//, '');
            console.log(cleanDocPath);
            
            const fullUrl = `http://localhost:8000/api/dokumentasi/${cleanDocPath}`;
                    console.log('Fetching image from:', fullUrl);
            
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 5000);
            
            const response = await fetch(fullUrl, { 
              credentials: 'include'
            });
            clearTimeout(timeout);
            
            if (!response.ok) throw new Error('Gagal memuat gambar');
            
            const blob = await response.blob();
            
            if (blob.type.startsWith('image/')) {
              docImages.push({
                url: URL.createObjectURL(blob),
                type: blob.type,
                isImage: true,
                width: 0,
                height: 0
              });
            } else {
              docImages.push({
                url: URL.createObjectURL(blob),
                type: blob.type,
                isImage: false
              });
            }
          } catch (error) {
            console.error('Error memproses dokumentasi:', doc, error);
            docImages.push({
              url: null,
              type: 'error',
              isImage: false,
              error: true
            });
          }
        }
        
        return {
          ...item,
          docImages: docImages
        };
      })
    );

    const exportElement = document.createElement('div');
    exportElement.style.width = '210mm';
    exportElement.style.padding = '15mm 20mm';
    exportElement.style.fontFamily = 'Arial, sans-serif';
    exportElement.style.position = 'absolute';
    exportElement.style.left = '-9999px';
    exportElement.style.top = '0';
    exportElement.style.background = 'white';
    
    document.body.appendChild(exportElement);

    const currentDate = new Date();
    const formattedDate = currentDate.toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    const formattedTime = currentDate.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit'
    });

    exportElement.innerHTML = `
      <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #16a085; padding-bottom: 10px;">
        <h2 style="margin: 0 0 5px 0; color: #16a085;">LAPORAN KEGIATAN TPPS</h2>
        <p style="margin: 0; font-size: 14px; color: #555;">Diunduh pada: ${formattedDate} ${formattedTime} WIB</p>
      </div>
      
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px;">
        <thead>
          <tr style="background-color: #16a085; color: white;">
            <th style="border: 1px solid #ddd; padding: 8px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #ddd; padding: 8px; width: 25%; text-align: center;">Nama Kegiatan</th>
            <th style="border: 1px solid #ddd; padding: 8px; width: 15%; text-align: center;">Jumlah Anggaran</th>
            <th style="border: 1px solid #ddd; padding: 8px; width: 55%; text-align: center;">Dokumentasi Kegiatan</th>
          </tr>
        </thead>
        <tbody>
          ${itemsWithDocs.map((item, index) => {
            let docHtml = '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">'; // Ubah justify-content ke center
            
            if (item.docImages && item.docImages.length > 0) {
              item.docImages.forEach((doc, docIndex) => {
                if (doc.isImage) {
                  docHtml += `
                    <div style="width: 200px; height: 200px; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                      <img src="${doc.url}" 
                           style="max-width: 100%; max-height: 100%; object-fit: cover;" 
                           alt="Dokumentasi ${docIndex + 1}" />
                    </div>
                  `;
                } else if (doc.error) {
                  docHtml += `
                    <div style="width: 80px; height: 80px; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                      <span style="font-size: 10px; color: #999; text-align: center;">Error<br>Loading</span>
                    </div>
                  `;
                } else {
                  docHtml += `
                    <div style="width: 80px; height: 80px; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                      <div style="text-align: center;">
                        <i class="bx bx-file" style="font-size: 24px; color: #666;"></i>
                        <span style="font-size: 10px; color: #666; display: block;">PDF</span>
                      </div>
                    </div>
                  `;
                }
              });
            } else {
              docHtml += '<span style="color: #999; font-size: 11px;">Tidak ada dokumentasi</span>';
            }
            
            docHtml += '</div>';
            
            return `
              <tr>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">${index + 1}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">${item.nama_kegiatan || '-'}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">${formatCurrency(item.jumlah_anggaran)}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">${docHtml}</td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    `;

    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4'
    });

    const options = {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      logging: false,
      scrollX: 0,
      scrollY: 0,
      windowWidth: exportElement.scrollWidth,
      windowHeight: exportElement.scrollHeight
    };

    const canvas = await html2canvas(exportElement, options);
    
    const imgData = canvas.toDataURL('image/png');
    const imgWidth = 190; 
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    
    pdf.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
    
    document.body.removeChild(exportElement);
    
    itemsWithDocs.forEach(item => {
      if (item.docImages) {
        item.docImages.forEach(img => {
          if (img.url) URL.revokeObjectURL(img.url);
        });
      }
    });

    pdf.save(`Laporan_Kegiatan_TPPS_${currentDate.toISOString().split('T')[0]}.pdf`);
    
  } catch (error) {
    console.error('Gagal mengekspor PDF:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Terjadi kesalahan saat membuat laporan PDF: ' + error.message
    });
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.search-box {
  max-width: 400px;
  border-radius: 8px;
}

.table-container {
  overflow-x: auto;
  padding: 16px;
  border-radius: 8px;
  position: relative;
  max-width: 100%;
}

.custom-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-top: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  table-layout: fixed;
}

.custom-table th,
.custom-table td {
  border-right: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding: 12px;
  text-align: center;
  word-wrap: break-word;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
}

.custom-table th {
  min-width: 80px;
  max-width: 150px;
  background-color: #f5f5f5;
  font-weight: 600;
}

.button-group {
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  overflow: hidden;
}

.button-group .square-button:first-child {
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
}

.button-group .square-button:last-child {
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}

.square-button {
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  border-radius: 0;
}

.preview-link {
  text-decoration: none;
}

.preview-container {
  width: 100%;
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.preview-image-full {
  max-width: 100%;
  max-height: 70vh;
  object-fit: contain;
}

.preview-thumbnail {
  max-width: 100%;
  max-height: 60px;
  object-fit: contain;
  border-radius: 4px;
}

.file-preview-box {
  position: relative;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 12px;
  text-align: center;
  background: #f5f5f5;
  height: 100%;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 0;
}

.file-preview-box:hover {
  border-color: #2196F3;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.file-preview-name {
  font-size: 0.8rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin: 0 8px;
  flex-grow: 1;
}

.file-carousel {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.preview-carousel {
  display: flex;
  align-items: center;
  gap: 16px;
}

.preview-content {
  flex-grow: 1;
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.error-message {
  color: #ff5252;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

.v-messages.error--text {
  color: #ff5252 !important;
  caret-color: #ff5252 !important;
}

.v-input--error .v-input__control>.v-input__slot {
  border-color: #ff5252 !important;
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

.v-theme--dark .file-preview-box {
  background-color: #2d2d2d;
  border-color: #444;
}

.v-theme--dark .file-preview-box:hover {
  border-color: #666;
}

@media (max-width: 600px) {
  .file-preview-name {
    max-width: 120px;
  }

  .file-carousel {
    flex-direction: column;
  }

  .file-preview-box {
    flex-direction: column;
    text-align: center;
  }

  .preview-carousel {
    flex-direction: column;
  }
}
</style>