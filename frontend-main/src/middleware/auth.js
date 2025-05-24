import { useAuthStore } from '@/services/auth'

const publicRoutes = [
  '/',
  '/login',
  '/dashboard-anak-stunting',
  '/dashboard-keluarga-berisiko',
  '/dashboard-program-intervensi',
  '/dashboard-regulasi',
]

const adminRoutes = [
  '/data-anak-stunting',
  '/data-keluarga-berisiko',
  '/data-cakupan',
  '/rencana-aksi',
  '/report-kegiatan',
  '/data-regulasi',
  '/user-management',
  '/role-management',
  '/data-template',
  '/register',
]

const opdRoutes = [
  '/data-anak-stunting',
  '/data-keluarga-berisiko',
  '/data-cakupan',
  '/rencana-aksi',
  '/report-kegiatan',
  '/data-regulasi'
]

const kaderRoutes = [
  '/data-anak-stunting',
  '/data-keluarga-berisiko',
  '/data-cakupan'
]

export default function authMiddleware(to, from, next) {
  const authStore = useAuthStore()
  const publicRoutes = [
    '/',
    '/login',
    '/register',
    '/dashboard-anak-stunting',
    '/dashboard-keluarga-berisiko', 
    '/dashboard-program-intervensi',
    '/dashboard-regulasi'
  ]

  if (publicRoutes.includes(to.path)) {
    return next()
  }

  if (!authStore.isAuthenticated) {
    return next({
      path: '/login',
      query: { redirect: to.fullPath }
    })
  }

  const userRole = authStore.user?.role
  let hasAccess = false

  if (userRole === 'Admin') {
    hasAccess = adminRoutes.includes(to.path)
  } else if ([
    'Dinas Kesehatan',
    'Dinas KB',
    'Dinas Sosial',
    'Dinas Pertanian',
    'Dinas PU',
    'Dispermades'
  ].includes(userRole)) {
    hasAccess = opdRoutes.includes(to.path)
  } else if (userRole === 'Kader Desa') {
    hasAccess = kaderRoutes.includes(to.path)
  }

  if (hasAccess) {
    next()
  } else {
    next('/error')
  }
}