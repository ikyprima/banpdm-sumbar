<script setup lang="ts">
import { Head, Link, useForm, usePage,router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/banpdm/Layout.vue';
import DataTable from '@/components/DataTable.vue'
import { h, ref, onMounted } from 'vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Skeleton } from '@/components/ui/skeleton'

import axios from 'axios'

export interface Asesor {
  id: number;
  nomor_induk: string;
  nama: string;
  email: string;
  no_hp: string;
  alamat: string;
  latitude: string;
  longitude: string;
  instansi: string | null;
  foto: string | null;
  id_wilayah: string;
  created_at: string | null;
  updated_at: string | null;
  deleted_at: string | null;
}

export interface DetailPenugasan {
  id: number;
  penugasan_id: number;
  sekolah_id: number;
  status: string;
  keterangan: string | null;
  tanggal_check_in: string | null;
  latitude_check_in: string | null;
  longitude_check_in: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface Penugasan {
  id: number;
  asesor_id: number;
  tanggal_penugasan: string;
  tanggal_penugasan_selesai: string;
  keterangan: string | null;
  status: string;
  created_at: string | null;
  updated_at: string | null;
  latitude_awal: string;
  longitude_akhir: string;
  tahun: string;
  asesor: Asesor;
  detail: DetailPenugasan[];
}

interface PaginationLinks {
  url: string | null
  label: string
  active: boolean
}

interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: PaginationLinks[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}



// const props = defineProps<{
//   data: PaginatedResponse<Kategori>
// }>()
// const payments: Kategori[] = props.data.data;

const listPenugasan = ref<Penugasan[]>([])

const dataResponse = ref<PaginatedResponse<Penugasan> | null>(null)
const isLoading = ref(false)


// const loadDataa = async () => {
//   try {
//     const response = await axios.get(route('data.kategori'))
//     listKategori.value = response.data.data
//     dataResponse.value = response.data
//   } catch (error) {
//     console.error('Gagal load data:', error)
//   }
// }

async function loadData(){
  try {
    isLoading.value = true

    await axios.get(route('get.data.penugasan'))
      .then((res) => {
          dataResponse.value = res.data.data
          listPenugasan.value = res.data.data.data

          listPenugasan.value = res.data.data.data.map((item : Penugasan)   => ({
            ...item,
            'tanggal_': item.tanggal_penugasan + ' s/d ' + item.tanggal_penugasan_selesai,
            'jumlah_lokasi': item.detail.length + ' Lokasi',          
            // 'asesor.email': item.asesor?.email ?? '',
          }));
      })
      .catch((error) => {
          //jika error.response.status Check status code
      
      
      }).finally(() => {
          //selesai 
          isLoading.value = false
      });   
  } catch (error) {
    console.error('Gagal load data:', error)
  }

}
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dokumen',
        href: '/dokumen',
    },
    {
        title: 'Kategori',
        href: '/dokumen/kategori',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const fieldsFromDB = [
  { key: 'tanggal_', label: 'Tanggal Penugasan', type: 'string', sortable: false },
  { key: 'asesor.nama', label: 'Nama Asesor', type: 'string', sortable: false },
  { key: 'asesor.nomor_induk', label: 'Nomor Induk', type: 'string', sortable: false },
  { key: 'jarak', label: 'Jarak (km)', type: 'string', sortable: false },
  { key: 'waktu', label: 'waktu', type: 'string', sortable: false },
  { key: 'jumlah_lokasi', label: 'Jumlah Lokasi', type: 'string', sortable: false },

  
]
// .map(field => ({
//   ...field,
//   sortable: field.type !== 'currency' || field.key === 'name',
// }))
const buttonDinamis = [
    {
        label: "Tambah",
        variant: "primary",
        onClick: "handleTambah"
    }
]

function handleTambah(val: any) {
    console.log("Tambah diklik", val);
      router.visit('/banpdm/penugasan/create') // atau route name seperti '/dashboard'

    // isDialogPreview.value = true
}


const handlers: Record<string, (val: any) => void> = {
    handleTambah,
};

function klikMethod(value: { action: string; value: any }) {
    const methodName = value.action;
    if (handlers[methodName]) {
        handlers[methodName](value.value);
    } else {
        console.warn(`Method ${methodName} tidak ditemukan`);
    }
}

const handleClickPaging = async (link: string) => {

    try {
    isLoading.value = true

    await axios.get(link)
      .then((res) => {
          dataResponse.value = res.data.data
          listPenugasan.value = res.data.data.data
      })
      .catch((error) => {
          //jika error.response.status Check status code
      
      
      }).finally(() => {
          //selesai 
          isLoading.value = false
      });   

  } catch (error) {
    console.error('Gagal load data:', error)
  }
}

onMounted(async () => {
  await loadData()
  
})

function handleEdit(id: string) {
  console.log('Edit item selected with ID:', id)
}


function handleSearch(search: string) {
  let url = dataResponse.value?.path??'';
  isLoading.value = true
  axios.get(url, {
      params: {
          search: search 
      }
  })
  .then((res) => {
      dataResponse.value = res.data.data
      listPenugasan.value = res.data.data.data
      isLoading.value = false
  })
  .catch((error) => {
      //jika error.response.status Check status code
      isLoading.value = false
  
  }).finally(() => {
      //selesai 
      isLoading.value = false
  });   
  
}
</script>


<template>
 
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Master Kategori" />
        <MasterLayout>
          <DataTable v-if="!isLoading"
          :data="listPenugasan" 
          :fieldsFromDB="fieldsFromDB"
          :currentPage="dataResponse?.current_page??0"
          :totalItems="dataResponse?.total??0" 
          :perPage="dataResponse?.per_page??0"
          :paginationLinks="dataResponse?.links??[]"
          :firstPageUrl="dataResponse?.first_page_url??''"
          :lastPageUrl="dataResponse?.last_page_url??''"
          :buttonDinamis="buttonDinamis"
          @edits="handleEdit"
          @clickPaging="handleClickPaging"
          @search="handleSearch"
          @button-click="klikMethod"
          />
            
            <div v-if="isLoading" class="overflow-x-auto w-full border rounded-md">
                <table class="w-full table-auto border-collapse">
                  <thead>
                    <tr class="border-b">
                      <th v-for="n in 4" :key="n" class="px-4 py-2 text-left">
                        <Skeleton class="h-4 w-24" />
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in 8" :key="row" class="border-b">
                      <td>
                        <div class="flex items-center space-x-4">
                          <Skeleton class="h-12 w-12 rounded-full" />
                          <div class="space-y-2">
                            <Skeleton class="h-5 min-w-2/3" />
                            <Skeleton class="h-2 w-[500px]" />
                          </div>
                        </div>
                      </td>
                    </tr>

                  </tbody>
                  <tfoot>
                    <tr class="border-b ">
                      <th v-for="n in 4" :key="n" class="px-4 py-2 text-left">
                        <Skeleton class="h-4 w-24" />
                      </th>
                    </tr>
                  </tfoot>
                </table>
                
              </div>
                
        </MasterLayout>
    </AppLayout>
</template>
