<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>{{ editMode ? 'Edit User' : 'Tambah User' }}</span>
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
                  v-model="formUser.username" 
                  label="Username" 
                  class="mb-6" 
                  required 
                />
                
                <VTextField 
                  v-model="formUser.password" 
                  label="Password (opsional)" 
                  type="password" 
                  class="mb-6" 
                />
                
                <VSelect
                  v-model="formUser.id_role"
                  label="Pilih Role"
                  :items="roles"
                  item-title="nama_role"
                  item-value="id_role"
                  class="mb-6"
                  required
                  :loading="loadingRoles"
                />

                <VSelect
                  v-model="formUser.id_desa"
                  label="Pilih Desa"
                  :items="desa"
                  item-title="title"
                  item-value="value"
                  class="mb-6"
                  required
                  :loading="loadingDesa"
                />
  
                <VFileInput 
                  v-model="foto_profil"
                  label="Foto Profil" 
                  accept=".jpg,.jpeg,.png" 
                  class="mb-4" 
                  @change="handleFileUpload"
                  messages="Format file: PNG, JPG, JPEG" 
                />
                
                <VImg v-if="imagePreview" :src="imagePreview" class="mb-4" max-height="150" contain />
              </div>
            </div>
          </VForm>
        </VCardText>
        
        <VCardActions class="pt-2 pb-4 px-4">
          <VSpacer />
          <VBtn color="primary" @click="saveUser" min-width="100">
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
const loadingRoles = ref(false);
const loadingDesa = ref(false);
const roles = ref([]);
const desa = ref([]);
const foto_profil = ref(null);
const imagePreview = ref(null);

const BASE_URL = 'http://localhost:8000'
  
const formUser = ref({
    id_user: null,
    username: '',
    password: '',
    role: '',
    desa: '',
    foto_profil: ''
});
  
const props = defineProps({
    dialog: Boolean,
    editMode: Boolean,
    editedUser: Object,
});
  
const emit = defineEmits(['update:dialog', 'save']);
  
watch(
    () => props.editedUser,
    (newValue) => {
      if (newValue) {
        formUser.value = {
          id_user: newValue.id_user || null,
          username: newValue.username || '',
          password: '', 
          id_role: newValue.id_role || '',
          id_desa: newValue.id_desa || '',
          foto_profil: newValue.foto_profil || ''
        };
        imagePreview.value = newValue.foto_profil ? `${BASE_URL}/${newValue.foto_profil}` : null;
        console.log("Preview Image Path:", imagePreview.value);
      }
    },
    { deep: true, immediate: true }
  );
  
  const closeDialog = () => {
    emit('update:dialog', false);
  };

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file && (file.type === 'image/jpeg' || file.type === 'image/png')) {
      foto_profil.value = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreview.value = e.target.result;
      };
      reader.readAsDataURL(file);
    } else {
      alert('Format file tidak valid! Hanya diperbolehkan PNG, JPG, dan JPEG.');
      foto_profil.value = null;
      imagePreview.value = null;
    }
  };
  
const fetchRoles = async () => {
    loadingRoles.value = true;
    try {
        const response = await apiClient.get('/roles', {
          params: { type: 'dropdown'}
        }); 
        roles.value = response.data.data; 
    } catch (error) {
        console.error('Gagal mengambil role:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal mengambil daftar role!',
            text: 'Silakan coba lagi.',
            confirmButtonText: 'OK',
        });
        } finally {
            loadingRoles.value = false;
        }
};
  
const fetchDesa = async () => {
    loadingDesa.value = true;
    try {
        const response = await apiClient.get('/desa'); 
        desa.value = response.data.data.map((item) => ({
          title: `${item.desa} - ${item.kecamatan}`,
          value: item.id,
        })); 
    } catch (error) {
        console.error('Gagal mengambil desa:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal mengambil daftar desa!',
            text: 'Silakan coba lagi.',
            confirmButtonText: 'OK',
        });
        } finally {
            loadingDesa.value = false;
        }
};

const saveUser = async () => {
    if (!formUser.value.username || !formUser.value.id_role) {
        await Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Cek lagi kolomnya!',
    });
      return;
    }
  
    const formData = new FormData();
    formData.append('username', formUser.value.username);
    formData.append('id_role', formUser.value.id_role);
    formData.append('id_desa', formUser.value.id_desa);
    if (formUser.value.password) {
      formData.append('password', formUser.value.password);
    }
    if (foto_profil.value) {
      formData.append('foto_profil', foto_profil.value);
    }
  
    try {
      let response;
      if (props.editMode && formUser.value.id_user) {
        formData.append('_method', 'PUT');
        response = await apiClient.post(`/users/${formUser.value.id_user}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      } else {
        response = await apiClient.post('/users', formData, {
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
  
onMounted(() => {
    fetchRoles();
    fetchDesa();
});
</script>