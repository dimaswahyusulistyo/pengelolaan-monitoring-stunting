<template>
    <VDialog v-model="isVisible" max-width="600" persistent="persistent">
        <VCard>
            <VCardTitle class="d-flex justify-space-between align-center">
                <span>Detail Penanganan</span>
                <VBtn icon variant="text" @click="closeDialog">
                    <i class="bx bx-x"></i>
                </VBtn>
            </VCardTitle>

            <VCardText>
                <VRow class="mb-4">
                    <VCol cols="12" md="6">
                        <div class="text-subtitle-1 font-weight-bold">Nama Anak:</div>
                        <div>{{ item.nama_anak }}</div>
                    </VCol>
                    <VCol cols="12" md="6">
                        <div class="text-subtitle-1 font-weight-bold">Desa/Kelurahan:</div>
                        <div>{{ item.desa?.nama_desa || 'N/A' }}</div>
                    </VCol>
                </VRow>

                <VRow class="mb-4">
                    <VCol cols="12">
                        <div class="d-flex align-center mb-2">
                            <div class="text-subtitle-1 font-weight-bold me-2">Progress Penanganan:</div>
                            <VChip :color="getProgressColor" small>{{ progressStatus }}</VChip>
                        </div>
                        <VProgressLinear
                            :model-value="progressPercentage"
                            height="12"
                            rounded="rounded"
                            :color="getProgressColor"
                            bg-color="grey-lighten-3">
                            <template v-slot:default="{ value }">
                                <strong>{{ progressData.progress }}</strong>
                            </template>
                        </VProgressLinear>
                    </VCol>
                </VRow>

                <div class="mb-4">
                    <h3 class="text-h6 font-weight-bold mb-2">OPD yang Menangani</h3>

                    <!-- Tabs for OPDs -->
                    <VTabs
                        v-model="activeTab"
                        show-arrows="show-arrows"
                        grow="grow"
                        slider-color="primary"
                        background-color="grey-lighten-4">
                        <VTab
                            v-for="(opd, index) in opdList"
                            :key="opd.id_user"
                            :value="index"
                            :color="opd.status === 'Sudah ada penanganan' ? 'success' : 'error'">
                            {{ opd.nama_opd }}
                            <VIcon size="small" class="ms-1">
                                {{ opd.status === 'Sudah ada penanganan' ? 'bx-check-circle' : 'bx-error-circle' }}
                            </VIcon>
                        </VTab>
                    </VTabs>

                    <VWindow v-model="activeTab" class="mt-4">
                        <VWindowItem v-for="(opd, index) in opdList" :key="opd.id_user" :value="index">
                            <VCard
                                variant="outlined"
                                :color="opd.status === 'Sudah ada penanganan' ? 'green-lighten-5' : 'red-lighten-5'">
                                <VCardItem>
                                    <VCardTitle>
                                        {{ opd.nama_opd }}
                                        <VChip
                                            class="ml-2"
                                            :color="opd.status === 'Sudah ada penanganan' ? 'success' : 'error'"
                                            size="small">
                                            {{ opd.status }}
                                        </VChip>
                                    </VCardTitle>

                                    <VCardSubtitle v-if="opd.tanggal_status">
                                        <small>Terakhir diupdate:
                                            {{ formatDate(opd.tanggal_status) }}</small>
                                    </VCardSubtitle>
                                </VCardItem>

                                <VCardText>
                                    <VForm
                                        v-if="canEditOPD(opd.id_user)"
                                        @submit.prevent="savePenanganan(opd.id_user)">
                                        <VRow>
                                            <VCol cols="12" md="12">
                                                <VSelect
                                                    v-model="formPenanganan[opd.id_user].status"
                                                    :items="statusOptions"
                                                    label="Status Penanganan"
                                                    item-title="text"
                                                    item-value="value"
                                                    density="compact"
                                                    variant="outlined"
                                                    :rules="[v => !!v || 'Status harus dipilih']"
                                                    required="required"/>
                                            </VCol>
                                            <VCol cols="12">
                                                <VTextarea
                                                    v-model="formPenanganan[opd.id_user].catatan"
                                                    label="Catatan Penanganan"
                                                    rows="3"
                                                    variant="outlined"
                                                    density="compact"
                                                    :placeholder="opd.keterangan || 'Tidak ada catatan sebelumnya'"/>
                                            </VCol>
                                            <VCol cols="12" class="d-flex justify-end">
                                                <VBtn
                                                    type="submit"
                                                    :color="opd.status === 'Sudah ada penanganan' ? 'success' : 'primary'"
                                                    size="small"
                                                    :loading="loading">
                                                    Simpan Perubahan
                                                </VBtn>
                                            </VCol>
                                        </VRow>
                                    </VForm>

                                    <div v-else>
                                        <VRow>
                                            <VCol cols="12" md="6">
                                                <VTextField
                                                    :model-value="opd.status"
                                                    label="Status Penanganan"
                                                    readonly="readonly"
                                                    density="compact"
                                                    variant="outlined"/>
                                            </VCol>
                                            <VCol cols="12">
                                                <VTextarea
                                                    :model-value="opd.keterangan || 'Tidak ada catatan'"
                                                    label="Catatan Penanganan"
                                                    rows="3"
                                                    readonly="readonly"
                                                    variant="outlined"
                                                    density="compact"/>
                                            </VCol>
                                        </VRow>
                                        <VAlert type="info" variant="tonal" class="mt-2">
                                            Hanya petugas OPD
                                            {{ opd.nama_opd }}
                                            yang dapat mengedit data ini
                                        </VAlert>
                                    </div>
                                </VCardText>
                            </VCard>
                        </VWindowItem>
                    </VWindow>
                </div>
            </VCardText>
        </VCard>
    </VDialog>
</template>
  
<script setup="setup">
    import {ref, watch, computed, onMounted} from 'vue';
    import apiClient from '@/services/api';
    import {useAuthStore} from '@/services/auth';

    const authStore = useAuthStore();
    const currentUser = computed(() => authStore.user);

    const props = defineProps({visible: Boolean, item: Object});

    const emit = defineEmits(['update:visible', 'refresh']);

    const isVisible = computed({
        get: () => props.visible,
        set: (value) => emit('update:visible', value)
    });

    const loading = ref(false);
    const opdList = ref([]);
    const activeTab = ref(0);
    const progressData = ref({progress: '0/0', status: 'Belum ada penanganan'});

    const canEditOPD = (opdUserId) => {
        return currentUser.value
            ?.id === opdUserId;
    };

    const progressPercentage = computed(() => {
    const [handled, total] = progressData.value.progress.split('/');
    if (total === '0') return 0;
    return (parseInt(handled) / parseInt(total)) * 100;
});

const statusOptions = ref([
    { value: 'Sudah ada penanganan', text: 'Sudah ada penanganan' },
    { value: 'Belum ada penanganan', text: 'Belum ada penanganan' }
]);

const getProgressColor = computed(() => {
    const [handled, total] = progressData.value.progress.split('/');
    const handledCount = parseInt(handled);
    const totalCount = parseInt(total);
    
    if (handledCount === 0) return 'error';
    if (handledCount === totalCount) return 'success';
    return 'warning';
});

const progressStatus = computed(() => {
    const [handled, total] = progressData.value.progress.split('/');
    const handledCount = parseInt(handled);
    const totalCount = parseInt(total);
    
    if (handledCount === 0) return 'Belum ada penanganan';
    if (handledCount === totalCount) return 'Sudah ditangani';
    return 'Dalam penanganan';
});

    const formPenanganan = ref({});

    const formatDate = (dateString) => {
        if (!dateString) 
            return null;
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };

    const fetchDataPenanganan = async () => {
        try {
            const response = await apiClient.get(
                `/anak-stunting/${props.item.id_anak}/penanganan`
            );
            opdList.value = response.data.data;

            opdList
                .value
                .forEach(opd => {
                    formPenanganan.value[opd.id_user] = {
                        status: opd.status,
                        catatan: opd.keterangan || ''
                    };
                });

            const findTabIndex = () => {
                const userUnhandledIndex = opdList
                    .value
                    .findIndex(
                        opd => opd.id_user === currentUser.value
                            ?.id && opd.status !== 'Sudah ada penanganan'
                    );
                if (userUnhandledIndex !== -1) 
                    return userUnhandledIndex;
                
                const userOpdIndex = opdList
                    .value
                    .findIndex(
                        opd => opd.id_user === currentUser.value
                            ?.id
                    );
                if (userOpdIndex !== -1) 
                    return userOpdIndex;
                
                const unhandledIndex = opdList
                    .value
                    .findIndex(opd => opd.status !== 'Sudah ada penanganan');
                if (unhandledIndex !== -1) 
                    return unhandledIndex;
                
                return 0;
            };

            activeTab.value = findTabIndex();
            fetchPenangananProgress();
        } catch (error) {
            console.error('Error fetching penanganan data:', error);
        }
    };

    const fetchPenangananProgress = async () => {
        try {
            const response = await apiClient.get(
                `/anak-stunting/${props.item.id_anak}/penanganan/progress`
            );
            progressData.value = response.data.data;
        } catch (error) {
            console.error('Error fetching progress data:', error);
        }
    };

    const savePenanganan = async (idUser) => {
        try {
            loading.value = true;
            const dataToSave = formPenanganan.value[idUser];

            if (!canEditOPD(idUser)) {
                await Swal.fire(
                    {icon: 'error', title: 'Akses Ditolak', text: 'Anda tidak memiliki izin untuk mengedit data OPD ini'}
                );
                return;
            }

            if (!dataToSave.status) {
                await Swal.fire(
                    {icon: 'error', title: 'Data Tidak Valid', text: 'Status penanganan harus dipilih'}
                );
                return;
            }

            const response = await apiClient.put(
                `/status-penanganan/${props.item.id_anak}/${idUser}`,
                {
                    status: dataToSave.status,
                    keterangan: dataToSave.catatan || null
                }
            );

            if (response.data.status === 'success') {
                await Swal.fire(
                    {icon: 'success', title: 'Berhasil!', text: 'Status penanganan berhasil diperbarui'}
                );
                emit('refresh');
                fetchDataPenanganan();
            } else {
                throw new Error(response.data.message || 'Gagal memperbarui status');
            }
        } catch (error) {
            console.error('Error saving penanganan:', error);
            let errorMessage = 'Gagal menyimpan data';

            if (error.response) {
                if (error.response.status === 404) {
                    errorMessage = 'Endpoint tidak ditemukan. Pastikan backend berjalan dengan benar.';
                } else if (
                    error.response.data
                        ?.message
                ) {
                    errorMessage = error.response.data.message;
                }
            }

            await Swal.fire({icon: 'error', title: 'Gagal!', text: errorMessage});
        } finally {
            loading.value = false;
        }
    };

    const closeDialog = () => {
        isVisible.value = false;
    };

    watch(() => props.visible, (newVal) => {
        if (newVal && props.item) {
            fetchDataPenanganan();
        }
    });
</script>