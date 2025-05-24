<template>
    <VDialog :model-value="dialog" @update:model-value="$emit('update:dialog', $event)" max-width="600px">
      <VCard class="form">
        <VCardTitle class="text-h5 d-flex align-center mb-2">
          <span>Tambah User</span>
          <VSpacer />
          <VBtn icon variant="text" @click="closeDialog">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
  
        <VCardText class="pt-4">
          <VForm ref="formRef">
            <div class="form-section">
              <div class="mt-4">
                <VTextField v-model="newUser.username" label="Username" class="mb-4" required :rules="usernameRules" />
                <VTextField v-model="newUser.password" label="Password" class="mb-4" required :rules="passwordRules" type="password"/>
                <VSelect
                  v-model="newUser.id_role"
                  label="Pilih Role"
                  :items="roles"
                  item-title="nama_role"
                  item-value="id_role"
                  class="mb-4"
                  required
                  :loading="loadingRoles"
                  :rules="roleRules"
                />
                <VSelect
                  v-model="newUser.id_desa"
                  label="Pilih Desa"
                  :items="desa"
                  item-title="title"
                  item-value="value"
                  class="mb-4"
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
          <VBtn color="primary" @click="saveUser" min-width="100">Simpan</VBtn>
          <VBtn color="grey-darken-1" variant="text" @click="closeDialog" min-width="100">Batal</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
</template>
  
<script setup>
import { ref, onMounted, defineProps, defineEmits } from 'vue';
import apiClient from '@/services/api';

const formRef = ref(null);
const loadingRoles = ref(false);
const loadingDesa = ref(false);
const roles = ref([]);
const desa = ref([]);
const kecamatan = ref([]);
const foto_profil = ref(null);
const imagePreview = ref(null);

const newUser = ref({
    username: '',
    password: '',
    id_role: '',
    id_desa: '',
});

const props = defineProps({
    dialog: Boolean,
});

const emit = defineEmits(['update:dialog', 'save']);

const closeDialog = () => {
    emit('update:dialog', false);
    resetForm();
};

const usernameRules = [v => !!v || 'Username wajib diisi'];
const passwordRules = [
    v => !!v || 'Password wajib diisi',
    v => (v && v.length >= 6) || 'Password minimal 6 karakter',
];
const roleRules = [v => !!v || 'Role wajib dipilih'];
  
const resetForm = () => {
    newUser.value = { username: '', password: '', id_role: '', id_desa: '', };
    foto_profil.value = null;
    imagePreview.value = null;
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
        Swal.fire({
            icon: 'error',
            title: 'Format file tidak valid!',
            text: 'Hanya diperbolehkan PNG, JPG, dan JPEG.',
            confirmButtonText: 'OK',
        });
        foto_profil.value = null;
        imagePreview.value = null;
    }
};

const saveUser = async () => {
        if (!newUser.value.username || !newUser.value.password || !newUser.value.id_role) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Semua kolom harus diisi!',
            confirmButtonText: 'OK'
        });
        return;
        }
    
        const formData = new FormData();
        formData.append('username', newUser.value.username);
        formData.append('password', newUser.value.password);
        formData.append('id_role', newUser.value.id_role);
        formData.append('id_desa', newUser.value.id_desa);
        if (foto_profil.value) {
        formData.append('foto_profil', foto_profil.value);
        }
    
        try {
        const response = await apiClient.post('/users', formData, {
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
        console.error('Error saving user:', error.response?.data);
        Swal.fire({
            icon: 'error',
            title: 'Gagal menyimpan!',
            text: 'Terjadi kesalahan, coba lagi!',
            confirmButtonText: 'OK'
        });
    }
};

onMounted(() => {
    fetchRoles();
    fetchDesa();
});
</script>