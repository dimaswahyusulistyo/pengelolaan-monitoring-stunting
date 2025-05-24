<template>
  <DefaultLayoutWithVerticalNav>
    <RouterView />
  </DefaultLayoutWithVerticalNav>
</template>

<script setup>
import DefaultLayoutWithVerticalNav from './components/navbar.vue'
</script>

<style lang="scss">
@use "@layouts/styles/default-layout";
</style>