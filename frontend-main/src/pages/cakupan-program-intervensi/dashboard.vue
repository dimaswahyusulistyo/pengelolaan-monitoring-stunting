<template>
  <VRow>
    <VCol cols="12">
      <VCard elevation="1" class="rounded-lg">
                <VCardItem>
                    <VCardTitle class="text-h5 font-weight-medium text-primary">Dashboard Capaian Cakupan Program Intervensi Percepatan Penurunan Stunting</VCardTitle>
                    <template #append>
                        <VChip color="primary" variant="tonal" size="small">{{ selectedTahun || 'Semua Tahun' }}</VChip>
                    </template>
                </VCardItem>

                <!-- Filters -->
                <VCardText>
                  <VRow>
                    <VCol cols="12" sm="4">
                      <VSelect
                        v-model="selectedKecamatan"
                        :items="kecamatanOptions"
                        label="Kecamatan"
                        hide-details="auto"
                        variant="outlined"
                        density="comfortable"
                        bg-color="surface"
                        prepend-inner-icon="bx-map"
                        clearable
                        @update:modelValue="handleKecamatanChange"
                        :loading="loading"
                        style="height: 40px;"
                      ></VSelect>
                    </VCol>

                    <VCol cols="12" sm="4">
                      <VMenu 
                        v-model="desaMenuOpen"
                        ref="desaMenuRef"
                        :close-on-content-click="false"
                        offset-y
                      >
                        <template v-slot:activator="{ props }">
                          <VBtn
                            v-bind="props"
                            color="primary"
                            variant="outlined"
                            size="small"
                            class="me-1 filter-btn"
                            style="position: relative; min-width: 100%; height: 40px; display: flex; justify-content: space-between;"
                          >
                            <div class="d-flex align-center">
                              <VIcon size="18" class="me-1">bx-home</VIcon>
                              <span class="text-truncate" style="max-width: 120px;">
                                {{ getDesaFilterLabel() }}
                              </span>
                            </div>
                            
                            <VIcon 
                              v-if="selectedDesa"
                              size="16"
                              color="error"
                              style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);"
                              @click.stop="resetDesaFilter"
                            >
                              bx-x
                            </VIcon>
                          </VBtn>
                        </template>
                        
                        <VList width="300">
                          <VListItem class="pa-2">
                            <VTextField
                              v-model="desaSearchQuery"
                              placeholder="Cari desa..."
                              density="compact"
                              hide-details
                              clearable
                              prepend-inner-icon="bx-search"
                              @click.stop
                              @input="filterDesaList"
                            />
                          </VListItem>
                          
                          <VDivider />

                          <div style="max-height: 300px; overflow-y: auto;">
                            <VListItem
                              v-for="desa in filteredDesaList"
                              :key="desa.id_desa"
                              @click="selectDesa(desa)"
                              :active="selectedDesa?.id_desa === desa.id_desa"
                            >
                              <VListItemTitle>
                                <div class="d-flex flex-column">
                                  <span class="font-weight-medium">{{ desa.nama_desa }}</span>
                                  <span class="text-caption text-secondary">
                                    {{ desa.kecamatan }} - {{ desa.puskesmas }}
                                  </span>
                                </div>
                              </VListItemTitle>
                            </VListItem>
                            
                            <VListItem v-if="filteredDesaList.length === 0">
                              <VListItemTitle class="text-center text-secondary pa-4">
                                {{ desaSearchQuery ? 'Desa tidak ditemukan' : 'Memuat data...' }}
                              </VListItemTitle>
                            </VListItem>
                          </div>

                          <VDivider />
                          <VListItem class="d-flex justify-end pa-2 gap-2">
                            <VBtn
                              size="small"
                              variant="text"
                              @click="resetDesaFilter"
                              density="comfortable"
                            >Reset</VBtn>
                            <VBtn
                              size="small"
                              color="primary"
                              @click="desaMenuOpen = false"
                              density="comfortable"
                            >Tutup</VBtn>
                          </VListItem>
                        </VList>
                      </VMenu>
                    </VCol>

                    <VCol cols="12" sm="4">
                      <VSelect
                        v-model="selectedTahun"
                        :items="tahunOptions"
                        label="Tahun"
                        hide-details="auto"
                        variant="outlined"
                        density="comfortable"
                        bg-color="surface"
                        prepend-inner-icon="bx-calendar"
                        clearable
                        :loading="loading"
                        style="height: 40px;"
                      ></VSelect>
                    </VCol>
                  </VRow>

                  <VRow class="mt-2">
                    <VCol cols="12">
                      <VBtn
                        color="primary"
                        block
                        @click="filterData"
                        prepend-icon="mdi-filter"
                        variant="flat"
                        :loading="loading"
                        :disabled="loading"
                      >
                        Terapkan Filter
                      </VBtn>
                    </VCol>
                  </VRow>
                  
                  <VSnackbar
                    v-model="error"
                    :timeout="3000"
                    color="error"
                  >
                    {{ error }}
                    <template v-slot:actions>
                      <VBtn
                        color="white"
                        variant="text"
                        @click="error = null"
                      >
                        Tutup
                      </VBtn>
                    </template>
                  </VSnackbar>
                </VCardText>
            </VCard>
    </VCol>

    <!-- Loading dan Error State -->
    <VCol cols="12" v-if="loading">
      <VProgressCircular indeterminate color="primary"></VProgressCircular>
    </VCol>
    <VCol cols="12" v-else-if="error">
      <VAlert type="error">{{ error }}</VAlert>
    </VCol>

    <!-- Statistik -->
    <VCol cols="12" v-else>
      <!-- REMAJA -->
      <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> REMAJA </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Remaja putri yang mengonsumsi Tablet Tambah Darah (TTD)']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Remaja putri yang menerima layanan pemeriksaan status anemia (hemoglobin)']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- CALON PENGANTIN/PASANGAN USIA SUBUR -->
      <VCard class="pa-4 mb-4" color="pink-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-pink-darken-3"> CALON PENGANTIN/PASANGAN USIA SUBUR </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Calon pengantin/calon ibu yang menerima Tablet Tambah Darah (TTD)</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Calon pengantin /calon ibu yang menerima Tablet Tambah Darah (TTD)']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Calon pasangan usia subur (PUS) yang memperoleh pemeriksaan kesehatan sebagai bagian dari pelayanan nikah']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Cakupan calon Pasangan Usia Subur (PUS) yang menerima pendampingan kesehatan reproduksi dan edukasi gizi sejak 3 bulan pranikah']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Pasangan calon pengantin yang mendapatkan bimbingan perkawinan dengan materi pencegahan stunting']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan tunai bersyarat']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Cakupan Pasangan Usia Subur (PUS) dengan status miskin dan penyandang masalah kesejahteraan sosial yang menerima bantuan pangan nontunai']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Cakupan Pasangan Usia Subur (PUS) fakir miskin dan orang tidak mampu yang menjadi Penerima Bantuan Iuran (PBI) Jaminan Kesehatan']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

       <!-- IBU HAMIL -->
       <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> IBU HAMIL </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Ibu hamil Kurang Energi Kronik (KEK) yang mendapatkan tambahan asupan gizi']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Ibu hamil yang mengonsumsi Tablet Tambah Darah (TTD) minimal 90 tablet selama masa kehamilan']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Persentase Unmeet Need pelayanan keluarga berencana</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Persentase Unmeet Need pelayanan keluarga berencana']?.rata_rata || 0, true)" 
                       :style="`width: ${dataCakupan['Persentase Unmeet Need pelayanan keluarga berencana']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Persentase Unmeet Need pelayanan keluarga berencana']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Persentase Kehamilan yang tidak diinginkan</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Persentase Kehamilan yang tidak diinginkan']?.rata_rata || 0, true)" 
                       :style="`width: ${dataCakupan['Persentase Kehamilan yang tidak diinginkan']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Persentase Kehamilan yang tidak diinginkan']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- BALITA -->
      <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> BALITA </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Bayi usia kurang dari 6 bulan mendapat air susu ibu (ASI) eksklusif']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Anak usia 6-23 bulan yang mendapat Makanan Pendamping Air Susu Ibu (MP-ASI)']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Anak berusia di bawah lima tahun (balita) gizi buruk yang mendapat pelayanan tata laksana gizi buruk']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Anak berusia di bawah lima tahun (balita) yang dipantau pertumbuhan dan perkembangannya</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Anak berusia di bawah lima tahun (balita)  yang dipantau pertumbuhan dan perkembangannya']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Anak berusia di bawah lima tahun (balita) gizi kurang yang mendapat tambahan asupan gizi']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Balita yang memperoleh imunisasi dasar lengkap</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Balita yang memperoleh imunisasi dasar lengkap']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Balita yang memperoleh imunisasi dasar lengkap']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Balita yang memperoleh imunisasi dasar lengkap']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- KELUARGA BERISIKO -->
      <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> KELUARGA BERISIKO </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga yang Stop BABS</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga yang Stop BABS']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga yang Stop BABS']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga yang Stop BABS']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga yang melaksanakan PHBS</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga yang melaksanakan PHBS']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga yang melaksanakan PHBS']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga yang melaksanakan PHBS']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga berisiko stunting yang mendapatkan promosi peningkatan konsumsi ikan dalam negeri']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Pelayanan Keluarga Berencana (KB) pascapersalinan</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Pelayanan Keluarga Berencana (KB) pascapersalinan']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Pelayanan Keluarga Berencana (KB) pascapersalinan']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Pelayanan Keluarga Berencana (KB) pascapersalinan']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga berisiko stunting yang memperoleh pendampingan</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga berisiko stunting yang memperoleh pendampingan']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga berisiko stunting yang memperoleh pendampingan']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga berisiko stunting yang memperoleh pendampingan']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga berisiko stunting yang mendapatkan manfaat sumber daya pekarangan untuk peningkatan asupan gizi']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- AIR MINUM DAN SANITASI -->
      <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> AIR MINUM DAN SANITASI </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Rumah tangga yang mendapatkan akses air minum layak</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Rumah tangga yang mendapatkan akses air minum layak']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Rumah tangga yang mendapatkan akses air minum layak']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Rumah tangga yang mendapatkan akses air minum layak']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Rumah tangga yang mendapatkan akses sanitasi (air limbah domestik) layak']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- PERLINDUNGAN SOSIAL -->
      <VCard class="pa-4 mb-4" color="teal-lighten-5">
        <VCardTitle class="text-h6">
          <span class="text-green-darken-2"> PERLINDUNGAN SOSIAL </span>
        </VCardTitle>
        <VCardText>
          <VRow class="align-center">
            <VCol cols="12" md="6" class="stat-item">
              <p>Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Kelompok Keluarga Penerima Manfaat (KPM) Program Keluarga Harapan (PKH) yang mengikuti Pertemuan Peningkatan Kemampuan Keluarga (P2K2) dengan modul kesehatan dan gizi']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12" md="6" class="stat-item">
              <p>Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur</p>
              <div class="custom-progress">
                <div class="progress-container">
                  <div class="progress-bar" :class="getColorClass(dataCakupan['Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur']?.rata_rata || 0)" 
                       :style="`width: ${dataCakupan['Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur']?.rata_rata || 0}%`">
                    <strong>{{ dataCakupan['Keluarga Penerima Manfaat (KPM) dengan ibu hamil, ibu menyusui, dan baduta yang menerima variasi bantuan pangan selain beras dan telur']?.rata_rata || 0 }}%</strong>
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import apiClient from '@/services/api';

const selectedKecamatan = ref(null);
const selectedDesa = ref(null);
const selectedTahun = ref(null);
const kecamatanOptions = ref([]);
const tahunOptions = ref([]);
const availableDesas = ref([]);
const filteredDesaList = ref([]);
const desaSearchQuery = ref('');
const loading = ref(false);
const error = ref(null);
const dashboardData = ref([]);
const desaMenuRef = ref(null);
const desaMenuOpen = ref(false);


onMounted(async () => {
  await fetchKecamatan();
  await fetchTahun();
  await fetchAllDesa();
  const currentYear = new Date().getFullYear();
  if (tahunOptions.value.includes(currentYear.toString())) {
    selectedTahun.value = currentYear.toString();
  } else if (tahunOptions.value.length > 0) {
    selectedTahun.value = tahunOptions.value[0];
  }
  await fetchData();
});

const fetchKecamatan = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/kecamatan');

    kecamatanOptions.value = ['Semua', ...new Set(
      response.data.data
        .map(item => item.kecamatan)
        .filter(kecamatan => kecamatan !== null)
    )];
  } catch (error) {
    console.error('Error fetching kecamatan options:', error);
  } finally {
    loading.value = false;
  }
};

const fetchAllDesa = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/desa');
    if (response.data && response.data.data) {
      availableDesas.value = response.data.data.map(item => ({
        id_desa: item.id,
        nama_desa: item.desa,
        kecamatan: item.kecamatan,
        puskesmas: item.puskesmas
      }));
      filteredDesaList.value = [...availableDesas.value];
    }
  } catch (err) {
    console.error('Error fetching all desa:', err);
    error.value = 'Gagal memuat data desa';
  } finally {
    loading.value = false;
  }
};

const fetchTahun = async () => {
  try {
    loading.value = true;
    const response = await apiClient.get('/tahun');
    if (response.data && response.data.data) {
      tahunOptions.value = response.data.data.map(item => item.tahun.toString());
    } else {
      const currentYear = new Date().getFullYear();
      tahunOptions.value = Array.from({length: 5}, (_, i) => (currentYear - i).toString());
    }
  } catch (err) {
    console.error('Error fetching tahun:', err);
    const currentYear = new Date().getFullYear();
    tahunOptions.value = Array.from({length: 5}, (_, i) => (currentYear - i).toString());
  } finally {
    loading.value = false;
  }
};

watch(selectedKecamatan, (newVal) => {
  if (newVal) {
    filterAvailableDesa();
  } else {
    filteredDesaList.value = [...availableDesas.value];
  }
});

watch(selectedDesa, (newDesa) => {
  if (newDesa) {
    selectedKecamatan.value = newDesa.kecamatan;
  }
});

const filterAvailableDesa = () => {
  let filteredDesas = [...availableDesas.value];
  
  if (selectedKecamatan.value && selectedKecamatan.value !== 'Semua') {
    filteredDesas = filteredDesas.filter(desa => 
      desa.kecamatan === selectedKecamatan.value
    );
  }
  
  if (desaSearchQuery.value) {
    const searchTerm = desaSearchQuery.value.toLowerCase();
    filteredDesas = filteredDesas.filter(desa => {
      const namaDesaLower = (desa.nama_desa || '').toLowerCase();
      const kecamatanLower = (desa.kecamatan || '').toLowerCase();
      const puskesmasLower = (desa.puskesmas || '').toLowerCase();
      
      return namaDesaLower.includes(searchTerm) || 
             kecamatanLower.includes(searchTerm) || 
             puskesmasLower.includes(searchTerm);
    });
  }
  
  filteredDesaList.value = filteredDesas;
};


const handleKecamatanChange = () => {
  if (selectedDesa.value) {
    if (selectedKecamatan.value && selectedKecamatan.value !== 'Semua') {
      const desa = availableDesas.value.find(d => 
        d.desa === selectedDesa.value.nama_desa && 
        d.kecamatan === selectedKecamatan.value
      );
      
      if (!desa) {
        selectedDesa.value = null;
        isUsingDefaultDesaFilter.value = false;
      }
    }
  }
  
  filterAvailableDesa();
};

const getDesaFilterLabel = () => {
  if (selectedDesa.value) return selectedDesa.value.nama_desa;
  return 'Semua Desa';
};


const filterDesaList = () => {
  filterAvailableDesa();
};

const selectDesa = (desa) => {
  selectedDesa.value = desa;
  desaMenuOpen.value = false;
};


const resetDesaFilter = () => {
  selectedDesa.value = null;
  desaSearchQuery.value = '';
  filterAvailableDesa();
};

const dataCakupan = computed(() => {
  const map = {};
  dashboardData.value.forEach(item => {
    map[item.indikator] = item;
  });
  return map;
});


const filterData = async () => {
  await fetchData();
};


const fetchData = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const params = new URLSearchParams();
    if (selectedKecamatan.value && selectedKecamatan.value !== 'Semua') {
      params.append('kecamatan', selectedKecamatan.value);
    }
    if (selectedDesa.value) {
      params.append('desa', selectedDesa.value.nama_desa);
    }
    if (selectedTahun.value) {
      params.append('tahun', selectedTahun.value);
    }


    const response = await apiClient.get(`/dashboard/cakupan-program?${params.toString()}`);
    
    if (response.data && response.data.data) {
      dashboardData.value = response.data.data;
    } else {
      dashboardData.value = [];
      error.value = 'Format data tidak valid';
    }
  } catch (err) {
    console.error('Error fetching dashboard data:', err);
    error.value = 'Gagal memuat data dashboard';
    dashboardData.value = [];
  } finally {
    loading.value = false;
  }
};


const getColorClass = (percentage, isNegative = false) => {
  if (isNegative) {
    if (percentage < 10) return 'bg-green';
    if (percentage < 20) return 'bg-yellow';
    if (percentage < 40) return 'bg-orange';
    return 'bg-red';
  } else {
    if (percentage >= 90) return 'bg-green';
    if (percentage >= 80) return 'bg-yellow';
    if (percentage >= 60) return 'bg-orange';
    return 'bg-red';
  }
};

</script>

<style scoped>
.custom-progress {
  flex-grow: 1;
  display: flex;
  align-items: flex-end;
}

.progress-container {
  width: 100%;
  background-color: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
  height: 24px; 
}

.progress-bar {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  transition: width 0.5s ease;
}

.bg-green {
  background-color: #4CAF50;
}
.bg-yellow {
  background-color: #FFC107;
}
.bg-orange {
  background-color: #FF9800;
}
.bg-red {
  background-color: #F44336;
}

.stat-item {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  height: auto; 
  min-height: 130px; 
  margin-bottom: 16px; 
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
}

.stat-item p {
  margin-bottom: 6px;
  line-height: 1.3;
  font-size: 14px;
  color: #555;
}

@media (max-width: 600px) {
  .stat-item {
    min-height: unset;
    height: auto;
    margin-bottom: 20px;
  }

  .progress-container {
    height: 20px;
  }

  .progress-bar {
    font-size: 12px;
  }
}
</style>
