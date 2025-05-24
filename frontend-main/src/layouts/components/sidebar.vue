<template>
  <VerticalNavSectionTitle :item="{ heading: 'Dashboard' }" />

  <VerticalNavLink :item="{
    title: 'Anak Stunting',
    icon: 'bx-child',
    to: '/dashboard-anak-stunting',
  }" />

  <VerticalNavLink :item="{
    title: 'Keluarga Berisiko',
    icon: 'bx-group',
    to: '/dashboard-keluarga-berisiko',
  }" />

  <VerticalNavLink :item="{
    title: 'Program Intervensi',
    icon: 'bx-customize',
    to: '/dashboard-program-intervensi',
  }" />

  <VerticalNavLink :item="{
    title: 'Regulasi',
    icon: 'bx-book-bookmark',
    to: '/dashboard-regulasi',
  }" />

  <VerticalNavSectionTitle :item="{ heading: 'Master Data' }" />

  <VerticalNavLink :item="{
    title: 'Data Anak Stunting',
    icon: 'bx-clipboard',
    to: '/data-anak-stunting',
  }" />

  <VerticalNavLink :item="{
    title: 'Data Keluarga Berisiko',
    icon: 'bx-home-heart',
    to: '/data-keluarga-berisiko',
  }" />

  <VerticalNavLink :item="{
    title: 'Data Program Intervensi',
    icon: 'bx-task',
    to: '/data-cakupan',
  }" />

  <VerticalNavSectionTitle :item="{ heading: 'Kegiatan TPPS' }" />
  <VerticalNavLink :item="{
    title: 'Laporan Kegiatan',
    icon: 'bx-file',
    to: '/report-kegiatan',
  }" />
  <VerticalNavLink :item="{
    title: 'Rencana Aksi',
    icon: 'bx-calendar',
    to: '/rencana-aksi',
  }" />

  <VerticalNavSectionTitle :item="{ heading: 'Manajemen Sistem' }" />
  <VerticalNavLink :item="{
    title: 'Data Regulasi',
    icon: 'bx-book',
    to: '/data-regulasi',
  }" />

  <VerticalNavLink :item="{
    title: 'User Management',
    icon: 'bx-user-check',
    to: '/user-management',
  }" />

  <VerticalNavLink :item="{
    title: 'Role Management',
    icon: 'bx-bxs-user-detail',
    to: '/role-management',
  }" />

  <VerticalNavLink :item="{
    title: 'Data Template',
    icon: 'bx-file-blank',
    to: '/data-template',
  }" />
</template>

<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>
