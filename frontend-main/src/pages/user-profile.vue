<template>
  <VDialog v-model="dialog" max-width="450">
    <VCard class="profile-card">
      <div class="profile-header">
        <VCardTitle class="text-h5 d-flex align-center">
          <span class="text-white">Profil Pengguna</span>
          <VSpacer />
          <VBtn icon variant="text" @click="dialog = false">
            <i class="bx bx-x"></i>
          </VBtn>
        </VCardTitle>
      </div>

      <VCardText class="py-2">
        <div class="profile-picture-container">
          <VAvatar size="100" class="profile-avatar">
            <VImg v-if="tempProfilePic" :src="tempProfilePic" />
            <VImg v-else-if="profilePic" :src="profilePic" />
            <VImg v-else :src="defaultProfilePic" />
          </VAvatar>
          <VBtn icon variant="tonal" size="small" class="edit-photo-btn" @click="$refs.fileInput.click()">
            <VIcon icon="bx-camera" />
          </VBtn>
          <input type="file" accept="image/*" style="display: none" ref="fileInput" @change="handleFileUpload">
        </div>

        <VRow v-if="isProfileChanged" justify="center" class="mt-2">
          <VCol cols="12" class="d-flex justify-center gap-2">
            <VBtn size="small" color="error" variant="outlined" @click="cancelProfileUpdate">
              Batal
            </VBtn>
            <VBtn size="small" color="primary" @click="saveProfileUpdate" :loading="profileSubmitting">
              Simpan
            </VBtn>
          </VCol>
        </VRow>

        <VRow class="mt-4" justify="center">
          <VCol cols="12">
            <VCard class="info-card elevation-1" variant="flat">
              <VCardItem class="py-2">
                <template #prepend>
                  <VAvatar color="info" variant="tonal" size="36">
                    <i class="bx bx-user text-h5"></i>
                  </VAvatar>
                </template>
                <VCardTitle>
                  <div class="info-label">Username</div>
                  <div class="info-value">{{ username }}</div>
                </VCardTitle>
              </VCardItem>
            </VCard>
          </VCol>

          <VCol cols="12">
            <VCard class="info-card elevation-1" variant="flat">
              <VCardItem class="py-2">
                <template #prepend>
                  <VAvatar color="success" variant="tonal" size="36">
                    <i class="bx bx-briefcase text-h5"></i>
                  </VAvatar>
                </template>
                <VCardTitle>
                  <div class="info-label">Role</div>
                  <div class="info-value">{{ role }}</div>
                </VCardTitle>
              </VCardItem>
            </VCard>
          </VCol>
        </VRow>

        <VRow justify="center" class="mt-4 mb-2">
          <VCol cols="12" class="text-center">
            <VBtn prepend-icon="bx bx-lock-alt" color="primary" variant="tonal" @click="showPasswordDialog = true">
              Ubah Password
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VDialog>

  <VDialog v-model="showPasswordDialog" max-width="400">
    <VCard>
      <VCardTitle class="text-h5 d-flex align-center mb-2">
        <span>Ubah Password</span>
        <VSpacer />
        <VBtn icon variant="text" @click="closeAllDialogs">
          <i class="bx bx-x"></i>
        </VBtn>
      </VCardTitle>

      <VCardText>
        <VBtn variant="text" class="mb-2" @click="showPasswordDialog = false">
          <i class="bx bx-arrow-back mr-2"></i> Kembali
        </VBtn>

        <VRow>
          <VCol cols="12">
            <VTextField label="Password Lama" v-model="oldPassword" :type="showOldPassword ? 'text' : 'password'"
              :error-messages="oldPasswordError" @input="validatePassword" @focus="oldPasswordTouched = true"
              @blur="oldPasswordTouched = true">
              <template #append-inner>
                <VIcon :icon="showOldPassword ? 'bx-hide' : 'bx-show'" @click="showOldPassword = !showOldPassword"
                  class="cursor-pointer" />
              </template>
            </VTextField>
          </VCol>

          <VCol cols="12">
            <VTextField label="Password Baru" v-model="newPassword" :type="showNewPassword ? 'text' : 'password'"
              :disabled="!oldPassword" :error-messages="newPasswordError" @input="validatePassword"
              @focus="newPasswordTouched = true" @blur="newPasswordTouched = true">
              <template #append-inner>
                <VIcon :icon="showNewPassword ? 'bx-hide' : 'bx-show'" @click="showNewPassword = !showNewPassword"
                  class="cursor-pointer" />
              </template>
            </VTextField>
          </VCol>

          <VCol cols="12">
            <VTextField label="Konfirmasi Password Baru" v-model="confirmPassword"
              :type="showConfirmPassword ? 'text' : 'password'" :disabled="!newPassword"
              :error-messages="confirmPasswordError" @input="validatePassword" @focus="confirmPasswordTouched = true"
              @blur="confirmPasswordTouched = true">
              <template #append-inner>
                <VIcon :icon="showConfirmPassword ? 'bx-hide' : 'bx-show'"
                  @click="showConfirmPassword = !showConfirmPassword" class="cursor-pointer" />
              </template>
            </VTextField>
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions class="pa-4 d-flex justify-end">
        <VBtn color="primary" @click="savePassword" :disabled="!isPasswordFormValid || passwordSubmitting"
          :loading="passwordSubmitting">
          Simpan
        </VBtn>
        <VBtn color="grey-darken-1" class="me-2" @click="resetPasswordForm" :disabled="passwordSubmitting">
          Reset
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore } from '@/services/auth'
import profileService from '@/services/profile'
import apiClient from '@/services/api';

const authStore = useAuthStore()
const dialog = defineModel()
const showPasswordDialog = ref(false)
const fileInput = ref(null)
const tempProfilePic = ref(null)
const selectedFile = ref(null)
const isProfileChanged = computed(() => !!tempProfilePic.value)
const profileSubmitting = ref(false)
const passwordSubmitting = ref(false)

const defaultProfilePic = '/images/avatars/default-avatar2.jpg'

// State terpisah untuk data user yang fresh dari API
const userData = ref(null)
const username = computed(() => userData.value?.username || authStore.user?.username || '')
const userId = computed(() => authStore.user?.id)

const role = computed(() => {
  // Prioritaskan data dari userData (fresh) daripada authStore
  const user = userData.value || authStore.user
  const roleData = user?.role
  if (roleData && typeof roleData === 'object') {
    return roleData.nama_role || roleData.name || ''
  }
  return roleData || ''
})

const fetchCurrentUser = async (id) => {
  if (!id) return
  try {
    const response = await apiClient.get(`/users/${id}`)
    // Update userData tanpa mengubah authStore
    userData.value = response.data
    
    // Hanya update authStore dengan field-field yang tidak tersimpan di local storage
    // atau field yang memang perlu diupdate di store
  } catch (error) {
    console.error('Gagal mengambil data user:', error)
  }
}

// Computed untuk profile picture yang menggunakan userData.value sebagai prioritas
const profilePic = computed(() => {
  // Gunakan userData.value terlebih dahulu (data fresh), baru authStore.user
  const user = userData.value || authStore.user
  
  if (user?.foto_profil) {
    const baseUrl = 'http://localhost:8000' 
    const photoPath = user.foto_profil

    if (photoPath.startsWith('http')) {
      return photoPath
    }

    const cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl
    const cleanPhotoPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`

    return `${cleanBaseUrl}${cleanPhotoPath}`
  }
  return defaultProfilePic
})

watch(userId, (id) => {
  if (id) fetchCurrentUser(id)
}, { immediate: true })

onMounted(() => {
  const id = authStore.user?.id
  if (id) {
    fetchCurrentUser(id)
  }
})

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']
  const maxSize = 2 * 1024 * 1024

  if (!allowedTypes.includes(file.type)) {
    alert('Hanya file JPG, JPEG, dan PNG yang diperbolehkan')
    resetFileInput()
    return
  }

  if (file.size > maxSize) {
    alert('Ukuran file maksimal 2MB')
    resetFileInput()
    return
  }

  selectedFile.value = file
  tempProfilePic.value = URL.createObjectURL(file)
}

const resetFileInput = () => {
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const saveProfileUpdate = async () => {
  if (!selectedFile.value) return

  profileSubmitting.value = true

  try {
    const response = await profileService.updateProfile({ foto_profil: selectedFile.value })
    
    // Tunggu sebentar untuk memastikan backend sudah terupdate
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Fetch ulang data user dan update userData langsung
    await fetchCurrentUser(authStore.user?.id)
    
    // Alternatif: Update foto_profil langsung tanpa fetch ulang
    // if (response.data?.foto_profil) {
    //   userData.value = {
    //     ...userData.value,
    //     foto_profil: response.data.foto_profil
    //   }
    // }
    
    alert('Foto profil berhasil diperbarui')
    resetProfileEdit()
  } catch (error) {
    console.error('Profile update error:', error)
    alert(error.response?.data?.message || 'Gagal mengubah foto profil')
  } finally {
    profileSubmitting.value = false
  }
}

const cancelProfileUpdate = () => {
  resetProfileEdit()
}

const resetProfileEdit = () => {
  tempProfilePic.value = null
  selectedFile.value = null
  resetFileInput()
}

const closeAllDialogs = () => {
  showPasswordDialog.value = false
  dialog.value = false
}

const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const showOldPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const oldPasswordError = ref('')
const newPasswordError = ref('')
const confirmPasswordError = ref('')
const isPasswordFormValid = computed(() =>
  oldPassword.value &&
  newPassword.value &&
  confirmPassword.value &&
  !oldPasswordError.value &&
  !newPasswordError.value &&
  !confirmPasswordError.value
)

const oldPasswordTouched = ref(false)
const newPasswordTouched = ref(false)
const confirmPasswordTouched = ref(false)

const validatePassword = () => {
  if (oldPasswordTouched.value) {
    oldPasswordError.value = oldPassword.value ? '' : 'Password lama harus diisi'
  }

  if (newPasswordTouched.value) {
    newPasswordError.value = ''
    if (newPassword.value) {
      if (newPassword.value.length < 8) {
        newPasswordError.value = 'Password baru minimal 8 karakter'
      } else if (newPassword.value === oldPassword.value) {
        newPasswordError.value = 'Password baru tidak boleh sama dengan password lama'
      }
    } else {
      newPasswordError.value = 'Password baru harus diisi'
    }
  }

  if (confirmPasswordTouched.value) {
    confirmPasswordError.value = ''
    if (confirmPassword.value) {
      if (confirmPassword.value !== newPassword.value) {
        confirmPasswordError.value = 'Konfirmasi password tidak sesuai'
      }
    } else {
      confirmPasswordError.value = 'Konfirmasi password harus diisi'
    }
  }
}

const savePassword = async () => {
  oldPasswordTouched.value = true
  newPasswordTouched.value = true
  confirmPasswordTouched.value = true

  validatePassword()

  if (!isPasswordFormValid.value) return

  const confirmResult = await Swal.fire({
    html: 'Anda yakin ingin mengubah <strong>password</strong>  Anda?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#5b8cff',
    cancelButtonColor: '#94a3b8',
    confirmButtonText: 'Ubah',
    cancelButtonText: 'Batal',
    background: '#f8fafc',
    customClass: { popup: 'rounded-lg shadow-xl', title: 'text-lg font-medium' }
  })

  if (!confirmResult.isConfirmed) return

  passwordSubmitting.value = true

  try {
    await profileService.changePassword({
      currentPassword: oldPassword.value,
      newPassword: newPassword.value,
      confirmPassword: confirmPassword.value
    })

    await Swal.fire({
      html: '<strong>Password</strong>  berhasil diperbarui',
      icon: 'success',
      background: '#f8fafc',
      customClass: { popup: 'rounded-lg shadow-xl' },
      timer: 3000,
      showConfirmButton: false
    })

    oldPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    showPasswordDialog.value = false

    oldPasswordTouched.value = false
    newPasswordTouched.value = false
    confirmPasswordTouched.value = false

  } catch (error) {
    if (error.response?.status === 422) {
      oldPasswordError.value = error.response.data?.message?.includes('current_password')
        ? 'Password saat ini tidak sesuai'
        : ''
    } else {
      await Swal.fire({
        title: 'Gagal!',
        html: error.response?.data?.message || 'Gagal mengubah password',
        icon: 'error',
        confirmButtonColor: '#ff6b6b',
        background: '#f8fafc',
        customClass: { popup: 'rounded-lg shadow-xl', title: 'text-lg font-medium' }
      })
    }
  } finally {
    passwordSubmitting.value = false
  }
}

const resetPasswordForm = () => {
  oldPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
  oldPasswordError.value = ''
  newPasswordError.value = ''
  confirmPasswordError.value = ''
  oldPasswordTouched.value = false
  newPasswordTouched.value = false
  confirmPasswordTouched.value = false
}

watch(() => authStore.isAuthenticated, (newVal) => {
  if (!newVal) {
    dialog.value = false
    showPasswordDialog.value = false
  }
})

watch(dialog, (newVal) => {
  if (!newVal) {
    resetProfileEdit()
    oldPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    oldPasswordError.value = ''
    newPasswordError.value = ''
    confirmPasswordError.value = ''
    oldPasswordTouched.value = false
    newPasswordTouched.value = false
    confirmPasswordTouched.value = false
  }
})

watch([oldPassword, newPassword, confirmPassword], () => {
  validatePassword()
})
</script>

<style scoped>
.profile-card {
  overflow: hidden;
}

.profile-header {
  background: linear-gradient(135deg, var(--v-primary-base), var(--v-primary-darken2));
  padding: 12px;
  margin-bottom: 24px;
}

.profile-picture-container {
  position: relative;
  display: flex;
  justify-content: center;
  margin: -50px auto 20px;
  width: 100%;
}

.profile-avatar {
  border: 3px solid white;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  background-color: white;
  width: 100px;
  height: 100px;
}

.edit-photo-btn {
  position: absolute;
  bottom: -10px;
  right: calc(50% - 80px + 30px);
  z-index: 2;
}

.info-card {
  transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid #eee;
  padding: 8px 12px;
}

.info-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
}

.info-label {
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-bottom: 2px;
}

.info-value {
  font-size: 1rem;
  font-weight: 500;
  color: var(--v-theme-on-surface);
}
</style>