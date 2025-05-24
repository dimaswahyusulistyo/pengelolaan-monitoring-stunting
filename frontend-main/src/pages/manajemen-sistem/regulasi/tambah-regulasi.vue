<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>Tambah Regulasi</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
  
        <VCardText class="pt-4">
          <VForm ref="formRef">
            <div class="form-section">
              <div class="mt-4">
                <VTextField v-model="newRegulasi.nama_surat_regulasi" label="Nama Surat Regulasi" class="mb-6" required />
                <VFileInput 
                  v-model="file_surat_regulasi" 
                  label="Dokumen" 
                  accept=".pdf" 
                  placeholder="Pilih file"
                  class="mb-6" 
                  @change="handleFileUpload"
                  messages="Format file: PDF" 
                />
              </div>
            </div>
          </VForm>
        </VCardText>
  
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveRegulasi" min-width="100">Simpan</VBtn>
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
const file_surat_regulasi = ref(null);
const newRegulasi = ref({
  nama_surat_regulasi: '',
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
    file_surat_regulasi.value = file;
  }
};

const saveRegulasi = async () => {
    if (!newRegulasi.value.nama_surat_regulasi || !file_surat_regulasi.value) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: 'Nama surat regulasi dan dokumen harus diisi!',
        confirmButtonText: 'OK'
      });
      return;
    }

    const formData = new FormData();
    formData.append('nama_surat_regulasi', newRegulasi.value.nama_surat_regulasi);
    formData.append('file_surat_regulasi', file_surat_regulasi.value);

    try {
    const response = await apiClient.post('/regulasi', formData, {
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
    newRegulasi.value = { nama_surat_regulasi: '' };
    file_surat_regulasi.value = null;
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
