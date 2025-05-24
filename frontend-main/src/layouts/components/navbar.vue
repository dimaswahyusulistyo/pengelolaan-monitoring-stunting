<template>
  <VerticalNavLayout>
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn class="ms-n3 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon icon="bx-menu" />
        </IconBtn>
        <VSpacer />
        <NavbarThemeSwitcher class="me-1" />
        <UserProfile v-if="authStore.isAuthenticated" />
      </div>
    </template>
    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <RouterLink to="/dashboard-anak-stunting" class="app-logo app-title-wrapper">
        <h1 class="">
          <img class="d-flex" src="/logo-kencana.png" alt="Logo" width="220" height="50" />
        </h1>
      </RouterLink>
      <IconBtn class="d-block d-lg-none" @click="toggleIsOverlayNavActive(false)">
        <VIcon icon="bx-x" />
      </IconBtn>
    </template>
    <template #vertical-nav-content>
      <NavItems />
    </template>
    <slot />
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/services/auth'
import NavItems from '@/layouts/components/sidebar.vue'
import logo from '@images/logo.svg?raw'
import Footer from '@/layouts/components/footer.vue'
import VerticalNavLayout from '@layouts/components/VerticalNavLayout.vue'
import NavbarThemeSwitcher from '@/layouts/components/theme-mode.vue'
import UserProfile from '@/layouts/components/user-menu.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

</script>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;
  position: sticky;
  top: 0;
  z-index: 10; 
  padding: 10px 0;
}
.app-logo-title {
  font-size: 1.25rem;
  font-weight: 500;
  line-height: 1.75rem;
  text-transform: uppercase;
}
</style>