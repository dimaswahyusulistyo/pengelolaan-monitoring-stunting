<template>
  <VDialog :modelValue="visible" @update:modelValue="emit('update:visible', $event)" persistent
    :max-width="showManualForm || showExcelForm ? '1200px' : '600px'">
    <VCard v-if="!showManualForm && !showExcelForm">
      <VCardTitle class="text-h5 pa-4 d-flex justify-space-between align-center">
        <span>Pilih Metode Input Data</span>
        <VBtn icon variant="text" @click="closeDialog" class="ml-2">
          <i class='bx bx-x' style="font-size: 24px;"></i>
        </VBtn>
      </VCardTitle>

      <VCardText>
        <div class="options-container pa-4">
          <VBtn block color="primary" size="large" class="mb-4 text-white" height="120" @click="handleManualInput">
            <div class="d-flex flex-column align-center">
              <i class='bx bx-edit text-h2 mb-2 text-white'></i>
              <span class="text-h6 text-white">Input Manual</span>
            </div>
          </VBtn>

          <VBtn block color="success" size="large" height="120" class="text-white" @click="handleExcelInput">
            <div class="d-flex flex-column align-center">
              <i class='bx bx-file text-h2 mb-2 text-white'></i>
              <span class="text-h6text-white">Import Excel</span>
            </div>
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <ManualInputForm v-if="showManualForm" @save="handleFormSave" @cancel="handleFormCancel" @closeDialog="closeDialog"
      @refresh-data="$emit('refresh-data')" />

    <ExcelInputForm v-if="showExcelForm" @fileImported="handleFormSave" @cancel="handleFormCancel"
      @closeDialog="closeDialog" />
  </VDialog>
</template>

<script setup>
import { ref } from 'vue';
import { VDialog, VCard, VCardTitle, VCardText, VCardActions, VBtn, VSpacer } from 'vuetify/components';
import ManualInputForm from './input-manual.vue';
import ExcelInputForm from './import-excel.vue';

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
    default: false
  }
});

const emit = defineEmits(['update:visible', 'fileImported', 'manualInput', 'refresh-data']);

const selectedFile = ref(null);
const showImportOptions = ref(true);
const showManualForm = ref(false);
const showExcelForm = ref(false);

const closeDialog = () => {
  selectedFile.value = null;
  showImportOptions.value = true;
  showManualForm.value = false;
  showExcelForm.value = false;
  emit('update:visible', false);
};

const handleManualInput = () => {
  showManualForm.value = true;
  showImportOptions.value = false;
  showExcelForm.value = false;
};

const handleExcelInput = () => {
  showImportOptions.value = false;
  showManualForm.value = false;
  showExcelForm.value = true;
};

const handleFormSave = (formData) => {
  emit('manualInput', formData);
  closeDialog();
};

const handleFormCancel = () => {
  showManualForm.value = false;
  showExcelForm.value = false;
  showImportOptions.value = true;
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