<template>
  <ThemeSwitcher :themes="themes" />
</template>

<script setup>
const themes = [
  {
    name: 'light',
    icon: 'bx-sun',
  },
  {
    name: 'dark',
    icon: 'bx-moon',
  },
]
</script>
