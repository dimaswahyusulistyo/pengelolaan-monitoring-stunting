<template>
    <div class="form-container">
      <VCard class="import-form" max-width="600px">
        <VCardTitle class="text-h5 pa-4 d-flex align-center">
          <span class="ml-4">Update Excel Data Cakupan Program Intervensi</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
  
        <VCardText>
          <VBtn variant="text" class="mb-2" @click="$emit('cancel')">
            <i class="bx bx-arrow-back mr-2"></i> Kembali
          </VBtn>
          
          <!-- Year Input as regular text field -->
          <VTextField
            v-model="selectedYear"
            label="Tahun"
            type="number"
            min="2000"
            max="2100"
            class="mb-4"
            :rules="[yearValidation]"
            required
          ></VTextField>
          
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
            :disabled="!selectedFile || !isYearValid"
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
  import { ref, computed } from 'vue';
  import apiClient from '@/services/api';
  
  const emit = defineEmits(['fileImported', 'closeDialog', 'cancel']);
  
  const dragOver = ref(false);
  const selectedFile = ref(null);
  const selectedYear = ref(new Date().getFullYear().toString()); 
  
  const yearValidation = (value) => {
    const year = parseInt(value);
    return (
      !isNaN(year) && 
      year >= 2000 && 
      year <= 2100
    ) || 'Tahun harus valid (2000-2100)';
  };
  
  const isYearValid = computed(() => {
    const year = parseInt(selectedYear.value);
    return !isNaN(year) && year >= 2000 && year <= 2100;
  });
  
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
    if (!isYearValid.value) {
      await window.Swal.fire({
        icon: 'error',
        title: 'Tahun Tidak Valid',
        text: 'Masukkan tahun yang valid (2000-2100)',
      });
      return;
    }

    const formData = new FormData();
    formData.append('file_import', selectedFile.value);
    formData.append('tahun', selectedYear.value);

    try {
      const response = await apiClient.post('/cakupan-program/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      const data = response.data;

      if (data.status === 'warning') {
        const warningDetails = Object.values(data.errors || {}).flat().join('\n') || '';

        await window.Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          html: `
            <p>${data.message}</p>
            <pre style="text-align:left; white-space:pre-wrap;">${warningDetails}</pre>
          `,
          width: 600,
        });
      } else {
        await window.Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: data.message,
          showConfirmButton: false,
          timer: 2000,
        });
      }

      emit('refresh-data');
      emit('closeDialog');
      emit('fileImported', selectedFile.value, selectedYear.value);
      selectedFile.value = null;

    } catch (error) {
      console.error('Error during import:', error);
      const { status, data } = error.response || {};

      if (status === 207 && data?.errors) {
        await window.Swal.fire({
          icon: 'success',
          title: 'Berhasil Diimpor dengan Catatan',
          text: data.message ?? 'Data berhasil diimpor, namun ada kekurangan.',
          showConfirmButton: true,
        });

        await window.Swal.fire({
          icon: 'warning',
          title: 'Catatan Data Tidak Lengkap',
          html: `<ul style="text-align:left">${data.errors.map(e => `<li>${e}</li>`).join('')}</ul>`,
          width: 600,
        });

        emit('refresh-data');
        emit('closeDialog');
        emit('fileImported', selectedFile.value, selectedYear.value);
        selectedFile.value = null;

      } else if (status === 422 || data?.message) {
        let errorMessage = data?.message ?? 'Gagal mengimpor data. Silakan cek kembali.';
        if (data?.errors) {
          const flatErrors = Object.values(data.errors).flat();
          errorMessage = flatErrors.join(', ');
        }

        await window.Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: errorMessage,
        });
      } else {
        await window.Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: 'Tidak dapat mengimpor data. Silakan coba lagi nanti.',
        });
      }
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