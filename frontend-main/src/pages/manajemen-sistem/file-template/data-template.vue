<template>
    <VRow>
      <VCol cols="12">
        <VCard title="DATA FILE TEMPLATE" class="rounded-lg">
          <VRow class="align-center px-4 py-2">
            <VCol cols="12" md="6" class="d-flex">
            
                <VMenu offset-y>
                <template v-slot:activator="{ props }">
                  <VBtn
                    v-bind="props"
                    color="primary"
                    class="me-2 rounded-lg d-flex justify-center align-center"
                    style="min-width: 40px; min-height: 40px;"
                  >
                    <VIcon>bx-filter</VIcon>
                  </VBtn>
                </template>

                <VList>
                  <VListItem @click="applySort('A-Z')">
                    <VListItemTitle>A - Z</VListItemTitle>
                  </VListItem>
                  <VListItem @click="applySort('Z-A')">
                    <VListItemTitle>Z - A</VListItemTitle>
                  </VListItem>
                  <VListItem @click="applySort('newest')">
                    <VListItemTitle>Terbaru</VListItemTitle>
                  </VListItem>
                  <VListItem @click="applySort('oldest')">
                    <VListItemTitle>Terlama</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>

              <VTextField 
                v-model="searchQuery" 
                prepend-inner-icon="bx-search" 
                placeholder="Search"
                class="search-box flex-grow-1" 
                clearable 
                density="comfortable" 
                @update:modelValue="fetchTemplates"
              />

            </VCol>
            <VCol cols="12" md="6" class="d-flex justify-end">
              <VBtn color="success" class="me-2 rounded-lg" @click="dialog = true">Tambah</VBtn>
            </VCol>
          </VRow>
  
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
                      <VBtn icon color="warning" @click="editTemplate(template)" class="square-button">
                        <i class="bx bxs-edit-alt"></i>
                      </VBtn>
                      <VBtn icon color="info" @click="downloadTemplate(template)" class="square-button">
                        <i class="bx bxs-download"></i>
                      </VBtn>
                      <VBtn icon color="error" @click="deleteTemplate(template)" class="square-button">
                        <i class="bx bxs-trash-alt"></i>
                      </VBtn>
                    </div>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>
  
          <div class="d-flex align-center justify-space-between pa-4">
            <div class="d-flex align-center">
              <span class="text-body-2 me-4">Items per page:</span>
              <VSelect v-model="itemsPerPage" :items="[5, 10, 15, 20]" variant="outlined" density="compact"
                hide-details class="items-per-page-select" style="width: 80px;" @update:modelValue="fetchTemplates" />
            </div>
  
            <VPagination v-model="currentPage" :length="totalPages" :total-visible="7" rounded="lg" @update:modelValue="fetchTemplates" />
  
            <div class="d-flex align-center">
              <span class="text-body-2">
                {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, totalItems) }}
                of {{ totalItems }}
              </span>
            </div>
          </div>
        </VCard>
    </VCol>

    <TambahTemplate v-model:dialog="dialog" @save="fetchTemplates" />

    <UpdateTemplate 
        v-model:dialog="editDialog"
        :editMode="editMode"
        :editedActivity="selectedTemplate"
        @save="fetchTemplates"
    />

    </VRow>
</template>

<script setup>
import { ref, computed, onMounted, watch} from 'vue';
import TambahTemplate from './tambah-template.vue';
import UpdateTemplate from './update-template.vue';
import apiClient from '@/services/api';

const templates = ref([]);
const totalPages = ref(1);
const totalItems = ref(0);
const currentPage = ref(1);
const itemsPerPage = ref(5);
const searchQuery = ref('');

const dialog = ref(false);
const editDialog = ref(false);
const editMode = ref(false);
const selectedTemplate = ref(null);


const sortOrder = ref('');

watch(itemsPerPage, () => {
    currentPage.value = 1; 
    fetchTemplates();
});


const fetchTemplates = async () => {
    try {
        const response = await apiClient.get('/template', {
            params: {
                search: searchQuery.value,
                page: currentPage.value,
                per_page: itemsPerPage.value,
                sort_filter: sortOrder.value,
            }
        });

        templates.value = response.data.data ?? [];
        totalPages.value = response.data.last_page;
        totalItems.value = response.data.total;
        } catch (error) {
        console.error('Error fetching templates:', error);
        templates.value = [];
        }
};

const applySort = (option) => {
  sortOrder.value = option;
  fetchTemplates();
};

onMounted(fetchTemplates);

const editTemplate = (template) => {
    selectedTemplate.value = { ...template };
    editMode.value = true;
    editDialog.value = true;
};

const deleteTemplate = async (template) => {
        if (!template || !template.id_template) {
        console.error("Error: Template tidak valid!", template);
        return;
    }

    const result = await Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang sudah dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        try {
            await apiClient.delete(`/template/${template.id_template}`);
            templates.value = templates.value.filter(t => t.id_template !== template.id_template);

            await Swal.fire({
                title: 'Terhapus!',
                text: 'Template berhasil dihapus.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error deleting template:', error);
        
            await Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menghapus template.',
                icon: 'error'
            });
            }
        }
};

const downloadTemplate = async (template) => {
    if (!template || !template.id_template) {
        console.error("Error: Template tidak valid!", template);
        return;
    }

    try {
        const response = await apiClient.get(`/template/${template.id_template}/download`, {
            responseType: "blob", 
        });

        const blob = new Blob([response.data], { type: response.headers["content-type"] });

        let fileName = template.nama_template || "downloaded_file.xlsx";
        const contentDisposition = response.headers["content-disposition"];
        if (contentDisposition) {
            const match = contentDisposition.match(/filename="(.+)"/);
            if (match) fileName = match[1];
        }

        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error("Error saat mengunduh template:", error);
    }
};

</script>

<style scoped>
    .search-box {
        max-width: 400px;
        border-radius: 8px;
}

    .table-container {
        overflow-x: auto;
        padding: 16px;
        border-radius: 8px;
        position: relative;
}

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
}

    .custom-table th,
    .custom-table td {
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        padding: 12px;
        text-align: center;
        white-space: nowrap;
}

    .custom-table th {
        min-width: 120px;
        background-color: #f5f5f5;
        font-weight: 600;
}

    .button-group {
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 8px;
        overflow: hidden;
}

    .button-group .square-button:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
}

    .button-group .square-button:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
}

    .square-button {
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
        border-radius: 0;
}

    .preview-link {
        text-decoration: none;
}

    .preview-container {
        width: 100%;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
}

    .preview-document {
        width: 100%;
        height: 600px;
        border: none;
}

    .v-theme--dark .custom-table th {
        background-color: #1e1e1e;
        color: #ffffff;
}

    .v-theme--dark .custom-table td {
        background-color: #2d2d2d;
        color: #ffffff;
}

    .v-theme--dark .custom-table tbody tr:hover {
        background-color: #3d3d3d;
}

    .v-theme--dark .custom-table,
    .v-theme--dark .custom-table td,
    .v-theme--dark .custom-table th {
        border-color: #444;
}

    .fixed-header {
        position: sticky;
        top: 0;
        background: white;
        z-index: 3;
        border-bottom: 2px solid #ddd;
}

</style>