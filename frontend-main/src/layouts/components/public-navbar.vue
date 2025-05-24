<template>
    <PublicVerticalNavLayout>
      <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
        <RouterLink to="/" class="app-logo app-title-wrapper">
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
    </PublicVerticalNavLayout>
  </template>
  
  <script setup>
  import PublicVerticalNavLayout from '@layouts/components/PublicVerticalNavLayout.vue'
  import NavItems from '@/layouts/components/sidebar.vue'
  import Footer from '@/layouts/components/footer.vue'
  </script>
  
  <style lang="scss" scoped>
  .app-logo {
    display: flex;
    align-items: center;
    column-gap: 0.75rem;
    position: sticky;
    top: 0;
    z-index: 10; 
    padding: 10px 0;
  }
  </style>