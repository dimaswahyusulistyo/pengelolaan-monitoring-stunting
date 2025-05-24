<template>
  <VDialog
    :modelValue="visible"
    @update:modelValue="emit('update:visible', $event)"
    persistent
    :max-width="showCreateForm || showUpdateForm ? '1200px' : '600px'"
  >
    <VCard v-if="!showCreateForm && !showUpdateForm">
      <VCardTitle class="text-h5 pa-4 d-flex justify-space-between align-center">
        <span>Tambah Data atau Update Data</span>
        <VBtn
          icon
          variant="text"
          @click="closeDialog"
          class="ml-2"
        >
          <i class='bx bx-x' style="font-size: 24px;"></i>
        </VBtn>
      </VCardTitle>
      
      <VCardText>
        <div class="options-container pa-4">
          <VBtn
            block
            color="primary"
            size="large"
            class="mb-4 text-white"
            height="120"
            @click="handleCreateInput"
          >
            <div class="d-flex flex-column align-center">
              <i class='bx bx-edit text-h2 mb-2 text-white'></i>
              <span class="text-h6 text-white">Tambah Data</span>
            </div>
          </VBtn>
  
          <VBtn
            block
            color="success"
            size="large"
            height="120"
            class="text-white"
            @click="handleUpdateInput"
          >
            <div class="d-flex flex-column align-center">
              <i class='bx bx-file text-h2 mb-2 text-white'></i>
              <span class="text-h6 text-white">Update Data</span>
            </div>
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <CreateInputForm
      v-if="showCreateForm"
      @save="handleFormSave"
      @cancel="handleFormCancel"
      @closeDialog="closeDialog"
      @refresh-data="$emit('refresh-data')"
    />

    <UpdateInputForm
      v-if="showUpdateForm"
      @fileImported="handleFormSave"
      @cancel="handleFormCancel"
      @closeDialog="closeDialog"
      @refresh-data="$emit('refresh-data')"
    />
  </VDialog>
</template>

<script setup>
import { ref } from 'vue';
import { VDialog, VCard, VCardTitle, VCardText, VCardActions, VBtn, VSpacer } from 'vuetify/components';
import CreateInputForm from './create-data.vue';
import UpdateInputForm from './update-data.vue';

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
    default: false
  }
});

const emit = defineEmits(['update:visible', 'fileImported', 'createData', 'refresh-data']);

const selectedFile = ref(null);
const showImportOptions = ref(true);
const showCreateForm = ref(false);
const showUpdateForm = ref(false);

const closeDialog = () => {
  selectedFile.value = null;
  showImportOptions.value = true;
  showCreateForm.value = false;
  showUpdateForm.value = false;
  emit('update:visible', false);
};

const handleCreateInput = () => {
  showCreateForm.value = true;
  showImportOptions.value = false;
  showUpdateForm.value = false;
};

const handleUpdateInput = () => {
  showImportOptions.value = false;
  showCreateForm.value = false;
  showUpdateForm.value = true;
};

const handleFormSave = (formData) => {
  emit('createInput', formData);
  closeDialog();
};

const handleFormCancel = () => {
  showCreateForm.value = false;
  showUpdateForm.value = false;
  showImportOptions.value = true;
};

const fetchData = async () => {
    try {
        const response = await apiClient.get('/cakupan-program');
        cakupanProgramData.value = response.data.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    }
};
</script>

<style scoped>
.drop-zone {
  border: 2px dashed #ccc;
  transition: all 0.3s ease;
}

.drag-over {
  border-color: #2196F3;
  background-color: rgba(33, 150, 243, 0.1);
}

.hidden-input {
  display: none;
}

.selected-file {
  text-align: center;
  padding: 8px;
  background-color: #f5f5f5;
  border-radius: 4px;
}

:deep(.v-dialog) {
  border-radius: 8px;
}

.options-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.options-container .v-btn {
  transition: transform 0.2s;
}

.options-container .v-btn:hover {
  transform: translateY(-2px);
}
</style>