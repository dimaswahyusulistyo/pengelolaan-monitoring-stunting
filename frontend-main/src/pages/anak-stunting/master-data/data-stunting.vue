<template>
    <VRow>
        <VCol cols="12">
            <VCard title="DATA ANAK STUNTING" class="rounded-lg">
              <VRow class="px-4 py-3">
                <VCol cols="12" sm="12" md="6" class="d-flex align-center flex-wrap gap-2 mb-3 mb-md-0">
                  <VMenu offset-y>
                    <template v-slot:activator="{ props }">
                      <VBtn
                        v-bind="props"
                        color="primary"
                        class="rounded-lg d-flex justify-center align-center"
                        style="min-width: 40px; min-height: 40px;"
                      >
                        <VIcon>bx-filter</VIcon>
                      </VBtn>
                    </template>
                    <VList>
                      <VListItem
                        v-for="(item, index) in sortOptions"
                        :key="index"
                        :value="item.value"
                        @click="applySort(item.value)"
                      >
                        <VListItemTitle>{{ item.title }}</VListItemTitle>
                      </VListItem>
                    </VList>
                  </VMenu>

                  <VTextField
                    v-model="searchQuery"
                    prepend-inner-icon="bx-search"
                    placeholder="Search"
                    persistent-placeholder
                    density="comfortable"
                    class="flex-grow-1"
                    hide-details
                  ></VTextField>
                </VCol>
                
                <VCol cols="12" sm="12" md="6" class="d-flex justify-start justify-md-end flex-wrap gap-2">
                  <VBtn v-if="isAllowedToAdd" color="info" class="rounded-lg" @click="handleDownload">
                    <VIcon class="me-2">bx-download</VIcon>Template
                  </VBtn>
                  <VBtn v-if="isAllowedToAdd" color="success" class="rounded-lg" @click="handleImport">Tambah</VBtn>
                  <VBtn color="warning" @click="exportData" class="rounded-lg">Export</VBtn>
                </VCol>
              </VRow>

              <VRow class="px-4 pb-3">
                <VCol cols="12">
                  <div class="d-flex flex-wrap gap-3">
                    <VMenu offset-y close-on-content-click>
                      <template v-slot:activator="{ props }">
                        <VBtn
                          v-bind="props"
                          color="primary"
                          variant="outlined"
                          size="small"
                          class="filter-btn"
                          style="position: relative; min-width: 100px; display: flex; justify-content: space-between;"
                        >
                          <div class="d-flex align-center">
                            <VIcon size="18" class="me-1">bx-calendar</VIcon>
                            <span v-if="!selectedYear">Tahun</span>
                            <span v-else class="text-truncate" style="max-width: 80px;">
                              {{ selectedYear.year || selectedYear }}
                            </span>
                          </div>
                          
                          <VIcon 
                            v-if="selectedYear"
                            size="16"
                            color="error"
                            style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);"
                            @click.stop="resetYearFilter"
                          >
                            bx-x
                          </VIcon>
                        </VBtn>
                      </template>
                      <VList>
                        <VListItem
                          v-for="year in availableYears"
                          :key="year.year || year"
                          @click="selectYear(year)"
                        >
                          <VListItemTitle>{{ year.year || year }}</VListItemTitle>
                        </VListItem>
                      </VList>
                    </VMenu>

                    <VMenu v-model="desaMenuOpen" offset-y :close-on-content-click="false">
                      <template v-slot:activator="{ props }">
                        <VBtn
                          v-bind="props"
                          color="primary"
                          variant="outlined"
                          size="small"
                          class="filter-btn"
                          style="position: relative; min-width: 100px; display: flex; justify-content: space-between;"
                        >
                          <div class="d-flex align-center">
                            <VIcon size="18" class="me-1">bx-map</VIcon>
                            <span class="text-truncate" style="max-width: 80px;">
                              {{ getDesaFilterLabel() }}
                            </span>
                          </div>
                          
                          <VIcon 
                            v-if="selectedDesa || (isUsingDefaultDesaFilter)"
                            size="16"
                            color="error"
                            style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);"
                            @click.stop="resetDesaFilter"
                          >
                            bx-x
                          </VIcon>
                        </VBtn>
                      </template>
                      
                      <VList width="300">
                        <VListItem class="pa-2">
                          <VTextField
                            v-model="desaSearchQuery"
                            placeholder="Cari desa..."
                            density="compact"
                            hide-details
                            clearable
                            prepend-inner-icon="bx-search"
                            @click.stop
                            @input="filterDesaList"
                          />
                        </VListItem>
                        
                        <VDivider />

                        <div style="max-height: 300px; overflow-y: auto;">
                          <VListItem
                            v-for="desa in filteredDesaList"
                            :key="desa.id_desa"
                            @click="selectDesa(desa.id_desa)"
                            :active="desa.id_desa === selectedDesa"
                          >
                            <VListItemTitle>
                              <div class="d-flex flex-column">
                                <span class="font-weight-medium">{{ desa.nama_desa }}</span>
                                <span class="text-caption text-secondary">
                                  {{ desa.kecamatan }} - {{ desa.puskesmas }}
                                </span>
                              </div>
                            </VListItemTitle>
                          </VListItem>
                          
                          <VListItem v-if="filteredDesaList.length === 0">
                            <VListItemTitle class="text-center text-secondary pa-4">
                              {{ desaSearchQuery ? 'Desa tidak ditemukan' : 'Memuat data...' }}
                            </VListItemTitle>
                          </VListItem>
                        </div>

                        <VDivider />
                        <VListItem class="d-flex justify-end pa-2 gap-2">
                          <VBtn
                            size="small"
                            variant="text"
                            @click="resetDesaFilter"
                            density="comfortable"
                          >Reset</VBtn>
                          <VBtn
                            size="small"
                            color="primary"
                            @click="desaMenuOpen = false"
                            density="comfortable"
                          >Tutup</VBtn>
                        </VListItem>
                      </VList>
                    </VMenu>

                    <VMenu offset-y close-on-content-click>
                      <template v-slot:activator="{ props }">
                        <VBtn
                          v-bind="props"
                          color="primary"
                          variant="outlined"
                          size="small"
                          class="filter-btn"
                          style="position: relative; min-width: 120px;"
                        >
                          <div class="d-flex align-center justify-space-between" style="width: 100%">
                            <div class="d-flex align-center text-truncate">
                              <VIcon size="18" class="me-1">bx-building-house</VIcon>
                              <span style="max-width: 70px" class="text-truncate">
                                {{ getCurrentOPDLabel() }}
                              </span>
                            </div>
                            
                            <VIcon 
                              v-if="isUsingDefaultOPDFilter || selectedOPD"
                              size="16"
                              color="error"
                              @click.stop="resetOPDFilter"
                              class="ml-1"
                            >
                              bx-x
                            </VIcon>
                          </div>
                        </VBtn>
                      </template>
                      
                      <VList>
                        <VListSubheader>Filter OPD</VListSubheader>
                        <VListItem
                          v-for="opd in availableOPDs"
                          :key="opd.value"
                          @click="selectOPD(opd.value)"
                          :class="{ 'v-list-item--active': (isUsingDefaultOPDFilter && opd.value === userOPD.value) || (!isUsingDefaultOPDFilter && opd.value === selectedOPD) }"
                        >
                          <VListItemTitle>{{ opd.label }}</VListItemTitle>
                          <VIcon 
                            v-if="(isUsingDefaultOPDFilter && opd.value === userOPD.value) || (!isUsingDefaultOPDFilter && opd.value === selectedOPD)"
                            size="16"
                            color="primary"
                            class="ml-2"
                          >
                            bx-check
                          </VIcon>
                        </VListItem>
                      </VList>
                    </VMenu>

                    <VMenu offset-y close-on-content-click>
                      <template v-slot:activator="{ props }">
                        <VBtn
                          v-bind="props"
                          color="primary"
                          variant="outlined"
                          size="small"
                          class="filter-btn"
                        >
                          <div class="d-flex align-center">
                            <VIcon size="18" class="me-1">bx-check-circle</VIcon>
                            <span class="text-truncate" style="max-width: 80px">{{ getStatusFilterLabel() }}</span>
                          </div>
                        </VBtn>
                      </template>
                      <VList>
                        <VListItem
                          v-for="status in statusOptions"
                          :key="status.value"
                          @click="selectStatus(status.value)"
                        >
                          <VListItemTitle>{{ status.label }}</VListItemTitle>
                          <VIcon 
                            v-if="filters.user_status_filter === status.value"
                            size="16"
                            color="primary"
                            class="ml-2"
                          >
                            bx-check
                          </VIcon>
                        </VListItem>
                      </VList>
                    </VMenu>
                  </div>
                </VCol>
              </VRow>

                <div class="table-container">
                    <VTable class="custom-table">
                        <thead>
                            <tr>
                                <th rowspan="3" class="text-center header-cell-top-left" style="width: 50px" >No</th>
                                <th v-if="isAllowedToAdd" rowspan="3" class="text-center">NIK</th>
                                <th rowspan="3" class="text-center">Nama Anak</th>
                                <th rowspan="3" class="text-center">Jenis Kelamin</th>
                                <th rowspan="3" class="text-center">Tanggal Lahir</th>
                                <th rowspan="3" class="text-center">Kecamatan</th>
                                <th rowspan="3" class="text-center">Puskesmas</th>
                                <th rowspan="3" class="text-center">Desa/Kelurahan</th>
                                <th rowspan="3" class="text-center">Posyandu</th>
                                <th rowspan="3" class="text-center">Progress Penanganan</th>
                                <th rowspan="3" class="text-center">Status</th>
                                <th colspan="12" class="text-center">Faktor Determinan</th>
                                <th rowspan="3" class="text-center header-cell-top-right">Aksi</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="text-center">JKN</th>
                                <th rowspan="2" class="text-center">Status Ekonomi</th>
                                <th rowspan="2" class="text-center">Imunisasi</th>
                                <th rowspan="2" class="text-center">BPNT</th>
                                <th rowspan="2" class="text-center">PKH</th>
                                <th rowspan="2" class="text-center">Jamban Sehat</th>
                                <th rowspan="2" class="text-center">Sumber Air Bersih</th>
                                <th rowspan="2" class="text-center">Kebiasaan Merokok</th>
                                <th rowspan="2" class="text-center">Riwayat Ibu Hamil</th>
                                <th rowspan="2" class="text-center">Penyakit Penyerta</th>
                                <th colspan="2" class="text-center">Kecacingan</th>
                            </tr>
                            <tr>
                                <th class="text-center">Menderita</th>
                                <th class="text-center">Mendapat Obat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in paginatedData" :key="item.id_anak">
                                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td v-if="isAllowedToAdd">{{ item.nik_anak }}</td>
                                <td>{{ item.nama_anak }}</td>
                                <td>{{ item.jenis_kelamin }}</td>
                                <td>{{ formatDate(item.tanggal_lahir) }}</td>
                                <td>{{ item.desa && item.desa.puskesmas && item.desa.puskesmas.kecamatan ? item.desa.puskesmas.kecamatan.nama_kecamatan : 'N/A' }}</td>
                                <td>{{ item.desa && item.desa.puskesmas ? item.desa.puskesmas.nama_puskesmas : 'N/A' }}</td>
                                <td>{{ item.desa ? item.desa.nama_desa : 'N/A' }}</td>
                                <td>{{ item.posyandu }}</td>
                                <td>
                                    <div @click="showPenangananDialog(item)">
                                    <v-chip
                                        :color="getStatusColor(item.penanganan_progress)"
                                        :text-color="getStatusTextColor(item.penanganan_progress)"
                                        small
                                    >
                                        {{ item.penanganan_progress }}
                                    </v-chip>
                                    </div>
                                </td>
                                <td>
                                  <div @click="showPenangananDialog(item)">
                                    <v-chip
                                      :color="getUserStatusColor(item.status_user)"
                                      small
                                    >
                                      <v-icon v-if="!item.status_user" icon="bx-block" size="small" class="mr-1"></v-icon>
                                      {{ item.status_user || 'Tidak Tergabung' }}
                                    </v-chip>
                                  </div>
                                </td>
                                <td v-html="getJKN(item.determinan.jkn)"></td>
                                <td>{{ getStatusEkonomiText(item.determinan.status_ekonomi) }}</td>
                                <td>{{ getImunisasiText(item.determinan.imunisasi) }}</td>
                                <td v-html="getYesNoIcon(item.determinan.bpnt)"></td>
                                <td v-html="getYesNoIcon(item.determinan.pkh)"></td>
                                <td v-html="getYesNoIcon(item.determinan.jamban_sehat)"></td>
                                <td v-html="getYesNoIcon(item.determinan.sumber_air_bersih)"></td>
                                <td v-html="getYesNoIcon(item.determinan.kebiasaan_merokok)"></td>
                                <td v-html="getYesNoIcon(item.determinan.riwayat_ibu_hamil)"></td>
                                <td>{{ item.determinan.penyakit_penyerta }}</td>
                                <td v-html="getYesNoIcon(item.determinan.kecacingan_menderita)"></td>
                                <td v-html="getYesNoIcon(item.determinan.kecacingan_obat)"></td>
                                <td>
                                <div class="button-group">
                                    <VBtn icon="icon" color="primary" @click="viewDetail(item)" class="square-button">
                                        <i class='bx bxs-info-circle'></i>
                                    </VBtn>
                                    <VBtn v-if="isAllowedToAdd" icon="icon" color="error" @click="deleteItem(item)" class="square-button">
                                        <i class='bx bxs-trash-alt'></i>
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
                        <VSelect
                            v-model="itemsPerPage"
                            :items="[5, 10, 15, 20]"
                            variant="outlined"
                            density="compact"
                            hide-details
                            class="items-per-page-select"
                            style="width: 80px;"
                        />
                    </div>
                    
                    <VPagination
                        v-model="currentPage"
                        :length="totalPages"
                        :total-visible="7"
                        rounded="lg"
                    />
                    
                    <div class="d-flex align-center">
                        <span class="text-body-2">
                            {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, filteredData.length) }} of {{ filteredData.length }}
                        </span>
                    </div>
                </div>
            </VCard>
        </VCol>
    </VRow>

    <ImportDialog
        v-model:visible="isImportDialogVisible"
        @file-imported="handleFileImported"
        @refresh-data="fetchData"
    />

    <DetailStunting
        v-model:visible="showDetail"
        :item="itemToView"
        :onClose="closeDialog"
        @refresh-data="fetchData"
    />

    <DownloadDialog
        v-model:visible="isDownloadDialogVisible"
    />

    <PenangananDialog
        v-model:visible="showPenanganan"
        :item="selectedItem"
        @refresh="fetchData"
    />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ExcelJS from 'https://cdn.jsdelivr.net/npm/exceljs@4.4.0/+esm';
import ImportDialog from './forms/tambah-data.vue';
import DetailStunting from './detail-stunting.vue';
import DownloadDialog from '../../cakupan-program-intervensi/master-data/forms/download-template.vue';
import PenangananDialog from './penanganan.vue';

import apiClient from '@/services/api';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();

const isDownloadDialogVisible = ref(false);
const showPenanganan = ref(false);
const showAllData = ref(false);
const showManualForm = ref(false);
const showDetail = ref(false);
const showDeleteSuccess = ref(false);
const showSuccess = ref(false);
const isImportDialogVisible = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const desaSearchQuery = ref('');
const showAllDesa = ref(false);
const desaMenuOpen = ref(false);

const stuntingData = ref([]);
const selectedItem = ref(null);
const itemToEdit = ref({});
const itemToView = ref({});
const selectedOPD = ref(null);
const selectedYear = ref(null);
const selectedDesa = ref(null);
const availableYears = ref([]);
const availableDesa = ref([]);
const filteredDesaList = ref([]);
const filters = ref({
  user_status_filter: 'unhandled'
});

const currentUser = computed(() => authStore.user);
const isAllowedToAdd = computed(() => {
  return ['Dinas Kesehatan', 'Kader Desa'].includes(currentUser.value?.role);
});

const userOPD = computed(() => {
  if (!currentUser.value) return null;
  const role = currentUser.value.role;
  if (role.includes('Dinas Kesehatan')) return 'dinkes';
  if (role.includes('Dinas Sosial')) return 'dinsos'; 
  if (role.includes('Dinas PU')) return 'dinpu';
  return null;
});

const isUsingDefaultOPDFilter = computed(() => {
  if (selectedOPD.value !== null) return false;
  if (showAllData.value) return false;
  return !!userOPD.value;
});

const isUsingDefaultDesaFilter = computed(() => {
  try {
    const userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    return !selectedDesa.value && !!userData?.id_desa;
  } catch (e) {
    console.error('Error checking default desa filter:', e);
    return false;
  }
});

const isYearOrDesaFilterActive = computed(() => {
  return selectedYear.value !== null || selectedDesa.value !== null;
});

const filteredData = computed(() => {
  let result = stuntingData.value;
  if (searchQuery.value) {
    result = result.filter(item => 
    item.nama_anak.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (item.nik_anak && item.nik_anak.toLowerCase().includes(searchQuery.value.toLowerCase()))
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

const statusOptions = ref([
  { label: 'Belum Ada Penanganan', value: 'unhandled' },
  { label: 'Sudah Ada Penanganan', value: 'handled' },
  { label: 'Semua Status', value: null }
]);

const sortOptions = ref([
  { title: 'A-Z (Nama)', value: 'name_asc' },
  { title: 'Z-A (Nama)', value: 'name_desc' },
  { title: 'Terbaru', value: 'latest' },
  { title: 'Terlama', value: 'oldest' },
]);

const availableOPDs = computed(() => {
  const baseOptions = [
    { label: 'Dinas Kesehatan', value: 'dinkes' },
    { label: 'Dinas Sosial', value: 'dinsos' },
    { label: 'Dinas PU', value: 'dinpu' },
    { label: 'Semua OPD', value: null }
  ];
  
  return baseOptions;
});

const itemsPerPageOptions = [5, 10, 15, 20];
const currentSort = ref('latest');

const getStatusColor = (progress) => {
  const [handled, total] = progress.split('/').map(Number);
  if (handled === 0) return 'error';
  if (handled === total) return 'success';
  return 'warning';
};

const getStatusTextColor = (progress) => {
  const [handled] = progress.split('/').map(Number);
  return handled === 0 ? 'white' : 'black';
};

const getStatusText = (progress) => {
  const [handled, total] = progress.split('/').map(Number);
  if (handled === 0) return 'Belum ada penanganan';
  if (handled === total) return 'Sudah ditangani';
  return 'Dalam penanganan';
};

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const getStatusEkonomiText = (value) => {
  const numericValue = Number(value);
  return numericValue === 1 ? 'Gakin' : 'Non Gakin';
};

const getImunisasiText = (value) => {
  const numericValue = Number(value);
  return numericValue === 1 ? 'Lengkap' : 'Tidak';
};

const getJKN = (value) => {
  const jknOptions = [
    { value: 1, title: 'BPJS Mandiri' },
    { value: 2, title: 'BPJS Pemerintah' },
    { value: 3, title: 'Tidak Punya' },
    { value: 4, title: 'Asuransi Swasta' },
  ];
  const selectedOption = jknOptions.find(option => option.value === Number(value));
  return selectedOption ? selectedOption.title : 'Tidak Diketahui';
};

const getYesNoIcon = (value) => {
  const numericValue = Number(value);
  return numericValue === 1 
    ? '<i class="bx bx-check text-success" style="font-size: 20px;"></i>' 
    : '<i class="bx bx-x text-error" style="font-size: 20px;"></i>';
};

const getGlobalStatusColor = (status) => {
  switch(status) {
    case 'Sudah ditangani': return 'success';
    case 'Dalam penanganan': return 'warning';
    default: return 'error';
  }
};

const getUserStatusColor = (status) => {
  return status === 'Sudah ada penanganan' ? 'success' : 'error';
};

const getDesaName = (id) => {
  const desa = availableDesa.value.find(d => d.id_desa === id);
  return desa ? `${desa.nama_desa} - ${desa.kecamatan}` : 'Desa';
};

const getOPDLabel = (value) => {
  const opd = availableOPDs.value.find(o => o.value === value);
  return opd ? opd.label : 'Semua OPD';
};

const getCurrentOPDLabel = () => {
  if (selectedOPD.value !== null) return getOPDLabel(selectedOPD.value);
  if (isUsingDefaultOPDFilter.value) return `Default: ${getOPDLabel(userOPD.value)}`;
  return 'Semua OPD';
};

const isOPDFilterActive = computed(() => {
  return selectedOPD.value !== null || isUsingDefaultOPDFilter.value;
});

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

const getStatusFilterLabel = () => {
  const selected = statusOptions.value.find(opt => opt.value === filters.value.user_status_filter);
  return selected ? selected.label : 'Status';
};

const handleDownload = () => {
  isDownloadDialogVisible.value = true;
};

const selectStatus = (status) => {
  filters.value.user_status_filter = status;
  fetchData();
};

const showPenangananDialog = (item) => {
  selectedItem.value = item;
  showPenanganan.value = true;
};

const selectOPD = (opd) => {
  selectedOPD.value = opd;
  showAllData.value = opd === null; 
  fetchData();
};

const resetOPDFilter = () => {
  if (userOPD.value) {
    selectedOPD.value = userOPD.value;
    showAllData.value = false;
  } else {
    selectedOPD.value = null;
    showAllData.value = true;
  }
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

const applySort = (sortValue) => {
  currentSort.value = sortValue;
  fetchData();
};

const filterDesaList = () => {
  if (!desaSearchQuery.value) {
    filteredDesaList.value = [...availableDesa.value];
    return;
  }
  
  const searchTerm = desaSearchQuery.value.toLowerCase();
  filteredDesaList.value = availableDesa.value.filter(desa => {
    const namaDesaLower = (desa.nama_desa || '').toLowerCase();
    const kecamatanLower = (desa.kecamatan || '').toLowerCase();
    const puskesmasLower = (desa.puskesmas || '').toLowerCase();
    
    return namaDesaLower.includes(searchTerm) || 
           kecamatanLower.includes(searchTerm) || 
           puskesmasLower.includes(searchTerm);
  });
};

const resetDesaFilter = () => {
  selectedDesa.value = null;
  desaSearchQuery.value = '';
  filteredDesaList.value = [...availableDesa.value];
  showAllDesa.value = true;
  fetchData();
};

const selectDesa = (idDesa) => {
  selectedDesa.value = idDesa;
  desaMenuOpen.value = false;
  fetchData();
};

const handleImport = () => {
  isImportDialogVisible.value = true;
};

const handleFileImported = (file) => {
  console.log('File imported:', file);
  isImportDialogVisible.value = false;
  showSuccess.value = true;
  fetchData();
  setTimeout(() => {
    fetchData();
  }, 300);
};

const viewDetail = (item) => {
  itemToView.value = { ...item };
  showDetail.value = true;
};

const closeDialog = () => {
  showDetail.value = false;
  showManualForm.value = false;
};

const handleSaveEdit = (editedItem) => {
  const index = stuntingData.value.findIndex(item => item.no === editedItem.no);
  if (index !== -1) {
    stuntingData.value[index] = { 
      ...stuntingData.value[index],
      ...editedItem
    };
  }
  showManualForm.value = false;
  itemToEdit.value = null;
};

const fetchData = async () => {
  try {
    let userData = null;
    try {
      userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    } catch (e) {
      console.error('Error reading user data:', e);
      userData = authStore.user;
    }
    
    const defaultIdDesa = userData?.id_desa || null;
    const idDesaToUse = selectedDesa.value || 
                       (!showAllDesa.value ? defaultIdDesa : null);
    
    const params = {
      search: searchQuery.value,
      year: selectedYear.value?.year || selectedYear.value,
      id_desa: idDesaToUse,
      opd_filter: selectedOPD.value || (isUsingDefaultOPDFilter.value ? userOPD.value : null),
      sort_name: currentSort.value === 'name_asc' ? 'asc' : 
                currentSort.value === 'name_desc' ? 'desc' : null,
      sort_date: currentSort.value === 'latest' ? 'latest' :
                currentSort.value === 'oldest' ? 'oldest' : null,
      show_all: selectedOPD.value === null || (!userOPD.value && !selectedOPD.value),
      user_id: currentUser.value?.id,
      user_status_filter: filters.value.user_status_filter
    };
    
    const response = await apiClient.get('/anak-stunting', { params });
    stuntingData.value = response.data.data.map(item => ({
      ...item,
      penanganan_progress: item.penanganan_progress || "0/0",
      status_user: item.status_user || null
    }));
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

const fetchDesaData = async () => {
  try {
    const response = await apiClient.get('/desa');
    if (response.data && response.data.data) {
      availableDesa.value = response.data.data.map(desa => ({
        id_desa: desa.id,
        nama_desa: desa.nama_desa || desa.desa || '',
        kecamatan: desa.kecamatan || 'N/A',
        puskesmas: desa.puskesmas || 'N/A'
      }));
      filteredDesaList.value = [...availableDesa.value];
    } else {
      console.error('Unexpected API response structure:', response.data);
    }
  } catch (error) {
    console.error('Error fetching desa data:', error);
  }
};

const fetchFilterOptions = async () => {
  try {
    const response = await apiClient.get('/anak-stunting');
    availableYears.value = response.data.filters.available_years.map(y => ({ year: y }));
    availableDesa.value = response.data.filters.available_desa;
  } catch (error) {
    console.error('Error fetching filter options:', error);
  }
};

const applyFilters = () => {
  fetchData();
};

const resetFilters = () => {
  selectedYear.value = null;
  selectedDesa.value = null;
  fetchData();
};

const deleteItem = async (item) => {
  const result = await window.Swal.fire({
    title: 'Apakah Anda yakin?',
    text: `Anda akan menghapus data ${item.nama_anak}. Data yang dihapus tidak dapat dikembalikan!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (result.isConfirmed) {
    try {
      await apiClient.delete(`/anak-stunting/${item.id_anak}`);
      fetchData();
      window.Swal.fire({
        title: 'Dihapus!',
        text: 'Data berhasil dihapus.',
        icon: 'success',
        showConfirmButton: false,
        timer: 2000    
      });
    } catch (error) {
      console.error('Error deleting item:', error);
      window.Swal.fire(
        'Gagal!',
        'Terjadi kesalahan saat menghapus data. Silakan cek konsol untuk detailnya.',
        'error'
      );
    }
  }
};

const exportData = async () => {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Data Anak Stunting');

    const headers = [
      'No.', 'NIK', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 'Kecamatan',
      'Puskesmas', 'Desa', 'Posyandu', 'Status Ekonomi', 'JKN',
      'Penyakit Penyerta', 'Imunisasi', 'Ketersediaan Jamban',
      'Sumber Air Bersih', 'Program PKH', 'Kebiasaan Merokok', 'BPNT',
      'Riwayat Ibu Hamil', 'Kecacingan Menderita', 'Kecacingan Mendapat Obat'
    ];
    
    const headerRow = worksheet.addRow(headers);
    
    headerRow.eachCell((cell) => {
      cell.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFFFFF00' } 
      };
      cell.font = { bold: true };
      cell.border = {
        top: { style: 'thin' },
        left: { style: 'thin' },
        bottom: { style: 'thin' },
        right: { style: 'thin' }
      };
      cell.alignment = { vertical: 'middle', horizontal: 'center' };
    });

    stuntingData.value.forEach((item, index) => {
      const row = [
        index + 1,
        item.nik_anak,
        item.nama_anak,
        item.jenis_kelamin === 'L' ? 'L' : 'P',
        item.tanggal_lahir,
        item.desa?.puskesmas?.kecamatan?.nama_kecamatan || '',
        item.desa?.puskesmas?.nama_puskesmas || '',
        item.desa?.nama_desa || '',
        item.posyandu || '',
        item.determinan?.status_ekonomi || '',
        item.determinan?.jkn || '',
        item.determinan?.penyakit_penyerta || '',
        item.determinan?.imunisasi || '',
        item.determinan?.jamban_sehat || '',
        item.determinan?.sumber_air_bersih || '',
        item.determinan?.pkh || '',
        item.determinan?.kebiasaan_merokok || '',
        item.determinan?.bpnt || '',
        item.determinan?.riwayat_ibu_hamil || '',
        item.determinan?.kecacingan_menderita || '',
        item.determinan?.kecacingan_obat || ''
      ];
      
      const dataRow = worksheet.addRow(row);

      dataRow.getCell(10).numFmt = '0'; // Status Ekonomi
      dataRow.getCell(11).numFmt = '0'; // JKN
      dataRow.getCell(13).numFmt = '0'; // Imunisasi
      dataRow.getCell(14).numFmt = '0'; // Jamban
      dataRow.getCell(15).numFmt = '0'; // Air Bersih
      dataRow.getCell(16).numFmt = '0'; // PKH
      dataRow.getCell(17).numFmt = '0'; // Merokok
      dataRow.getCell(18).numFmt = '0'; // BPNT
      dataRow.getCell(19).numFmt = '0'; // Ibu Hamil
      dataRow.getCell(20).numFmt = '0'; // Kecacingan Menderita
      dataRow.getCell(21).numFmt = '0'; // Kecacingan Obat
    });
    
    worksheet.getColumn(5).numFmt = 'yyyy-mm-dd';
    
    worksheet.columns = [
      { key: 'No', width: 5 },
      { key: 'NIK', width: 20 },
      { key: 'Nama', width: 20 },
      { key: 'JK', width: 10 },
      { key: 'TglLahir', width: 12 },
      { key: 'Kecamatan', width: 15 },
      { key: 'Puskesmas', width: 15 },
      { key: 'Desa', width: 15 },
      { key: 'Posyandu', width: 15 },
      { key: 'StatusEkonomi', width: 10 },
      { key: 'JKN', width: 10 },
      { key: 'Penyakit', width: 15 },
      { key: 'Imunisasi', width: 10 },
      { key: 'Jamban', width: 10 },
      { key: 'AirBersih', width: 10 },
      { key: 'PKH', width: 10 },
      { key: 'Merokok', width: 10 },
      { key: 'BPNT', width: 10 },
      { key: 'IbuHamil', width: 10 },
      { key: 'Kecacingan1', width: 10 },
      { key: 'Kecacingan2', width: 10 }
    ];
    
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Data_Anak_Stunting_${new Date().toISOString().split('T')[0]}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
  } catch (error) {
    console.error('Error exporting data:', error);
    window.Swal.fire({
      title: 'Gagal Export',
      text: 'Terjadi kesalahan saat mengekspor data.',
      icon: 'error'
    });
  }
};

onMounted(() => {
  fetchDesaData();
  fetchFilterOptions();
  
  try {
    const userData = JSON.parse(localStorage.getItem('user')) || authStore.user;
    if (userData?.id_desa) {
      selectedDesa.value = userData.id_desa;
    }

    if (userOPD.value) {
      selectedOPD.value = userOPD.value;
      showAllData.value = false;
    } else {
      selectedOPD.value = null;
      showAllData.value = true;
    }
    
    fetchData();
  } catch (e) {
    console.error('Error initializing filters:', e);
  }
});
</script>

<style scoped>
.status-container {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-text {
    font-size: 0.8rem;
    white-space: nowrap;
}

.status-success, .status-warning {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
  text-align: center;
  cursor: default;
}

.status-success {
  background-color: #4CAF50;
  color: white;
}

.status-warning {
  background-color: #FFC107;
  color: black;
}

.table-container {
    overflow-x: auto;
    padding: 16px;
    border-radius: 8px;
    position: relative;
}

.custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.custom-table th,
.custom-table td {
    border-right: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    padding: 12px;
    text-align: center;
    white-space: nowrap; 
}

.custom-table th {
    min-width: 120px;
    background-color: #f5f5f5;
    font-weight: 600;
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

.button-group {
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 8px;
    overflow: hidden;
  }

.square-button {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    margin: 0;
    border-radius: 0;
}

.square-button:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.square-button:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
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

.fixed-header {
    position: sticky;
    top: 0;
    background: white;
    z-index: 3;
    border-bottom: 2px solid #ddd;
}

.fixed-column {
    position: sticky;
    left: 0;
    background: white;
    z-index: 2;
    border-right: 2px solid #ddd;
}

.fixed-no {
    left: 0;
    min-width: 60px;
    max-width: 70px;
}

.fixed-nik {
    left: 120px;
    min-width: 160px;
    max-width: 180px;
}

.fixed-nama {
    left: 291.5px;
    min-width: 200px;
    max-width: 220px;
}

.items-per-page-select :deep(.v-field__input) {
    padding-top: 8px;
    padding-bottom: 8px;
    min-height: 40px;
}

.items-per-page-select :deep(.v-field) {
    border-radius: 8px;
}

.v-pagination {
    justify-content: center;
}

.v-chip {
  max-width: 180px;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  cursor: pointer;
}

.v-btn--icon.v-btn--density-default {
  width: 24px;
  height: 24px;
}

.v-field__append-inner {
  padding-top: 0;
  align-items: center;
}

.filter-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 8px;
}

.filter-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  text-align: left;
  overflow: hidden;
  min-width: 110px;
}
</style>