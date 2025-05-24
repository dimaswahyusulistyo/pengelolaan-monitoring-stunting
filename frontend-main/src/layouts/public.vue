<template>
    <PublicLayoutWithVerticalNav>
      <RouterView />
    </PublicLayoutWithVerticalNav>
  </template>
  
  <script setup>
  import PublicLayoutWithVerticalNav from './components/public-navbar.vue'
  </script>
  
  <style lang="scss">
  @use "@layouts/styles/default-layout";
  </style>