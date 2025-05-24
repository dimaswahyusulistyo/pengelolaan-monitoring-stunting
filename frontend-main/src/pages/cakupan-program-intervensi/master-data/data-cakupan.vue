<template>
    <VRow>
        <VCol cols="12">
            <VCard title="DATA CAKUPAN PROGRAM INTERVENSI PERCEPATAN PENURUNAN STUNTING" class="rounded-lg">
              <VRow class="align-center px-4 py-2">
                

                <VCol v-if="authStore.user.role !== 'Kader Desa'" cols="12" class="d-flex justify-end mt-4">
                  <VBtn color="info" class="me-2 rounded-lg" @click="handleDownload">
                    <VIcon class="me-2">bx-download</VIcon>Template
                  </VBtn>

                  <VBtn color="success" class="me-2 rounded-lg" @click="handleImport">Tambah</VBtn>

                  <VBtn color="warning" @click="exportData" class="me-2 rounded-lg">Export</VBtn>
                </VCol>

                <VCol cols="12" class="text-end text-caption text-grey-darken-1 mt-2">
                  Terakhir diubah pada: {{ lastUpdated}}
                </VCol>

                <VCol cols="12" md="3">
                  <VSelect
                    v-model="selectedKecamatan"
                    :items="kecamatanOptions"
                    label="Pilih Kecamatan"
                    variant="outlined"
                    bg-color="surface"
                    prepend-inner-icon="bx-map"
                    clearable="clearable"
                    density="comfortable"
                  />
                </VCol>

                <VCol cols="12" md="3">
                  <VSelect
                    v-model="selectedPuskesmas"
                    :items="puskesmasOptions"
                    label="Pilih Puskesmas"
                    variant="outlined"
                    bg-color="surface"
                    prepend-inner-icon="bx-map"
                    clearable="clearable"
                    density="comfortable"
                  />
                </VCol>

                <VCol cols="12" md="3">
                  <VSelect
                    v-model="selectedDesa"
                    :items="desaOptions"
                    label="Pilih Desa"
                    bg-color="surface"
                    prepend-inner-icon="bx-home-alt"
                    clearable="clearable"
                    density="comfortable"
                  />
                </VCol>

                <VCol cols="12" md="3">
                  <VSelect
                    v-model="selectedTahun"
                    :items="tahunOptions"
                    label="Pilih Tahun"
                    @change="selectedTahun = String($event)"
                    bg-color="surface"
                    prepend-inner-icon="bx-calendar"
                    clearable="clearable"
                    density="comfortable"
                  />
                </VCol>
              </VRow>
  
            <div class="table-container">
                <VTable class="custom-table elevation-1">
                    <thead>
                    <tr>
                        <th class="text-center" rowspan="2">No</th>
                        <th class="text-center" rowspan="2">Kecamatan</th>
                        <th class="text-center" rowspan="2">Puskesmas</th>
                        <th class="text-center" rowspan="2">Desa</th>
                        <th class="text-center header-pupulasi" colspan="2">POPULASI</th>
                        <th class="text-center header-remaja" colspan="2">REMAJA</th>
                        <th class="text-center header-pus" colspan="7">CALON PENGANTIN/PASANGAN USIA SUBUR</th>
                        <th class="text-center header-ibu-hamil" colspan="4">IBU HAMIL</th>
                        <th class="text-center header-balita" colspan="6">ANAK DI BAWAH USIA LIMA TAHUN (BALITA)</th>
                        <th class="text-center header-keluarga-berisiko" colspan="6">KELUARGA BERISIKO</th>
                        <th class="text-center header-air-minum" colspan="2">AIR MINUM DAN SANITASI</th>
                        <th class="text-center header-perlindungan-sosial" colspan="2">PERLINDUNGAN SOSIAL</th>
                        <th class="text-center header-anggaran" colspan="4">ANGGARAN PERCEPATAN PENURUNAN STUNTING DI DESA</th>
                        <!-- <th class="text-center" rowspan="2">Aksi</th> -->
                    </tr>
                    <tr>
                        <th class="text-center">Total Populasi Keluarga</th>
                        <th class="text-center">Total Populasi Anak</th>
                        <th class="text-center">Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)</th>
                        <th class="text-center">Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)</th>
                        <th class="text-center">Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)</th>
                        <th class="text-center">Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah</th>
                        <th class="text-center">Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah</th>
                        <th class="text-center">Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting</th>
                        <th class="text-center">Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat</th>
                        <th class="text-center">Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai</th>
                        <th class="text-center">Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan</th>
                        <th class="text-center">Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi</th>
                        <th class="text-center">Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan</th>
                        <th class="text-center">Persentase Unmeet Need pelayanan keluarga berencana</th>
                        <th class="text-center">Persentase Kehamilan yang tidak diinginkan</th>
                        <th class="text-center">Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif</th>
                        <th class="text-center">Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)</th>
                        <th class="text-center">Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk</th>
                        <th class="text-center">Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya</th>
                        <th class="text-center">Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi</th>
                        <th class="text-center">Balita yang memperoleh imunisasi dasar lengkap</th>
                        <th class="text-center">Keluarga yang Stop BABS</th>
                        <th class="text-center">Keluarga yang melaksanakan PHBS</th>
                        <th class="text-center">Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri</th>
                        <th class="text-center">Pelayanan Keluarga Berencana (KB) pascapersalinan</th>
                        <th class="text-center">Keluarga berisiko stunting yang memperoleh pendampingan</th>
                        <th class="text-center">Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi</th>
                        <th class="text-center">Rumah tangga yang mendapatkan akses air minum layak</th>
                        <th class="text-center">Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak</th>
                        <th class="text-center">Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi</th>
                        <th class="text-center">Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur</th>
                        <th class="text-center">DD</th>
                        <th class="text-center">APBDES</th>
                        <th class="text-center">BUMDES</th>
                        <th class="text-center">CSR</th>

                    </tr>
                    </thead>
                      <tbody>
                        <tr v-for="(dataCakupan, index) in paginatedData" :key="index">
                          <td class="text-center">{{ index + 1 }}</td>
                          <td class="text-center">{{ dataCakupan.kecamatan }}</td>
                          <td class="text-center">{{ dataCakupan.puskesmas }}</td>
                          <td class="text-center">{{ dataCakupan.desa }}</td>
                          <td class="text-center">{{ dataCakupan["Total Populasi Keluarga"] }}</td>
                          <td class="text-center">{{ dataCakupan["Total Populasi Anak"] }}</td>
                          <td class="text-center">{{ dataCakupan["Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)"] }}</td>
                          <td class="text-center">{{ dataCakupan["Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)"] }}</td>
                          <td class="text-center">{{ dataCakupan["Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)"] }}</td>
                          <td class="text-center">{{ dataCakupan["Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah"] }}</td>
                          <td class="text-center">{{ dataCakupan["Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah"] }}</td>
                          <td class="text-center">{{ dataCakupan["Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting"] }}</td>
                          <td class="text-center">{{ dataCakupan["Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat"] }}</td>
                          <td class="text-center">{{ dataCakupan["Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai"]}}</td>
                          <td class="text-center">{{ dataCakupan["Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan"] }}</td>
                          <td class="text-center">{{ dataCakupan["Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi"] }}</td>
                          <td class="text-center">{{ dataCakupan["Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan"] }}</td>
                          <td class="text-center">{{ dataCakupan["Persentase Unmeet Need pelayanan keluarga berencana"] }}</td>
                          <td class="text-center">{{ dataCakupan["Persentase Kehamilan yang tidak diinginkan"] }}</td>
                          <td class="text-center">{{ dataCakupan["Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif"] }}</td>
                          <td class="text-center">{{ dataCakupan["Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)"] }}</td>
                          <td class="text-center">{{ dataCakupan["Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk"] }}</td>
                          <td class="text-center">{{ dataCakupan["Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya"] }}</td>
                          <td class="text-center">{{ dataCakupan["Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi"] }}</td>
                          <td class="text-center">{{ dataCakupan["Balita yang memperoleh imunisasi dasar lengkap"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga yang Stop BABS"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga yang melaksanakan PHBS"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri"] }}</td>
                          <td class="text-center">{{ dataCakupan["Pelayanan Keluarga Berencana (KB) pascapersalinan"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga berisiko stunting yang memperoleh pendampingan"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi"] }}</td>
                          <td class="text-center">{{ dataCakupan["Rumah tangga yang mendapatkan akses air minum layak"] }}</td>
                          <td class="text-center">{{ dataCakupan["Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak"] }}</td>
                          <td class="text-center">{{ dataCakupan["Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi"] }}</td>
                          <td class="text-center">{{ dataCakupan["Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur"] }}</td>
                          <td class="text-center">{{ dataCakupan["DD"] }}</td>
                          <td class="text-center">{{ dataCakupan["APBDES"] }}</td>
                          <td class="text-center">{{ dataCakupan["BUMDES"] }}</td>
                          <td class="text-center">{{ dataCakupan["CSR"] }}</td>
                        </tr>
                      </tbody>
                </VTable>
                </div>
  
            <div class="d-flex align-center justify-space-between pa-4">
                    <div class="d-flex align-center">
                        <span class="text-body-2 me-4">Items per page:</span>
                        <VSelect v-model="itemsPerPage" :items="[5, 10, 15, 20, 177]" variant="outlined" density="compact"
                            hide-details class="items-per-page-select" style="width: 80px;" />
                    </div>

                    <VPagination v-model="currentPage" :length="totalPages" :total-visible="7" rounded="lg" />

                    <div class="d-flex align-center">
                        <span class="text-body-2">
                            {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage,
                                filteredData.length) }}
                            of {{ filteredData.length }}
                        </span>
                    </div>
                </div>
        </VCard>
      </VCol>
      <ImportDialog
        v-model:visible="isImportDialogVisible"
        @file-imported="handleFileImported"
        @refresh-data="fetchDataCakupan"
    />
    <DownloadDialog
        v-model:visible="isDownloadDialogVisible"
    />
    </VRow>
  </template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import {
    VRow,
    VCol,
    VCard,
    VTable,
    VTextField,
    VBtn,
    VPagination
} from 'vuetify/components';
import apiClient from '@/services/api';
import ImportDialog from './forms/tambah-data.vue';
import DownloadDialog from './forms/download-template.vue';
import ExcelJS from 'exceljs';
import { useAuthStore } from '@/services/auth';
const authStore = useAuthStore()

const emit = defineEmits(['update:visible']);
const showCreateForm = ref(false);
const isDownloadDialogVisible = ref(false);

const closeDialog = () => {
  showCreateForm.value = false;
  emit('update:visible', false);
};

const isImportDialogVisible = ref(false);

const handleImport = () => {
  isImportDialogVisible.value = true;
};

const handleDownload = () => {
  isDownloadDialogVisible.value = true;
};

const handleFileImported = (file) => {
  console.log('File imported:', file);
  isImportDialogVisible.value = false;
  showSuccess.value = true;
  fetchDataCakupan();
};

  
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const dataCakupan = ref([]);

const kecamatanOptions = ref([]);
const puskesmasOptions = ref([]);
const desaOptions = ref([]);
const tahunOptions = ref([]);

const selectedKecamatan = ref('');
const selectedPuskesmas = ref('');
const selectedDesa = ref('');
const selectedTahun = ref('');

const lastUpdated = ref("")



const fetchDataCakupan = async () => {
  try {
    const response = await apiClient.get('/cakupan-program', {
      params: {
        tahun: selectedTahun.value,
        kecamatan: selectedKecamatan.value,
        puskesmas: selectedPuskesmas.value,
        desa: selectedDesa.value,
      }
    });

    dataCakupan.value = response.data.data ?? [];
    tahunOptions.value = (response.data.list_tahun ?? []).map(String);

    kecamatanOptions.value = [...new Set(dataCakupan.value.map(item => item.kecamatan).filter(Boolean))];
    puskesmasOptions.value = [...new Set(dataCakupan.value.map(item => item.puskesmas).filter(Boolean))];
    desaOptions.value = [...new Set(dataCakupan.value.map(item => item.desa).filter(Boolean))];

    if (response.data.last_updated) {
    const date = new Date(response.data.last_updated);
    lastUpdated.value = new Intl.DateTimeFormat('id-ID', {
      dateStyle: 'long',
      timeStyle: 'short'
    }).format(date);
  }

  } catch (error) {
    console.error('Error fetching data cakupan:', error);
    dataCakupan.value = [];
  }
};

watch([selectedTahun, selectedKecamatan, selectedPuskesmas, selectedDesa], fetchDataCakupan);

onMounted(fetchDataCakupan);
  
const filteredData = computed(() => {
  return dataCakupan.value.filter(item => {
    const matchesSearch = searchQuery.value
      ? (item.kecamatan?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
         item.puskesmas?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
         item.desa?.toLowerCase().includes(searchQuery.value.toLowerCase()))
      : true;

    const matchesKecamatan = selectedKecamatan.value
      ? item.kecamatan === selectedKecamatan.value
      : true;

    const matchesPuskesmas = selectedPuskesmas.value
      ? item.puskesmas === selectedPuskesmas.value
      : true;

    const matchesDesa = selectedDesa.value
      ? item.desa === selectedDesa.value
      : true;

    const matchesTahun = selectedTahun.value
      ? item.tahun === String(selectedTahun.value)
      : true;

    return matchesSearch && matchesKecamatan && matchesPuskesmas && matchesDesa && matchesTahun;
  });
});
  
  const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredData.value.slice(start, end);
  });
  
  const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / itemsPerPage.value);
  });

  const exportData = () => {
    const headers = [
        'No',
        'Kecamatan',
        'Puskesmas',
        'Desa',
        'Total Populasi Keluarga',
        'Total Populasi Anak',
        'Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)',
        'Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)',
        'Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)',
        'Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah',
        'Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah',
        'Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting',
        'Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat',
        'Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai',
        'Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan',
        'Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi',
        'Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan',
        'Persentase Unmeet Need pelayanan keluarga berencana',
        'Persentase Kehamilan yang tidak diinginkan',
        'Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif',
        'Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)',
        'Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk',
        'Anak berusia di bawah lima tahun (balita) yang dipantau pertumbuhan dan perkembangannya',
        'Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi',
        'Balita yang memperoleh imunisasi dasar lengkap',
        'Keluarga yang Stop BABS',
        'Keluarga yang melaksanakan PHBS',
        'Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri',
        'Pelayanan Keluarga Berencana (KB) pascapersalinan',
        'Keluarga berisiko stunting yang memperoleh pendampingan',
        'Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi',
        'Rumah tangga yang mendapatkan akses air minum layak',
        'Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak',
        'Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi',
        'Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur',
        'DD',
        'APBDES',
        'BUMDES',
        'CSR'
    ];

    const rows = dataCakupan.value.map((item, index) => [
        index + 1,
        item.kecamatan,
        item.puskesmas,
        item.desa,
        item["Total Populasi Keluarga"],
        item["Total Populasi Anak"],
        item["Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)"],
        item["Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)"],
        item["Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)"],
        item["Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah"],
        item["Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah"],
        item["Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting"],
        item["Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat"],
        item["Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai"],
        item["Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan"],
        item["Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi"],
        item["Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan"],
        item["Persentase Unmeet Need pelayanan keluarga berencana"],
        item["Persentase Kehamilan yang tidak diinginkan"],
        item["Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif"],
        item["Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)"],
        item["Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk"],
        item["Anak berusia di bawah lima tahun (balita) yang dipantau pertumbuhan dan perkembangannya"],
        item["Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi"],
        item["Balita yang memperoleh imunisasi dasar lengkap"],
        item["Keluarga yang Stop BABS"],
        item["Keluarga yang melaksanakan PHBS"],
        item["Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri"],
        item["Pelayanan Keluarga Berencana (KB) pascapersalinan"],
        item["Keluarga berisiko stunting yang memperoleh pendampingan"],
        item["Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi"],
        item["Rumah tangga yang mendapatkan akses air minum layak"],
        item["Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak"],
        item["Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi"],
        item["Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur"],
        item["DD"],
        item["APBDES"],
        item["BUMDES"],
        item["CSR"]
    ]);

    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Data Capaian Program');
    
    const headerStyle = {
        font: { bold: true, size: 11 },
        alignment: { vertical: 'middle', horizontal: 'center', wrapText: true },
        fill: {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFD3D3D3' } 
        },
        border: {
            top: { style: 'thin' },
            left: { style: 'thin' },
            bottom: { style: 'thin' },
            right: { style: 'thin' }
        }
    };
    
    const dataStyle = {
        alignment: { vertical: 'middle' },
        border: {
            top: { style: 'thin' },
            left: { style: 'thin' },
            bottom: { style: 'thin' },
            right: { style: 'thin' }
        }
    };
    
    const headerRow = worksheet.addRow(headers);
    headerRow.eachCell((cell) => {
        cell.style = headerStyle;
    });
    
    rows.forEach(rowData => {
        const row = worksheet.addRow(rowData);
        row.eachCell((cell) => {
            cell.style = dataStyle;
        });
    });
    
    const calculateWidth = (text) => {
        
        if (!text) return 12; 
        const contentLength = String(text).length;
        return Math.min(20, Math.max(12, contentLength * 1.2));
    };
    
    const columnWidths = [];
    headers.forEach((header, i) => {
        columnWidths[i] = calculateWidth(header);
    });
    
    const rowsToCheck = Math.min(50, rows.length);
    for (let i = 0; i < rowsToCheck; i++) {
        const row = rows[i];
        row.forEach((cell, j) => {
            if (cell) {
                const cellWidth = calculateWidth(cell);
                columnWidths[j] = Math.max(columnWidths[j] || 0, cellWidth);
            }
        });
    }
    
    worksheet.columns.forEach((column, i) => {
        column.width = columnWidths[i];
    });
    
    worksheet.autoFilter = {
        from: { row: 1, column: 1 },
        to: { row: 1, column: headers.length }
    };
    
    worksheet.views = [
    
    ];
    
    const today = new Date();
    const formattedDate = today.toISOString().slice(0, 10);
    const filename = `data-capaian-program-intervensi-${formattedDate}.xlsx`;
    
    workbook.xlsx.writeBuffer().then(buffer => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        window.URL.revokeObjectURL(url);
    });
};


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
    table-layout: fixed;
    width: 100%;
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

.custom-table th, 
.custom-table td {
    font-size: 10px; 
    padding: 4px 8px; 
    white-space: nowrap; 
    width: auto;
  white-space: normal; 
  word-wrap: break-word; 
  text-align: center;
  padding: 10px;
  border: 1px solid #ddd;
}

.custom-table th {
    background-color: #f8f9fa; 
    font-weight: bold; 
    text-transform: uppercase; 
}

.button-group {
    display: flex;
    gap: 4px; 
}

.square-button {
    min-width: 30px !important; 
    height: 30px !important;
}

</style>