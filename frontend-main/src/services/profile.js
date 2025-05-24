import apiClient from '@/services/api'

export default {
  getProfile() {
    return apiClient.get('/profile')
  },
  updateProfile(data) {
    const formData = new FormData()
    
    if (data.foto_profil) formData.append('foto_profil', data.foto_profil)
    formData.append('_method', 'PUT') 
  
    return apiClient.post('/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  changePassword(data) {
    return apiClient.put('/profile/password', {
      current_password: data.currentPassword,
      new_password: data.newPassword,
      confirm_password: data.confirmPassword
    })
  }
}