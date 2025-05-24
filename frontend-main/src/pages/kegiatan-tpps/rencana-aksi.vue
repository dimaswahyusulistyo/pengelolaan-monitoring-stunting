<template>
    <VRow>
        <VCol cols="12">
            <VCard
                title="SINERGI PELAKSANAAN AKSI KONVERGENSI DENGAN SIKLUS
                      PERENCANAAN DAN PENGANGGARAN KABUPATEN/KOTA"
                class="main-title timeline-container rounded-lg">

                <div class="timeline-wrapper my-4">
                    <VCard flat="flat" class="timeline-container">
                        <div class="timeline-header mb-2 d-flex align-center">
                            <h3 class="text-h6 font-weight-bold">Progress Timeline Tahun
                                {{ selectedYear }}</h3>
                            <VSpacer/>
                            <VSelect
                                v-model="selectedYear"
                                :items="yearOptions"
                                label="Tahun"
                                density="compact"
                                hide-details="hide-details"
                                class="year-selector ms-2"
                                style="width: 120px;"
                                @update:model-value="handleYearChange"/>
                        </div>

                        <div class="timeline-visual">
                            <div class="timeline-line">
                                <div
                                    v-for="(month, index) in months"
                                    :key="index"
                                    class="month-marker"
                                    :class="[
                          { 'has-data': hasDataForMonth(month.value) },
                          { 'current-month': isCurrentMonth(month.value) },
                          getTimelineStatusClass(month.value)
                        ]"
                                    @click="filterByMonth(month.value)">
                                    <div class="month-dot" :style="{ backgroundColor: getMonthColor(month.value) }">
                                        <VTooltip activator="parent" location="top">
                                            {{ month.label }}
                                            -
                                            {{ getMonthStatusText(month.value) }}
                                        </VTooltip>
                                    </div>
                                    <div
                                        class="month-label"
                                        :style="{ color: isCurrentMonth(month.value) ? 'var(--primary)' : '' }">
                                        {{ month.short }}
                                    </div>
                                </div>

                                <div class="progress-indicator" :style="{ width: progressPercentage + '%' }"></div>
                            </div>

                            <div class="status-indicators">
                                <div
                                    v-for="(item, monthIndex) in timelineData"
                                    :key="monthIndex"
                                    class="status-item"
                                    :style="{ left: getMonthPosition(item.month) + '%' }"
                                    v-if="item && item.count > 0">
                                    <VBadge
                                        :color="getStatusColor(item.status)"
                                        :content="getItemCount(item.month)"
                                        inline="inline">
                                        <VIcon
                                            :color="getStatusColor(item.status)"
                                            size="small"
                                            :icon="getStatusIcon(item.status)"
                                            @click="filterByMonth(item.month)">
                                            <VTooltip activator="parent" location="bottom">
                                                {{ item.count }}
                                                aksi di
                                                {{ getMonthName(item.month) }}
                                            </VTooltip>
                                        </VIcon>
                                    </VBadge>
                                </div>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="timeline-legend mt-2 d-flex flex-wrap">
                            <div class="legend-item d-flex align-center me-4">
                                <div class="legend-dot" style="background-color: #4CAF50;"></div>
                                <span class="text-caption">Selesai</span>
                            </div>
                            <div class="legend-item d-flex align-center me-4">
                                <div class="legend-dot" style="background-color: #F44336;"></div>
                                <span class="text-caption">Terlewat</span>
                            </div>
                            <div class="legend-item d-flex align-center me-4">
                                <div class="legend-dot" style="background-color: #2196F3;"></div>
                                <span class="text-caption">Bulan Ini</span>
                            </div>
                            <div class="legend-item d-flex align-center me-4">
                                <div class="legend-dot" style="background-color: #ccc;"></div>
                                <span class="text-caption">Belum Ada Data</span>
                            </div>
                        </div>
                    </VCard>
                </div>

                <h3 class="text-h6 font-weight-bold mb-4 ms-4">Detail Rencana Aksi</h3>
                <VRow class="align-center px-4 py-2">
                    <VCol cols="12" md="6" class="d-flex">
                        <VTextField
                            v-model="searchQuery"
                            prepend-inner-icon="bx-search"
                            placeholder="Search"
                            class="search-box flex-grow-1"
                            clearable="clearable"
                            density="comfortable"
                            @click:clear="clearFilters"/>
                    </VCol>
                    <VCol cols="12" md="6" class="d-flex justify-end">
                        <VChip
                            v-if="selectedMonthFilter !== null"
                            class="me-2"
                            closable="closable"
                            @click:close="clearMonthFilter">
                            {{ getMonthName(selectedMonthFilter) }}
                        </VChip>
                        <VBtn color="success" class="me-2 rounded-lg" @click="openAddDialog">Tambah</VBtn>
                        <VBtn color="warning" @click="exportData" class="me-2 rounded-lg">Export</VBtn>
                    </VCol>
                </VRow>

                <div class="table-container">
                    <VTable class="custom-table elevation-1">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Aksi</th>
                                <th class="text-center">Deskripsi Aksi</th>
                                <th class="text-center">Bulan</th>
                                <th class="text-center header-cell-top-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in paginatedData" :key="item.id">
                                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td>{{ item.nama_aksi }}</td>
                                <td>
                                    <VTooltip location="top">
                                        <template #activator="{ props }">
                                        <span v-bind="props" class="truncate-text">
                                            {{ truncateText(item.deskripsi_aksi, 50) }}
                                        </span>
                                        </template>
                                        <span>{{ item.deskripsi_aksi }}</span>
                                    </VTooltip>
                                </td>
                                <td>{{ formatDate(item.bulan_aksi) }}</td>
                                <td>
                                    <div class="button-group">
                                        <VBtn icon="icon" color="warning" @click="editItem(item)" class="square-button">
                                            <i class="bx bxs-edit-alt"></i>
                                        </VBtn>
                                        <VBtn
                                            icon="icon"
                                            color="success"
                                            @click="viewDetail(item)"
                                            class="square-button">
                                            <i class="bx bxs-info-circle"></i>
                                        </VBtn>
                                        <VBtn icon="icon" color="error" @click="deleteItem(item)" class="square-button">
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
                        <VSelect
                            v-model="itemsPerPage"
                            :items="[5, 10, 15, 20]"
                            variant="outlined"
                            density="compact"
                            hide-details="hide-details"
                            class="items-per-page-select"
                            style="width: 80px;"/>
                    </div>

                    <VPagination
                        v-model="currentPage"
                        :length="totalPages"
                        :total-visible="7"
                        rounded="lg"/>

                    <div class="d-flex align-center">
                        <span class="text-body-2">
                            {{ filteredData.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}-{{ Math.min(currentPage * itemsPerPage, filteredData.length) }}
                            of
                            {{ filteredData.length }}
                        </span>
                    </div>
                </div>
            </VCard>
        </VCol>

        <VDialog v-model="dialog" max-width="600px">
            <VCard class="form">
                <VCardTitle class="text-h5 d-flex align-center mb-2">
                    <span>{{ formMode === 'add' ? 'Tambah' : 'Edit' }}
                        Rencana Aksi Daerah</span>
                    <VSpacer/>
                    <VBtn icon="icon" variant="text" @click="dialog = false">
                        <i class="bx bx-x"></i>
                    </VBtn>
                </VCardTitle>

                <VCardText class="pt-4">
                    <VForm ref="formRef">
                        <div class="form-section">
                            <div class="mt-4">
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="newItem.nama_aksi"
                                    label="Nama Aksi"
                                    class="mb-6"
                                    :rules="[v => !!v || 'Harus diisi']"
                                    required="required"/>
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="newItem.deskripsi_aksi"
                                    label="Deskripsi Aksi"
                                    class="mb-6"
                                    :rules="[v => !!v || 'Harus diisi']"
                                    required="required"/>
                                <VTextField
                                    persistent-placeholder="persistent-placeholder"
                                    v-model="newItem.bulan_aksi"
                                    label="Bulan Aksi"
                                    type="date"
                                    class="mb-6"
                                    :rules="[v => !!v || 'Harus diisi']"
                                    required="required"/>
                            </div>
                        </div>
                    </VForm>
                </VCardText>

                <VCardActions class="pt-2 pb-4 px-4">
                    <VSpacer/>
                    <VBtn color="primary" @click="saveItem" min-width="100">Simpan</VBtn>
                    <VBtn
                        color="grey-darken-1"
                        variant="text"
                        @click="dialog = false"
                        min-width="100">Batal</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>

        <VDialog v-model="detailDialog" max-width="900px">
            <VCard>
                <VCardTitle class="d-flex align-center pa-4">
                    <span>Detail Rencana Aksi Daerah</span>
                    <VSpacer/>
                    <VBtn icon="icon" variant="text" @click="detailDialog = false">
                        <i class="bx bx-x"></i>
                    </VBtn>
                </VCardTitle>

                <VDivider/>

                <VCardText class="pa-4">
                    <VRow>
                        <VCol cols="12">
                            <VCard variant="outlined">
                                <VCardTitle class="pb-2 pt-4 px-4">Informasi Kegiatan</VCardTitle>
                                <VCardText>
                                    <VRow>
                                        <VCol cols="12">
                                            <p class="text-subtitle-2 mb-1">Nama Aksi:</p>
                                            <p class="text-body-1 font-weight-medium mb-4">{{ detailItem.nama_aksi }}</p>

                                            <p class="text-subtitle-2 mb-1">Deskripsi Aksi:</p>
                                            <p class="text-body-1 font-weight-medium">{{ detailItem.deskripsi_aksi }}</p>

                                            <p class="text-subtitle-2 mb-1">Bulan Aksi:</p>
                                            <p class="text-body-1 font-weight-medium">{{ formatDate(detailItem.bulan_aksi) }}</p>
                                        </VCol>
                                    </VRow>
                                </VCardText>
                            </VCard>
                        </VCol>
                    </VRow>
                </VCardText>

                <VDivider/>

                <VCardActions class="pa-4">
                    <VSpacer/>
                    <VBtn color="grey-darken-1" variant="text" @click="detailDialog = false">Tutup</VBtn>
                </VCardActions>
            </VCard>
        </VDialog>
    </VRow>
</template>

<script setup="setup">
    import {ref, computed, onMounted, watch} from 'vue';
    import apiClient from '@/services/api';

    import { jsPDF } from 'jspdf';
    import html2canvas from 'html2canvas';

    const searchQuery = ref('');
    const currentPage = ref(1);
    const itemsPerPage = ref(5);
    const dialog = ref(false);
    const formRef = ref(null);
    const detailDialog = ref(false);
    const formMode = ref('add');
    const detailItem = ref({});
    const items = ref([]);
    const selectedMonthFilter = ref(null);

    const newItem = ref(
        {id_rad: null, nama_aksi: '', deskripsi_aksi: '', bulan_aksi: ''}
    );

    const months = [
        {
            label: 'Januari',
            short: 'JAN',
            value: 1,
            color: '#E91E63'
        }, {
            label: 'Februari',
            short: 'FEB',
            value: 2,
            color: '#FF5722'
        }, {
            label: 'Maret',
            short: 'MAR',
            value: 3,
            color: '#FF9800'
        }, {
            label: 'April',
            short: 'APR',
            value: 4,
            color: '#8BC34A'
        }, {
            label: 'Mei',
            short: 'MEI',
            value: 5,
            color: '#00BCD4'
        }, {
            label: 'Juni',
            short: 'JUN',
            value: 6,
            color: '#673AB7'
        }, {
            label: 'Juli',
            short: 'JUL',
            value: 7,
            color: '#9E9E9E'
        }, {
            label: 'Agustus',
            short: 'AGU',
            value: 8,
            color: '#2196F3'
        }, {
            label: 'September',
            short: 'SEP',
            value: 9,
            color: '#4CAF50'
        }, {
            label: 'Oktober',
            short: 'OKT',
            value: 10,
            color: '#FFEB3B'
        }, {
            label: 'November',
            short: 'NOV',
            value: 11,
            color: '#FF5722'
        }, {
            label: 'Desember',
            short: 'DES',
            value: 12,
            color: '#E91E63'
        }
    ];

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();
    const selectedYear = ref(currentYear);

    const yearOptions = ref([currentYear]);

    const getMonthFromDate = (dateString) => {
        if (!dateString) 
            return null;
        return new Date(dateString).getMonth() + 1;
    };

    const getYearFromDate = (dateString) => {
        if (!dateString) 
            return null;
        return new Date(dateString).getFullYear();
    };

    const timelineData = computed(() => {
        const monthlyData = Array(12)
            .fill()
            .map((_, index) => ({
                month: index + 1,
                items: [],
                count: 0,
                status: 'pending'
            }));

        if (items.value && items.value.length) {
            items
                .value
                .forEach(item => {
                    if (!item.bulan_aksi) 
                        return;
                    
                    const date = new Date(item.bulan_aksi);
                    const itemYear = date.getFullYear();
                    const month = date.getMonth() + 1;

                    if (itemYear === selectedYear.value && month >= 1 && month <= 12) {
                        const monthIndex = month - 1;
                        monthlyData[monthIndex]
                            .items
                            .push(item);
                        monthlyData[monthIndex].count++;

                        const isPastMonth = (month < currentMonth && itemYear === currentYear) || itemYear < currentYear;
                        const isCurrentMonth = month === currentMonth && itemYear === currentYear;

                        if (isPastMonth) {
                            monthlyData[monthIndex].status = 'completed';
                        } else if (isCurrentMonth) {
                            monthlyData[monthIndex].status = 'inProgress';
                        }
                    }
                });
        }

        return monthlyData;
    });

    const progressPercentage = computed(() => {
        if (selectedYear.value < currentYear) {
            return 100;
        }

        if (selectedYear.value > currentYear) 
            return 0;

        const completedMonths = currentMonth - 1;
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const currentDay = currentDate.getDate();
        const currentMonthProgress = (currentDay / daysInMonth);

        return ((completedMonths + currentMonthProgress) / 12) * 100;
    });

    function getTimelineStatusClass(monthValue) {
        const monthIndex = monthValue - 1;
        const monthData = timelineData.value[monthIndex];

        if (!monthData) 
            return 'status-pending';
        
        if (selectedYear.value < currentYear) {
            return monthData.count > 0
                ? 'status-completed'
                : 'status-missed';
        }

        if (selectedYear.value > currentYear) {
            return 'status-pending';
        }

        if (monthValue < currentMonth) {
            return monthData.count > 0
                ? 'status-completed'
                : 'status-missed';
        }

        if (monthValue === currentMonth) {
            return monthData.count > 0
                ? 'status-inProgress'
                : 'status-current';
        }

        return 'status-pending';
    }

    function getMonthColor(monthValue) {
        if (selectedYear.value < currentYear) {
            return hasDataForMonth(monthValue)
                ? '#4CAF50'
                : '#F44336';
        }

        if (selectedYear.value > currentYear) {
            return '#ccc';
        }

        if (monthValue < currentMonth) {
            return hasDataForMonth(monthValue)
                ? '#4CAF50'
                : '#F44336';
        }

        if (monthValue === currentMonth) {
            return hasDataForMonth(monthValue)
                ? '#FFC107'
                : '#2196F3';
        }

        return '#ccc';
    }

    function getMonthStatusText(monthValue) {
        const monthData = timelineData.value[monthValue - 1];
        if (!monthData) 
            return 'Tidak ada data';
        
        const count = monthData.count;

        if (selectedYear.value < currentYear) {
            return count > 0
                ? `Selesai (${count} aksi)`
                : 'Terlewat (tidak ada data)';
        }

        if (selectedYear.value > currentYear) {
            return 'Belum Dimulai';
        }

        if (monthValue < currentMonth) {
            return count > 0
                ? `Selesai (${count} aksi)`
                : 'Terlewat (tidak ada data)';
        }

        if (monthValue === currentMonth) {
            return count > 0
                ? `Dalam Proses (${count} aksi)`
                : 'Bulan Ini (belum ada aksi)';
        }

        return 'Belum Dimulai';
    }

    function hasDataForMonth(monthValue) {
        const monthIndex = monthValue - 1;
        return timelineData.value[monthIndex]
            ?.count > 0 || false;
    }

    function getItemCount(monthValue) {
        const monthIndex = monthValue - 1;
        return timelineData.value[monthIndex]
            ?.count || 0;
    }

    function isCurrentMonth(monthValue) {
        return monthValue === currentMonth && selectedYear.value === currentYear;
    }

    function getMonthPosition(monthValue) {
        return ((monthValue - 1) / 11) * 100;
    }

    function getMonthName(monthValue) {
        const month = months.find(m => m.value === monthValue);
        return month
            ? month.label
            : '';
    }

    function getStatusColor(status) {
        switch (status) {
            case 'completed':
                return 'success';
            case 'inProgress':
                return 'warning';
            case 'missed':
                return 'error';
            case 'current':
                return 'info';
            default:
                return 'grey';
        }
    }

    function getStatusIcon(status) {
        switch (status) {
            case 'completed':
                return 'bx-check-circle';
            case 'inProgress':
                return 'bx-time';
            case 'missed':
                return 'bx-error';
            case 'current':
                return 'bx-calendar';
            default:
                return 'bx-question-mark';
        }
    }

    function filterByMonth(monthValue) {
        if (!hasDataForMonth(monthValue)) 
            return;
        
        selectedMonthFilter.value = monthValue;
        currentPage.value = 1;
    }

    function clearMonthFilter() {
        selectedMonthFilter.value = null;
    }

    function clearFilters() {
        searchQuery.value = '';
        selectedMonthFilter.value = null;
    }

    function handleYearChange() {
        clearFilters();
        fetchData();
    }

    watch(selectedYear, () => {
        fetchData();
    });

    const fetchData = async () => {
        try {
            const response = await apiClient.get('/rad', {
                params: {
                    tahun: selectedYear.value
                }
            });
            items.value = response.data.data;
            if (response.data.available_years) {
                yearOptions.value = response.data.available_years;
            }
        } catch (error) {
            console.error('Gagal mengambil data:', error);
        }
    };

    const filteredData = computed(() => {
        let filtered = items.value;

        filtered = filtered.filter(item => {
            if (!item.bulan_aksi) return false;
            const itemYear = getYearFromDate(item.bulan_aksi);
            return itemYear === selectedYear.value;
        });

        if (searchQuery.value) {
            filtered = filtered.filter(
                item => item.nama_aksi.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                    item.deskripsi_aksi.toLowerCase().includes(searchQuery.value.toLowerCase())
            );
        }

        if (selectedMonthFilter.value !== null) {
            filtered = filtered.filter(item => {
                if (!item.bulan_aksi) return false;
                const itemMonth = getMonthFromDate(item.bulan_aksi);
                return itemMonth === selectedMonthFilter.value;
            });
        }

        return filtered;
    });

    const paginatedData = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return filteredData
            .value
            .slice(start, end);
    });

    const totalPages = computed(() => {
        return Math.ceil(filteredData.value.length / itemsPerPage.value) || 1;
    });

    const openAddDialog = () => {
        formMode.value = 'add';
        newItem.value = {
            id_rad: null,
            nama_aksi: '',
            deskripsi_aksi: '',
            bulan_aksi: ''
        };
        dialog.value = true;
    };

    const saveItem = async () => {
        if (!newItem.value.nama_aksi || !newItem.value.deskripsi_aksi || !newItem.value.bulan_aksi) {
            await window.Swal.fire({
                icon: 'error', 
                title: 'Gagal!', 
                text: 'Mohon lengkapi semua field yang wajib diisi',
                showConfirmButton: false,
                timer: 2000
            });
            return;
        }

        try {
            if (formMode.value === 'edit' && newItem.value.id_rad) {
                await apiClient.put(`/rad/${newItem.value.id_rad}`, newItem.value);
                await window.Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data berhasil diperbarui',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                await apiClient.post('/rad', newItem.value);
                await window.Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
            dialog.value = false;
            fetchData();
        } catch (error) {
            console.error('Gagal menyimpan data:', error);
            await window.Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data',
                showConfirmButton: false,
                timer: 2000
            });
        }
    };

    const truncateText = (text, maxLength) => {
        if (!text) return '';
        return text.length > maxLength 
            ? text.substring(0, maxLength) + '...' 
            : text;
    };

    const formatDate = (date) => {
        if (!date) 
            return '';
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const editItem = (item) => {
        formMode.value = 'edit';
        newItem.value = {
            id_rad: item.id_rad,
            nama_aksi: item.nama_aksi,
            deskripsi_aksi: item.deskripsi_aksi,
            bulan_aksi: item.bulan_aksi
        };

        dialog.value = true;
    };

    const viewDetail = (item) => {
        detailItem.value = {
            ...item
        };
        detailDialog.value = true;
    };

    const deleteItem = async (item) => {

        if (!item.id_rad) {
            console.error('ID_RAD tidak ditemukan:', item);
            await window
                .Swal
                .fire(
                    {icon: 'error', title: 'Gagal!', text: 'Tidak dapat menghapus: ID_RAD tidak ditemukan'}
                );
            return;
        }

        const result = await window
            .Swal
            .fire({
                title: 'Apakah Anda yakin?',
                text: `Anda akan menghapus "${item.nama_aksi}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            });

        if (result.isConfirmed) {
            try {
                await apiClient.delete(`/rad/${item.id_rad}`);
                await window
                    .Swal
                    .fire(
                        {icon: 'success', title: 'Terhapus!', text: 'Data telah berhasil dihapus.'}
                    );
                fetchData();
            } catch (error) {
                console.error('Gagal menghapus data:', error);
                await window
                    .Swal
                    .fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.response
                            ?.data
                                ?.message || 'Terjadi kesalahan saat menghapus data'
                    });
            }
        }
    };

    const exportData = async () => {
        try {
            const exportElement = document.createElement('div');
            exportElement.style.width = '210mm';
            exportElement.style.padding = '15mm 20mm';
            exportElement.style.fontFamily = 'Arial';
            exportElement.style.position = 'relative';
            exportElement.style.minHeight = '297mm';

            const now = new Date();
            const formattedDate = now.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });
            const formattedTime = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            exportElement.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin: 10px 0;">LAPORAN RENCANA AKSI DAERAH</h2>
                <h3 style="margin: 5px 0;">TAHUN ${selectedYear.value}</h3>
            </div>
            <div style="text-align: right; margin-bottom: 10px; font-size: 12px;">
                Tanggal: ${formattedDate} ${formattedTime}
            </div>
            `;

            const table = document.createElement('table');
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.fontSize = '12px';
            table.style.marginBottom = '20px';

            const thead = document.createElement('thead');
            thead.innerHTML = `
            <tr style="background-color: #16a085; color: white;">
                <th style="border: 1px solid #ddd; padding: 8px; width: 5%;">No</th>
                <th style="border: 1px solid #ddd; padding: 8px; width: 25%;">Nama Aksi</th>
                <th style="border: 1px solid #ddd; padding: 8px; width: 50%;">Deskripsi Aksi</th>
                <th style="border: 1px solid #ddd; padding: 8px; width: 20%;">Bulan Pelaksanaan</th>
            </tr>
            `;

            const tbody = document.createElement('tbody');
            filteredData.value.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${index + 1}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${item.nama_aksi}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${item.deskripsi_aksi}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${formatDate(item.bulan_aksi)}</td>
                `;
                tbody.appendChild(row);
            });
            
            table.appendChild(thead);
            table.appendChild(tbody);
            exportElement.appendChild(table);

            exportElement.style.position = 'fixed';
            exportElement.style.left = '-9999px';
            document.body.appendChild(exportElement);

            const canvas = await html2canvas(exportElement, {
                scale: 2,
                logging: false,
                useCORS: true
            });
            
            const imgData = canvas.toDataURL('image/png');
            
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            
            const imgWidth = doc.internal.pageSize.getWidth() - 40;
            const pageHeight = doc.internal.pageSize.getHeight();
            const contentHeight = pageHeight - 30;
            const imgHeight = canvas.height * imgWidth / canvas.width;
            
            const totalPages = Math.ceil(imgHeight / contentHeight);
            
            const addFooter = (pageNum) => {
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                
                doc.text(`Halaman ${pageNum} dari ${totalPages}`, doc.internal.pageSize.getWidth() / 2, pageHeight - 10, { align: 'center' });
            };
            
            let heightLeft = imgHeight;
            let position = 0;
            let currentPage = 1;
            
            doc.addImage(imgData, 'PNG', 20, 15, imgWidth, imgHeight, undefined, 'FAST');
            addFooter(currentPage);
            
            while (heightLeft > contentHeight) {
                position = heightLeft - imgHeight;
                heightLeft -= contentHeight;
                
                doc.addPage();
                currentPage++;
                
                doc.addImage(
                    imgData, 
                    'PNG', 
                    20,
                    15 - position,
                    imgWidth, 
                    imgHeight, 
                    undefined, 
                    'FAST'
                );
                
                addFooter(currentPage);
            }
            
            document.body.removeChild(exportElement);

            let filename = `Laporan_RAD_${selectedYear.value}`;
            if (selectedMonthFilter.value !== null) {
                filename += `_${getMonthName(selectedMonthFilter.value)}`;
            }
            
            doc.save(`${filename}.pdf`);
            
        } catch (error) {
            console.error('Gagal mengekspor PDF:', error);
            await window.Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat membuat laporan PDF'
            });
        }
    };

    onMounted(() => {
        selectedYear.value = currentYear;
        fetchData();
    });
</script>

<style scoped="scoped">
    .timeline-wrapper {
        padding: 16px 0;
    }

    .timeline-container {
        padding: 16px;
        border-radius: 8px;
    }

    .timeline-visual {
        position: relative;
        padding: 30px 0;
        margin-top: 16px;
    }

    .timeline-line {
        position: relative;
        height: 4px;
        background-color: #e0e0e0;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        margin: 0 8px;
    }

    .progress-indicator {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background-color: var(--primary, #1976D2);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .month-marker.status-completed .month-dot {
        background-color: #4CAF50 !important;
    }

    .month-marker.status-inProgress .month-dot {
        background-color: #FFC107 !important;
    }

    .month-marker.status-missed .month-dot {
        background-color: #F44336 !important;
    }

    .month-marker.status-current .month-dot {
        background-color: #2196F3 !important;
    }

    .progress-indicator {
        background-color: var(--primary);
        height: 6px;
        transition: width 0.5s ease;
    }

    .month-marker {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        transform: translateX(-50%);
        z-index: 2;
    }

    .month-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: #ccc;
        position: relative;
        top: -6px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .has-data .month-dot {
        cursor: pointer;
    }

    .has-data .month-dot:hover {
        transform: scale(1.2);
    }

    .current-month .month-dot {
        box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.2);
    }

    .month-label {
        font-size: 10px;
        font-weight: bold;
        margin-top: 4px;
        color: #666;
    }

    .current-month .month-label {
        font-weight: bold;
    }

    .status-indicators {
        position: absolute;
        width: 100%;
        top: 45px;
        height: 20px;
    }

    .status-item {
        position: absolute;
        transform: translateX(-50%);
    }

    .timeline-legend {
        margin-top: 30px;
    }

    .legend-item {
        margin-right: 16px;
    }

    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .legend-dot.current {
        background-color: #fff;
        box-shadow: 0 0 0 2px var(--primary, #1976D2);
    }

    .year-selector {
        width: 120px;
    }

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

    .custom-table thead th {
        font-weight: bold;
        background-color: #f5f5f5;
        text-align: center;
        padding: 12px;
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }

    .custom-table tbody td {
        font-weight: normal;
        text-align: center;
        padding: 12px;
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
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

    .v-theme--dark .custom-table th {
        background-color: #1e1e1e;
        color: #ffffff;
        border-color: #444;
    }

    .v-theme--dark .custom-table td {
        background-color: #2d2d2d;
        color: #ffffff;
        border-color: #444;
    }

    .v-theme--dark .custom-table tbody tr:hover {
        background-color: #3d3d3d;
    }

    .v-theme--dark .v-card-title {
        color: #ffffff;
    }

    .v-theme--dark .v-card-text {
        color: rgba(255, 255, 255, 0.87);
    }

    .v-theme--dark .text-subtitle-2 {
        color: rgba(255, 255, 255, 0.7);
    }

    .v-theme--dark .text-body-1 {
        color: rgba(255, 255, 255, 0.87);
    }

    .truncate-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        max-width: 200px;
    }
</style>