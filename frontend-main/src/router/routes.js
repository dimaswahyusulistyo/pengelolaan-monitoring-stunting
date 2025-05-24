import { createRouter, createWebHistory } from 'vue-router'
import authMiddleware from '@/middleware/auth'

const routes = [
  { path: '/', redirect: 'dashboard-anak-stunting' },
    {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard-anak-stunting',
        name: 'DashboardAnakStunting',
        component: () => import('@/pages/anak-stunting/dashboard.vue'),
      },
      {
        path: 'dashboard-keluarga-berisiko',
        component: () => import('@/pages/keluarga-berisiko/dashboard.vue'),
      },
      {
        path: 'dashboard-program-intervensi',
        component: () => import('@/pages/cakupan-program-intervensi/dashboard.vue'),
      },
      {
        path: 'dashboard-regulasi',
        component: () => import('@/pages/manajemen-sistem/regulasi/dashboard-regulasi.vue'),
      },
      {
        path: 'data-anak-stunting',
        name: 'DataAnakStunting',
        component: () => import('@/pages/anak-stunting/master-data/data-stunting.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'data-keluarga-berisiko',
        component: () => import('@/pages/keluarga-berisiko/master-data/data-kb.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'data-cakupan',
        component: () => import('@/pages/cakupan-program-intervensi/master-data/data-cakupan.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'rencana-aksi',
        component: () => import('@/pages/kegiatan-tpps/rencana-aksi.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'report-kegiatan',
        component: () => import('@/pages/kegiatan-tpps/report-kegiatan.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'data-regulasi',
        component: () => import('@/pages/manajemen-sistem/regulasi/data-regulasi.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'user-management',
        component: () => import('@/pages/manajemen-sistem/user-management/data-user.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'role-management',
        component: () => import('@/pages/manajemen-sistem/role-management/data-role.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'data-template',
        component: () => import('@/pages/manajemen-sistem/file-template/data-template.vue'),
        beforeEnter: authMiddleware
      },
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: 'error',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router