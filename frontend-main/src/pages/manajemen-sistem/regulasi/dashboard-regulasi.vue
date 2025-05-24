<template>
    <VRow>
      <VCol cols="12">
        <VCard title="Dashboard Regulasi" class="rounded-lg">
          <VRow class="align-center px-4 py-2">
            <VCol cols="12" md="6" class="d-flex">
  
              <VMenu offset-y>
                  <template v-slot:activator="{ props }">
                    <VBtn
                      v-bind="props"
                      color="primary"
                      class="me-2 rounded-lg d-flex justify-center align-center"
                      style="min-width: 40px; min-height: 40px;"
                    >
                      <VIcon>bx-filter</VIcon>
                    </VBtn>
                  </template>
  
                  <VList>
                    <VListItem @click="applySort('A-Z')">
                      <VListItemTitle>A - Z</VListItemTitle>
                    </VListItem>
                    <VListItem @click="applySort('Z-A')">
                      <VListItemTitle>Z - A</VListItemTitle>
                    </VListItem>
                    <VListItem @click="applySort('newest')">
                      <VListItemTitle>Terbaru</VListItemTitle>
                    </VListItem>
                    <VListItem @click="applySort('oldest')">
                      <VListItemTitle>Terlama</VListItemTitle>
                    </VListItem>
                  </VList>
                </VMenu>
  
              <VTextField 
                v-model="searchQuery" 
                prepend-inner-icon="bx-search" 
                placeholder="Search"
                class="search-box flex-grow-1" 
                clearable 
                density="comfortable"
                @update:modelValue="fetchRegulasi" /> 
            </VCol>
  
          </VRow>
  
          <div class="table-container">
            <VTable class="custom-table elevation-1">
              <thead>
                <tr>
                  <th class="text-center">No</th>
                  <th class="text-center">Nama Surat</th>
                  <th class="text-center">Dokumen</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(regulasi, index) in regulasis" :key="regulasi.id_regulasi">
                  <td class="text-center">{{ index + 1 }}</td>
                  <td class="text-center">{{ regulasi.nama_surat_regulasi }}</td>
                  <td>
                    <div v-if="regulasi.file_surat_regulasi" class="d-flex justify-center align-center">
                      <VBtn variant="text" color="primary" @click="openFilePreview(regulasi)" class="preview-link">
                        <i class="bx bx-file me-2"></i> Lihat File
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
                <VSelect v-model="itemsPerPage" :items="[5, 10, 15, 20]" variant="outlined" density="compact"
                  hide-details class="items-per-page-select" style="width: 80px;" @update:modelValue="fetchRegulasi" />
              </div>
    
              <VPagination v-model="currentPage" :length="totalPages" :total-visible="7" rounded="lg" @update:modelValue="fetchRegulasi" />
    
              <div class="d-flex align-center">
                <span class="text-body-2">
                  {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, totalItems) }}
                  of {{ totalItems }}
                </span>
              </div>
            </div>
        </VCard>
      </VCol>
  
      <template>
        <VDialog v-model="previewDialog" max-width="800">
    <VCard>
      <!-- Header -->
      <VCardTitle class="d-flex align-center pa-4">
        <span>Pratinjau File</span>
        <VSpacer />
        <VBtn icon variant="text" @click="previewDialog = false">
          <VIcon>mdi-close</VIcon>
        </VBtn>
      </VCardTitle>
  
      <VDivider />
  
      <!-- Body -->
      <VCardText class="pa-4">
        <div v-if="isPreviewPDF" class="preview-container" style="height: 70vh;">
          <embed 
            :src="previewFileUrl" 
            type="application/pdf" 
            width="100%" 
            height="100%"
            style="border: none;"
            frameborder="0">
        </div>
        <div v-else class="preview-container text-center">
          <VIcon size="64" color="grey" class="mb-4">mdi-file</VIcon>
          <p class="text-body-1 mb-4">File tidak dapat ditampilkan secara langsung</p>
          <VBtn color="primary" :href="previewFileUrl" target="_blank" download>
            Unduh File
          </VBtn>
        </div>
      </VCardText>
  
      <VDivider />
  
      <!-- Footer -->
      <VCardActions class="pa-4">
        <VSpacer />
        <VBtn color="grey-darken-1" variant="text" @click="previewDialog = false">Tutup</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  
  </template>
  
      <TambahRegulasi v-model:dialog="dialog" @save="fetchRegulasi" />
      <UpdateRegulasi 
          v-model:dialog="editDialog"
          :editMode="editMode"
          :editedRegulasi="selectedRegulasi"
          @save="fetchRegulasi"
      />
  
    </VRow>
  </template>
  
  <script setup>
  import { ref, onMounted, computed, watch } from 'vue';
  import TambahRegulasi from './tambah-regulasi.vue';
  import UpdateRegulasi from './update-regulasi.vue';
  import apiClient from '@/services/api';
  
  const regulasis = ref([]);
  const totalPages = ref(1);
  const totalItems = ref(0);
  const currentPage = ref(1);
  const itemsPerPage = ref(5);
  const searchQuery = ref('');
  const selectedRegulasi = ref(null);
  const editMode = ref(false);
  const editDialog = ref(false);
  const dialog = ref(false);
  
  const previewDialog = ref(false);
  const previewFileUrl = ref("");
  const isPreviewPDF = ref(false);
  
  const sortOrder = ref('');
  
  watch(itemsPerPage, () => {
      currentPage.value = 1; 
      fetchRegulasi();
  });
  
  const fetchRegulasi = async () => {
      try {
          const response = await apiClient.get('/regulasi', {
              params: {
                  search: searchQuery.value,
                  page: currentPage.value,
                  per_page: itemsPerPage.value,
                  sort_filter: sortOrder.value,
              },
          });
  
          regulasis.value = response.data.data ?? [];
          totalPages.value = response.data.last_page;
          totalItems.value = response.data.total;
      } catch (error) {
          console.error('Error fetching regulasi:', error);
          regulasis.value = [];
      }
  };
  
  const applySort = (option) => {
    sortOrder.value = option;
    fetchRegulasi();
  };
  
  onMounted(fetchRegulasi);
  
  const editRegulasi = (regulasi) => {
      selectedRegulasi.value = { ...regulasi };
      editMode.value = true;
      editDialog.value = true;
  };
  
  const deleteRegulasi = async (regulasi) => {
      if (!regulasi || !regulasi.id_regulasi) {
          console.error("Error: Regulasi tidak valid!", regulasi);
          return;
      }
  
      const result = await Swal.fire({
          title: 'Apakah Anda yakin?',
          text: "Data yang sudah dihapus tidak bisa dikembalikan!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal'
      });
  
      if (result.isConfirmed) {
          try {
              await apiClient.delete(`/regulasi/${regulasi.id_regulasi}`);
              fetchRegulasi();
  
              await Swal.fire({
                  title: 'Terhapus!',
                  text: 'Regulasi berhasil dihapus.',
                  icon: 'success',
                  timer: 2000,
                  showConfirmButton: false
              });
          } catch (error) {
              console.error('Error deleting regulasi:', error);
  
              await Swal.fire({
                  title: 'Gagal!',
                  text: 'Terjadi kesalahan saat menghapus regulasi.',
                  icon: 'error'
              });
          }
      }
  };
  
  const openFilePreview = async (regulasi) => {
    if (!regulasi || !regulasi.file_surat_regulasi) {
      alert("File tidak tersedia.");
      return;
    }
  
    try {
      const filename = regulasi.file_surat_regulasi.split('/').pop();
      previewFileUrl.value = `${apiClient.defaults.baseURL}/files/${filename}`;
      await apiClient.head(`/files/${filename}`);
      isPreviewPDF.value = filename.toLowerCase().endsWith(".pdf");
      previewDialog.value = true;
    } catch (error) {
      console.error('Error accessing file:', error);
      
      if (error.response?.status === 404) {
        alert("File tidak ditemukan di server");
      } else {
        alert("Terjadi kesalahan saat memuat file");
      }
    }
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
  
  .preview-document {
    width: 100%;
    height: 600px;
    border: none;
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
  </style>
  