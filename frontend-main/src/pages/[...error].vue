<script setup>
import misc404 from '@images/pages/404.png'
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    router.push('/')
  }
}
</script>

<template>
  <div class="misc-wrapper">
    <ErrorHeader
      status-code="403"
      title=" Akses Ditolak ⚠️"
      description="Anda tidak memiliki izin untuk mengakses menu ini."
    />

    <div class="text-center">
      <VBtn
        @click="goBack"
        class="mb-6"
      >
        Back to Previous Page
      </VBtn>
    </div>

    <!-- 👉 Image -->
    <div class="misc-avatar w-100 text-center">
      <VImg
        :src="misc404"
        alt="Page Not Found"
        :max-width="300"
        class="mx-auto"
      />
    </div>
  </div>
</template>

<style lang="scss">
@use "@core/scss/template/pages/misc.scss";
</style>