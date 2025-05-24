<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>Tambah Role</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
  
        <VCardText class="pt-4">
          <VForm ref="formRef">
            <div class="form-section">
              <div class="mt-4">
                <VTextField v-model="newRole.nama_role" label="Role" class="mb-4" required :rules="nama_roleRules" />
              </div>
            </div>
          </VForm>
        </VCardText>
  
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveRole" min-width="100">Simpan</VBtn>
          <VBtn color="grey-darken-1" variant="text" @click="closeDialog" min-width="100">Batal</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</template>
  
<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import apiClient from '@/services/api';

const formRef = ref(null);

const newRole = ref({
    nama_role: '',
});

const props = defineProps({
    dialog: Boolean,
});

const emit = defineEmits(['update:dialog', 'save']);

const closeDialog = () => {
    emit('update:dialog', false);
    resetForm();
};

const nama_roleRules = [v => !!v || 'Role wajib diisi'];

const resetForm = () => {
    newRole.value = { nama_role: ''};
};

const saveRole = async () => {
        if (!newRole.value.nama_role) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Semua kolom harus diisi!',
            confirmButtonText: 'OK'
        });
        return;
        }
    
        const formData = new FormData();
        formData.append('nama_role', newRole.value.nama_role);

        try {
        const response = await apiClient.post('/roles', formData, {
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
        resetForm();
        closeDialog();
        } catch (error) {
        console.error('Error saving role:', error.response?.data);
        Swal.fire({
            icon: 'error',
            title: 'Gagal menyimpan!',
            text: 'Terjadi kesalahan, coba lagi!',
            confirmButtonText: 'OK'
        });
    }
};
</script>