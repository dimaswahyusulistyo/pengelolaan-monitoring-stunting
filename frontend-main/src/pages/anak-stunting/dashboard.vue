<template>
    <VRow>
        <VCol cols="12" md="8">
            <VCard elevation="1" class="rounded-lg">
                <VCardItem>
                    <VCardTitle class="text-h5 font-weight-medium text-primary">Dashboard Statistik Stunting</VCardTitle>
                    <template #append>
                        <VChip color="primary" variant="tonal" size="small">{{ selectedTahun || 'Semua Tahun' }}</VChip>
                    </template>
                </VCardItem>

                <!-- Filters -->
                <VCardText>
                    <VRow>
                      <VCol cols="12" sm="4">
                            <VSelect
                                v-model="selectedKecamatan"
                                :items="kecamatanOptions"
                                label="Kecamatan"
                                hide-details="hide-details"
                                variant="outlined"
                                density="compact"
                                bg-color="surface"
                                prepend-inner-icon="bx-map"
                                clearable="clearable"
                                @update:modelValue="handleKecamatanChange"></VSelect>
                        </VCol>

                        <VCol cols="12" sm="4">
                            <VMenu  v-model="desaMenuOpen" offset-y :close-on-content-click="false">
                                <template v-slot:activator="{ props }">
                                    <VBtn
                                        v-bind="props"
                                        color="primary"
                                        variant="outlined"
                                        size="small"
                                        class="me-1 filter-btn"
                                        style="position: relative; min-width: 100%; height: 40px; display: flex; justify-content: space-between;"
                                    >
                                        <div class="d-flex align-center">
                                            <VIcon size="18" class="me-1">bx-home</VIcon>
                                            <span class="text-truncate" style="max-width: 120px;">
                                                {{ getDesaFilterLabel() }}
                                            </span>
                                        </div>
                                        
                                        <VIcon 
                                            v-if="selectedDesa || isUsingDefaultDesaFilter"
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
                                            @click="selectDesa(desa)"
                                            :active="desa.id_desa === selectedDesa?.id_desa"
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
                        </VCol>

                        <VCol cols="12" sm="4">
                            <VSelect
                                v-model="selectedTahun"
                                :items="tahunOptions"
                                label="Tahun"
                                hide-details="hide-details"
                                variant="outlined"
                                density="compact"
                                bg-color="surface"
                                prepend-inner-icon="bx-calendar"
                                clearable="clearable"></VSelect>
                        </VCol>
                    </VRow>

                    <VRow class="mt-2">
                        <VCol cols="12">
                            <VBtn
                                color="primary"
                                block="block"
                                @click="filterData"
                                prepend-icon="bx-filter"
                                variant="flat"
                                :loading="loading">
                                Terapkan Filter
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Total Anak -->
        <VCol cols="12" md="4">
            <VCard elevation="1" class="rounded-lg h-100" @click="navigateToStuntingData">
                <VCardItem>
                    <template #prepend>
                        <VAvatar size="48" color="primary" variant="tonal" class="elevation-0 mr-4">
                            <VIcon icon="bx-group" size="24" color="primary"></VIcon>
                        </VAvatar>
                    </template>

                    <div>
                        <div class="text-body-2 text-medium-emphasis">Total Anak Terdata</div>
                        <div class="text-h4 font-weight-bold">{{ totalAnak }}</div>
                        <div v-if="isLoggedIn" class="text-caption text-success">
                            <VIcon icon="bx-check-circle" size="14" class="mr-1"></VIcon>
                            {{ handledCount }} anak sudah ditangani
                        </div>
                    </div>
                </VCardItem>

                <VDivider></VDivider>

                <VCardText class="pt-3">
                    <VRow>
                        <VCol
                            v-for="(item, index) in genderData"
                            :key="index"
                            cols="6"
                            class="d-flex align-center">
                            <VAvatar
                                size="36"
                                rounded="rounded"
                                variant="tonal"
                                :color="item.avatarColor === 'info' ? 'primary' : 'error'"
                                class="mr-3 elevation-0">
                                <VIcon :icon="item.avatarIcon" size="18"></VIcon>
                            </VAvatar>

                            <div>
                                <div class="text-caption text-medium-emphasis">{{ item.title }}</div>
                                <div class="text-body-1 font-weight-medium">{{ item.value }}</div>
                            </div>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Trend dan Statistik Umur -->
        <VCol cols="12" md="4">
            <!-- Trend Chart -->
            <VCard elevation="1" class="rounded-lg mb-4">
                <VCardItem>
                    <VCardTitle class="text-subtitle-1">Tren Tahunan Stunting</VCardTitle>
                </VCardItem>
                <VCardText>
                    <VueApexCharts
                        type="line"
                        height="180"
                        :options="trendChartOptions"
                        :series="trendSeries"/>
                </VCardText>
            </VCard>

            <!-- Statistik umur Chart -->
            <VCard elevation="1" class="rounded-lg">
                <VCardItem>
                    <VCardTitle class="text-subtitle-1">Statistik Stunting Berdasarkan Rentang Umur</VCardTitle>
                </VCardItem>
                <VCardText>
                    <VueApexCharts
                        type="bar"
                        :height="180"
                        :options="chartOptions"
                        :series="series"
                        class="rounded-lg overflow-hidden"/>
                </VCardText>
            </VCard>
        </VCol>

        <!-- Map -->
        <VCol cols="12" md="8">
          <VCard elevation="1" class="rounded-lg">
            <VCardItem>
              <VCardTitle class="text-subtitle-1">
                <div class="d-flex align-center">
                  <VIcon icon="bx-child" class="me-2" />
                  Peta Sebaran Stunting
                </div>
              </VCardTitle>
              <template v-slot:append>
                <div class="d-flex gap-2">
                  <VMenu v-model="showMapLayersMenu" location="bottom end">
                    <template v-slot:activator="{ props }">
                      <VBtn
                        v-bind="props"
                        color="primary"
                        variant="outlined"
                        size="small"
                        class="me-1 filter-btn"
                        style="position: relative; height: 40px;"
                        prepend-icon="bx-layer"
                      >
                        <span class="text-truncate" style="max-width: 120px;">
                          {{ currentBaseMapName }}
                        </span>
                      </VBtn>
                    </template>
                    <VList width="200">
                      <VListItem
                        v-for="layer in baseMapLayers"
                        :key="layer.id"
                        @click="changeBaseMap(layer.id)"
                        :active="currentBaseMap === layer.id"
                      >
                        <template v-slot:prepend>
                          <VIcon :icon="layer.icon" />
                        </template>
                        <VListItemTitle>{{ layer.name }}</VListItemTitle>
                        <template v-slot:append v-if="currentBaseMap === layer.id">
                          <VIcon icon="bx-check" color="primary" />
                        </template>
                      </VListItem>
                    </VList>
                  </VMenu>
                </div>
              </template>
            </VCardItem>

            <VCardText class="pa-4">
              <!-- Map Container with conditional blur -->
              <div
                ref="mapContainer"
                style="height: 465px; width: 100%;"
                class="rounded-lg overflow-hidden"
              ></div>

              <!-- Map Legend -->
              <div class="mt-3 d-flex flex-wrap justify-center gap-2">
                <VChip 
                  size="small" 
                  color="error" 
                  variant="flat" 
                  label
                  style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                  Sangat Tinggi (≥40%)
                </VChip>
                
                <VChip 
                  size="small" 
                  color="warning" 
                  variant="flat" 
                  label
                  style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                  Tinggi (30-39.9%)
                </VChip>
                
                <VChip
                  size="small"
                  variant="flat"
                  label
                  style="background-color: #FFFF00; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                  Sedang (20-29.9%)
                </VChip>
                
                <VChip 
                  size="small" 
                  color="success" 
                  variant="flat" 
                  label
                  style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                  Rendah (<20%)
                </VChip>
                
                <VChip 
                  size="small" 
                  color="#D1D5DB" 
                  class="text-grey" 
                  variant="flat" 
                  label
                  style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                  Data Tidak Tersedia
                </VChip>
              </div>
            </VCardText>
          </VCard>
        </VCol>
    </VRow>
</template>
  
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useTheme } from 'vuetify';
import VueApexCharts from 'vue3-apexcharts';
import 'leaflet/dist/leaflet.css';
import apiClient from '@/services/api';
import { useAuthStore } from '@/services/auth';
import { useRouter } from 'vue-router';

const router = useRouter();

const authStore = useAuthStore();
const theme = useTheme();

const isDarkMode = computed(() => theme.global.current.value.dark);

const isLoggedIn = computed(() => authStore.isAuthenticated);
const loggedInUser = computed(() => authStore.user || JSON.parse(localStorage.getItem('user')));

const genderData = ref([]);
const totalAnak = ref(0);
const ageGroupData = ref([]);
const series = ref([]);
const chartOptions = ref({});
const yearlyTrend = ref([]);
const trendSeries = ref([]);
const mapContainer = ref(null);
const selectedKecamatan = ref(null);
const selectedDesa = ref(null);
const selectedTahun = ref(null);
const kecamatanOptions = ref([]);
const desaOptions = ref([]);
const tahunOptions = ref([]);
const handledCount = ref(0);
const availableDesas = ref([]);
const filteredDesaList = ref([]);
const desaSearchQuery = ref('');
const isUsingDefaultDesaFilter = ref(false);
const showAllDesa = ref(false);
const desaMenuOpen = ref(false);
const currentYear = computed(() => new Date().getFullYear());

const showMapOptions = ref(false);
const isBlurred = ref(false);
const selectedLocationData = ref(null);
const selectedKecamatanGeo = ref(null);
const currentBaseMap = ref('osm');

let trafficLayer = null;
let baseLayers = {};
let markersLayer = null;

const showMapLayersMenu = ref(false);

const baseMapLayers = [
  { 
    id: 'osm', 
    name: 'Street Map', 
    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', 
    attribution: '© OpenStreetMap contributors',
    icon: 'bx-map'
  },
  { 
    id: 'satellite', 
    name: 'Satellite', 
    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 
    attribution: '© Esri',
    icon: 'bx-map-alt'
  },
  { 
    id: 'topo', 
    name: 'Topographic', 
    url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', 
    attribution: '© OpenTopoMap',
    icon: 'bx-landscape'
  },
  { 
    id: 'dark', 
    name: 'Dark Mode', 
    url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png', 
    attribution: '© Stadia Maps',
    icon: 'bx-moon'
  },
];

const loading = ref(false);

const trendChartOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  markers: {
    size: 5,
    hover: { size: 7 }
  },
  colors: ['#4F46E5', '#10B981'],
  xaxis: {
    categories: yearlyTrend.value.map(item => item.year.toString()),
    labels: {
      style: {
        colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
        fontSize: '11px',
        fontFamily: 'Inter, sans-serif',
      }
    },
    title: {
      text: 'Tahun',
      style: {
        color: isDarkMode.value ? '#E2E8F0' : '#1E293B',
        fontSize: '11px',
        fontFamily: 'Inter, sans-serif',
      }
    }
  },
  yaxis: [{
    title: {
      text: 'Kasus',
      style: {
        color: isDarkMode.value ? '#E2E8F0' : '#1E293B',
        fontSize: '11px',
        fontFamily: 'Inter, sans-serif',
      }
    },
    min: 0,
    labels: {
      style: {
        colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
        fontSize: '11px',
        fontFamily: 'Inter, sans-serif',
      }
    }
  }],
  tooltip: {
    shared: true,
    intersect: false,
    theme: isDarkMode.value ? 'dark' : 'light',
    y: {
      formatter: function (value, { seriesIndex }) {
        return seriesIndex === 0 ? `${value} Kasus` : `${value}%`;
      }
    }
  },
  legend: { 
    show: false,
    labels: {
      colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
    }
  },
  grid: {
    borderColor: isDarkMode.value ? '#334155' : '#F3F4F6',
    strokeDashArray: 4,
    padding: { bottom: 10 }
  }
}));

const navigateToStuntingData = () => {
  const queryParams = {};
  
  if (selectedTahun.value) {
    queryParams.tahun = selectedTahun.value;
  }
  
  if (selectedKecamatan.value) {
    queryParams.kecamatan = selectedKecamatan.value;
  }
  
  if (selectedDesa.value) {
    queryParams.desa = selectedDesa.value.id_desa;
  }
  
  router.push({
    name: 'DataAnakStunting',
    query: queryParams
  });
};

const getDesaFilterLabel = () => {
  if (selectedDesa.value) return selectedDesa.value.nama_desa;
  if (isUsingDefaultDesaFilter.value) {
    const defaultDesa = availableDesas.value.find(d => d.id_desa === loggedInUser.value?.id_desa);
    return defaultDesa ? `Default: ${defaultDesa.nama_desa}` : 'Desa';
  }
  return 'Semua Desa';
};

const filterDesaList = () => {
  if (!desaSearchQuery.value) {
    filteredDesaList.value = [...availableDesas.value];
    return;
  }
  
  const searchTerm = desaSearchQuery.value.toLowerCase();
  filteredDesaList.value = availableDesas.value.filter(desa => {
    const namaDesaLower = (desa.nama_desa || '').toLowerCase();
    const kecamatanLower = (desa.kecamatan || '').toLowerCase();
    const puskesmasLower = (desa.puskesmas || '').toLowerCase();
    
    return namaDesaLower.includes(searchTerm) || 
           kecamatanLower.includes(searchTerm) || 
           puskesmasLower.includes(searchTerm);
  });
};

const selectDesa = (desa) => {
  selectedDesa.value = desa;
  selectedKecamatan.value = availableDesas.value.find(d => d.id_desa === desa.id_desa)?.id_kecamatan || null;
  isUsingDefaultDesaFilter.value = false;
  showAllDesa.value = false;
  desaMenuOpen.value = false;
  filterData();
};

const fetchInitialData = async () => {
  try {
    loading.value = true;
    const params = {};
    
    selectedTahun.value = currentYear.value;

    if (loggedInUser.value?.id_desa) {
      params.desa = loggedInUser.value.id_desa;
      isUsingDefaultDesaFilter.value = true;
    }
    
    const response = await apiClient.get('/dashboard/anak-stunting', { params });
    processDashboardData(response.data.data);

    kecamatanOptions.value = [
      { value: null, title: 'Semua Kecamatan' },
      ...response.data.data.filter_options.kecamatan.map(k => ({
        value: k.id_kecamatan,
        title: k.nama_kecamatan
      }))
    ];

    availableDesas.value = response.data.data.filter_options.desa.map(d => ({
      id_desa: d.id_desa,
      nama_desa: d.nama_desa,
      kecamatan: d.kecamatan,
      puskesmas: d.puskesmas,
      id_kecamatan: d.id_kecamatan
    }));
    
    filteredDesaList.value = [...availableDesas.value];

    tahunOptions.value = response.data.data.filter_options.tahun
      .sort((a, b) => b - a)
      .map(t => ({ 
        value: t, 
        title: t.toString() 
      }));

    if (loggedInUser.value?.id_desa) {
      const desaTerpilih = response.data.data.filter_options.desa.find(
        d => d.id_desa === loggedInUser.value.id_desa
      );
      if (desaTerpilih) {
        selectedKecamatan.value = desaTerpilih.id_kecamatan;
        selectedDesa.value = {
          id_desa: desaTerpilih.id_desa,
          nama_desa: desaTerpilih.nama_desa,
          kecamatan: desaTerpilih.kecamatan,
          puskesmas: desaTerpilih.puskesmas,
          id_kecamatan: desaTerpilih.id_kecamatan
        };
      }
    } else {
      selectedKecamatan.value = null;
      selectedDesa.value = null;
      isUsingDefaultDesaFilter.value = false;
    }
  } catch (error) {
    console.error('Error fetching initial data:', error);
  } finally {
    loading.value = false;
  }
};

const filterData = async () => {
  try {
    loading.value = true;
    selectedKecamatanGeo.value = null;

    const params = {
      tahun: selectedTahun.value || currentYear.value,
      kecamatan: selectedKecamatan.value
    };

    if (selectedDesa.value && !isUsingDefaultDesaFilter.value) {
      params.desa = selectedDesa.value.id_desa;
    }
    
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });
    
    const response = await apiClient.get('/dashboard/anak-stunting', { params });
    
    if (response.data.status === 'error') {
      throw new Error(response.data.message);
    }
    
    processDashboardData(response.data.data);
    updateMap(response.data.data.prevalensi_by_kecamatan);
  } catch (error) {
    console.error('Error filtering data:', error);
  } finally {
    loading.value = false;
  }
};

const handleKecamatanChange = async (newVal) => {
  try {
    selectedDesa.value = null;
    selectedKecamatanGeo.value = null;
    
    if (!newVal) {
      if (loggedInUser.value?.id_desa) {
        const response = await apiClient.get('/dashboard/anak-stunting', {
          params: { desa: loggedInUser.value.id_desa }
        });
        
        const desaTerpilih = response.data.data.filter_options.desa.find(
          d => d.id_desa === loggedInUser.value.id_desa
        );
        
        if (desaTerpilih) {
          selectedKecamatan.value = desaTerpilih.id_kecamatan;
          selectedDesa.value = {
            id_desa: desaTerpilih.id_desa,
            nama_desa: desaTerpilih.nama_desa,
            kecamatan: desaTerpilih.kecamatan,
            puskesmas: desaTerpilih.puskesmas,
            id_kecamatan: desaTerpilih.id_kecamatan
          };
          isUsingDefaultDesaFilter.value = true;
        }
      } else {
        isUsingDefaultDesaFilter.value = false;
        showAllDesa.value = true;
      }
      return;
    }
    
    isUsingDefaultDesaFilter.value = false;
    showAllDesa.value = false;
    
    filteredDesaList.value = availableDesas.value.filter(
      desa => desa.id_kecamatan === newVal
    );
    
  } catch (error) {
    console.error('Error fetching desa options:', error);
  }
};

const resetDesaFilter = () => {
  selectedDesa.value = null;
  desaSearchQuery.value = '';
  isUsingDefaultDesaFilter.value = false;
  
  if (loggedInUser.value?.id_desa) {
    const defaultDesa = availableDesas.value.find(d => d.id_desa === loggedInUser.value?.id_desa);
    if (defaultDesa) {
      selectedKecamatan.value = defaultDesa.id_kecamatan;
    }
  }
  
  filterData();
};

const processDashboardData = (data) => {
  totalAnak.value = data.total;
  handledCount.value = data.handled;
  const ageGroups = data.age_groups || [];
  yearlyTrend.value = data.yearly_trend || [];

  const allAgeGroups = [
    "0-5 bulan", 
    "6-11 bulan", 
    "12-23 bulan", 
    "24-59 bulan", 
    ">59 bulan"
  ];

  const maleData = allAgeGroups.map(() => 0);
  const femaleData = allAgeGroups.map(() => 0);
  const categories = [...allAgeGroups];

  trendSeries.value = [{
    name: 'Jumlah Kasus Stunting',
    type: 'line',
    data: yearlyTrend.value.map(item => item.total_stunting)
  }];

  ageGroups.forEach(group => {
    const ageIndex = allAgeGroups.indexOf(group.age_group);
    if (ageIndex !== -1) {
      if (group.jenis_kelamin === 'L') {
        maleData[ageIndex] = group.total;
      } else if (group.jenis_kelamin === 'P') {
        femaleData[ageIndex] = group.total;
      }
    }
  });

  series.value = [
    { name: 'Laki-laki', data: maleData },
    { name: 'Perempuan', data: femaleData }
  ];

  updateChartOptions(categories);

  const totalMale = maleData.reduce((sum, val) => sum + val, 0);
  const totalFemale = femaleData.reduce((sum, val) => sum + val, 0);

  genderData.value = [
    {
      title: 'Laki-laki',
      value: totalMale,
      avatarColor: 'info',
      avatarIcon: 'bx-male'
    }, 
    {
      title: 'Perempuan',
      value: totalFemale,
      avatarColor: 'error',
      avatarIcon: 'bx-female'
    }
  ];

  ageGroupData.value = allAgeGroups.map((ageGroup, index) => ({
    age_group: ageGroup,
    male: maleData[index],
    female: femaleData[index],
    total: maleData[index] + femaleData[index]
  }));
};

const updateChartOptions = (categories) => {
  chartOptions.value = {
    chart: {
      type: 'bar',
      toolbar: { show: false },
      fontFamily: 'Inter, sans-serif',
      stacked: true,
      background: 'transparent',
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '60%',
        endingShape: 'rounded',
        borderRadius: 4
      }
    },
    dataLabels: { enabled: false },
    stroke: { show: false },
    xaxis: {
      categories: categories,
      labels: {
        style: {
          colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
          fontSize: '11px',
          fontFamily: 'Inter, sans-serif',
        }
      },
      title: {
        text: 'Rentang Umur',
        style: {
          color: isDarkMode.value ? '#E2E8F0' : '#1E293B',
          fontSize: '11px',
          fontFamily: 'Inter, sans-serif',
        }
      }
    },
    yaxis: {
      title: {
        text: 'Jumlah Anak',
        style: {
          color: isDarkMode.value ? '#E2E8F0' : '#1E293B',
          fontSize: '11px',
          fontFamily: 'Inter, sans-serif',
        }
      },
      labels: {
        style: {
          colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
          fontSize: '11px',
          fontFamily: 'Inter, sans-serif',
        }
      }
    },
    colors: ['#4F46E5', '#F43F5E'],
    markers: {
      size: 5,
      colors: ['#4F46E5', '#F43F5E'],
      strokeColors: '#fff',
      strokeWidth: 2
    },
    tooltip: {
      enabled: true,
      theme: isDarkMode.value ? 'dark' : 'light',
      y: { formatter: (value) => `${value} Anak` }
    },
    grid: {
      borderColor: isDarkMode.value ? '#334155' : '#F3F4F6',
      strokeDashArray: 4,
      padding: { bottom: 10 }
    },
    legend: {
      position: 'top',
      horizontalAlign: 'right',
      floating: true,
      fontFamily: 'Inter, sans-serif',
      fontSize: '12px',
      labels: {
        colors: isDarkMode.value ? '#E2E8F0' : '#1E293B',
      },
      markers: { radius: 12 }
    }
  };
};

const currentBaseMapName = computed(() => {
  const layer = baseMapLayers.find(l => l.id === currentBaseMap.value);
  return layer ? layer.name : 'Peta';
});

let map = null;
let geojsonLayer = null;

const formatNumber = (num) => {
  if (num === null || num === undefined) return 'Tidak tersedia';
  return num.toLocaleString('id-ID');
};

const changeBaseMap = (layerId) => {
  if (!map || !baseLayers) return;
  
  Object.values(baseLayers).forEach(layer => {
    if (map.hasLayer(layer)) {
      map.removeLayer(layer);
    }
  });
  
  if (baseLayers[layerId]) {
    map.addLayer(baseLayers[layerId]);
  }
  
  currentBaseMap.value = layerId;
  showMapLayersMenu.value = false;
};

const initializeMap = async () => {
  if (typeof window !== 'undefined') {
    const L = await import('leaflet');

    if (mapContainer.value) {
      map = L.map(mapContainer.value).setView([-7.6386, 110.9500], 10.5);
      
      baseLayers = {};
      baseMapLayers.forEach(layer => {
        baseLayers[layer.id] = L.tileLayer(layer.url, {
          attribution: layer.attribution,
          maxZoom: 19
        });
      });
    
      baseLayers[currentBaseMap.value].addTo(map);

      trafficLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m@221097413,traffic&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '© Google'
      });

      markersLayer = L.layerGroup().addTo(map);

      try {
        const response = await apiClient.get('/dashboard/anak-stunting', {
          params: { tahun: currentYear.value }
        });
        if (response.data.status === 'success') {
          updateMap(response.data.data.prevalensi_by_kecamatan);
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    }
  }
};

const updateMap = async (stuntingData, filterYear = null) => {
  if (!map) return;

  if (geojsonLayer) {
    map.removeLayer(geojsonLayer);
    geojsonLayer = null;
  }

  try {
    const currentData = Array.isArray(stuntingData) ? stuntingData : [];
    const response = await fetch('/kecamatan.geojson');
    const geojsonData = await response.json();
    const L = await import('leaflet');

    let currentHighlightedLayer = null;

    geojsonData.features = geojsonData.features.map((feature) => {
      const kecamatan = currentData.find(k => k.id_kecamatan == feature.properties.id_kecamatan);
      
      if (kecamatan) {
        feature.properties = {
          ...feature.properties,
          ...kecamatan,
          fill: kecamatan.fill || '#D1D5DB'
        };
      } else {
        feature.properties.fill = '#D1D5DB';
        feature.properties.total_stunting = 0;
        feature.properties.total_anak = 0;
        feature.properties.prevalensi = null;
        feature.properties.kategori = filterYear ? `Data ${filterYear} tidak tersedia` : 'Data tidak tersedia';
      }
      
      return feature;
    });

    geojsonLayer = L.geoJSON(geojsonData, {
      style: (feature) => ({
        fillColor: feature.properties.fill,
        weight: 1,
        opacity: 1,
        color: isDarkMode.value ? '#64748B' : '#E5E7EB',
        dashArray: feature.properties.kategori?.includes('tidak tersedia') ? '3' : '0',
        fillOpacity: isBlurred.value ? 0.3 : 0.7,
        zIndex: 1
      }),
      onEachFeature: (feature, layer) => {
        if (feature.properties) {
          const popupContent = `
            <div style="min-width: 200px;">
              <h4 style="margin: 0 0 8px 0; font-size: 16px; color: #1E293B; 
                text-shadow: 1px 1px 2px rgba(255,255,255,0.8);">
                ${feature.properties.nama_kecamatan}
                ${filterYear ? `<span style="font-size: 12px; color: #64748B;"> (${filterYear})</span>` : ''}
              </h4>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div>
                  <span style="font-size: 12px; color: #64748B; 
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Total Anak:</span>
                  <p style="margin: 4px 0; font-weight: 600; color: #1E293B;
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">
                    ${formatNumber(feature.properties.total_anak)}
                  </p>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748B; 
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Jumlah Stunting:</span>
                  <p style="margin: 4px 0; font-weight: 600; color: #1E293B;
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">
                    ${formatNumber(feature.properties.total_stunting)}
                  </p>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748B; 
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Prevalensi:</span>
                  <p style="margin: 4px 0; font-weight: 600; color: white;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.8); 
                    background: ${getPrevalenceColor(feature.properties.prevalensi)}; 
                    padding: 2px 6px; border-radius: 4px; display: inline-block;">
                    ${feature.properties.prevalensi !== null ? `${feature.properties.prevalensi}%` : 'N/A'}
                  </p>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748B; 
                    text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Kategori:</span>
                  <p style="margin: 4px 0;">
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 12px; 
                      font-size: 12px; background: ${getCategoryBgColor(feature.properties.kategori)}; 
                      color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
                      font-weight: 500;">
                      ${feature.properties.kategori}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          `;

          layer.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'custom-popup',
            closeButton: true,
            autoClose: false,
            closeOnClick: false
          });

          layer.on('popupclose', () => {
            if (currentHighlightedLayer === layer) {
              layer.setStyle({
                weight: 1,
                color: isDarkMode.value ? '#64748B' : '#E5E7EB',
                dashArray: feature.properties.kategori?.includes('tidak tersedia') ? '3' : '0',
                fillOpacity: isBlurred.value ? 0.3 : 0.7
              });
              currentHighlightedLayer = null;
            }
          });

          layer.on('click', (e) => {
            if (currentHighlightedLayer && currentHighlightedLayer !== layer) {
              currentHighlightedLayer.setStyle({
                weight: 1,
                color: isDarkMode.value ? '#64748B' : '#E5E7EB',
                dashArray: currentHighlightedLayer.feature.properties.kategori?.includes('tidak tersedia') ? '3' : '0',
                fillOpacity: isBlurred.value ? 0.3 : 0.7,
                zIndex: 1
              });
              currentHighlightedLayer.closePopup();
            }

            layer.setStyle({
              weight: 3,
              color: '#696CFF',
              dashArray: '',
              fillOpacity: 0.8,
              zIndex: 1000
            });

            layer.bringToFront();
            layer.openPopup();
            currentHighlightedLayer = layer;
          });
        }
      }
    });

    geojsonLayer.addTo(map);

    if (selectedKecamatan.value && geojsonLayer) {
      geojsonLayer.eachLayer((layer) => {
        if (layer.feature.properties.id_kecamatan == selectedKecamatan.value) {
          selectedKecamatanGeo.value = layer;
          map.fitBounds(layer.getBounds(), {
            padding: [50, 50],
            maxZoom: 14
          });
        }
      });
    } else {
      map.fitBounds(geojsonLayer.getBounds(), {
        padding: [50, 50]
      });
    }

    setTimeout(() => {
      map.invalidateSize();
    }, 200);

  } catch (error) {
    console.error('Error updating map:', error);
  }
};

const getPrevalenceColor = (prevalence) => {
  if (prevalence === null) return '#D1D5DB';
  if (prevalence >= 40) return '#FF3E1D';
  if (prevalence >= 30) return '#FFA500';
  if (prevalence >= 20) return '#FFFF00';
  return '#71DD37';
};

const getCategoryBgColor = (category) => {
  switch (category) {
    case 'Sangat Tinggi': return '#FF3E1D';
    case 'Tinggi': return '#FFA500';
    case 'Sedang': return '#FFFF00';
    case 'Rendah': return '#71DD37';
    default: return '#D1D5DB';
  }
};

watch(selectedTahun, (newVal) => {
  filterData();
});

watch(
  () => theme.global.current.value.dark,
  (newIsDark) => {
    if (geojsonLayer) {
      geojsonLayer.eachLayer((layer) => {
        layer.setStyle({
          color: newIsDark ? '#64748B' : '#E5E7EB'
        });
      });
    }
  }
);

watch(
  () => isBlurred.value,
  (newBlurState) => {
    if (geojsonLayer) {
      geojsonLayer.eachLayer((layer) => {
        layer.setStyle({
          fillOpacity: newBlurState ? 0.3 : 0.7
        });
      });
    }
  }
);

watch(
  () => selectedKecamatan.value,
  (newVal) => {
    if (!newVal) {
      filteredDesaList.value = [...availableDesas.value];
    } else {
      filteredDesaList.value = availableDesas.value.filter(
        desa => desa.id_kecamatan === newVal
      );
    }
  },
  { immediate: true }
);

watch(selectedKecamatan, (newVal) => {
  if (!newVal) {
    filteredDesaList.value = [...availableDesas.value];
  } else {
    filteredDesaList.value = availableDesas.value.filter(
      desa => desa.id_kecamatan === newVal
    );
  }
});

onMounted(() => {
  fetchInitialData();
  initializeMap();
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
    map = null;
  }
});

</script>
  
<style scoped > 
.filter-btn {
  min-width: 150px;
  justify-content: space-between;
}

:deep(.v-list-item--active) {
  background-color: rgba(79, 70, 229, 0.08);
}

:deep(.v-list) {
  padding: 8px 0;
}

:deep(.v-list-item__prepend .v-icon) {
  margin-right: 12px;
  color: inherit;
}

:deep(.v-card) {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
}

:deep(.v-card-title) {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
}

:deep(.v-card-text) {
    padding-top: 0;
}

:deep(.v-select .v-field__input) {
    min-height: 38px;
    font-size: 14px;
}

:deep(.v-select .v-input__details) {
    display: none;
}

:deep(.leaflet-container) {
    z-index: 1 !important;
    position: relative;
}

:deep(.leaflet-popup) {
    z-index: 2;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

:deep(.v-btn) {
    text-transform: none;
    font-weight: 500;
}

:deep(.v-btn[title]) {
  position: relative;
}

:deep(.v-btn[title]::after) {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.75);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s;
}

:deep(.v-btn[title]:hover::after) {
  opacity: 1;
}

.h-100 {
    height: 100%;
}

.map-layers-menu {
    position: absolute;
    top: 60px;
    right: 20px;
    z-index: 1000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    width: 200px;
}

.map-layer-item {
    padding: 8px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.map-layer-item:hover {
    background-color: #f5f5f5;
}

.map-layer-item.active {
    background-color: #e3f2fd;
}

.map-layer-icon {
    margin-right: 8px;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 8px !important;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
  border: 2px solid #E2E8F0 !important; /* Border lebih tebal */
  padding: 12px !important;
  background: white !important;
}

:deep(.leaflet-popup-content) {
    margin: 12px;
    font-family: 'Inter', sans-serif;
}

:deep(.leaflet-popup-tip) {
    background: white;
}

.custom-marker {
    display: flex;
    justify-content: center;
    align-items: center;
}

.map-info-card {
    position: absolute;
    bottom: 80px;
    right: 20px;
    z-index: 1000;
    width: 250px;
}

.map-blur {
    filter: blur(4px);
    opacity: 0.8;
}

.map-options-card {
    position: absolute;
    top: 80px;
    right: 20px;
    z-index: 1000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

:deep(.leaflet-popup) {
    z-index: 500 !important;
    margin-bottom: 40px !important;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    margin-top: 20px !important;
}

:deep(.leaflet-popup-tip-container) {
    display: none !important;
}

:deep(.leaflet-popup-content-wrapper::after) {
    content: "" !important;
    position: absolute !important;
    bottom: -18px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 24px !important;
    height: 40px !important;
    background-image: url("data:image/svg+xml,%3Csvg width='24' height='40' viewBox='0 0 24 40' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 0C5.37258 0 0 5.37258 0 12C0 20.5 12 40 12 40C12 40 24 20.5 24 12C24 5.37258 18.6274 0 12 0Z' fill='%23696CFF'/%3E%3Cpath d='M12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6Z' fill='white'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-size: contain !important;
    z-index: 1000 !important;
    pointer-events: none !important;
}

:deep(.leaflet-popup-close-button) {
    position: absolute !important;
    right: 8px !important;
    top: 8px !important;
    color: #64748B !important;
    font-size: 18px !important;
    background: white !important;
    width: 24px !important;
    height: 24px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid #E2E8F0 !important;
    transition: all 0.2s ease !important;
    z-index: 1001 !important;
}

:deep(.leaflet-popup-close-button:hover) {
    color: #EF4444 !important;
    background: #FEE2E2 !important;
    border-color: #EF4444 !important;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
    border: 1px solid #E2E8F0 !important;
    padding-top: 8px !important;
}
</style>