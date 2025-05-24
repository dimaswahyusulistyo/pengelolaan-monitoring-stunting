<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>{{ editMode ? 'Edit File Template' : 'Tambah File Template' }}</span>
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
                  v-model="formActivity.nama_template" 
                  label="Nama File Template" 
                  class="mb-6" 
                  required 
                />
                
                <VFileInput 
                  v-model="file_template" 
                  label="Dokumen" 
                  accept=".xls,.xlsx" 
                  placeholder="Pilih file" 
                  class="mb-6" 
                  @update:model-value="handleTemplateUpload" 
                  messages="Format file: EXCEL" 
                />
                
                <div v-if="formActivity.file_template" class="text-caption mt-2">
                  <strong>File sebelumnya: </strong> 
                  <a v-if="isValidUrl(formActivity.file_template)" :href="formActivity.file_template" target="_blank">
                    {{ getFileName(formActivity.file_template) }}
                  </a>
                  <span v-else>{{ formActivity.file_template }}</span>
                </div>
              </div>
            </div>
          </VForm>
        </VCardText>
        
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveActivity" min-width="100">
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
const file_template = ref(null);
const formActivity = ref({ id_template: null, nama_template: '', file_template: '' });

const props = defineProps({
    dialog: Boolean,
    editMode: Boolean, 
    editedActivity: Object,
});

const emit = defineEmits(['update:dialog', 'save']);

watch(
    () => props.editedActivity,
    (newValue) => {
        if (newValue) {
            formActivity.value = { 
            id_template: newValue.id_template || null,
            nama_template: newValue.nama_template || '', 
            file_template: newValue.file_template || '' 
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
        file_template.value = files[0]; 
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

const saveActivity = async () => {
    if (!formActivity.value.nama_template) {
        await Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Nama template harus diisi!',
    });
    return;
    }

    const formData = new FormData();
        formData.append('nama_template', formActivity.value.nama_template);
        if (file_template.value instanceof File) {
            formData.append('file_template', file_template.value);
        }
        if (props.editMode && formActivity.value.id_template) {
            formData.append('_method', 'PUT'); 
        }
        for (let pair of formData.entries()) {
        }

    try {
        let response;
        if (props.editMode && formActivity.value.id_template) {
        response = await apiClient.post(`/template/${formActivity.value.id_template}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        } else {
        response = await apiClient.post('/template', formData, {
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