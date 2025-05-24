<template>
    <VDialog
        :modelValue="visible"
        @update:modelValue="$emit('update:visible', $event)"
        persistent="persistent"
        max-width="600px">
        <VCard class="manual-form">
            <VCardTitle class="text-h5 pa-4 d-flex align-center">
                <span class="ml-4">Edit Manual Data Stunting</span>
                <VSpacer/>
                <VBtn icon="icon" variant="text" @click="handleClose">
                    <i class="bx bx-x"></i>
                </VBtn>
            </VCardTitle>

            <VCardText>
                <VForm ref="formRef">
                    <div class="form-section mt-6">
                        <div class="section-header">
                            <h3 class="section-title">Data Anak</h3>
                        </div>
                        <br>
                        <br>
                        <VRow>
                            <VCol cols="12" md="6">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.nama_anak"
                                    label="Nama Anak"
                                    :rules="[v => !!v || 'Nama anak harus diisi']"
                                    required="required"/>
                            </VCol>

                            <VCol cols="12" md="6">
                                <VSelect
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.jenis_kelamin"
                                    :items="jenisKelaminOptions"
                                    label="Jenis Kelamin"
                                    item-title="title"
                                    item-value="value"
                                    :rules="[v => !!v || 'Jenis kelamin harus dipilih']"
                                    required="required"/>
                            </VCol>

                            <VCol cols="12" md="6">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.tanggal_lahir"
                                    label="Tanggal Lahir"
                                    type="date"
                                    :rules="[v => !!v || 'Tanggal lahir harus diisi']"
                                    required="required"
                                    />
                            </VCol>

                            <VCol cols="12" md="6">
                                <VTextField
                                    persistent-placeholder
                                    v-model="form.nik_anak"
                                    label="NIK"
                                    :rules="[
                                    v => !!v || 'NIK harus diisi',
                                    v => (v && v.toString().length === 16) || 'NIK harus 16 digit',
                                    v => /^\d{16}$/.test(v) || 'Hanya 16 digit angka yang diperbolehkan'
                                    ]"
                                    required
                                    type="number"
                                    counter
                                    maxlength="16"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)"
                                />
                            </VCol>

                            <VCol cols="12" md="4">
                                <VAutocomplete
                                    v-model="form.id_desa"
                                    :items="filteredDesaList"
                                    label="Desa/Kelurahan"
                                    item-title="nama_desa"
                                    item-value="id_desa"
                                    :rules="[v => !!v || 'Desa harus dipilih']"
                                    @update:model-value="handleDesaChange"
                                    v-model:search="desaSearch"
                                    :loading="loadingDesa"
                                    clearable
                                    no-filter
                                    placeholder="Cari desa/kelurahan..."
                                    @update:search="handleSearchInput"
                                >
                                    <template #item="{ props, item }">
                                    <VListItem v-bind="props">
                                        <VListItemSubtitle>
                                        Kec. {{ item.raw.kecamatan }} - {{ item.raw.puskesmas }}
                                        </VListItemSubtitle>
                                    </VListItem>
                                    </template>
                                    <template #no-data>
                                    <VListItem>
                                        <VListItemTitle>
                                        {{ desaSearch.length < 2 ? 'Ketik minimal 2 karakter' : 'Data tidak ditemukan' }}
                                        </VListItemTitle>
                                    </VListItem>
                                    </template>
                                </VAutocomplete>
                            </VCol>

                            <VCol cols="12" md="4">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.kecamatan"
                                    label="Kecamatan"
                                    :rules="[v => !!v || 'Kecamatan harus diisi']"
                                    required="required"
                                    readonly="readonly"/>
                            </VCol>

                            <VCol cols="12" md="4">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.puskesmas"
                                    label="Puskesmas"
                                    :rules="[v => !!v || 'Puskesmas harus diisi']"
                                    required="required"
                                    readonly="readonly"/>
                            </VCol>
                        
                            <VCol cols="12" md="12">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.posyandu"
                                    label="Posyandu"
                                    :rules="[v => !!v || 'Posyandu harus diisi']"
                                    required="required"/>
                            </VCol>
                        </VRow>
                    </div>
                    <VDivider class="my-4"/>
                      <div class="form-section mt-6">
                          <div class="section-header">
                              <h3 class="section-title">Faktor Determinan</h3>
                          </div>
                          <br>
                          <br>
                        <VRow>
                            
                            <VCol cols="12" md="4">
                                <VSelect
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.determinan.jkn"
                                    :items="jknOptions"
                                    label="JKN"
                                    item-title="title"
                                    item-value="value"/>
                            </VCol>

                            <VCol cols="12" md="8">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="form.determinan.penyakit_penyerta"
                                    label="Penyakit Penyerta"
                                    item-title="title"
                                    item-value="value"/>
                            </VCol>

                            
                            <div class="table-container">
                                <VTable class="custom-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="1" class="text-center header-cell-top-left" style="width: 50px">No</th>
                                            <th rowspan="1" class="text-center" style="width: 60%" >Faktor Determinan</th>
                                            <th rowspan="1" class="text-center" style="width: 20%">Opsi 1</th>
                                            <th rowspan="1" class="text-center" style="width: 20%">Opsi 2</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in paginatedData" :key="item.id_faktor">
                                            <td class="text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                            <td><b>{{ item.nama_faktor }}</b> {{ item.keterangan }}</td>
                                            <td class="text-center">
                                                <VRadio
                                                    v-model="selectedOptions[item.id_faktor]"
                                                    :value="1"
                                                    @change="handleRadioChange(item.id_faktor, 1)"/>
                                            </td>
                                            <td class="text-center">
                                                <VRadio
                                                    v-model="selectedOptions[item.id_faktor]"
                                                    :value="2"
                                                    @change="handleRadioChange(item.id_faktor, 2)"/>
                                            </td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </div>
                        </VRow>
                    </div>
                </VForm>
            </VCardText>

            <VCardActions class="pa-4">
                <VSpacer/>
                <VBtn color="primary" @click="handleSubmit">Simpan</VBtn>
                <VBtn color="grey-darken-1" variant="text" @click="resetForm">Reset</VBtn>
            </VCardActions>
        </VCard>
    </VDialog>
</template>

<script setup="setup">
    import {ref, reactive, watch, onMounted} from 'vue';
    import {
        VDialog,
        VForm,
        VTextField,
        VSelect,
        VBtn,
        VCard,
        VCardTitle,
        VCardText,
        VCardActions,
        VSpacer,
        VRow,
        VCol,
        VDivider
    } from 'vuetify/components';
    import apiClient from '@/services/api';

    const props = defineProps({
        visible: {
            type: Boolean,
            required: true,
            default: false
        },
        item: {
            type: Object,
            required: true
        },
        onClose: {
            type: Function,
            required: true
        }
    });

    const emit = defineEmits(
        ['update:visible', 'save', 'cancel', 'closeDialog', 'refresh-data']
    );
    const loading = ref(false);
    const showError = ref(false);
    const errorMessage = ref('');

    const desaSearch = ref('');
    const loadingDesa = ref(false);
    const allDesas = ref([]);
    const desaList = ref([]);

    const handleSearchInput = (val) => {
  desaSearch.value = val;
  if (!val || val.length < 2) {
    desaList.value = [...allDesas.value];
  }
};

const filteredDesaList = computed(() => {
  if (!desaSearch.value || desaSearch.value.length < 2) {
    return desaList.value;
  }
  
  const searchTerm = desaSearch.value.toLowerCase();
  return allDesas.value.filter(desa => {
    const searchString = `${desa.nama_desa} ${desa.kecamatan} ${desa.puskesmas}`.toLowerCase();
    return searchString.includes(searchTerm);
  });
});

const loadDesaData = async () => {
  try {
    loadingDesa.value = true;
    const response = await apiClient.get('/desa');
    
    if (!response.data || !Array.isArray(response.data.data)) {
      throw new Error('Invalid data format from API');
    }
    
    allDesas.value = response.data.data.map(desa => ({
      id_desa: desa.id,
      nama_desa: desa.nama_desa || desa.desa || '',
      kecamatan: desa.kecamatan || '',
      puskesmas: desa.puskesmas || '',
      fullInfo: `${desa.nama_desa || desa.desa} - ${desa.kecamatan} (${desa.puskesmas})`
    }));
    
    desaList.value = [...allDesas.value];
    
    if (props.item && props.item.id_desa) {
      fillFormWithItemData();
    }
  } catch (err) {
    console.error('Error loading desa data:', err);
    showError.value = true;
    errorMessage.value = 'Gagal memuat data desa: ' + err.message;
  } finally {
    loadingDesa.value = false;
  }
};

const handleDesaChange = (desaId) => {
  if (!desaId) {
    form.kecamatan = '';
    form.puskesmas = '';
    return;
  }

  const selectedDesa = allDesas.value.find(desa => desa.id_desa === desaId);
  if (selectedDesa) {
    form.kecamatan = selectedDesa.kecamatan;
    form.puskesmas = selectedDesa.puskesmas;
  } else {
    console.log('Desa not found in list');
  }
};

    const formRef = ref(null);
    const form = reactive({
        nama_anak: '',
        jenis_kelamin: '',
        tanggal_lahir: '',
        nik_anak: '',
        kecamatan: '',
        puskesmas: '',
        id_desa: null,
        posyandu: '',
        determinan: {
            jkn: null,
            status_ekonomi: null,
            imunisasi: null,
            bpnt: null,
            pkh: null,
            jamban_sehat: null,
            kebiasaan_merokok: null,
            penyakit_penyerta: '',
            riwayat_ibu_hamil: null,
            sumber_air_bersih: null,
            kecacingan_menderita: null,
            kecacingan_obat: null
        }
    });

    const faktorDeterminan = ref([
        { id_faktor: 1, nama_faktor: 'Status Ekonomi', keterangan: '(Gakin:1; Non Gakin:2)' },
        { id_faktor: 2, nama_faktor: 'Imunisasi', keterangan: '(Lengkap:1; Tidak:2)' },
        { id_faktor: 3, nama_faktor: 'BPNT', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 4, nama_faktor: 'PKH', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 5, nama_faktor: 'Jamban Sehat', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 6, nama_faktor: 'Sumber Air Bersih', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 7, nama_faktor: 'Kebiasaan Merokok', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 8, nama_faktor: 'Riwayat Ibu Hamil', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 9, nama_faktor: 'Menderita Cacingan', keterangan: '(Ya:1; Tidak:2)' },
        { id_faktor: 10, nama_faktor: 'Mendapat Obat', keterangan: '(Ya:1; Tidak:2)' },
    ]);

    const selectedOptions = reactive({});

    const handleRadioChange = (idFaktor, value) => {
        selectedOptions[idFaktor] = value;
    };

    const currentPage = ref(1);
    const itemsPerPage = ref(10);

    const paginatedData = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return faktorDeterminan
            .value
            .slice(start, end);
    });

    const yesNoOptions = [
        {
            value: 1,
            title: 'Ya'
        }, {
            value: 2,
            title: 'Tidak'
        }
    ];

    const jenisKelaminOptions = [
        {
            value: 'L',
            title: 'Laki-laki'
        }, {
            value: 'P',
            title: 'Perempuan'
        }
    ];

    const statusEkonomiOptions = [
        {
            value: 1,
            title: 'Gakin'
        }, {
            value: 2,
            title: 'Non Gakin'
        }
    ];

    const imunisasiOptions = [
        {
            value: 1,
            title: 'Lengkap'
        }, {
            value: 2,
            title: 'Tidak Lengkap'
        }
    ];

    const jknOptions = [
        {
            value: 1,
            title: 'BPJS Mandiri'
        }, {
            value: 2,
            title: 'BPJS Pemerintah'
        }, {
            value: 3,
            title: 'Tidak Punya'
        }, {
            value: 4,
            title: 'Asuransi Swasta'
        }
    ];

    const handleClose = () => {
        props.onClose();
        emit('update:visible', false);
        emit('closeDialog');
    };

    watch(() => form.id_desa, (newDesaId) => {
        if (newDesaId && desaList.value.length > 0) {
            handleDesaChange(newDesaId);
        }
    });

    const fillFormWithItemData = () => {
        if (props.item) {
            form.nama_anak = props.item.nama_anak || '';
            form.jenis_kelamin = props.item.jenis_kelamin || '';
            form.tanggal_lahir = props.item.tanggal_lahir || '';
            form.nik_anak = props.item.nik_anak || '';
            form.id_desa = props.item.id_desa || null;
            form.posyandu = props.item.posyandu || '';

            if (props.item.id_desa && allDesas.value.length > 0) {
            const selectedDesa = allDesas.value.find(desa => desa.id_desa === props.item.id_desa);
            if (selectedDesa) {
                form.kecamatan = selectedDesa.kecamatan;
                form.puskesmas = selectedDesa.puskesmas;
            } else {
                apiClient.get(`/desa/${props.item.id_desa}`)
                .then(response => {
                    const desa = response.data.data;
                    form.kecamatan = desa.kecamatan;
                    form.puskesmas = desa.puskesmas;

                    allDesas.value.push({
                    id_desa: desa.id,
                    nama_desa: desa.nama_desa || desa.desa || '',
                    kecamatan: desa.kecamatan || '',
                    puskesmas: desa.puskesmas || ''
                    });
                })
                .catch(error => {
                    console.error('Error fetching desa details:', error);
                });
            }
            }

            if (props.item.determinan) {
                form.determinan = {
                    jkn: typeof props.item.determinan.jkn === 'number'
                        ? props.item.determinan.jkn
                        : Number(props.item.determinan.jkn), 
                    status_ekonomi: typeof props.item.determinan.status_ekonomi === 'number'
                        ? props.item.determinan.status_ekonomi
                        : (
                            props.item.determinan.status_ekonomi === '1'
                                ? 1
                                : 2
                        ),
                    imunisasi: typeof props.item.determinan.imunisasi === 'number'
                        ? props.item.determinan.imunisasi
                        : (
                            props.item.determinan.imunisasi === '1'
                                ? 1
                                : 2
                        ),
                    bpnt: typeof props.item.determinan.bpnt === 'number'
                        ? props.item.determinan.bpnt
                        : (
                            props.item.determinan.bpnt === '1'
                                ? 1
                                : 2
                        ),
                    pkh: typeof props.item.determinan.pkh === 'number'
                        ? props.item.determinan.pkh
                        : (
                            props.item.determinan.pkh === '1'
                                ? 1
                                : 2
                        ),
                    sumber_air_bersih: typeof props.item.determinan.sumber_air_bersih === 'number'
                        ? props.item.determinan.sumber_air_bersih
                        : (
                            props.item.determinan.sumber_air_bersih === '1'
                                ? 1
                                : 2
                        ),
                    jamban_sehat: typeof props.item.determinan.jamban_sehat === 'number'
                        ? props.item.determinan.jamban_sehat
                        : (
                            props.item.determinan.jamban_sehat === '1'
                                ? 1
                                : 2
                        ),
                    kebiasaan_merokok: typeof props.item.determinan.kebiasaan_merokok === 'number'
                        ? props.item.determinan.kebiasaan_merokok
                        : (
                            props.item.determinan.kebiasaan_merokok === '1'
                                ? 1
                                : 2
                        ),
                    riwayat_ibu_hamil: typeof props.item.determinan.riwayat_ibu_hamil === 'number'
                        ? props.item.determinan.riwayat_ibu_hamil
                        : (
                            props.item.determinan.riwayat_ibu_hamil === '1'
                                ? 1
                                : 2
                        ),
                    penyakit_penyerta: props.item.determinan.penyakit_penyerta || '',
                    kecacingan_menderita: typeof props.item.determinan.kecacingan_menderita === 'number'
                        ? props.item.determinan.kecacingan_menderita
                        : (
                            props.item.determinan.kecacingan_menderita === '1'
                                ? 1
                                : 2
                        ),
                    kecacingan_obat: typeof props.item.determinan.kecacingan_obat === 'number'
                        ? props.item.determinan.kecacingan_obat
                        : (
                            props.item.determinan.kecacingan_obat === '1'
                                ? 1
                                : 2
                        )
                };

                faktorDeterminan
                    .value
                    .forEach(item => {
                        let value = null;

                        switch (item.nama_faktor) {
                            case 'Status Ekonomi':
                                value = form.determinan.status_ekonomi;
                                break;
                            case 'Imunisasi':
                                value = form.determinan.imunisasi;
                                break;
                            case 'BPNT':
                                value = form.determinan.bpnt;
                                break;
                            case 'PKH':
                                value = form.determinan.pkh;
                                break;
                            case 'Jamban Sehat':
                                value = form.determinan.jamban_sehat;
                                break;
                            case 'Sumber Air Bersih':
                                value = form.determinan.sumber_air_bersih;
                                break;
                            case 'Kebiasaan Merokok':
                                value = form.determinan.kebiasaan_merokok;
                                break;
                            case 'Riwayat Ibu Hamil':
                                value = form.determinan.riwayat_ibu_hamil;
                                break;
                            case 'Menderita Cacingan':
                                value = form.determinan.kecacingan_menderita;
                                break;
                            case 'Mendapat Obat':
                                value = form.determinan.kecacingan_obat;
                                break;
                            default:
                                console.warn(`Faktor determinan "${item.nama_faktor}" tidak ditemukan.`);
                                value = null;
                        }

                        selectedOptions[item.id_faktor] = value !== undefined && value !== null
                            ? Number(value)
                            : null;
                    });
            }
        }
    };

    watch(() => props.item, (newItem) => {
        if (newItem && desaList.value.length > 0) {
            fillFormWithItemData();
        }
    }, {immediate: true});

    watch(() => props.visible, (isVisible) => {
        if (isVisible && props.item && desaList.value.length > 0) {
            fillFormWithItemData();
        }
    });

    const resetForm = () => {
        fillFormWithItemData();
    };

    const handleSubmit = async () => {
        const {valid} = await formRef
            .value
            .validate();

        if (!valid) {
            await window.Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Mohon lengkapi semua field yang wajib diisi',
            });
            return;
        }

        loading.value = true;

        try {
            const payload = {
                nik_anak: form.nik_anak,
                nama_anak: form.nama_anak,
                jenis_kelamin: form.jenis_kelamin,
                tanggal_lahir: form.tanggal_lahir,
                id_desa: form.id_desa,
                posyandu: form.posyandu,
                penyakit_penyerta: form.determinan.penyakit_penyerta || '',
                jkn: form.determinan.jkn.toString(),
                status_ekonomi: selectedOptions[1] === 1? '1': '2',
                imunisasi: selectedOptions[2] === 1? '1': '2',
                bpnt: selectedOptions[3] === 1? '1': '2',
                pkh: selectedOptions[4] === 1? '1': '2',
                jamban_sehat: selectedOptions[5] === 1? '1': '2',
                sumber_air_bersih: selectedOptions[6] === 1? '1': '2',
                kebiasaan_merokok: selectedOptions[7] === 1? '1': '2',
                riwayat_ibu_hamil: selectedOptions[8] === 1? '1': '2',
                kecacingan_menderita: selectedOptions[9] === 1? '1': '2',
                kecacingan_obat: selectedOptions[10] === 1? '1': '2'
            };

            const response = await apiClient.put(
                `/anak-stunting/${props.item.id_anak}`,
                payload
            );
            
            emit('refresh-data');
            emit('save', response.data);
            resetForm();

            handleClose();

            await window.Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data stunting berhasil diperbarui.',
                showConfirmButton: false,
                timer: 2000,
                });
        } catch (error) {
            showError.value = true;
            console.error('Error during submission:', error);
            let errorMessage = 'Terjadi kesalahan saat menyimpan data. Silakan cek konsol untuk detailnya.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage = errors.join(', ');
    }

    await window.Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: errorMessage,
    });
  } finally {
    loading.value = false;
  }
    };

    onMounted(async () => {
        await loadDesaData();
        if (props.item) {
            fillFormWithItemData();
        }
    });
</script>

<style scoped>
.manual-form {
    max-width: 800px;
    margin: 0 auto;
    padding: 0;
}

.section-header {
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding-bottom: 8px;
    margin-bottom: 16px;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

:deep(.v-input) {
    margin-bottom: 12px;
}

.table-container {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-top: 8px;
    margin-bottom: 16px;
    width: 100%;
}

.custom-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.custom-table th,
.custom-table td {
    border-right: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    padding: 12px 16px;
    vertical-align: middle;
}

.custom-table th {
    background-color: #f5f5f5;
    font-weight: 600;
    text-transform: none;
    color: #333;
}

.header-cell-top-left {
    border-top-left-radius: 8px;
}

.header-cell-top-right {
    border-top-right-radius: 8px;
}

.custom-table tr:last-child td:first-child {
    border-bottom-left-radius: 8px;
}

.custom-table tr:last-child td:last-child {
    border-bottom-right-radius: 8px;
}

.custom-table tbody tr:hover {
    background-color: #f8f9fa;
}

.custom-table th:nth-child(1),
.custom-table td:nth-child(1) {
    width: 50px;
}

.custom-table th:nth-child(2),
.custom-table td:nth-child(2) {
    width: 65%;
}

.custom-table th:nth-child(3),
.custom-table td:nth-child(3),
.custom-table th:nth-child(4),
.custom-table td:nth-child(4) {
    width: 15%;
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

:deep(.v-radio) {
    margin: 0 auto;
    display: flex;
    justify-content: center;
}

:deep(.v-selection-control) {
    margin: 0 auto;
}
</style>