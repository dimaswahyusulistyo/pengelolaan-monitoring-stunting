<template>
  <VRow>
    <VCol cols="12">
      <VCard elevation="1" class="rounded-lg">
        <VCardItem>
          <VCardTitle class="text-h5 font-weight-medium text-primary">Dashboard Keluarga Berisiko</VCardTitle>
          <template #append>
            <div class="d-flex align-center gap-2">
              <VChip color="primary" variant="tonal" size="small">{{ selectedTahun || 'Semua Tahun' }}</VChip>
            </div>
          </template>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol cols="12" sm="4">
              <VSelect v-model="selectedKecamatan"
                :items="[{ title: 'Semua Kecamatan', value: null }, ...kecamatanOptions]" label="Kecamatan"
                hide-details="auto" variant="outlined" density="comfortable" bg-color="surface"
                prepend-inner-icon="bx-map" clearable @update:modelValue="handleKecamatanChange" :loading="loading"
                style="height: 40px;"></VSelect>
            </VCol>
            <VCol cols="12" sm="4">
              <VMenu v-model="desaMenuOpen" ref="desaMenuRef" :close-on-content-click="false" offset-y>
                <template v-slot:activator="{ props }">
                  <VBtn v-bind="props" color="primary" variant="outlined" size="small" class="me-1 filter-btn"
                    style="position: relative; min-width: 100%; height: 40px; display: flex; justify-content: space-between;">
                    <div class="d-flex align-center">
                      <VIcon size="18" class="me-1">bx-home</VIcon>
                      <span class="text-truncate" style="max-width: 120px;">
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
                    <VTextField v-model="desaSearch" placeholder="Cari desa..." density="compact" hide-details clearable
                      prepend-inner-icon="bx-search" @click.stop @input="filterDesaList"
                      @click:clear="resetDesaSearch" />
                  </VListItem>

                  <VDivider />

                  <div style="max-height: 300px; overflow-y: auto;">
                    <VListItem v-for="desa in filteredDesaList" :key="desa.id_desa" @click="selectDesa(desa)"
                      :active="selectedDesa?.id_desa === desa.id_desa">
                      <VListItemTitle>
                        <div class="d-flex flex-column">
                          <span class="font-weight-medium">{{ desa.nama_desa }}</span>
                          <span class="text-caption text-secondary">
                            {{ desa.nama_kecamatan }} - {{ desa.puskesmas || '' }}
                          </span>
                        </div>
                      </VListItemTitle>
                    </VListItem>

                    <VListItem v-if="filteredDesaList.length === 0">
                      <VListItemTitle class="text-center text-secondary pa-4">
                        {{ desaSearch ? 'Desa tidak ditemukan' : 'Memuat data...' }}
                      </VListItemTitle>
                    </VListItem>
                  </div>

                  <VDivider />
                  <VListItem class="d-flex justify-end pa-2 gap-2">
                    <VBtn size="small" variant="text" @click="resetDesaFilter" density="comfortable">Reset</VBtn>
                    <VBtn size="small" color="primary" @click="desaMenuOpen = false" density="comfortable">Tutup</VBtn>
                  </VListItem>
                </VList>
              </VMenu>
            </VCol>

            <VCol cols="12" sm="4">
              <VSelect v-model="selectedTahun" :items="[{ title: 'Semua Tahun', value: null }, ...tahunOptions]"
                label="Tahun" hide-details variant="outlined" density="comfortable" bg-color="surface"
                prepend-inner-icon="bx-calendar" clearable style="height: 40px;" />
            </VCol>
          </VRow>

          <VRow class="mt-2">
            <VCol cols="12">
              <VBtn color="primary" block @click="fetchDashboardData" prepend-icon="bx-filter" variant="flat"
                :loading="loading">
                Terapkan Filter
              </VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="5">
      <VCard elevation="1" class="rounded-lg mb-4">
        <VCardItem>
          <template #prepend>
            <VAvatar size="48" color="primary" variant="tonal" class="elevation-0 mr-4">
              <VIcon icon="bx-group" size="24" color="primary"></VIcon>
            </VAvatar>
          </template>
          <div>
            <div>
              <VCardTitle class="text-h5 font-weight-medium">Total Keluarga Berisiko</VCardTitle>
            </div>
            <div class="text-h4 font-weight-bold">{{ dashboardData.total }}</div>
            <div class="text-caption text-medium-emphasis">
              <VIcon icon="bx-info-circle" size="14" class="mr-1"></VIcon> Berdasarkan data terkini
            </div>
            <div class="text-caption text-medium-emphasis">
              <VIcon icon="bx-calendar" size="14" class="mr-1"></VIcon> {{ currentTime }}
            </div>
          </div>
        </VCardItem>
      </VCard>

      <VCard elevation="1" class="rounded-lg">
        <VCardItem>
          <VCardTitle class="text-h5 font-weight-medium">Faktor Keluarga Berisiko</VCardTitle>
        </VCardItem>
        <VCardText class="pt-0">
          <div class="mb-4">
            <div class="text-caption text-medium-emphasis mb-2">Fasilitas Lingkungan Tidak Sehat</div>
            <VRow>
              <VCol>
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="error" class="mr-3 elevation-0">
                    <VIcon icon="bx-water" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">Keluarga Tidak Mempunyai</div>
                    <div class="text-body-1 font-weight-medium">Sumber Air Minum Utama yang Layak</div>
                    <div class="text-body-1 font-weight-bold">{{
                      dashboardData.fasilitas_stats.sumber_air_minum_tidak_layak }}</div>
                  </div>
                </div>
              </VCol>
              <VCol cols="12">
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="error" class="mr-3 elevation-0">
                    <VIcon icon="bx-home" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-caption text-medium-emphasis">Keluarga Tidak Mempunyai</div>
                    <div class="text-body-1 font-weight-medium">Jamban yang Layak</div>
                    <div class="text-body-1 font-weight-bold">{{ dashboardData.fasilitas_stats.jamban_tidak_layak }}
                    </div>
                  </div>
                </div>
              </VCol>
            </VRow>
          </div>

          <VDivider class="my-3" />

          <div>
            <div class="text-caption text-medium-emphasis mb-2">Pasangan Usia Subur (PUS) 4 Terlalu</div>
            <VRow>
              <VCol cols="12" sm="6">
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="warning" class="mr-3 elevation-0">
                    <VIcon icon="bx-face" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-body-1 font-weight-medium">Terlalu Muda</div>
                    <div class="text-caption text-medium-emphasis">(Umur Istri < 20 Tahun)</div>
                        <div class="text-body-1 font-weight-bold">{{ dashboardData.pus_stats.terlalu_muda }}</div>
                    </div>
                  </div>
              </VCol>
              <VCol cols="12" sm="6">
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="warning" class="mr-3 elevation-0">
                    <VIcon icon="bx-user" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-body-1 font-weight-medium">Terlalu Tua</div>
                    <div class="text-caption text-medium-emphasis">(Umur Istri 35 - 40 Tahun)</div>
                    <div class="text-body-1 font-weight-bold">{{ dashboardData.pus_stats.terlalu_tua }}</div>
                  </div>
                </div>
              </VCol>
              <VCol cols="12" sm="6">
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="warning" class="mr-3 elevation-0">
                    <VIcon icon="bx-time" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-body-1 font-weight-medium">Terlalu Dekat</div>
                    <div class="text-caption text-medium-emphasis">(< 2 Tahun)</div>
                        <div class="text-body-1 font-weight-bold">{{ dashboardData.pus_stats.terlalu_dekat }}</div>
                    </div>
                  </div>
              </VCol>
              <VCol cols="12" sm="6">
                <div class="d-flex align-center">
                  <VAvatar size="36" rounded variant="tonal" color="warning" class="mr-3 elevation-0">
                    <VIcon icon="bx-group" size="18"></VIcon>
                  </VAvatar>
                  <div>
                    <div class="text-body-1 font-weight-medium">Terlalu Banyak</div>
                    <div class="text-caption text-medium-emphasis">(≥ 3 Anak)</div>
                    <div class="text-body-1 font-weight-bold">{{ dashboardData.pus_stats.terlalu_banyak }}</div>
                  </div>
                </div>
              </VCol>
            </VRow>
          </div>

          <VDivider class="my-3" />

          <div>
            <div class="d-flex align-center">
              <VAvatar size="36" rounded variant="tonal" color="info" class="mr-3 elevation-0">
                <VIcon icon="bx-heart" size="18"></VIcon>
              </VAvatar>
              <div>
                <div class="text-body-1 font-weight-medium">Bukan Peserta KB Modern</div>
                <div class="text-body-1 font-weight-bold">{{ dashboardData.kb_stats.non_modern }}</div>
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="7">
      <VCard elevation="1" class="rounded-lg">
        <VCardItem>
          <VCardTitle class="text-h5 font-weight-medium">Peta Sebaran Keluarga Berisiko</VCardTitle>
          <template #append>
            <VMenu v-model="showMapLayersMenu" location="bottom end">
              <template v-slot:activator="{ props }">
                <VBtn v-bind="props" color="primary" variant="outlined" size="small" class="me-1 filter-btn"
                  style="position: relative; height: 40px;" prepend-icon="bx-layer">
                  <span class="text-truncate" style="max-width: 120px;">
                    {{ currentBaseMapName }}
                  </span>
                </VBtn>
              </template>
              <VList width="200">
                <VListItem v-for="layer in baseMapLayers" :key="layer.id" @click="changeBaseMap(layer.id)"
                  :active="currentBaseMap === layer.id">
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
          </template>
        </VCardItem>
        <VCardText class="pa-4">
          <div ref="mapContainer" style="height: 568px; width: 100%;" class="rounded-lg overflow-hidden"></div>
          <div class="mt-3 d-flex flex-wrap justify-center gap-2">
            <VChip size="small" color="error" variant="flat" label
              style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
              Sangat Tinggi (≥40%)
            </VChip>
            <VChip size="small" color="warning" variant="flat" label
              style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
              Tinggi (30-39%)
            </VChip>
            <VChip size="small" variant="flat" label
              style="background-color: #FFFF00; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
              Sedang (20-29%)
            </VChip>
            <VChip size="small" color="success" variant="flat" label
              style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
              Rendah (<20%) 
            </VChip>
            <VChip size="small" color="#D1D5DB" class="text-grey" variant="flat" label
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
import 'leaflet/dist/leaflet.css';
import apiClient from '@/services/api';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();
const theme = useTheme();
const isDarkMode = computed(() => theme.global.current.value.dark);
const isLoggedIn = computed(() => authStore.isAuthenticated);
const loggedInUser = computed(() => authStore.user || JSON.parse(localStorage.getItem('user')));

const mapContainer = ref(null);
const loading = ref(false);
const currentTime = ref(new Date().toLocaleString('id-ID', {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
}));

const selectedKecamatan = ref(null);
const selectedDesa = ref(null);
const selectedTahun = ref(null);
const desaSearch = ref('');
const desaMenuRef = ref(null);
const desaMenuOpen = ref(false);
const selectedKecamatanGeo = ref(null);
const availableDesas = ref([]);
const filteredDesaList = ref([]);
const isUsingDefaultDesaFilter = ref(false);

const dashboardData = ref({
  total: 0,
  fasilitas_stats: {
    sumber_air_minum_tidak_layak: 0,
    jamban_tidak_layak: 0
  },
  pus_stats: {
    terlalu_muda: 0,
    terlalu_tua: 0,
    terlalu_dekat: 0,
    terlalu_banyak: 0
  },
  kb_stats: {
    non_modern: 0
  },
  prevalensi_by_kecamatan: [],
  filter_options: {
    kecamatan: [],
    desa: [],
    tahun: []
  }
});

const showMapLayersMenu = ref(false);
const currentBaseMap = ref('osm');

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

let map = null;
let geojsonLayer = null;
let baseLayers = {};

const currentBaseMapName = computed(() => {
  const layer = baseMapLayers.find(l => l.id === currentBaseMap.value);
  return layer ? layer.name : 'Peta';
});

const kecamatanOptions = computed(() => {
  return dashboardData.value.filter_options.kecamatan.map(k => k.nama_kecamatan);
});

const tahunOptions = computed(() => {
  if (!dashboardData.value.filter_options?.tahun) return [];

  return dashboardData.value.filter_options.tahun.map(year => ({
    title: year.toString(),
    value: year
  }));
});

const getDesaFilterLabel = () => {
  if (selectedDesa.value) return selectedDesa.value.nama_desa;
  if (isUsingDefaultDesaFilter.value) {
    const defaultDesa = availableDesas.value.find(d => d.id_desa === loggedInUser.value?.id_desa);
    return defaultDesa ? `Default: ${defaultDesa.nama_desa}` : 'Semua Desa';
  }
  return 'Semua Desa';
};

const fetchAllDesa = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/desa');
    if (response.data && response.data.data) {
      availableDesas.value = response.data.data.map(item => ({
        id_desa: item.id,
        nama_desa: item.desa,
        nama_kecamatan: item.kecamatan,
        puskesmas: item.puskesmas,
        id_kecamatan: item.id_kecamatan
      }));
      filteredDesaList.value = [...availableDesas.value];
    }
  } catch (err) {
    console.error('Error fetching all desa:', err);
  } finally {
    loading.value = false;
  }
};

const filterAvailableDesa = () => {
  let filteredDesas = [...availableDesas.value];

  if (selectedKecamatan.value) {
    filteredDesas = filteredDesas.filter(desa =>
      desa.nama_kecamatan === selectedKecamatan.value
    );
  }

  if (desaSearch.value) {
    const searchTerm = desaSearch.value.toLowerCase();
    filteredDesas = filteredDesas.filter(desa => {
      const namaDesaLower = (desa.nama_desa || '').toLowerCase();
      const kecamatanLower = (desa.nama_kecamatan || '').toLowerCase();
      const puskesmasLower = (desa.puskesmas || '').toLowerCase();

      return namaDesaLower.includes(searchTerm) ||
        kecamatanLower.includes(searchTerm) ||
        puskesmasLower.includes(searchTerm);
    });
  }

  filteredDesaList.value = filteredDesas;
};

const fetchDashboardData = async () => {
  try {
    loading.value = true;
    updateCurrentTime();

    const params = new URLSearchParams();

    if (selectedKecamatan.value) {
      const kecamatan = dashboardData.value.filter_options.kecamatan.find(
        k => k.nama_kecamatan === selectedKecamatan.value
      );
      if (kecamatan) {
        params.append('kecamatan', kecamatan.id_kecamatan);
      } else {
        params.append('kecamatan', selectedKecamatan.value);
      }
    }

    if (selectedDesa.value) {
      params.append('desa', selectedDesa.value.id_desa);
    }

    if (selectedTahun.value) {
      params.append('tahun', selectedTahun.value);
    }

    const response = await apiClient.get(`/dashboard/keluarga-berisiko?${params.toString()}`);

    if (response.data.status === 'success') {
      dashboardData.value = response.data.data;
      updateMap();
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', {
      error: error.message,
      config: error.config,
      response: error.response?.data
    });
  } finally {
    loading.value = false;
  }
};

const handleKecamatanChange = (newVal) => {
  selectedKecamatan.value = newVal;
  if (selectedDesa.value) {
    const desa = availableDesas.value.find(d =>
      d.id_desa === selectedDesa.value.id_desa &&
      d.nama_kecamatan === newVal
    );
    if (!desa) {
      selectedDesa.value = null;
    }
  }
  filterAvailableDesa();
};

const selectDesa = (desa) => {
  selectedDesa.value = desa;
  selectedKecamatan.value = desa.nama_kecamatan;
  isUsingDefaultDesaFilter.value = false;
  desaMenuOpen.value = false;
};

const resetDesaFilter = (e) => {
  if (e) e.stopPropagation();
  selectedDesa.value = null;
  desaSearch.value = '';
  isUsingDefaultDesaFilter.value = false;
  filterAvailableDesa();
};

const filterDesaList = () => {
  filterAvailableDesa();
};

const resetDesaSearch = () => {
  desaSearch.value = '';
  filterAvailableDesa();
};

const updateCurrentTime = () => {
  currentTime.value = new Date().toLocaleString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
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
      map = L.map(mapContainer.value).setView([-7.6386, 110.9500], 10.4);

      baseLayers = {};
      baseMapLayers.forEach(layer => {
        baseLayers[layer.id] = L.tileLayer(layer.url, {
          attribution: layer.attribution,
          maxZoom: 19
        });
      });

      baseLayers[currentBaseMap.value].addTo(map);

      await updateMap();
    }
  }
};

const updateMap = async () => {
  if (!map || !dashboardData.value.prevalensi_by_kecamatan) return;

  if (geojsonLayer) {
    map.removeLayer(geojsonLayer);
  }

  try {
    const response = await fetch('/kecamatan.geojson');
    const geojsonData = await response.json();
    const L = await import('leaflet');

    selectedKecamatanGeo.value = null;

    geojsonData.features = geojsonData.features.map(feature => {
      const kecamatanData = dashboardData.value.prevalensi_by_kecamatan.find(
        k => k.id_kecamatan == feature.properties.id_kecamatan
      );

      return {
        ...feature,
        properties: {
          ...feature.properties,
          ...kecamatanData,
          fill: kecamatanData?.fill || '#D1D5DB',
          total: kecamatanData?.total || 0,
          total_keluarga: kecamatanData?.total_keluarga || null,
          prevalensi: kecamatanData?.prevalensi || null,
          kategori: kecamatanData?.kategori || 'Data tidak tersedia',
          nama_kecamatan: kecamatanData?.nama_kecamatan || feature.properties.name
        }
      };
    });

    geojsonLayer = L.geoJSON(geojsonData, {
      style: (feature) => ({
        fillColor: feature.properties.fill,
        weight: 1,
        opacity: 1,
        color: isDarkMode.value ? '#64748B' : '#E5E7EB',
        dashArray: feature.properties.kategori === 'Data tidak tersedia' ? '3' : '0',
        fillOpacity: 0.7
      }),
      onEachFeature: (feature, layer) => {
        layer.bindPopup(`
          <div style="min-width: 200px;">
            <h4 style="margin: 0 0 8px 0; font-size: 16px; color: #1E293B; 
              text-shadow: 1px 1px 2px rgba(255,255,255,0.8);">
              ${feature.properties.nama_kecamatan}
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
              <div>
                <span style="font-size: 12px; color: #64748B; 
                  text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Total Keluarga:</span>
                <p style="margin: 4px 0; font-weight: 600; color: #1E293B;
                  text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">
                  ${feature.properties.total_keluarga !== null ? feature.properties.total_keluarga : 'Tidak tersedia'}
                </p>
              </div>
              <div>
                <span style="font-size: 12px; color: #64748B; 
                  text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">Keluarga Berisiko:</span>
                <p style="margin: 4px 0; font-weight: 600; color: #1E293B;
                  text-shadow: 1px 1px 1px rgba(255,255,255,0.6);">
                  ${feature.properties.total || 'Tidak tersedia'}
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
                    ${feature.properties.kategori || 'Data tidak tersedia'}
                  </span>
                </p>
              </div>
            </div>
          </div>
        `);

        layer.on('click', (e) => {
          if (selectedKecamatanGeo.value) {
            selectedKecamatanGeo.value.setStyle({
              weight: 1,
              color: isDarkMode.value ? '#64748B' : '#E5E7EB',
              dashArray: feature.properties.kategori === 'Data tidak tersedia' ? '3' : '0',
              fillOpacity: 0.7
            });
          }

          layer.setStyle({
            weight: 3,
            color: '#4F46E5',
            dashArray: '',
            fillOpacity: 0.9
          });

          selectedKecamatanGeo.value = layer;
        });
      }
    }).addTo(map);

    if (selectedKecamatanGeo.value) {
      map.fitBounds(selectedKecamatanGeo.value.getBounds(), {
        padding: [50, 50],
        maxZoom: 12
      });
    } else {
      map.fitBounds(geojsonLayer.getBounds(), { padding: [50, 50] });
    }

    setTimeout(() => {
      map.invalidateSize();
    }, 100);
  } catch (error) {
    console.error('Error updating map:', error);
  }
};

const getPrevalenceColor = (prevalence) => {
  if (prevalence === null) return '#D1D5DB';
  if (prevalence >= 40) return '#FF5252';
  if (prevalence >= 30) return '#FB8C00';
  if (prevalence >= 20) return '#FDD835';
  return '#4CAF50';
};

const getCategoryBgColor = (category) => {
  switch (category) {
    case 'Sangat Tinggi': return '#FF5252';
    case 'Tinggi': return '#FB8C00';
    case 'Sedang': return '#FDD835';
    case 'Rendah': return '#4CAF50';
    default: return '#D1D5DB';
  }
};

const fetchInitialData = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/dashboard/keluarga-berisiko');

    if (response.data.status === 'success') {
      dashboardData.value = {
        ...response.data.data,
        filter_options: {
          kecamatan: response.data.data.filter_options?.kecamatan || [],
          desa: response.data.data.filter_options?.desa || [],
          tahun: response.data.data.filter_options?.tahun || []
        }
      };
      updateMap();
    }
  } catch (error) {
    console.error('Error fetching initial data:', error);
    console.error('Error response:', error.response?.data);
  } finally {
    loading.value = false;
  }
};

watch(() => theme.global.current.value.dark, () => {
  if (map) updateMap();
});

watch(selectedDesa, (newDesa) => {
  if (newDesa) {
    selectedKecamatan.value = newDesa.nama_kecamatan;
  }
});

watch(selectedKecamatan, (newVal) => {
  filterAvailableDesa();
});

onMounted(() => {
  fetchInitialData();
  fetchAllDesa();
  initializeMap();
  setInterval(updateCurrentTime, 60000);
});
</script>

<style scoped>
:deep(.v-card) {
  border: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

:deep(.v-btn) {
  text-transform: none;
  font-weight: 500;
}

.h-100 {
  height: 100%;
}
</style>