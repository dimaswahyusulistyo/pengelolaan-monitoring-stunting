import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const user = ref(JSON.parse(localStorage.getItem('user')) || null)
  const token = ref(localStorage.getItem('token') || sessionStorage.getItem('token'))
  const userId = ref(localStorage.getItem('userId') || null)

  const isAuthenticated = computed(() => !!token.value)
  const currentUserId = computed(() => userId.value)
  const userRole = computed(() => user.value?.role)
  const userBidang = computed(() => user.value?.bidang)

  function setToken(newToken) {
    token.value = newToken
    localStorage.setItem('token', newToken)
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  function setUser(userData) {
    user.value = userData
    userId.value = userData.id_user
    localStorage.setItem('user', JSON.stringify(userData))
    localStorage.setItem('userId', userData.id_user)
  }

  async function fetchProfile() {
    try {
      const response = await apiClient.get('/profile')
      setUser(response.data.data)
      return response.data
    } catch (error) {
      console.error('Failed to fetch profile:', error)
      throw error
    }
  }

  async function logout() {
    try {
      await apiClient.post('/logout')
    } catch (error) {
      console.error('Logout failed:', error)
    } finally {
      token.value = null
      user.value = null
      userId.value = null
      localStorage.removeItem('token')
      sessionStorage.removeItem('token')
      localStorage.removeItem('user')
      localStorage.removeItem('userId')
      delete apiClient.defaults.headers.common['Authorization']
      router.push('/login')
    }
  }

  function loadFromStorage() {
    const storedToken = localStorage.getItem('token')
    const storedUser = localStorage.getItem('user')
    const storedUserId = localStorage.getItem('userId')
    
    if (storedToken) {
      token.value = storedToken
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
    }
    
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        console.error('Failed to parse user data:', e)
        localStorage.removeItem('user')
      }
    }

    if (storedUserId) {
      userId.value = storedUserId
    }
  }

  return {
    user,
    token,
    userId,
    isAuthenticated,
    currentUserId,
    userRole,
    userBidang,
    setToken,
    setUser,
    fetchProfile,
    logout,
    loadFromStorage
  }
})