<template>
  <VRow>
    <VCol cols="12">
      <VCard title="DATA KELUARGA BERISIKO" class="rounded-lg">
        <VRow class="px-4 py-2">
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
            <VBtn v-if="isAllowedToAdd" color="info" class="me-2 mb-2 rounded-lg" @click="handleTemplate">
              <VIcon class="me-2">bx-download</VIcon>Template
            </VBtn>
            <VBtn v-if="isAllowedToAdd" color="success" class="me-2 mb-2 rounded-lg" @click="handleImport">Tambah</VBtn>
            <VBtn color="warning" @click="exportData" class="mb-2 rounded-lg">Export</VBtn>
          </VCol>
        </VRow>

        <VRow class="px-4 pb-3">
          <VCol cols="12" class="d-flex flex-wrap gap-3">
            <VMenu offset-y close-on-content-click>
              <template v-slot:activator="{ props }">
                <VBtn v-bind="props" color="primary" variant="outlined" size="small" class="filter-btn"
                  style="position: relative; min-width: 150px; display: flex; justify-content: space-between;">
                  <div class="d-flex align-center">
                    <VIcon size="18" class="me-1">bx-calendar</VIcon>
                    <span v-if="!selectedYear">Tahun</span>
                    <span v-else class="text-truncate" style="max-width: 80px;">
                      {{ selectedYear.year || selectedYear }}
                    </span>
                  </div>

                  <VIcon v-if="selectedYear" size="16" color="error"
                    style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);"
                    @click.stop="resetYearFilter">
                    bx-x
                  </VIcon>
                </VBtn>
              </template>
              <VList>
                <VListItem v-for="year in availableYears" :key="year.year || year" @click="selectYear(year)">
                  <VListItemTitle>{{ year.year || year }}</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>

            <VMenu v-model="desaMenuOpen" :close-on-content-click="false" ref="desaMenuRef">
              <template v-slot:activator="{ props }">
                <VBtn v-bind="props" color="primary" variant="outlined" size="small" class="filter-btn"
                  style="position: relative; min-width: 150px; display: flex; justify-content: space-between;">
                  <div class="d-flex align-center">
                    <VIcon size="18" class="me-1">bx-map</VIcon>
                    <span class="text-truncate" style="max-width: 80px;">
                      {{ getDesaFilterLabel() }}
                    </span>
                  </div>

                  <VIcon v-if="selectedDesa || isUsingDefaultDesaFilter" size="16" color="error"
                    style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);"
                    @click.stop="resetDesaFilter">
                    bx-x
                  </VIcon>
                </VBtn>
              </template>

              <VList width="300">
                <VListItem class="pa-2">
                  <VTextField v-model="desaSearch" placeholder="Cari desa/kelurahan..." density="compact" hide-details
                    clearable prepend-inner-icon="bx-search" @click.stop @input="filterDesaList" />
                </VListItem>

                <VDivider />

                <div style="max-height: 300px; overflow-y: auto;">
                  <VListItem v-for="desa in computedFilteredDesaList" :key="desa.id_desa"
                    @click="selectDesa(desa.id_desa)" :active="desa.id_desa === selectedDesa">
                    <VListItemTitle>
                      <div class="d-flex flex-column">
                        <span class="font-weight-medium">{{ desa.nama_desa }}</span>
                        <span class="text-caption text-secondary">
                          Kec. {{ desa.kecamatan }}
                        </span>
                      </div>
                    </VListItemTitle>
                  </VListItem>
                </div>

                <VDivider />
                <VListItem class="d-flex justify-end pa-2 gap-2">
                  <VBtn size="small" variant="text" @click="resetDesaFilter" density="comfortable">
                    Reset
                  </VBtn>
                  <VBtn size="small" color="primary" @click="closeDesaMenu" density="comfortable">
                    Tutup
                  </VBtn>
                </VListItem>
              </VList>
            </VMenu>
          </VCol>
        </VRow>

        <div class="table-container">
          <VTable class="custom-table elevation-1">
            <thead>
              <tr>
                <th rowspan="4" class="text-center">No</th>
                <th v-if="isAllowedToAdd" rowspan="4" class="text-center">No. Kartu Keluarga</th>
                <th v-if="isAllowedToAdd" rowspan="4" class="text-center">NIK Kepala Keluarga</th>
                <th rowspan="4" class="text-center">Nama Kepala Keluarga</th>
                <th v-if="isAllowedToAdd" rowspan="4" class="text-center">NIK Istri</th>
                <th rowspan="4" class="text-center">Nama Istri</th>
                <th rowspan="4" class="text-center">Desa</th>
                <th rowspan="4" class="text-center">Kecamatan</th>
                <th colspan="4" class="text-center">Sasaran</th>
                <th colspan="8" class="text-center">Penapisan</th>
                <th rowspan="4" class="text-center">Jenis Pendampingan yang Diterima</th>
                <th rowspan="4" class="text-center">Aksi</th>
              </tr>
              <tr>
                <th colspan="2" class="text-center">Punya Anak</th>
                <th rowspan="3" class="text-center">PUS</th>
                <th rowspan="3" class="text-center">PUS Hamil</th>
                <th colspan="2" class="text-center">Fasilitas Lingkungan Tidak Sehat</th>
                <th colspan="6" class="text-center">PUS</th>
              </tr>
              <tr>
                <th rowspan="2" class="text-center">0-23 Bulan</th>
                <th rowspan="2" class="text-center">24-59 Bulan</th>
                <th rowspan="2" class="text-center">Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak</th>
                <th rowspan="2" class="text-center">Keluarga Tidak Mempunyai Jamban yang Layak</th>
                <th colspan="5" class="text-center">PUS 4 Terlalu</th>
                <th rowspan="2" class="text-center">Bukan Peserta KB Modern</th>
              </tr>
              <tr>
                <th class="text-center">Terlalu Muda (Umur Istri < 20 Tahun)</th>
                <th class="text-center">Terlalu Tua (Umur Istri 35 sd 40 Tahun)</th>
                <th class="text-center">Terlalu Dekat (< 2 Tahun)</th>
                <th class="text-center">Terlalu Banyak (≥ 3 Anak)</th>
                <th class="text-center">PUS 4 Terlalu</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in paginatedData" :key="item.id_keluarga || index">
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td v-if="isAllowedToAdd">{{ item.no_kartu_keluarga }}</td>
                <td v-if="isAllowedToAdd">{{ item.nik_kepala_keluarga }}</td>
                <td>{{ item.nama_kepala_keluarga }}</td>
                <td v-if="isAllowedToAdd">{{ item.nik_istri }}</td>
                <td>{{ item.nama_istri }}</td>
                <td>{{ item.desa?.nama_desa || 'N/A' }}</td>
                <td>{{ item.desa?.kecamatan?.nama_kecamatan || 'N/A' }}</td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.punya_anak_0_23_bulan)" size="20">
                    {{ getIcon(item.determinan_k_b?.punya_anak_0_23_bulan) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.punya_anak_24_59_bulan)" size="20">
                    {{ getIcon(item.determinan_k_b?.punya_anak_24_59_bulan) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus_hamil)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus_hamil) }}
                  </VIcon>
                </td>

                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.sumber_air_minum_tidak_layak)" size="20">
                    {{ getIcon(item.determinan_k_b?.sumber_air_minum_tidak_layak) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.jamban_tidak_layak)" size="20">
                    {{ getIcon(item.determinan_k_b?.jamban_tidak_layak) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus_4_terlalu_muda)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus_4_terlalu_muda) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus_4_terlalu_tua)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus_4_terlalu_tua) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus_4_terlalu_dekat)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus_4_terlalu_dekat) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.pus_4_terlalu_banyak)" size="20">
                    {{ getIcon(item.determinan_k_b?.pus_4_terlalu_banyak) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(calculatePus4Terlalu(item))" size="20">
                    {{ getIcon(calculatePus4Terlalu(item)) }}
                  </VIcon>
                </td>
                <td class="text-center">
                  <VIcon :color="getIconColor(item.determinan_k_b?.bukan_peserta_kb_modern)" size="20">
                    {{ getIcon(item.determinan_k_b?.bukan_peserta_kb_modern) }}
                  </VIcon>
                </td>

                <td>{{ getJenisPendampingan(item.jenis_pendampingan_diterima) || 'N/A' }}</td>
                <td>
                  <div class="button-group">
                    <VBtn icon color="primary" @click="viewDetail(item)" class="square-button">
                      <i class="bx bxs-info-circle"></i>
                    </VBtn>
                    <VBtn v-if="isAllowedToAdd" icon color="error" @click="deleteItem(item)" class="square-button">
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
              class="items-per-page-select" style="width: 80px;" />
          </div>

          <VPagination v-model="currentPage" :length="totalPages" :total-visible="7" rounded="lg" />

          <div class="d-flex align-center">
            <span class="text-body-2">
              {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, filteredData.length) }}
              of {{ filteredData.length }}
            </span>
          </div>
        </div>
      </VCard>
    </VCol>
  </VRow>

  <ImportDialog v-model:visible="importDialog" @file-imported="handleFileImported" @refresh-data="fetchData" />

  <DetailKB v-model:visible="showDetail" :item="itemToView" @edit-item="handleEditFromDetail" />

  <EditInputManual v-model:visible="showEditForm" :item="itemToEdit" @save="handleSaveEdit"
    @cancel="handleCancelEdit" />

  <DownloadDialog v-model:visible="isDownloadDialogVisible" />
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { VRow, VCol, VCard, VTable, VBtn, VSelect, VPagination, VTextField, VIcon } from 'vuetify/components';
import apiClient from '@/services/api';
import ImportDialog from './forms/tambah-data.vue';
import DetailKB from './detail-kb.vue';
import EditInputManual from './forms/edit-input-manual.vue';
import DownloadDialog from '../../cakupan-program-intervensi/master-data/forms/download-template.vue';
import ExcelJS from 'exceljs';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();

const searchQuery = ref('');
const berisikoData = ref([]);
const importDialog = ref(false);
const showDetail = ref(false);
const showEditForm = ref(false);
const itemToView = ref(null);
const itemToEdit = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const isDownloadDialogVisible = ref(false);
const currentSort = ref('latest');
const selectedYear = ref(null);
const selectedDesa = ref(null);
const desaSearch = ref('');
const availableYears = ref([]);
const availableDesa = ref([]);
const desaMenuOpen = ref(false);
const desaMenuRef = ref(null);

const currentUser = computed(() => authStore.user);
const isAllowedToAdd = computed(() => {
  return ['Dinas KB'].includes(currentUser.value?.role);
});

const sortOptions = ref([
  { title: 'A-Z (Nama Kepala Keluarga)', value: 'name_asc' },
  { title: 'Z-A (Nama Kepala Keluarga)', value: 'name_desc' },
  { title: 'Terbaru', value: 'latest' },
  { title: 'Terlama', value: 'oldest' }
]);

const isUsingDefaultDesaFilter = computed(() => {
  try {
    const userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    return !selectedDesa.value && !!userData?.id_desa;
  } catch (e) {
    console.error('Error checking default desa filter:', e);
    return false;
  }
});

const fetchData = async () => {
  try {
    const params = {
      search: searchQuery.value,
      sort: currentSort.value,
      year: selectedYear.value?.year || selectedYear.value,
      id_desa: selectedDesa.value,
    };

    const response = await apiClient.get('/keluarga-berisiko', { params });
    berisikoData.value = response.data.data;
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

const applySort = (sortValue) => {
  currentSort.value = sortValue;
  fetchData();
};

const selectYear = (year) => {
  selectedYear.value = year;
  fetchData();
};

const resetYearFilter = () => {
  selectedYear.value = null;
  fetchData();
};

const filterDesaList = () => {
  if (!desaSearch.value) {
    filteredDesaList.value = [...availableDesa.value];
    return;
  }

  const searchTerm = desaSearch.value.toLowerCase();
  filteredDesaList.value = availableDesa.value.filter(desa =>
    desa.nama_desa.toLowerCase().includes(searchTerm) ||
    (desa.kecamatan && desa.kecamatan.toLowerCase().includes(searchTerm))
  );
};

const filteredDesaList = ref([]);

const computedFilteredDesaList = computed(() => {
  if (!desaSearch.value || desaSearch.value.length < 2) {
    return [...availableDesa.value].sort((a, b) =>
      a.kecamatan.localeCompare(b.kecamatan) ||
      a.nama_desa.localeCompare(b.nama_desa)
    );
  }

  const searchTerm = desaSearch.value.toLowerCase();
  return availableDesa.value.filter(desa => {
    const searchString = `${desa.nama_desa} ${desa.kecamatan}`.toLowerCase();
    return searchString.includes(searchTerm);
  }).sort((a, b) =>
    a.kecamatan.localeCompare(b.kecamatan) ||
    a.nama_desa.localeCompare(b.nama_desa)
  );
});

const closeDesaMenu = () => {
  desaMenuOpen.value = false;
};

const selectDesa = (idDesa) => {
  selectedDesa.value = idDesa;
  fetchData();
  closeDesaMenu();
};

const resetDesaFilter = () => {
  selectedDesa.value = null;
  desaSearch.value = '';
  filteredDesaList.value = [...availableDesa.value];
  fetchData();
};

const getDesaName = (id) => {
  if (!id) return 'Desa';
  const desa = availableDesa.value.find(d => d.id_desa === id);
  return desa ? `${desa.nama_desa}` : 'Desa';
};

const getDesaFilterLabel = () => {
  try {
    const userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    const defaultIdDesa = userData?.id_desa || null;

    if (selectedDesa.value) {
      return getDesaName(selectedDesa.value);
    }

    if (defaultIdDesa && !selectedDesa.value) {
      return `Default: ${getDesaName(defaultIdDesa)}`;
    }
  } catch (e) {
    console.error('Error getting desa filter label:', e);
  }
  return 'Desa';
};

const getIcon = (value) => {
  const numValue = typeof value === 'string' ? parseInt(value) : value;

  if (numValue === 1) return 'bx-check';
  if (numValue === 2) return 'bx-x';
  if (numValue === 3) return 'bx-minus';
  return 'bx-minus';
};

const getIconColor = (value) => {
  const numValue = typeof value === 'string' ? parseInt(value) : value;

  if (numValue === 1) return 'success';
  if (numValue === 2) return 'error';
  if (numValue === 3) return 'grey';
  return 'warning';
};

const calculatePus4Terlalu = (item) => {
    if (!item?.determinan_k_b) return null;
    
    if (item.determinan_k_b.pus_4_terlalu !== undefined && item.determinan_k_b.pus_4_terlalu !== null) {
        return item.determinan_k_b.pus_4_terlalu;
    }
    
    const terlalu_muda = item.determinan_k_b.pus_4_terlalu_muda;
    const terlalu_tua = item.determinan_k_b.pus_4_terlalu_tua;
    const terlalu_dekat = item.determinan_k_b.pus_4_terlalu_dekat;
    const terlalu_banyak = item.determinan_k_b.pus_4_terlalu_banyak;

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].includes(1)) {
        return 1;
    }
    
    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].every(val => val === 2)) {
        return 2;
    }
    
    return 3;
};

const filteredData = computed(() => {
  let result = berisikoData.value;

  if (searchQuery.value) {
    const searchTerm = searchQuery.value.toLowerCase();
    result = result.filter(item =>
      item.nama_kepala_keluarga?.toLowerCase().includes(searchTerm) ||
      item.no_kartu_keluarga?.includes(searchQuery.value) ||
      item.nik_kepala_keluarga?.includes(searchQuery.value)
    );
  }

  return result;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredData.value.length / itemsPerPage.value);
});

const getJenisPendampingan = (value) => {
  if (!value || (Array.isArray(value) && value.length === 0)) {
    return 'N/A';
  }

  let values = [];

  if (Array.isArray(value)) {
    values = [...value].sort((a, b) => a - b);
  } else if (typeof value === 'string') {
    try {
      values = JSON.parse(value).sort((a, b) => a - b);
    } catch (e) {
      values = value.split(',')
        .map(v => parseInt(v.trim()))
        .filter(v => !isNaN(v))
        .sort((a, b) => a - b);
    }
  }

  return values.join(', ');
};

const handleTemplate = () => {
  isDownloadDialogVisible.value = true;
};

const handleImport = () => {
  importDialog.value = true;
};

const exportData = async () => {
  try {
    const loadingSwal = window.Swal.fire({
      title: 'Menyiapkan data...',
      allowOutsideClick: false,
      didOpen: () => {
        window.Swal.showLoading();
      }
    });

    const params = {
      search: searchQuery.value,
      sort: currentSort.value,
      year: selectedYear.value?.year || selectedYear.value,
      id_desa: selectedDesa.value,
      all: true
    };

    const response = await apiClient.get('/keluarga-berisiko', { params });
    const allData = response.data.data;

    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Data Keluarga Berisiko');

    const titleRow = worksheet.addRow(['DATA KELUARGA BERISIKO']);
    titleRow.font = { bold: true, size: 16 };
    titleRow.alignment = { horizontal: 'center' };
    worksheet.mergeCells('A1:U1');

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
    const dateRow = worksheet.addRow([`Diunduh pada: ${formattedDate} ${formattedTime} WIB`]);
    dateRow.font = { italic: true };
    dateRow.alignment = { horizontal: 'center' };
    worksheet.mergeCells('A2:U2');

    worksheet.addRow([]);

    const headerStyle = {
      font: { bold: true, color: { argb: 'FFFFFFFF' } },
      fill: {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF3A87AD' }
      },
      alignment: {
        vertical: 'middle',
        horizontal: 'center',
        wrapText: true
      },
      border: {
        top: { style: 'thin', color: { argb: 'FF000000' } },
        left: { style: 'thin', color: { argb: 'FF000000' } },
        bottom: { style: 'thin', color: { argb: 'FF000000' } },
        right: { style: 'thin', color: { argb: 'FF000000' } }
      }
    };

    const cellStyle = {
      alignment: {
        vertical: 'middle',
        horizontal: 'center',
        wrapText: true
      },
      border: {
        top: { style: 'thin', color: { argb: 'FF000000' } },
        left: { style: 'thin', color: { argb: 'FF000000' } },
        bottom: { style: 'thin', color: { argb: 'FF000000' } },
        right: { style: 'thin', color: { argb: 'FF000000' } }
      }
    };

    worksheet.mergeCells('A4:A8'); // No
    worksheet.mergeCells('B4:B8'); // No. Kartu Keluarga
    worksheet.mergeCells('C4:C8'); // NIK Kepala Keluarga
    worksheet.mergeCells('D4:D8'); // Nama Kepala Keluarga
    worksheet.mergeCells('E4:E8'); // NIK Istri
    worksheet.mergeCells('F4:F8'); // Nama Istri
    worksheet.mergeCells('G4:G8'); // Desa
    worksheet.mergeCells('H4:H8'); // Kecamatan
    worksheet.mergeCells('I4:L4'); // Sasaran
    worksheet.mergeCells('I5:J5'); // Punya Anak
    worksheet.mergeCells('K5:K8'); // PUS
    worksheet.mergeCells('L5:L8'); // PUS Hamil
    worksheet.mergeCells('M4:T4'); // Penapisan
    worksheet.mergeCells('M5:N5'); // Fasilitas Lingkungan Tidak Sehat
    worksheet.mergeCells('O5:S5'); // PUS 4 Terlalu
    worksheet.mergeCells('T5:T8'); // Bukan Peserta KB Modern
    worksheet.mergeCells('U4:U8'); // Jenis Pendampingan

    worksheet.mergeCells('I6:I8'); // 0-24 Bulan
    worksheet.mergeCells('J6:J8'); // 24-59 Bulan
    worksheet.mergeCells('M6:M8'); // Sumber Air Tidak Layak
    worksheet.mergeCells('N6:N8'); // Jamban Tidak Layak
    worksheet.mergeCells('O6:O8'); // Terlalu Muda
    worksheet.mergeCells('P6:P8'); // Terlalu Tua
    worksheet.mergeCells('Q6:Q8'); // Terlalu Dekat
    worksheet.mergeCells('R6:R8'); // Terlalu Banyak
    worksheet.mergeCells('S6:S8'); // PUS 4 Terlalu

    worksheet.getCell('A4').value = 'No';
    worksheet.getCell('B4').value = 'No. Kartu Keluarga';
    worksheet.getCell('C4').value = 'NIK Kepala Keluarga';
    worksheet.getCell('D4').value = 'Nama Kepala Keluarga';
    worksheet.getCell('E4').value = 'NIK Istri';
    worksheet.getCell('F4').value = 'Nama Istri';
    worksheet.getCell('G4').value = 'Desa';
    worksheet.getCell('H4').value = 'Kecamatan';
    worksheet.getCell('I4').value = 'Sasaran';
    worksheet.getCell('I5').value = 'Punya Anak';
    worksheet.getCell('I6').value = '0-23 Bulan';
    worksheet.getCell('J6').value = '24-59 Bulan';
    worksheet.getCell('K5').value = 'PUS';
    worksheet.getCell('L5').value = 'PUS Hamil';
    worksheet.getCell('M4').value = 'Penapisan';
    worksheet.getCell('M5').value = 'Fasilitas Lingkungan Tidak Sehat';
    worksheet.getCell('M6').value = 'Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak';
    worksheet.getCell('N6').value = 'Keluarga Tidak Mempunyai Jamban yang Layak';
    worksheet.getCell('O5').value = 'PUS 4 Terlalu';
    worksheet.getCell('O6').value = 'Terlalu Muda (Umur Istri < 20 Tahun)';
    worksheet.getCell('P6').value = 'Terlalu Tua (Umur Istri 35 sd 40 Tahun)';
    worksheet.getCell('Q6').value = 'Terlalu Dekat (< 2 Tahun)';
    worksheet.getCell('R6').value = 'Terlalu Banyak (≥ 3 Anak)';
    worksheet.getCell('S6').value = 'PUS 4 Terlalu';
    worksheet.getCell('T5').value = 'Bukan Peserta KB Modern';
    worksheet.getCell('U4').value = 'Jenis Pendampingan yang Diterima';

    for (let i = 4; i <= 8; i++) {
      for (let j = 1; j <= 22; j++) {
        const cell = worksheet.getRow(i).getCell(j);
        if (cell.value) {
          cell.style = headerStyle;
        }
      }
    }

    allData.forEach((item, index) => {
      const row = worksheet.addRow([
        index + 1,
        item.no_kartu_keluarga || '-',
        item.nik_kepala_keluarga || '-',
        item.nama_kepala_keluarga || '-',
        item.nik_istri || '-',
        item.nama_istri || '-',
        item.desa?.nama_desa || '-',
        item.desa?.kecamatan?.nama_kecamatan || '-',
        getStatusText(item.determinan_k_b?.punya_anak_0_23_bulan),
        getStatusText(item.determinan_k_b?.punya_anak_24_59_bulan),
        getStatusText(item.determinan_k_b?.pus),
        getStatusText(item.determinan_k_b?.pus_hamil),
        getStatusText(item.determinan_k_b?.sumber_air_minum_tidak_layak),
        getStatusText(item.determinan_k_b?.jamban_tidak_layak),
        getStatusText(item.determinan_k_b?.pus_4_terlalu_muda),
        getStatusText(item.determinan_k_b?.pus_4_terlalu_tua),
        getStatusText(item.determinan_k_b?.pus_4_terlalu_dekat),
        getStatusText(item.determinan_k_b?.pus_4_terlalu_banyak),
        getStatusText(calculatePus4Terlalu(item)),
        getStatusText(item.determinan_k_b?.bukan_peserta_kb_modern),
        getJenisPendampingan(item.jenis_pendampingan_diterima) || '-'
      ]);

      row.eachCell({ includeEmpty: true }, (cell) => {
        cell.style = cellStyle;
      });
    });

    worksheet.autoFilter = {
      from: 'A4',
      to: `U${worksheet.rowCount}`
    };

    worksheet.columns = [
      { width: 5 },  // No
      { width: 20 }, // No. Kartu Keluarga
      { width: 20 }, // NIK Kepala Keluarga
      { width: 25 }, // Nama Kepala Keluarga
      { width: 20 }, // NIK Istri
      { width: 25 }, // Nama Istri
      { width: 20 }, // Desa
      { width: 20 }, // Kecamatan
      { width: 10 }, // 0-23 Bulan
      { width: 10 }, // 24-59 Bulan
      { width: 10 },  // PUS
      { width: 10 }, // PUS Hamil
      { width: 25 }, // Sumber Air Tidak Layak
      { width: 25 }, // Jamban Tidak Layak
      { width: 15 }, // Terlalu Muda
      { width: 15 }, // Terlalu Tua
      { width: 15 }, // Terlalu Dekat
      { width: 15 }, // Terlalu Banyak
      { width: 15 }, // PUS 4 Terlalu
      { width: 15 }, // Bukan Peserta KB Modern
      { width: 25 }  // Jenis Pendampingan
    ];

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `Data_Keluarga_Berisiko_${currentDate.toISOString().split('T')[0]}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    loadingSwal.close();

  } catch (error) {
    console.error('Error exporting data:', error);
    window.Swal.fire({
      title: 'Gagal!',
      text: 'Terjadi kesalahan saat mengekspor data',
      icon: 'error',
      confirmButtonColor: '#ff6b6b'
    });
  }
};

const getStatusText = (value) => {
  const numValue = typeof value === 'string' ? parseInt(value) : value;
  if (numValue === 1) return 'V';
  if (numValue === 2) return 'X';
  if (numValue === 3) return '-';
  return '';
};

const viewDetail = (item) => {
  itemToView.value = { ...item };
  showDetail.value = true;
};

const deleteItem = async (item) => {
  const result = await window.Swal.fire({
    title: 'Apakah Anda yakin?',
    html: `Anda akan menghapus data <strong>${item.nama_kepala_keluarga}</strong>. Data yang dihapus tidak dapat dikembalikan!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ff6b6b',
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    background: '#f8fafc',
    customClass: {
      popup: 'rounded-lg shadow-xl',
      title: 'text-lg font-medium'
    }
  });

  if (result.isConfirmed) {
    try {
      await apiClient.delete(`/keluarga-berisiko/${item.id_keluarga}`);
      await fetchData();
      await window.Swal.fire({
        html: 'Data <strong>keluarga berisiko</strong> berhasil dihapus',
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
    } catch (error) {
      window.Swal.fire({
        title: 'Gagal!',
        html: 'Terjadi kesalahan saat menghapus data',
        icon: 'error',
        confirmButtonColor: '#ff6b6b',
        background: '#f8fafc',
        customClass: {
          popup: 'rounded-lg shadow-xl',
          title: 'text-lg font-medium'
        }
      });
    }
  }
};

const handleFileImported = () => {
  importDialog.value = false;
  fetchData();
};

const handleEditFromDetail = (item) => {
  itemToEdit.value = item;
  showDetail.value = false;
  showEditForm.value = true;
};

const handleSaveEdit = () => {
  showEditForm.value = false;
  fetchData();
  showDetail.value = true;
};

const handleCancelEdit = () => {
  showEditForm.value = false;
  showDetail.value = true;
};

watch(searchQuery, (newVal) => {
  if (!newVal) {
    fetchData();
  }
});

onMounted(async () => {
  await fetchData();
  try {
    const response = await apiClient.get('/keluarga-berisiko');
    availableYears.value = response.data.filters.available_years.map(y => ({ year: y }));

    const desaResponse = await apiClient.get('/desa');
    availableDesa.value = desaResponse.data.data.map(d => ({
      id_desa: d.id,
      nama_desa: d.desa || d.nama_desa,
      kecamatan: d.kecamatan || (d.puskesmas?.kecamatan?.nama_kecamatan ?? 'N/A')
    })).sort((a, b) =>
      a.kecamatan.localeCompare(b.kecamatan) ||
      a.nama_desa.localeCompare(b.nama_desa)
    );

    filteredDesaList.value = [...availableDesa.value];

    const userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    if (userData?.id_desa) {
      selectedDesa.value = userData.id_desa;
    }
  } catch (error) {
    console.error('Error fetching filter options:', error);
  }
});
</script>

<style scoped>
@media (max-width: 960px) {
  .filter-btn {
    margin-right: 8px;
  }
}

.search-box {
  width: 100%;
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

.fixed-header {
  position: sticky;
  top: 0;
  background: white;
  z-index: 3;
  border-bottom: 2px solid #ddd;
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
</style>