<template>
  <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success" bordered>
    <VAvatar class="cursor-pointer" color="primary" variant="tonal">
      <VImg v-if="userAvatar && userAvatar !== defaultAvatar" :src="userAvatar" :key="avatarKey" />
      <VImg v-else :src="defaultAvatar" />
    </VAvatar>

    <VMenu activator="parent" width="230" location="bottom end" offset="14px">
      <VList>
        <VListItem>
          <template #prepend>
            <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success">
              <VAvatar color="primary" variant="tonal">
                <VImg v-if="userAvatar && userAvatar !== defaultAvatar" :src="userAvatar" :key="avatarKey" />
                <VImg v-else :src="defaultAvatar" />
              </VAvatar>
            </VBadge>
          </template>
          <VListItemTitle class="font-weight-semibold">{{ username }}</VListItemTitle>
          <VListItemSubtitle>{{ userRole }}</VListItemSubtitle>
        </VListItem>

        <VDivider class="my-2" />

        <VListItem @click="showProfileDialog = true">
          <template #prepend>
            <VIcon class="me-2" icon="bx-user" size="22" />
          </template>
          <VListItemTitle>Profil</VListItemTitle>
        </VListItem>

        <VDivider class="my-2" />

        <VListItem @click="handleLogout">
          <template #prepend>
            <VIcon class="me-2" icon="bx-log-out" size="22" />
          </template>
          <VListItemTitle>Keluar</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>

    <UserProfileDialog 
      v-model="showProfileDialog" 
      @profile-updated="handleProfileUpdate"
    />
  </VBadge>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/services/auth'
import UserProfileDialog from '@/pages/user-profile.vue'
import apiClient from '@/services/api';

const router = useRouter()
const authStore = useAuthStore()
const showProfileDialog = ref(false)
const defaultAvatar = '/images/avatars/default-avatar2.jpg'

// State terpisah untuk data user yang fresh dari API
const userData = ref(null)
const userId = computed(() => authStore.user?.id)
const avatarKey = ref(0) // Key untuk memaksa re-render gambar

const fetchCurrentUser = async (id) => {
  if (!id) return
  try {
    const response = await apiClient.get(`/users/${id}`)
    // Update userData tanpa mengubah authStore atau localStorage
    userData.value = response.data
    
    // Hanya update field tertentu di authStore tanpa menyentuh localStorage
    // TIDAK melakukan: authStore.user = { ...authStore.user, ...response.data }
    // Sebaliknya, gunakan method updateUserData jika ada, atau skip update authStore
    if (authStore.updateUserData) {
      authStore.updateUserData({
        foto_profil: response.data.foto_profil,
        username: response.data.username,
        // hanya field yang diperlukan untuk UI
      })
    }
    
    // Force update avatar key to trigger re-render
    avatarKey.value++
  } catch (error) {
    console.error('Gagal mengambil data user:', error)
  }
}

// Watch untuk perubahan userId - fetch data awal
watch(userId, (id) => {
  if (id) fetchCurrentUser(id)
}, { immediate: true })

// Watch untuk perubahan foto_profil di userData (data fresh dari API)
watch(() => userData.value?.foto_profil, (newPhoto, oldPhoto) => {
  if (newPhoto !== oldPhoto && newPhoto !== undefined) {
    // Force re-render avatar dengan key baru
    avatarKey.value++
    
    // Update authStore untuk konsistensi UI tanpa mengubah localStorage
    if (authStore.updateUserData && newPhoto) {
      authStore.updateUserData({ foto_profil: newPhoto })
    }
  }
}, { deep: false })

// Watch untuk perubahan foto_profil di authStore (jika ada update dari komponen lain)
watch(() => authStore.user?.foto_profil, (newPhoto, oldPhoto) => {
  if (newPhoto !== oldPhoto && newPhoto !== undefined) {
    
    // Sinkronkan dengan userData jika berbeda
    if (userData.value && userData.value.foto_profil !== newPhoto) {
      userData.value = { ...userData.value, foto_profil: newPhoto }
    }
    
    // Force re-render avatar
    avatarKey.value++
    
    // Optional: Delay untuk memastikan image cache cleared
    nextTick(() => {
      avatarKey.value++
    })
  }
}, { deep: false })

// Watch untuk perubahan authStore.currentUserData (jika menggunakan dual state)
watch(() => authStore.currentUserData?.foto_profil, (newPhoto, oldPhoto) => {
  if (newPhoto !== oldPhoto && newPhoto !== undefined) {
    console.log('Foto profil berubah dari currentUserData:', oldPhoto, '→', newPhoto)
    
    // Sinkronkan dengan userData
    if (userData.value && userData.value.foto_profil !== newPhoto) {
      userData.value = { ...userData.value, foto_profil: newPhoto }
    }
    
    // Force re-render avatar
    avatarKey.value++
  }
}, { deep: false })

// Computed untuk avatar yang menggunakan userData sebagai prioritas
const userAvatar = computed(() => {
  // Prioritaskan userData.value (fresh data) daripada authStore.user
  const user = userData.value || authStore.currentUser || authStore.user
  
  if (user?.foto_profil) {
    const baseUrl = 'http://localhost:8000' 
    const photoPath = user.foto_profil

    if (photoPath.startsWith('http')) {
      // Tambahkan timestamp dan avatarKey untuk menghindari cache
      return `${photoPath}?t=${Date.now()}&k=${avatarKey.value}`
    }

    const cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl
    const cleanPhotoPath = photoPath.startsWith('/') ? photoPath : `/${photoPath}`

    // Tambahkan timestamp dan avatarKey untuk menghindari cache
    return `${cleanBaseUrl}${cleanPhotoPath}?t=${Date.now()}&k=${avatarKey.value}`
  }
  return defaultAvatar
})

// Computed untuk username dan role dengan prioritas userData
const username = computed(() => {
  const user = userData.value || authStore.currentUser || authStore.user
  return user?.username || ''
})

const userRole = computed(() => {
  const user = userData.value || authStore.currentUser || authStore.user
  const role = user?.role
  if (typeof role === 'object') {
    return role.nama_role || role.name || ''
  }
  return role || ''
})

onMounted(() => {
  const id = authStore.user?.id
  if (id) {
    fetchCurrentUser(id)
  }
})

// Handler untuk event dari dialog profile
const handleProfileUpdate = async (updatedUserData) => {
  console.log('Profile updated:', updatedUserData)
  
  // Update userData lokal langsung tanpa menyentuh authStore atau localStorage
  if (userData.value) {
    userData.value = { ...userData.value, ...updatedUserData }
  }
  
  // Opsional: Update authStore hanya untuk UI reactivity, tanpa localStorage
  if (authStore.updateUserData) {
    authStore.updateUserData(updatedUserData)
  }
  
  // Force update avatar untuk re-render
  avatarKey.value++
  
  // Refresh data dari server untuk sinkronisasi setelah delay kecil
  await nextTick()
  setTimeout(async () => {
    await fetchCurrentUser(authStore.user?.id)
  }, 500) // Delay 500ms untuk memastikan backend sudah update
}

const handleLogout = async () => {
  try {
    await authStore.logout() // Ini akan menghapus localStorage
    router.push('/login')
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal logout')
  }
}

// Method untuk force refresh avatar (bisa dipanggil dari komponen parent)
const refreshAvatar = async () => {
  avatarKey.value++
  await fetchCurrentUser(authStore.user?.id)
}

// Expose method untuk dipanggil dari komponen parent jika diperlukan
defineExpose({
  refreshAvatar,
  fetchCurrentUser
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>