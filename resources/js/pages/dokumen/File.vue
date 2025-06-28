<script setup lang="ts">
import { Head, Link, useForm, usePage,router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/dokumen/Layout.vue';
import DataTable from '@/components/DataTable.vue'
import { h, ref, onMounted } from 'vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import axios from 'axios'

import { Skeleton } from '@/components/ui/skeleton'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'

interface File {
  id: string;
  title_file: string;
  description: string;
  name: string;
  original_name: string;
  filepath: string;
  extension: string;
  mime_type: string;
  size: number;
  user_id: string | null;
  categories_id: number;
  is_public: number;
  downloads: number;
  created_at: string | null;
  updated_at: string | null;
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
interface FileDataKlik {
  filepath: string;
  // Add other properties that you expect to be on the data object
}



// const props = defineProps<{
//   data: PaginatedResponse<Kategori>
// }>()
// const payments: Kategori[] = props.data.data;
const isDialogPreview = ref(false)
const urlPdfPreview = ref('');
const listData = ref<File[]>([])
const isLoading = ref(false)
const dataResponse = ref<PaginatedResponse<File> | null>(null)

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
     await axios.get(route('data.file'))
      .then((res) => {
          dataResponse.value = res.data.data
          listData.value = res.data.data.data
      })
      .catch((error) => {
          //jika error.response.status Check status code
      
      
      }).finally(() => {
          //selesai 
          isLoading.value = false
      });   
    // const response =  await axios.get(route('data.file'))
 
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
  // { key: 'select', label: '', type: 'select', sortable: false ,size : 250},
  { key: 'title_file', label: 'Title', type: 'string', sortable: false ,size : 250},
  { key: 'description', label: 'Deskripsi', type: 'string', sortable: false },
  { key: 'kategori', label: 'kategori', type: 'string', sortable: false },
  // { key: 'original_name', label: 'Nama File', type: 'string', sortable: false },
  { key: 'downloads', label: 'Download', type: 'string', sortable: false },
  { key: 'size', label: 'Ukuran', type: 'string', sortable: false },
]
// .map(field => ({
//   ...field,
//   sortable: field.type !== 'currency' || field.key === 'name',
// }))


const handleClickPaging = async (link: string) => {

    try {
    isLoading.value = true

    await axios.get(link)
      .then((res) => {
          dataResponse.value = res.data.data
          listData.value = res.data.data.data
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

  // const response = await axios.get(link)
}

onMounted(async () => {
  await loadData()
})

function handlePreview(data: FileDataKlik) {
  axios.get(route('data.preview.file'), {
      params: {
          path_file: data.filepath 
      }
  })
  .then((res) => {
      urlPdfPreview.value = res.data.url
      isDialogPreview.value = true
  })
  .catch((error) => {
      //jika error.response.status Check status code
  
  
  }).finally(() => {
      //selesai 
     
  });   

}
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
      listData.value = res.data.data.data
        isLoading.value = false
  })
  .catch((error) => {
      //jika error.response.status Check status code
  
  
  }).finally(() => {
      //selesai 
     
  });   
  
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Master Kategori" />
        <MasterLayout>
          
          <DataTable  v-if="!isLoading"
          :data="listData" 
          :fieldsFromDB="fieldsFromDB"
          :currentPage="dataResponse?.current_page??0"
          :totalItems="dataResponse?.total??0" 
          :perPage="dataResponse?.per_page??0"
          :paginationLinks="dataResponse?.links??[]"
          :firstPageUrl="dataResponse?.first_page_url??''"
          :lastPageUrl="dataResponse?.last_page_url??''"
          @edits="handleEdit"
          @clickPaging="handleClickPaging"
          @search="handleSearch"
          @previews="handlePreview"
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
                  <div class="flex items-center space-x-4">
                        <Skeleton class="h-12 w-12 rounded-full" />
                        <div class="space-y-2">
                          <Skeleton class="h-5 min-w-2/3" />
                          <Skeleton class="h-2 w-[500px]" />
                        </div>
                      </div>  
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

           <Dialog v-model:open="isDialogPreview">
               
                <DialogContent class="sm:max-w-[425px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh] min-h-[90vh] min-w-[80vw]">
                  <DialogHeader class="p-6 pb-0">
                    <DialogTitle>Edit profile</DialogTitle>
                    <DialogDescription>
                      Make changes to your profile here. Click save when you're done.
                    </DialogDescription>
                  </DialogHeader>
                  <div class="grid gap-4 py-2 px-4">
                   <iframe
                    :src="urlPdfPreview"
                    type="application/pdf"
                    class="w-full h-full border rounded"
                  >
                  </iframe>
                  </div>
                  <DialogFooter class="p-6 pt-0">
                    <Button @click="isDialogPreview = false">
                      Tutup
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>

        </MasterLayout>
    </AppLayout>
    
</template>
