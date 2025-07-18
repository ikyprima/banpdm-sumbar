<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/admin/LayoutFull.vue';
import DataTable from '@/components/DataTable.vue';
import { type PaginatedResponse } from '@/types';
import { h, ref, onMounted } from 'vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import axios from 'axios'

interface InterfaceListData {
    prefix: string
    name: string
    slug: string | null
    dataTypeId: string | null
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin', 
        href: '/admin/dashboards',
    },
    {
        title: 'Database Manager',
        href: '/admin/database-manager',
    }
];

const page = usePage<SharedData>();

const listData = ref<InterfaceListData[]>([])
const dataResponse = ref<PaginatedResponse<InterfaceListData> | null>(null)

const isLoading = ref(false)
const isDialogPreview = ref(false)
const fieldsFromDB = [
    { key: 'name', label: 'Name Table', type: 'string', sortable: false },
    { key: 'prefix', label: 'Prefix', type: 'string', sortable: false },
    { key: 'slug', label: 'slug', type: 'string', sortable: false },
]
const buttonDinamis = [
    {
        label: "Tambah",
        variant: "primary",
        onClick: "handleTambah"
    }
]

async function loadData(){
    try {
        isLoading.value = true

        await axios.get(route('admin.list-table'))
        .then((res) => {
            dataResponse.value = res.data
            listData.value = res.data
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


function handleTambah(val: any) {
    console.log("Tambah diklik", val);
    isDialogPreview.value = true
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
}

const handleEdit = async (id : string) => {
    console.log('Edit item selected with ID:', id)
}

const handleSearch = async (search : string) => {
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
        isLoading.value = false
    
    }).finally(() => {
        //selesai 
        isLoading.value = false
    });   
    
}

onMounted(async () => {
    await loadData()
})


</script>


<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Database Manager" />
        <MasterLayout>
            <div class="space-y-6">
                <DataTable v-if="!isLoading"
                    :data="listData" 
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
            </div>
            <Dialog v-model:open="isDialogPreview">
            
                <DialogContent class="sm:max-w-[425px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh] min-h-[90vh] min-w-[95vw]">
                    <DialogHeader class="p-6 pb-0">
                        <DialogTitle>Tambah Table Baru</DialogTitle>
                        
                        <hr class="h-0.5 my-4 bg-gray-200 border-0 dark:bg-gray-700">
                    </DialogHeader>
                    <div class="grid grid-rows-6">
                        <div class="grid grid-flow-col grid-rows-3 gap-4 px-6">
                            <div class="row-span-3">
                                <Label class="py-2" for="email">Name Table</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="username"
                                    placeholder="Email address"
                                />
                            </div>
                            <div class="col-span-2"></div>
                            <div class="col-span-2 row-span-2 " >
                                <Button class="mx-2 shadow-xl/20"> Add Column</Button>
                                <Button class="mx-2 shadow-xl/20"> Add Timestamp </Button>
                                <Button class="mx-2 shadow-xl/20"> Add Softdelete </Button>
                            
                            </div>
                        </div>
                    
                        <div class="row-span-5 px-6">
                            <div class="rounded-md border border-gray-300 overflow-x-auto">
                                <table class="min-w-full border-collapse text-left">
                                <thead class="bg-gray-100 border-b">
                                    <tr>
                                    <th class="px-4 py-2 border-r">Name</th>
                                    <th class="px-4 py-2 border-r">Type</th>
                                    <th class="px-4 py-2 border-r">Length</th>
                                    <th class="px-4 py-2 border-r">Not Null</th>
                                    <th class="px-4 py-2 border-r">Unsigned</th>
                                    <th class="px-4 py-2 border-r">Auto Increment</th>
                                    <th class="px-4 py-2 border-r">Index</th>
                                    <th class="px-4 py-2 border-r">Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- data rows -->
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr class="h-0.5 my-2 mx-4 bg-gray-200 border-0 dark:bg-gray-700">   
                    <DialogFooter class="p-6 pt-0">
                        
                        <Button @click="isDialogPreview = false">
                            Simpan
                        </Button>
                        <Button class="bg-red-600 hover:bg-amber-600" @click="isDialogPreview = false">
                            Batal
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </MasterLayout>
    </AppLayout>
</template>
