import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import router from './router/routes.js'
import { useAuthStore } from './services/auth'

import '@core/scss/template/index.scss'
import '@layouts/styles/index.scss'
import '@styles/styles.scss'

const app = createApp(App)

registerPlugins(app)

app.use(router)

const authStore = useAuthStore()
authStore.loadFromStorage()

const rememberMe = localStorage.getItem('rememberMe') === 'true'

if (!rememberMe && !sessionStorage.getItem('activeTab')) {
  authStore.logout()
}
sessionStorage.setItem('activeTab', 'true')


app.mount('#app')