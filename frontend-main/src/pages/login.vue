<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4" :class="$vuetify.theme.dark ? 'bg-grey-darken-4' : 'bg-grey-lighten-4'">
    <div class="position-relative my-sm-16">
      <VCard
        class="auth-card"
        max-width="500"
        :class="[$vuetify.display.smAndUp ? 'pa-8' : 'pa-4']"
        elevation="8"
        rounded="lg"
      >
        <VCardItem class="justify-center pt-4">
          <RouterLink to="/" class="app-logo">
            <img class="d-flex" src="/logo-kencana.png" alt="Logo" width="220" height="50" />
          </RouterLink>
        </VCardItem>
        <VCardText class="text-center pb-0">
          <h4 class="text-h6 font-weight-bold mb-2" style="letter-spacing: 0.5px;" 
              :style="{ 
                color: $vuetify.theme.dark ? '#696CFF' : '#696CFF',
                textShadow: $vuetify.theme.dark ? '0 1px 2px rgba(0,0,0,0.3)' : 'none'
              }">
            SISTEM KENDALI CEGAH DAN PENANGANAN STUNTING KARANGANYAR
          </h4>
          <VDivider class="my-4"></VDivider>
          <p class="text-body-1" :class="$vuetify.theme.dark ? 'text-lighten-2' : 'text-medium-emphasis'">
            Silakan login untuk melanjutkan
          </p>
        </VCardText>
        <VCardText class="pt-4">
          <VForm @submit.prevent="login">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.username"
                  label="Username"
                  placeholder="Masukkan username Anda"
                  :error-messages="errors.username"
                  prepend-inner-icon="bx-user"
                  variant="outlined"
                  :color="$vuetify.theme.dark ? '#696CFF' : '#696CFF'"
                  density="comfortable"
                  required
                  :bg-color="undefined"
                />
              </VCol>
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  prepend-inner-icon="bx-lock-alt"
                  :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  :error-messages="errors.password"
                  variant="outlined"
                  :color="$vuetify.theme.dark ? '#696CFF' : '#696CFF'"
                  density="comfortable"
                  required
                  :bg-color="undefined"
                />
                <div class="d-flex align-center justify-space-between flex-wrap my-4">
                  <VCheckbox
                    v-model="form.remember"
                    label="Ingat saya"
                    density="comfortable"
                    :color="$vuetify.theme.dark ? '#90caf9' : '#244D8A'"
                  />
                </div>
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :color="$vuetify.theme.dark ? '#90caf9' : '#696CFF'"
                  class="mt-2 py-5 font-weight-medium"
                  :class="{ 'text-white': !$vuetify.theme.dark }"
                  elevation="2"
                  rounded="lg"
                >
                  <span>LOGIN</span>
                  <!-- <VIcon end icon="bx-log-in" class="ms-1"></VIcon> -->
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
        
        <VCardText class="text-center text-body-2 pt-0" :class="$vuetify.theme.dark ? 'text-lighten-2' : 'text-medium-emphasis'">
          <p class="mb-0">© {{ new Date().getFullYear() }} Kabupaten Karanganyar. All rights reserved.</p>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/services/auth'
import apiClient from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  username: '',
  password: '',
  remember: false,
})

const isPasswordVisible = ref(false)
const isLoading = ref(false)
const errors = ref({
  username: '',
  password: '',
})

const login = async () => {
  isLoading.value = true
  errors.value = { username: '', password: '' }
  
  try {
    const response = await apiClient.post('/login', {
      username: form.value.username,
      password: form.value.password
    })

    const userData = response.data.data
    authStore.setToken(userData.token)
    authStore.setUser({
      id: userData.id_user,
      username: userData.username,
      role: userData.role,
      bidang: userData.bidang,
      id_desa: userData.id_desa
    })

    if (form.value.remember) {
      localStorage.setItem('token', userData.token)
      localStorage.setItem('rememberMe', 'true')
    } else {
      sessionStorage.setItem('token', userData.token)
      localStorage.removeItem('rememberMe')
    }

    const redirect = router.currentRoute.value.query.redirect
    if (redirect) {
      return router.push(redirect)
    }

    const role = userData.role
    if (role === 'Admin') {
      router.push({ name: 'DashboardAnakStunting' })
    } else if ([
      'Dinas Kesehatan',
      'Dinas KB',
      'Dinas Sosial',
      'Dinas Pertanian',
      'Dinas PU',
      'Dispermades'
    ].includes(role)) {
      router.push({ name: 'DashboardAnakStunting' })
    } else if (role === 'Kader Desa') {
      router.push({ name: 'DataAnakStunting' })
    } else {
      router.push('/')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      errors.value = {
        username: 'Invalid credentials',
        password: 'Invalid credentials'
      }
    } else {
      console.error('Login error:', error)
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style lang="scss">
@use "@core/scss/template/pages/page-auth";
.auth-card {
  transition: all 0.3s ease;
}

:deep(.v-field--variant-outlined.v-field--active) {
  background-color: transparent !important;
}

:deep(.v-field__input) {
  background-color: transparent !important;
}
</style>