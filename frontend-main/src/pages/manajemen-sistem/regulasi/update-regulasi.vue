<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>{{ editMode ? 'Edit Surat Regulasi' : 'Tambah Surat Regulasi' }}</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
        
        <VCardText class="pt-4">
          <VForm ref="formRef">
            <div class="form-section">
              <div class="mt-4">
                <VTextField 
                  v-model="formRegulasi.nama_surat_regulasi" 
                  label="Nama Surat Regulasi" 
                  class="mb-6" 
                  required 
                />
                
                <VFileInput 
                  v-model="file_surat_regulasi" 
                  label="Surat Regulasi" 
                  accept=".pdf" 
                  placeholder="Pilih file" 
                  class="mb-6" 
                  @update:model-value="handleTemplateUpload" 
                  messages="Format file: PDF" 
                />
                
                <div v-if="formRegulasi.file_surat_regulasi" class="text-caption mt-2">
                  <strong>File sebelumnya: </strong> 
                  <a v-if="isValidUrl(formRegulasi.file_surat_regulasi)" :href="formRegulasi.file_surat_regulasi" target="_blank">
                    {{ getFileName(formRegulasi.file_surat_regulasi) }}
                  </a>
                  <span v-else>{{ formRegulasi.file_surat_regulasi }}</span>
                </div>
              </div>
            </div>
          </VForm>
        </VCardText>
        
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveRegulasi" min-width="100">
            {{ editMode ? 'Update' : 'Simpan' }}
          </VBtn>
          <VBtn color="grey-darken-1" variant="text" @click="closeDialog" min-width="100">
            Batal
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </template>
  

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue';
import apiClient from '@/services/api';

const formRef = ref(null);
const file_surat_regulasi = ref(null);
const formRegulasi = ref({ id_regulasi: null, nama_surat_regulasi: '', file_surat_regulasi: '' });

const props = defineProps({
    dialog: Boolean,
    editMode: Boolean, 
    editedRegulasi: Object,
});

const emit = defineEmits(['update:dialog', 'save']);

watch(
    () => props.editedRegulasi,
    (newValue) => {
        if (newValue) {
            formRegulasi.value = { 
            id_regulasi: newValue.id_regulasi || null,
            nama_surat_regulasi: newValue.nama_surat_regulasi || '', 
            file_surat_regulasi: newValue.file_surat_regulasi || '' 
        };
        }
    },
    { deep: true, immediate: true }
);

const closeDialog = () => {
    emit('update:dialog', false);
};

const handleTemplateUpload = (files) => {
    if (files && files.length > 0) {
        file_surat_regulasi.value = files[0]; 
    }
};

const getFileName = (filePath) => filePath.split('/').pop();

const isValidUrl = (string) => {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
};

const saveRegulasi = async () => {
    if (!formRegulasi.value.nama_surat_regulasi) {
        await Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Nama template harus diisi!',
    });
    return;
    }

    const formData = new FormData();
        formData.append('nama_surat_regulasi', formRegulasi.value.nama_surat_regulasi);
        if (file_surat_regulasi.value instanceof File) {
            formData.append('file_surat_regulasi', file_surat_regulasi.value);
        }
        if (props.editMode && formRegulasi.value.id_regulasi) {
            formData.append('_method', 'PUT'); 
        }
        for (let pair of formData.entries()) {
        }

    try {
        let response;
        if (props.editMode && formRegulasi.value.id_regulasi) {
        response = await apiClient.post(`/regulasi/${formRegulasi.value.id_regulasi}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        } else {
        response = await apiClient.post('/regulasi', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        }

        Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: response.data.message,
        });

        emit('save', response.data.data);
        closeDialog();
    } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: error.response?.data.message || 'Terjadi kesalahan saat menyimpan data.',
        });
    }
};
</script>