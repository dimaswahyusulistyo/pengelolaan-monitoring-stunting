<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>{{ editMode ? 'Edit Role' : 'Tambah Role' }}</span>
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
                  v-model="formRole.nama_role" 
                  label="Role" 
                  class="mb-6" 
                  required 
                />
              </div>
            </div>
          </VForm>
        </VCardText>
        
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveRole" min-width="100">
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
import { ref, defineProps, defineEmits, watch, onMounted } from 'vue';
import apiClient from '@/services/api';
  
const formRef = ref(null);
  
const formRole = ref({
    id_role: null,
    nama_role: ''
});
  
const props = defineProps({
    dialog: Boolean,
    editMode: Boolean,
    editedRole: Object,
});
  
const emit = defineEmits(['update:dialog', 'save']);
  
watch(
    () => props.editedRole,
    (newValue) => {
      if (newValue) {
        formRole.value = {
          id_role: newValue.id_role || null,
          nama_role: newValue.nama_role || ''
        };
      }
    },
    { deep: true, immediate: true }
  );
  
  const closeDialog = () => {
    emit('update:dialog', false);
  };

const saveRole = async () => {
    if (!formRole.value.nama_role) {
        await Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Cek lagi kolomnya!',
    });
      return;
    }
  
    const formData = new FormData();
    formData.append('nama_role', formRole.value.nama_role);
  
    try {
      let response;
      if (props.editMode && formRole.value.id_role) {
        formData.append('_method', 'PUT');
        response = await apiClient.post(`/roles/${formRole.value.id_role}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      } else {
        response = await apiClient.post('/roles', formData, {
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
      console.error('Error:', error.response?.data || error.message);
      alert('Terjadi kesalahan saat menyimpan data.');
    }
};
</script>