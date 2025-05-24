<template>
    <VDialog v-model="internalVisible" max-width="700">
      <VCard class="custom-card">
        <VCardTitle class="text-h5 pa-4 d-flex justify-space-between align-center">
        <span>Daftar File Template</span>
            <VBtn icon class="close-button" @click="closeDialog" variant="text">
                <i class="bx bx-x"></i>
            </VBtn>
        </VCardTitle>
        <VCardText>
          <div class="table-container">
            <VTable class="custom-table elevation-1">
              <thead>
                <tr>
                  <th class="text-center">No</th>
                  <th class="text-center">Nama File Template</th>
                  <th class="text-center header-cell-top-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(template, index) in templates" :key="template.id_template">
                  <td class="text-center">{{ index + 1 }}</td>
                  <td class="text-center">{{ template.nama_template }}</td>
                  <td class="text-center">
                    <div class="button-group">
                      <VBtn icon color="info" @click="downloadTemplate(template)" class="square-button">
                        <i class="bx bxs-download"></i>
                      </VBtn>
                    </div>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn text @click="closeDialog">Tutup</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </template>
  
<script setup>
import { ref, onMounted, watch } from 'vue'
import apiClient from '@/services/api'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:visible'])

const internalVisible = ref(props.visible)

watch(() => props.visible, (newVal) => {
    internalVisible.value = newVal
})


watch(internalVisible, (val) => {
    emit('update:visible', val)
})


const templates = ref([])

const fetchTemplates = async () => {
    try {
        const response = await apiClient.get('/template-by-role')
        templates.value = response.data.data ?? []
    } catch (error) {
        console.error('Error fetching templates:', error)
        templates.value = []
    }
}

onMounted(fetchTemplates)

const downloadTemplate = async (template) => {
    if (!template || !template.id_template) {
        console.error("Error: Template tidak valid!", template)
        return
    }

    try {
        const response = await apiClient.get(`/template/${template.id_template}/download`, {
        responseType: "blob",
        })

        const blob = new Blob([response.data], { type: response.headers["content-type"] })
        let fileName = template.nama_template || "downloaded_file.xlsx"
        const contentDisposition = response.headers["content-disposition"]
        if (contentDisposition) {
            const match = contentDisposition.match(/filename="(.+)"/)
            if (match) fileName = match[1]
        }

        const link = document.createElement("a")
        link.href = URL.createObjectURL(blob)
        link.download = fileName
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        } catch (error) {
        console.error("Error saat mengunduh template:", error)
        }
    }

const closeDialog = () => {
    internalVisible.value = false
}
</script>

<style scoped>
.custom-card {
    position: relative; 
    padding: 16px;
}

.card-title {
    font-weight: bold;
    font-size: 18px;
    padding-bottom: 8px;
    text-align: center;
}

.table-container {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 10px;
}

.custom-table thead th {
    background-color: #f5f5f5;
    font-weight: 600;
    border-bottom: 1px solid #e0e0e0;
    padding: 8px;
}

.custom-table tbody td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.button-group {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.square-button {
    width: 36px;
    height: 36px;
}

.close-button {
    position: absolute;
    top: 8px;
    right: 8px;
    color: #757575;
    transition: color 0.3s ease;
    z-index: 10;
}

.close-button:hover {
    color: #f44336;
}
</style>