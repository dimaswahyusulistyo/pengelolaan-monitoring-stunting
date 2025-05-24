<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>Tambah File Template</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
  
        <VCardText class="pt-4">
          <VForm ref="formRef">
            <div class="form-section">
              <div class="mt-4">
                <VTextField v-model="newTemplate.nama_template" label="Nama File Template" class="mb-6" required />
                <VFileInput 
                  v-model="file_template" 
                  label="Dokumen" 
                  accept=".xls,.xlsx" 
                  placeholder="Pilih file"
                  class="mb-6" 
                  @change="handleFileUpload"
                  messages="Format file: EXCEL" 
                />
              </div>
            </div>
          </VForm>
        </VCardText>
  
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveTemplate" min-width="100">Simpan</VBtn>
          <VBtn color="grey-darken-1" variant="text" @click="closeDialog" min-width="100">Batal</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import apiClient from '@/services/api';

const showSuccess = ref(false);

const formRef = ref(null);
const file_template = ref(null);
const newTemplate = ref({
  nama_template: '',
});

const props = defineProps({
  dialog: Boolean,
});

const emit = defineEmits(['update:dialog', 'save']);

const closeDialog = () => {
  emit('update:dialog', false);
};

const handleFileUpload = (event) => {
  const file = event.target.files ? event.target.files[0] : null;
  if (file) {
    file_template.value = file;
  }
};

const saveTemplate = async () => {
    if (!newTemplate.value.nama_template || !file_template.value) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: 'Nama file template dan dokumen harus diisi!',
        confirmButtonText: 'OK'
      });
      return;
    }

    const formData = new FormData();
    formData.append('nama_template', newTemplate.value.nama_template);
    formData.append('file_template', file_template.value);

    try {
    const response = await apiClient.post('/template', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    await Swal.fire({
      icon: 'success',
      title: 'Data berhasil ditambahkan!',
      showConfirmButton: false,
      timer: 3000
    });

    emit('save', response.data);
    newTemplate.value = { nama_template: '' };
    file_template.value = null;
    closeDialog();
  } catch (error) {
    console.error('Error saving template:', error.response?.data);
    
    Swal.fire({
      icon: 'error',
      title: 'Gagal menyimpan!',
      text: 'Pastikan format file benar.',
      confirmButtonText: 'OK'
    });
  }
};
</script>
