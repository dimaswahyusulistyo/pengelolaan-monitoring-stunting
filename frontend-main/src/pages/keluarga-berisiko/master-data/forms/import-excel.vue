<template>
  <div class="form-container">
    <VCard class="import-form" max-width="600px">
      <VCardTitle class="text-h5 pa-4 d-flex align-center">
        <span class="ml-4">Import Excel Data Keluarga Berisiko</span>
        <VSpacer />
        <VBtn icon variant="text" @click="closeDialog">
          <i class="bx bx-x"></i>
        </VBtn>
      </VCardTitle>

      <VCardText>
        <VBtn variant="text" class="mb-2" @click="$emit('cancel')">
          <i class="bx bx-arrow-back mr-2"></i> Kembali
        </VBtn>
        
        <div v-if="isProcessing" class="progress-container mt-4">
          <VProgressLinear v-model="progress" height="25" color="primary" striped>
            <template v-slot:default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </VProgressLinear>
          <p class="text-caption text-center mt-2">
            Sedang memproses data...
          </p>
        </div>

        <div class="drop-zone pa-8 rounded-lg" :class="{ 'drag-over': dragOver }" 
             @dragover="handleDragOver" @dragleave="handleDragLeave" @drop="handleDrop">
          <div class="text-center">
            <i class='bx bx-cloud-upload text-h2 mb-4'></i>
            <h3 class="text-h6 mb-2">Drag and drop your Excel file here</h3>
            <p class="text-body-2 mb-4">or</p>
            <VBtn color="primary" @click="$refs.fileInput.click()">
              Browse Files
            </VBtn>
            <input ref="fileInput" type="file" accept=".xlsx,.xls" 
                   class="hidden-input" @change="handleFileInput" />
          </div>
          <div v-if="selectedFile" class="selected-file mt-4">
            <p class="text-body-2">Selected file: {{ selectedFile.name }}</p>
          </div>
        </div>
      </VCardText>

      <VCardActions class="pa-4">
        <VSpacer />
        <VBtn color="primary" @click="importFile" :disabled="!selectedFile || isProcessing">
          Import
        </VBtn>
        <VBtn color="grey-darken-1" variant="text" @click="closeDialog">
          Batal
        </VBtn>
      </VCardActions>
    </VCard>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/services/api';
import { useAuthStore } from '@/services/auth';

const authStore = useAuthStore();
const emit = defineEmits(['fileImported', 'closeDialog', 'cancel', 'refresh-data']);

const dragOver = ref(false);
const selectedFile = ref(null);
const isProcessing = ref(false);
const progress = ref(0);
const desaList = ref([]);

onMounted(async () => {
  try {
    const response = await apiClient.get('/desa', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    desaList.value = response.data.data.map(desa => ({
      id_desa: desa.id,
      nama_desa: desa.nama_desa || desa.desa || '',
      kecamatan: desa.kecamatan || '',
      puskesmas: desa.puskesmas || ''
    }));
  } catch (error) {
    console.error('Failed to load desa list:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Gagal Memuat Data Desa',
      text: 'Tidak dapat memuat daftar desa. Silakan muat ulang halaman.'
    });
  }
});

function getDesaId(desaName) {
  if (!desaName || !desaList.value || !Array.isArray(desaList.value)) {
    console.error('Invalid desa lookup:', { desaName, desaList: desaList.value });
    return null;
  }
  
  const cleanName = desaName.toString().trim().toLowerCase();
  const found = desaList.value.find(d => 
    (d.nama_desa?.toString().trim().toLowerCase() === cleanName) ||
    (d.desa?.toString().trim().toLowerCase() === cleanName)
  );
  
  if (!found) {
    console.warn(`Desa not found: ${desaName}`);
  }
  
  return found ? found.id_desa : null;
}

async function handleKkDuplicate(kkNumber, rowData) {
    try {
        const checkResponse = await apiClient.post('/keluarga-berisiko/check-no-kk', {
            no_kartu_keluarga: kkNumber
        }, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`
          }
        });
        
        if (checkResponse.data.exists) {
            const result = await Swal.fire({
                title: 'KK Sudah Terdaftar',
                html: `
                    <div class="text-left mb-4">
                        <p>No KK <strong>${kkNumber}</strong> sudah terdaftar atas nama:</p>
                        <div class="text-center my-3 py-2 bg-grey-lighten-4 rounded">
                            <strong class="text-primary">${checkResponse.data.nama_kepala_keluarga}</strong>
                        </div>
                        <p class="mt-3">Silakan pilih tindakan:</p>
                    </div>
                `,
                icon: 'warning',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Update Data',
                denyButtonText: 'Lewati',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusConfirm: false,
                customClass: {
                    confirmButton: 'btn-success',
                    denyButton: 'btn-warning',
                    cancelButton: 'btn-danger',
                    actions: 'mt-4 gap-2'
                },
                buttonsStyling: true
            });

            if (result.isConfirmed) {
                const updateData = transformRowToUpdateData(rowData);
                const updateResponse = await apiClient.put(
                    `/keluarga-berisiko/${checkResponse.data.id_keluarga}`,
                    updateData,
                    {
                      headers: {
                        'Authorization': `Bearer ${authStore.token}`
                      }
                    }
                );
                return { action: 'updated', data: updateResponse.data };
            } else if (result.isDenied) {
                return { action: 'skipped' };
            } else {
                throw new Error('Import dibatalkan oleh pengguna');
            }
        } else {
            const createResponse = await apiClient.post(
                '/keluarga-berisiko',
                transformRowToCreateData(rowData),
                {
                  headers: {
                    'Authorization': `Bearer ${authStore.token}`
                  }
                }
            );
            return { action: 'created', data: createResponse.data };
        }
    } catch (error) {
        console.error('Error handling KK duplicate:', error);
        
        // Handle validation errors specifically
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            let errorMessage = 'Validasi gagal:<br><ul>';
            
            Object.entries(errors).forEach(([field, messages]) => {
                errorMessage += `<li>${field}: ${messages.join(', ')}</li>`;
            });
            
            errorMessage += '</ul>';
            throw new Error(errorMessage);
        }
        
        throw error;
    }
}

// Transform Excel row to create data format
function transformRowToCreateData(row) {
    return {
        no_kartu_keluarga: String(row['No Kartu Keluarga']).replace(/\./g, ''),
        nik_kepala_keluarga: String(row['NIK Kepala Keluarga']).replace(/\./g, ''),
        nama_kepala_keluarga: row['Nama Kepala Keluarga'],
        nik_istri: row['NIK Istri'] ? String(row['NIK Istri']).replace(/\./g, '') : null,
        nama_istri: row['Nama Istri'],
        id_desa: getDesaId(row['Desa']),
        kecamatan: row['Kecamatan'],
        punya_anak_0_23_bulan: parseInt(row['Punya Anak 0-23 Bulan']) || 1,
        punya_anak_24_59_bulan: parseInt(row['Punya Anak 24-59 Bulan']) || 1,
        pus: parseInt(row['PUS']) || 1,
        pus_hamil: parseInt(row['PUS Hamil']) || 1,
        sumber_air_minum_tidak_layak: parseInt(row['Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak']) || 1,
        jamban_tidak_layak: parseInt(row['Keluarga Tidak Mempunyai Jamban yang Layak']) || 1,
        pus_4_terlalu_muda: parseInt(row['PUS 4 Terlalu Muda (Umur Istri <20 Tahun)']) || 1,
        pus_4_terlalu_tua: parseInt(row['PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)']) || 1,
        pus_4_terlalu_dekat: parseInt(row['PUS 4 Terlalu Dekat (<2 Tahun)']) || 1,
        pus_4_terlalu_banyak: parseInt(row['PUS 4 Terlalu Banyak (≥3 Anak)']) || 1,
        pus_4_terlalu: calculatePus4TerlaluFromRow(row),
        bukan_peserta_kb_modern: parseInt(row['Bukan Peserta KB Modern']) || 1,
        jenis_pendampingan_diterima: row['Jenis Pendampingan yang Diterima'] 
            ? row['Jenis Pendampingan yang Diterima'].toString().split(',').map(Number)
            : []
    };
}

function transformRowToUpdateData(row) {
    return {
        no_kartu_keluarga: String(row['No Kartu Keluarga']).replace(/\./g, ''),
        nik_kepala_keluarga: String(row['NIK Kepala Keluarga']).replace(/\./g, ''),
        nama_kepala_keluarga: row['Nama Kepala Keluarga'],
        nik_istri: row['NIK Istri'] ? String(row['NIK Istri']).replace(/\./g, '') : null,
        nama_istri: row['Nama Istri'],
        id_desa: getDesaId(row['Desa']),
        kecamatan: row['Kecamatan'],
        punya_anak_0_23_bulan: parseInt(row['Punya Anak 0-23 Bulan']) || 1,
        punya_anak_24_59_bulan: parseInt(row['Punya Anak 24-59 Bulan']) || 1,
        pus: parseInt(row['PUS']) || 1,
        pus_hamil: parseInt(row['PUS Hamil']) || 1,
        sumber_air_minum_tidak_layak: parseInt(row['Keluarga Tidak Mempunyai Sumber Air Minum Utama yang Layak']) || 1,
        jamban_tidak_layak: parseInt(row['Keluarga Tidak Mempunyai Jamban yang Layak']) || 1,
        pus_4_terlalu_muda: parseInt(row['PUS 4 Terlalu Muda (Umur Istri <20 Tahun)']) || 1,
        pus_4_terlalu_tua: parseInt(row['PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)']) || 1,
        pus_4_terlalu_dekat: parseInt(row['PUS 4 Terlalu Dekat (<2 Tahun)']) || 1,
        pus_4_terlalu_banyak: parseInt(row['PUS 4 Terlalu Banyak (≥3 Anak)']) || 1,
        pus_4_terlalu: calculatePus4TerlaluFromRow(row), 
        bukan_peserta_kb_modern: parseInt(row['Bukan Peserta KB Modern']) || 1,
        jenis_pendampingan_diterima: row['Jenis Pendampingan yang Diterima'] 
            ? row['Jenis Pendampingan yang Diterima'].toString().split(',').map(Number)
            : []
    };
}

function calculatePus4TerlaluFromRow(row) {
    const terlalu_muda = parseInt(row['PUS 4 Terlalu Muda (Umur Istri <20 Tahun)']) || 1;
    const terlalu_tua = parseInt(row['PUS 4 Terlalu Tua (Umur Istri 35 sd 40 Tahun)']) || 1;
    const terlalu_dekat = parseInt(row['PUS 4 Terlalu Dekat (<2 Tahun)']) || 1;
    const terlalu_banyak = parseInt(row['PUS 4 Terlalu Banyak (≥3 Anak)']) || 1;

    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].includes(1)) {
        return 1;
    }
    
    if ([terlalu_muda, terlalu_tua, terlalu_dekat, terlalu_banyak].every(val => val === 2)) {
        return 2;
    }
    
    return 3;
}

const handleDragOver = (e) => {
  e.preventDefault();
  dragOver.value = true;
};

const handleDragLeave = (e) => {
  e.preventDefault();
  dragOver.value = false;
};

const handleDrop = (e) => {
  e.preventDefault();
  dragOver.value = false;
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    handleFile(files[0]);
  }
};

const handleFileInput = (e) => {
  const file = e.target.files[0];
  if (file) {
    handleFile(file);
  }
};

const handleFile = (file) => {
  const validTypes = [
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'application/octet-stream'
  ];
  
  const validExtensions = ['.xlsx', '.xls'];
  const fileName = file.name.toLowerCase();
  
  if (validTypes.includes(file.type) || validExtensions.some(ext => fileName.endsWith(ext))) {
    selectedFile.value = file;
  } else {
    window.Swal.fire({
      icon: 'error',
      title: 'File Tidak Valid',
      text: 'Harap unggah file Excel (.xlsx atau .xls)',
    });
  }
};

const importFile = async () => {
    if (!selectedFile.value) {
      await window.Swal.fire({
        icon: 'error',
        title: 'Tidak Ada File',
        text: 'Silakan pilih file terlebih dahulu',
      });
      return;
    }

    isProcessing.value = true;
    progress.value = 0;

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const parseResponse = await apiClient.post('/keluarga-berisiko/parse-excel', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${authStore.token}`
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    progress.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                }
            }
        });

        const rows = parseResponse.data.rows;
        const results = {
            total: rows.length,
            created: 0,
            updated: 0,
            skipped: 0,
            errors: 0,
            errorDetails: []
        };

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            try {
                const kkNumber = String(row['No Kartu Keluarga']).replace(/\./g, '');

                const result = await handleKkDuplicate(kkNumber, row);
                
                if (result.action === 'created') {
                    results.created++;
                } else if (result.action === 'updated') {
                    results.updated++;
                } else if (result.action === 'skipped') {
                    results.skipped++;
                }

                progress.value = Math.round(((i + 1) / rows.length) * 100);
                
            } catch (error) {
                results.errors++;
                results.errorDetails.push({
                    row: i + 1, 
                    error: error.message
                });
                console.error(`Error processing row ${i}:`, error);
            }
        }

        await Swal.fire({
            icon: 'success',
            title: 'Import Selesai',
            html: `
                <div class="text-left">
                    <p>Hasil import data Keluarga Berisiko:</p>
                    <ul>
                        <li>Total data: ${results.total}</li>
                        <li>Data baru: ${results.created}</li>
                        <li>Data diupdate: ${results.updated}</li>
                        <li>Data dilewati: ${results.skipped}</li>
                        <li>Error: ${results.errors}</li>
                    </ul>
                    ${results.errorDetails.length > 0 ? `
                    <div class="mt-3">
                        <p>Detail error:</p>
                        <ul class="text-sm">
                            ${results.errorDetails.map(e => `<li>Baris ${e.row}: ${e.error}</li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            width: '600px'
        });

        emit('refresh-data');
        emit('fileImported');
        selectedFile.value = null;

    } catch (error) {
        console.error('Error during import:', error);
        
        let errorMessage = 'Gagal mengimpor data. Silakan cek konsol untuk detailnya.';
        if (error.response?.status === 401) {
            errorMessage = 'Sesi Anda telah habis. Silakan login kembali.';
            authStore.logout();
        } 
        else if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(', ');
        } else if (error.message) {
            errorMessage = error.message;
        }

        await Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            html: errorMessage,
            confirmButtonText: 'OK',
            width: '600px'
        });
    } finally {
        isProcessing.value = false;
        progress.value = 0;
    }
};

const closeDialog = () => {
  selectedFile.value = null;
  emit('closeDialog');
};
</script>

<style scoped>
.form-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.import-form {
  width: 100%;
  max-width: 600px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.drop-zone {
  border: 2px dashed #ccc;
  transition: all 0.3s;
  background-color: #f8fafc;
}

.drop-zone.drag-over {
  border-color: #5b8cff;
  background-color: rgba(91, 140, 255, 0.1);
}

.hidden-input {
  display: none;
}

.selected-file {
  padding: 8px;
  background-color: #f0f4f8;
  border-radius: 4px;
  text-align: center;
}

.progress-container {
  margin-top: 16px;
}
</style>