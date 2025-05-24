<template>
  <VDialog
      :modelValue="visible"
      @update:modelValue="$emit('update:visible', $event)"
      persistent="persistent"
      max-width="600px"
      :onClose="closeDialog">
      <VCard class="manual-form">
          <VCardTitle class="text-h5 pa-4 d-flex align-center">
              <span class="ml-4">Detail Data Anak Stunting</span>
              <VSpacer/>
              <VBtn icon="icon" variant="text" @click="onClose">
                  <i class="bx bx-x"></i>
              </VBtn>
          </VCardTitle>

          <VCardText>
              <!-- Card 1: Data Anak Stunting -->
              <div class="card-container">
                  <div :class="['flip-card', { 'flipped': isFlipped }]">
                      <div class="flip-card-inner">
                          <div class="flip-card-front">
                              <VCard class="health-card">
                                  <div class="health-card-header d-flex align-center pa-4 justify-space-between">
                                      <div class="d-flex align-center gap-4">
                                          <div class="garuda-icon">
                                              <img src="/icon-garuda.png" alt="Watermark"/>
                                          </div>
                                          <span class="text-h5 text-white">Data Anak Stunting</span>
                                      </div>
                                      <VBtn v-if="isAllowedToAdd" color="warning" @click="editItem(item)" class="rounded-pill px-5">
                                          <i class="bx bxs-edit-alt mr-2"></i>
                                          Edit
                                      </VBtn>
                                  </div>

                                  <div class="health-card-content pa-6 position-relative">
                                      <div class="watermark">
                                          <img src="/icon-kra.png" alt="Watermark"/>
                                      </div>

                                      <VRow>
                                          <VCol cols="12">
                                              <div v-if="isAllowedToEdit" class="d-flex justify-space-between align-center">
                                                  <div class="label-text">Status</div>
                                                  <VBtn
                                                      v-if="isAllowedToEdit"
                                                      :color="statusPenanganan?.status === 'Sudah ada penanganan' ? '#00897B' : 'error'"
                                                      outlined="outlined"
                                                      class="rounded-pill text-caption px-3"
                                                      small="small"
                                                      style="text-transform: none"
                                                      @click="isFlipped = !isFlipped">
                                                      {{ statusPenanganan?.status || 'Belum ada penanganan' }}
                                                      <span v-if="statusPenanganan?.status === 'Sudah ada penanganan'">
                                                          -
                                                          {{ formatDate(statusPenanganan.tanggal_status) }}
                                                      </span>
                                                  </VBtn>
                                                  <VBtn
                                                      v-else
                                                      :color="statusPenanganan?.status === 'Sudah ada penanganan' ? '#00897B' : 'error'"
                                                      outlined="outlined"
                                                      class="rounded-pill text-caption px-3"
                                                      small="small"
                                                      style="text-transform: none"
                                                      disabled="disabled">
                                                      {{ statusPenanganan?.status || 'Belum ada penanganan' }}
                                                      <span v-if="statusPenanganan?.status === 'Sudah ada penanganan'">
                                                          -
                                                          {{ formatDate(statusPenanganan.tanggal_status) }}
                                                      </span>
                                                  </VBtn>
                                              </div>
                                          </VCol>
                                      </VRow>

                                      <VRow>
                                          <VCol cols="12" md="6">
                                              <div v-if="isAllowedToAdd" class="mb-4">
                                                  <div class="label-text">Nomor NIK</div>
                                                  <div class="value-text">{{ item.nik_anak }}</div>
                                              </div>
                                              <div class="mb-4">
                                                  <div class="label-text">Nama</div>
                                                  <div class="value-text">{{ item.nama_anak }}</div>
                                              </div>
                                              <div class="mb-4">
                                                  <div class="label-text">Alamat</div>
                                                  <div class="value-text">{{ item.desa?.puskesmas?.kecamatan?.nama_kecamatan || 'N/A' }}</div>
                                              </div>
                                          </VCol>

                                          <VCol cols="12" md="6">
                                              <div class="mb-4">
                                                  <div class="label-text">Tanggal Lahir</div>
                                                  <div class="value-text">{{ formatDate(item.tanggal_lahir) }}</div>
                                              </div>
                                              <div class="mb-4">
                                                  <div class="label-text">Jenis Kelamin</div>
                                                  <div class="value-text">{{ item.jenis_kelamin }}</div>
                                              </div>
                                              <div class="mb-4">
                                                  <div class="label-text">Posyandu</div>
                                                  <div class="value-text">{{ item.posyandu }}</div>
                                              </div>
                                          </VCol>
                                      </VRow>
                                  </div>
                              </VCard>
                          </div>

                          <div class="flip-card-back">
                              <VCard class="health-card">
                                  <div class="health-card-header d-flex align-center pa-5">
                                      <div class="d-flex align-center gap-4">
                                          <div class="garuda-icon">
                                              <img src="/icon-garuda.png" alt="Watermark"/>
                                          </div>
                                          <span class="text-h5 text-white">Penanganan Status Stunting</span>
                                      </div>
                                  </div>

                                  <div class="health-card-content pa-6 position-relative">
                                      <div class="watermark">
                                          <img src="/icon-kra.png" alt="Watermark"/>
                                      </div>

                                      <VForm @submit.prevent="handleSubmit">
                                          <VRow>
                                              <VCol cols="12" md="12">
                                                  <VSelect
                                                      v-model="formPenanganan.status"
                                                      :items="statusOptions"
                                                      label="Status Penanganan"
                                                      item-title="text"
                                                      item-value="value"
                                                      :rules="[v => !!v || 'Status harus dipilih']"
                                                      required="required"/>
                                              </VCol>
                                              <VCol cols="12">
                                                  <VTextarea
                                                      v-model="formPenanganan.catatan"
                                                      label="Catatan Penanganan"
                                                      rows="2"
                                                      :placeholder="statusPenanganan?.keterangan ? statusPenanganan.keterangan : 'Tidak ada catatan sebelumnya'"
                                                      :model-value="formPenanganan.catatan || ''"/>
                                              </VCol>
                                          </VRow>

                                          <VRow class="mt-4">
                                              <VCol cols="6">
                                                  <VBtn type="submit" color="primary" block="block" :loading="isSubmitting">
                                                      Simpan Perubahan
                                                  </VBtn>
                                              </VCol>
                                              <VCol cols="6">
                                                  <VBtn color="grey" @click="isFlipped = false" block="block">
                                                      Kembali
                                                  </VBtn>
                                              </VCol>
                                          </VRow>
                                      </VForm>
                                  </div>
                              </VCard>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Card 2: Faktor Determinan -->
              <div class="card-container">
                  <VCard class="health-card">
                      <div class="health-card-header d-flex align-center pa-4">
                          <div class="d-flex align-center gap-4">
                              <div class="garuda-icon">
                                  <img src="/icon-garuda.png" alt="Watermark"/>
                              </div>
                              <span class="text-h5 text-white">Faktor Determinan</span>
                          </div>
                      </div>

                      <div class="health-card-content pa-6 position-relative">
                          <div class="watermark">
                              <img src="/icon-kra.png" alt="Watermark"/>
                          </div>

                          <!-- Dinas Kesehatan Section -->
                          <div class="department-section mb-6">
                              <div class="department-header d-flex align-center mb-4">
                                  <div class="department-icon">
                                      <v-icon icon="bx-first-aid" color="#00897B" size="large"></v-icon>
                                  </div>
                                  <div class="department-title text-h6 ml-2">Dinas Kesehatan</div>
                              </div>
                              
                              <VRow>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.imunisasi === '2'}">
                                          <div class="factor-label">Status Imunisasi</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.imunisasi === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.imunisasi === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.imunisasi === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ item.determinan?.imunisasi === '1' ? 'Lengkap' : 'Tidak Lengkap' }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.jkn === '3'}">
                                          <div class="factor-label">JKN</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.jkn === '3'}">
                                              <v-icon 
                                                  :icon="item.determinan?.jkn === '3' ? 'bx-error-circle' : 'bx-check-circle'" 
                                                  :color="item.determinan?.jkn === '3' ? 'error' : 'success'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getJKN(item.determinan?.jkn) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.kecacingan_menderita === '1'}">
                                          <div class="factor-label">Menderita Cacingan</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.kecacingan_menderita === '1'}">
                                              <v-icon 
                                                  :icon="item.determinan?.kecacingan_menderita === '2' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.kecacingan_menderita === '2' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.kecacingan_menderita) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.kecacingan_obat === '2'}">
                                          <div class="factor-label">Mendapat Obat Cacing</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.kecacingan_obat === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.kecacingan_obat === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.kecacingan_obat === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.kecacingan_obat) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.kebiasaan_merokok === '1'}">
                                          <div class="factor-label">Kebiasaan Merokok</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.kebiasaan_merokok === '1'}">
                                              <v-icon 
                                                  :icon="item.determinan?.kebiasaan_merokok === '2' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.kebiasaan_merokok === '2' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.kebiasaan_merokok) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.riwayat_ibu_hamil === '2'}">
                                          <div class="factor-label">Riwayat Ibu Hamil</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.riwayat_ibu_hamil === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.riwayat_ibu_hamil === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.riwayat_ibu_hamil === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.riwayat_ibu_hamil) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3">
                                          <div class="factor-label">Penyakit Penyerta</div>
                                          <div class="factor-value">
                                              <v-icon 
                                                  :icon="item.determinan?.penyakit_penyerta ? 'bx-error-circle' : 'bx-check-circle'" 
                                                  :color="item.determinan?.penyakit_penyerta ? 'warning' : 'success'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ item.determinan?.penyakit_penyerta || 'Tidak Ada' }}
                                          </div>
                                      </div>
                                  </VCol>
                              </VRow>
                          </div>

                          <!-- Dinas Sosial Section -->
                          <div class="department-section mb-6">
                              <div class="department-header d-flex align-center mb-4">
                                  <div class="department-icon">
                                      <v-icon icon="bx-group" color="#E53935" size="large"></v-icon>
                                  </div>
                                  <div class="department-title text-h6 ml-2">Dinas Sosial</div>
                              </div>
                              
                              <VRow>
                                  <VCol cols="12" md="4">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.status_ekonomi === '2'}">
                                          <div class="factor-label">Status Ekonomi</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.status_ekonomi === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.status_ekonomi === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.status_ekonomi === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ item.determinan?.status_ekonomi === '1' ? 'Gakin' : 'Non Gakin' }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="4">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.bpnt === '2'}">
                                          <div class="factor-label">BPNT</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.bpnt === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.bpnt === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.bpnt === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.bpnt) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="4">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.pkh === '2'}">
                                          <div class="factor-label">PKH</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.pkh === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.pkh === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.pkh === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.pkh) }}
                                          </div>
                                      </div>
                                  </VCol>
                              </VRow>
                          </div>

                          <!-- Dinas Pekerjaan Umum Section -->
                          <div class="department-section">
                              <div class="department-header d-flex align-center mb-4">
                                  <div class="department-icon">
                                      <v-icon icon="bx-water" color="#1976D2" size="large"></v-icon>
                                  </div>
                                  <div class="department-title text-h6 ml-2">Dinas Pekerjaan Umum</div>
                              </div>
                              
                              <VRow>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.sumber_air_bersih === '2'}">
                                          <div class="factor-label">Sumber Air Bersih</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.sumber_air_bersih === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.sumber_air_bersih === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.sumber_air_bersih === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.sumber_air_bersih) }}
                                          </div>
                                      </div>
                                  </VCol>
                                  <VCol cols="12" md="6">
                                      <div class="factor-field mb-3" :class="{'factor-issue': item.determinan?.jamban_sehat === '2'}">
                                          <div class="factor-label">Jamban Sehat</div>
                                          <div class="factor-value" :class="{'text-error': item.determinan?.jamban_sehat === '2'}">
                                              <v-icon 
                                                  :icon="item.determinan?.jamban_sehat === '1' ? 'bx-check-circle' : 'bx-error-circle'" 
                                                  :color="item.determinan?.jamban_sehat === '1' ? 'success' : 'error'" 
                                                  size="small" 
                                                  class="mr-1">
                                              </v-icon>
                                              {{ getYesNoText(item.determinan?.jamban_sehat) }}
                                          </div>
                                      </div>
                                  </VCol>
                              </VRow>
                          </div>
                      </div>
                  </VCard>
              </div>
          </VCardText>
      </VCard>
  </VDialog>

  <EditInputManual
      :visible="showManualForm"
      :item="itemToEdit"
      :onClose="closeEditDialog"
      @update:visible="showManualForm = $event"
      @refresh-data="handleRefreshAfterEdit"/>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue';
import EditInputManual from './forms/edit-input-manual.vue';

import apiClient from '@/services/api';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
    default: false
  },
  item: {
    type: Object,
    required: true
  },
  onClose: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['update:visible', 'closeDialog', 'refresh-data']);

const isFlipped = ref(false);
const isSubmitting = ref(false);
const showManualForm = ref(false);

const item = ref({});
const itemToEdit = ref({});
const statusPenanganan = ref(null);
const detailData = ref({});
const formPenanganan = reactive({
  status: null,
  catatan: ''
});

const statusOptions = [
  { text: 'Belum ada penanganan', value: 'Belum ada penanganan' },
  { text: 'Sudah ada penanganan', value: 'Sudah ada penanganan' }
];

const opdOptions = [
  { text: 'Dinas Kesehatan', value: 'dinas_kesehatan' },
  { text: 'Dinas Sosial', value: 'dinas_sosial' },
  { text: 'Dinas Pemberdayaan Masyarakat', value: 'dinas_pemberdayaan_masyarakat' }
];

const currentUser = computed(() => authStore.user);
const isAllowedToAdd = computed(() => {
  return ['Dinas Kesehatan', 'Kader Desa'].includes(currentUser.value?.role);
});

const isAllowedToEdit = computed(() => {
  return statusPenanganan.value?.canEdit;
});

const isInIntervention = computed(() => {
  return props.item?.is_intervention || false;
});

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const getYesNoText = (value) => {
  if (value === "1") return 'Ya';
  if (value === "2") return 'Tidak';
  return '-';
};

const getJKN = (value) => {
  const jknOptions = [
    { value: 1, title: 'BPJS Mandiri' },
    { value: 2, title: 'BPJS Pemerintah' },
    { value: 3, title: 'Tidak Punya' },
    { value: 4, title: 'Asuransi Swasta' }
  ];
  const selectedOption = jknOptions.find(option => option.value === Number(value));
  return selectedOption ? selectedOption.title : 'Tidak Diketahui';
};

const getStatusClass = (isPositive) => {
  return isPositive ? 'status-positive' : 'status-negative';
};

const getJKNIcon = (value) => {
  const numValue = Number(value);
  if (numValue === 1 || numValue === 2) return 'bx bx-check-circle';
  if (numValue === 3) return 'bx bx-error-circle';
  if (numValue === 4) return 'bx bx-shield-alt';
  return 'bx bx-question-mark';
};

const getJKNColor = (value) => {
  const numValue = Number(value);
  if (numValue === 1 || numValue === 2) return 'success';
  if (numValue === 3) return 'warning';
  if (numValue === 4) return 'info';
  return 'grey';
};

const loadDetailData = async (id_anak) => {
  try {
    const response = await apiClient.get(`/anak-stunting/${id_anak}`);
    detailData.value = response.data;
  } catch (error) {
    console.error('Failed to load detail data:', error);
  }
};

const fetchStatusPenanganan = async () => {
  try {
    resetForm();

    if (!props.item?.id_anak || !authStore.user?.id) return;
    
    const permissionCheck = await apiClient.get(
      `/anak-stunting/${props.item.id_anak}/check-edit-permission/${authStore.user.id}`
    );

    const response = await apiClient.get(
      `/status-penanganan/${props.item.id_anak}/${authStore.user.id}`
    );

    if (response.data.status === 'success') {
      statusPenanganan.value = {
        ...response.data.data,
        canEdit: permissionCheck.data.is_allowed
      } || {
        status: 'Belum ada penanganan',
        keterangan: null,
        tanggal_status: null,
        canEdit: permissionCheck.data.is_allowed
      };

      formPenanganan.status = statusPenanganan.value.status;
      formPenanganan.catatan = statusPenanganan.value.keterangan;
    }
  } catch (error) {
    console.error('Failed to load status:', error);
    statusPenanganan.value = {
      status: 'Belum ada penanganan',
      keterangan: null,
      tanggal_status: null,
      canEdit: false
    };
  }
};

const resetForm = () => {
  formPenanganan.status = null;
  formPenanganan.catatan = '';
  statusPenanganan.value = null;
  isFlipped.value = false;
};

const editItem = (item) => {
  itemToEdit.value = { ...item };
  showManualForm.value = true;
};

const handleRefreshAfterEdit = async () => {
  try {
    const response = await apiClient.get(`/anak-stunting/${props.item.id_anak}`);
    item.value = { ...response.data.data };
    itemToEdit.value = { ...response.data.data };
    await fetchStatusPenanganan();
    emit('refresh-data');
  } catch (error) {
    console.error('Error refreshing data:', error);
  }
};

const closeEditDialog = () => {
  showManualForm.value = false;
};

const handleSubmit = async () => {
  if (!isAllowedToEdit.value) {
    await Swal.fire({
      icon: 'error', 
      title: 'Tidak diizinkan', 
      text: 'Anda tidak memiliki hak untuk mengubah status penanganan ini'
    });
    return;
  }

  if (!formPenanganan.status) {
    await Swal.fire({
      icon: 'error', 
      title: 'Gagal!', 
      text: 'Status penanganan harus dipilih'
    });
    return;
  }

  if (!authStore.user?.id) {
    await Swal.fire({
      icon: 'error', 
      title: 'Gagal!', 
      text: 'User ID tidak tersedia, silakan login ulang'
    });
    return;
  }

  isSubmitting.value = true;

  try {
    const payload = {
      status: formPenanganan.status,
      keterangan: formPenanganan.catatan || null
    };

    const response = await apiClient.put(
      `/status-penanganan/${props.item.id_anak}/${authStore.user.id}`,
      payload
    );

    if (response.data.status === 'success') {
      statusPenanganan.value = response.data.data;
      isFlipped.value = false;
      emit('refresh-data');

      await Swal.fire({
        icon: 'success', 
        title: 'Berhasil!', 
        text: 'Status penanganan berhasil diperbarui'
      });
    } else {
      throw new Error(response.data.message || 'Gagal memperbarui status');
    }
  } catch (error) {
    console.error('Failed to update status:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: error.response?.data?.message || error.message || 'Gagal memperbarui status penanganan'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const closeDialog = () => {
  showManualForm.value = false;
  emit('closeDialog');
};

watch(() => props.item, (newItem) => {
  if (newItem?.id_anak) {
    item.value = { ...newItem };
    resetForm();
    fetchStatusPenanganan();
  }
}, { immediate: true, deep: true });

onMounted(() => {
  if (props.item?.id_anak) {
    loadDetailData(props.item.id_anak);
  }
});
</script>

<style scoped>
.manual-form {
  max-width: 1000px;
  margin: 0 auto;
  background-color: rgb(var(--v-theme-background));
  padding: 16px;
}

.card-container {
  margin-bottom: 20px;
  position: relative;
}

.card-container:last-child {
  margin-bottom: 0;
}

.flip-card {
  perspective: 1000px;
  width: 100%;
  height: auto;
  position: relative;
}

.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}

.flip-card.flipped .flip-card-inner {
  transform: rotateY(180deg);
}

.flip-card-back,
.flip-card-front {
  width: 100%;
  backface-visibility: hidden;
}

.flip-card-back {
  position: absolute;
  top: 0;
  left: 0;
  transform: rotateY(180deg);
  height: 100%;
}

.health-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  background-color: rgb(var(--v-theme-surface));
  height: 100%;
}

.health-card-header {
  background-color: #00897B !important;
  color: white !important;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.health-card-content {
  position: relative;
  padding: 24px;
  background-color: rgb(var(--v-theme-surface));
}

.label-text {
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 4px;
}

.value-text {
  font-size: 1rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.9);
  line-height: 1.5;
}

.garuda-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.2);
}

.garuda-icon img {
  width: 60%;
  height: auto;
}

.watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.08;
  pointer-events: none;
  z-index: 0;
}

.watermark img {
  width: 200px;
  height: auto;
}

.department-section {
  border-bottom: 1px solid rgba(var(--v-border-color), 1);
  padding-bottom: 24px;
  margin-bottom: 24px;
}

.department-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.department-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.department-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.department-title {
  font-weight: 600;
  margin-left: 12px;
  color: rgba(var(--v-theme-on-surface), 0.9);
  font-size: 1.125rem;
}

.factor-field {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 14px 16px;
  transition: all 0.3s ease;
  margin-bottom: 12px;
  border: 1px solid #e9ecef;
}

.factor-field:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.factor-issue {
  background-color: #fff5f5;
  border-left: 4px solid #ff6b6b;
}

.factor-label {
  color: #495057;
  font-size: 0.875rem;
  margin-bottom: 6px;
  font-weight: 500;
}

.factor-value {
  font-weight: 600;
  font-size: 1rem;
  display: flex;
  align-items: center;
  color: #212529; 
}

@media (max-width: 960px) {
  .health-card-content {
    padding: 20px;
  }
  
  .department-section {
    padding-bottom: 20px;
    margin-bottom: 20px;
  }
  
  .factor-field {
    padding: 12px 14px;
  }
}

@media (max-width: 600px) {
  .health-card-header {
    padding: 16px;
  }
  
  .health-card-header > div {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .card-container {
    margin-bottom: 16px;
  }
  
  .garuda-icon {
    width: 40px;
    height: 40px;
  }
  
  .department-title {
    font-size: 1rem;
  }
  
  .factor-field {
    padding: 12px;
  }
}

.v-theme--dark {
  .health-card-header {
    background-color: #00695C !important;
  }
  
  .health-card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
  }
  
  .factor-field {
    background-color: #2b3035;
    border-color: #3a4149;
  }
  
  .factor-field:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }
  
  .factor-issue {
    background-color: rgba(255, 107, 107, 0.1);
    border-left-color: #ff6b6b;
  }
  
  .factor-label {
    color: rgba(255, 255, 255, 0.7);
  }
  
  .factor-value {
    color: rgba(255, 255, 255, 0.9);
  }
  
  .status-good {
    color: #69db7c;
  }
  
  .status-bad {
    color: #ff8787;
  }
  
  .watermark {
    opacity: 0.05;
  }
  
  .department-icon {
    background-color: rgba(var(--v-theme-primary), 0.2);
  }
}

@media (max-width: 960px) {
  .health-card-content {
    padding: 20px;
  }
  
  .department-section {
    padding-bottom: 20px;
    margin-bottom: 20px;
  }
  
  .factor-field {
    padding: 12px 14px;
  }
}

@media (max-width: 600px) {
  .health-card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
  }
  
  .garuda-icon {
    width: 40px;
    height: 40px;
  }
  
  .department-title {
    font-size: 1rem;
  }
  
  .factor-field {
    padding: 12px;
  }
}
</style>