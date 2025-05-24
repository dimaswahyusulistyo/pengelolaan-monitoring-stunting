<template>
  <div class="form-container">
    <VCard class="import-form" max-width="600px">
      <VCardTitle class="text-h5 pa-4 d-flex align-center">
        <span class="ml-4">Import Excel Data Stunting</span>
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
          <VProgressLinear
            v-model="progress"
            height="25"
            color="primary"
            striped
          >
            <template v-slot:default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </VProgressLinear>
          <p class="text-caption text-center mt-2">
            Sedang memproses data...
          </p>
        </div>
        <div
          class="drop-zone pa-8 rounded-lg"
          :class="{ 'drag-over': dragOver }"
          @dragover="handleDragOver"
          @dragleave="handleDragLeave"
          @drop="handleDrop"
        >
          <div class="text-center">
            <i class='bx bx-cloud-upload text-h2 mb-4'></i>
            <h3 class="text-h6 mb-2">Drag and drop your Excel file here</h3>
            <p class="text-body-2 mb-4">or</p>
            <VBtn
              color="primary"
              @click="$refs.fileInput.click()"
            >
              Browse Files
            </VBtn>
            <input
              ref="fileInput"
              type="file"
              accept=".xlsx,.xls"
              class="hidden-input"
              @change="handleFileInput"
            />
          </div>
          <div v-if="selectedFile" class="selected-file mt-4">
            <p class="text-body-2">Selected file: {{ selectedFile.name }}</p>
          </div>
        </div>
      </VCardText>

      <VCardActions class="pa-4">
        <VSpacer />
        <VBtn
          color="primary"
          @click="importFile"
          :disabled="!selectedFile"
        >
          Import
        </VBtn>
        <VBtn
          color="grey-darken-1"
          variant="text"
          @click="closeDialog"
        >
          Batal
        </VBtn>
      </VCardActions>
    </VCard>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import apiClient from '@/services/api';

const emit = defineEmits(['fileImported', 'closeDialog', 'cancel', 'refresh-data']);

const dragOver = ref(false);
const selectedFile = ref(null);
const isProcessing = ref(false);
const progress = ref(0);
const desaList = ref([]);

onMounted(async () => {
  try {
    const response = await apiClient.get('/desa');
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

async function handleNikDuplicate(nik, rowData) {
    try {
        const checkResponse = await apiClient.post('/anak-stunting/check-nik', {
            nik_anak: nik
        });
        
        if (checkResponse.data.exists) {
            const result = await Swal.fire({
                title: 'NIK Sudah Terdaftar',
                html: `
                    <div class="text-left mb-4">
                        <p>NIK <strong>${nik}</strong> sudah terdaftar atas nama:</p>
                        <div class="text-center my-3 py-2 bg-grey-lighten-4 rounded">
                            <strong class="text-primary">${checkResponse.data.nama_anak}</strong>
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
                    `/anak-stunting/${checkResponse.data.id_anak}`,
                    updateData
                );
                return { action: 'updated', data: updateResponse.data };
            } else if (result.isDenied) {
                return { action: 'skipped' };
            } else {
                throw new Error('Import dibatalkan oleh pengguna');
            }
        } else {
            const createResponse = await apiClient.post(
                '/anak-stunting',
                transformRowToCreateData(rowData)
            );
            return { action: 'created', data: createResponse.data };
        }
    } catch (error) {
        console.error('Error handling NIK duplicate:', error);
        throw error;
    }
}

function transformRowToCreateData(row) {
    return {
        nik_anak: String(row['NIK']).replace('.', ''),
        nama_anak: row['Nama'],
        jenis_kelamin: row['Jenis Kelamin'],
        tanggal_lahir: formatDate(row['Tanggal Lahir']),
        id_desa: getDesaId(row['Desa']),
        posyandu: row['Posyandu'],
        jkn: row['JKN'] ?? 1,
        status_ekonomi: row['Status Ekonomi'] ?? 1,
        imunisasi: row['Imunisasi'] ?? 1,
        bpnt: row['BPNT'] ?? 1,
        pkh: row['Program PKH'] ?? 1,
        jamban_sehat: row['Ketersediaan Jamban'] ?? 1,
        kebiasaan_merokok: row['Kebiasaan Merokok'] ?? 1,
        penyakit_penyerta: row['Penyakit Penyerta'] ?? '',
        riwayat_ibu_hamil: row['Riwayat Ibu Hamil'] ?? 1,
        sumber_air_bersih: row['Sumber Air Bersih'] ?? 1,
        kecacingan_menderita: row['Menderita'] ?? 1,
        kecacingan_obat: row['Mendapat Obat'] ?? 1
    };
}

function transformRowToUpdateData(row) {
    return {
        nik_anak: String(row['NIK']).replace('.', ''),
        nama_anak: row['Nama'],
        jenis_kelamin: row['Jenis Kelamin'],
        tanggal_lahir: formatDate(row['Tanggal Lahir']),
        id_desa: getDesaId(row['Desa']),
        posyandu: row['Posyandu'],
        jkn: row['JKN'] ?? 1,
        status_ekonomi: row['Status Ekonomi'] ?? 1,
        imunisasi: row['Imunisasi'] ?? 1,
        bpnt: row['BPNT'] ?? 1,
        pkh: row['Program PKH'] ?? 1,
        jamban_sehat: row['Ketersediaan Jamban'] ?? 1,
        kebiasaan_merokok: row['Kebiasaan Merokok'] ?? 1,
        penyakit_penyerta: row['Penyakit Penyerta'] ?? '',
        riwayat_ibu_hamil: row['Riwayat Ibu Hamil'] ?? 1,
        sumber_air_bersih: row['Sumber Air Bersih'] ?? 1,
        kecacingan_menderita: row['Menderita'] ?? 1,
        kecacingan_obat: row['Mendapat Obat'] ?? 1
    };
}

function formatDate(value) {
    if (!value) return null;
    if (typeof value === 'number') {
        const date = new Date((value - 25569) * 86400 * 1000);
        return date.toISOString().split('T')[0];
    }
    return value.split(' ')[0];
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
  if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 
      file.type === 'application/vnd.ms-excel') {
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
    if (!selectedFile.value) return;

    isProcessing.value = true;
    progress.value = 0;

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const parseResponse = await apiClient.post('/anak-stunting/parse-excel', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: (progressEvent) => {
                progress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
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
              const nik = String(row['NIK']).replace(/\./g, '');

                const result = await handleNikDuplicate(nik, row);
                
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
                    row: row,
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
                    <p>Hasil import data:</p>
                    <ul>
                        <li>Total data: ${results.total}</li>
                        <li>Data baru: ${results.created}</li>
                        <li>Data diupdate: ${results.updated}</li>
                        <li>Data dilewati: ${results.skipped}</li>
                        <li>Error: ${results.errors}</li>
                    </ul>
                </div>
            `,
            showConfirmButton: true,
        });

        emit('refresh-data');
        emit('fileImported');
        selectedFile.value = null;

    } catch (error) {
        console.error('Error during import:', error);
        
        let errorMessage = 'Gagal mengimpor data. Silakan cek konsol untuk detailnya.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(', ');
        }

        await Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: errorMessage,
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
  padding: 16px;
}

.import-form {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}

.drop-zone {
  border: 2px dashed #ccc;
  transition: all 0.3s ease;
}

.drag-over {
  border-color: #2196F3;
  background-color: rgba(33, 150, 243, 0.1);
}

.hidden-input {
  display: none;
}

.selected-file {
  text-align: center;
  padding: 8px;
  background-color: #f5f5f5;
  border-radius: 4px;
}
</style>