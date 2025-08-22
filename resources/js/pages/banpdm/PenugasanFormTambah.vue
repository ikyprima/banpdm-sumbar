<script setup lang="ts">
import { Head, Link, usePage,router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/banpdm/Layout.vue';
import { h, ref,Ref,onMounted,watch,computed,nextTick   } from 'vue'
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import type { DateRange } from "reka-ui"
import { Textarea } from "@/components/ui/textarea"
import { vAutoAnimate } from "@formkit/auto-animate/vue"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm,useFieldArray } from "vee-validate"
import * as z from "zod"
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import debounce from 'lodash/debounce'
import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"

import { Check, Search,ChevronsUpDown } from "lucide-vue-next"

import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from "@/components/ui/combobox"

import { Input } from "@/components/ui/input"
import { toastStatus } from '@/lib/toastStatus'

import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"



import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
} from "@internationalized/date"
import { CalendarIcon } from "lucide-vue-next"
import { cn } from "@/lib/utils"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { RangeCalendar } from "@/components/ui/range-calendar"
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import Label from '@/components/ui/label/Label.vue';
import L, { point } from 'leaflet' 
import 'leaflet/dist/leaflet.css'
import "leaflet-routing-machine";
import "leaflet-routing-machine/dist/leaflet-routing-machine.css";
import peopleIconUrl from '../../images/icons/people-nearby-svgrepo-com.svg'
import schoolIconUrl from '../../images/icons/school-svgrepo-com.svg'

let map: L.Map | null = null
type Sekolah = {
  id: number
  npsn: string
  nama: string
  alamat: string,
  latitude: string;
  longitude: string;
  wilayah: Wilayah
  // wilayah: { 
  //   nama: string,
  //   lat : string,
  //   lng : string,
  //   path: string 
  // }
}

interface Wilayah {
  kode: string;
  nama: string;
  lat : string,
  lng : string,
  path: number[][] | number[][][] 
}

interface Asesor {
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
  wilayah: Wilayah;
}

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const items = ref<Sekolah[]>([])
const sekolahList = ref<Sekolah[]>([])
const asesorList = ref<Asesor[]>([])
const selected = ref<Sekolah | null>(null)
const selectedAsesor = ref<Asesor | null>(null)
const searchTerm = ref("") 
const searchAsesor = ref("") 
const keterangan = ref("") 
const titleMap = ref("") 
const descriptionMap = ref("") 
const loading = ref(false)
const openUbahKordinat = ref(false)
const isDialogKonfirmasi = ref(false)
const asesorMarkers = ref<L.Marker[]>([])

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


const df = new DateFormatter("id-ID", {
  dateStyle: "medium",
})
let drawnPolygons: L.Polygon[] = [] 
interface Coordinate {
  lat: number;
  lng: number;
}
interface routeInfo {
  jarak: string;
  waktu: string;
}
const closeModalKonfirmasi = () => {
      inertiaForm.reset();
      rute.value = {
        jarak: '',
        waktu: ''
      };
      items.value = [];
      waypoints.value = [];
      resetForm();
};
// hitung jarak Euclidean (cukup untuk sorting kasar)
// kalau mau lebih presisi pakai haversine formula
function distance(a: Coordinate, b: Coordinate): number {
  const dx = a.lat - b.lat;
  const dy = a.lng - b.lng;
  return Math.sqrt(dx * dx + dy * dy);
}

function sortWaypoints(start: Coordinate, points: Coordinate[]): Coordinate[] {
  const sorted: Coordinate[] = [];
  const remaining = [...points];

  let current = start;
  while (remaining.length > 0) {
    // cari titik terdekat dari current
    let nearestIndex = 0;
    let nearestDist = distance(current, remaining[0]);

    for (let i = 1; i < remaining.length; i++) {
      const d = distance(current, remaining[i]);
      if (d < nearestDist) {
        nearestDist = d;
        nearestIndex = i;
      }
    }

    const next = remaining.splice(nearestIndex, 1)[0];
    sorted.push(next);
    current = next;
  }
  return sorted;
}
const waypoints = ref<Coordinate[]>([]);

const rute = ref<routeInfo>({
  jarak: '',
  waktu: ''
});
let routingControl: L.Routing.Control;
function openUbahKordinatModal(val:string) {

  if (val === "lihatRute") {
    if (waypoints.value.length < 1) {
      toastStatus(
        'danger',
        'Tidak Ada Tujuan',
        'Silahkan pilih pilih tujuan terlebih dahulu (minimal 1 tujuan)',
        4000,
      )
      return;
    }
    titleMap.value = "Lihat Rute"
    descriptionMap.value = "Silahkan lihat rute yang telah disarankan, kemudian simpan rute"
  }else{
    titleMap.value = "Ubah Titik Kordinat Awal"
    descriptionMap.value = "Silahka klik di peta untuk ubah titik kordinat awal asesor"
  }
   
  if (inertiaForm.id) {
      openUbahKordinat.value = !openUbahKordinat.value
      if (openUbahKordinat.value) {
          
        nextTick(() => {
          drawnPolygons.forEach(p => map?.removeLayer(p))
          drawnPolygons = []
          
          map = L.map('map').setView([parseFloat(inertiaForm.lat), parseFloat(inertiaForm.ltd)] , 12)
        
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
          }).addTo(map)
        
           // Decode path dari JSON string
                const pathData = typeof selectedAsesor.value?.wilayah.path === "string"
                  ? JSON.parse(selectedAsesor.value?.wilayah.path)
                  : selectedAsesor.value?.wilayah.path

                const polygonList = Array.isArray(pathData[0][0])
                  ? pathData as number[][][]
                  : [pathData as number[][]]

                const allBounds = L.latLngBounds([])

                polygonList.forEach(polygonCoords => {
                  const latlngs = polygonCoords.map(([lat, lng]) => L.latLng(lat, lng))
                  const polygon = L.polygon(latlngs, { color: 'yellow' }).addTo(map!)
                  drawnPolygons.push(polygon)
                  allBounds.extend(polygon.getBounds())
                })

                const asesorIcon = L.icon({
                  iconUrl: peopleIconUrl, // hasil import
                  iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -30]
                })

              
                const lat = parseFloat(selectedAsesor.value?selectedAsesor.value.latitude.toString():"")
                const lng = parseFloat(selectedAsesor.value?selectedAsesor.value.longitude.toString():"")
                if (!isNaN(lat) && !isNaN(lng)) {
                    if(val === "lihatRute"){
                      routingControl = L.Routing.control({
                          waypoints: [
                            L.latLng(lat, lng), // titik awal
                          ],
                          routeWhileDragging: true,
                          addWaypoints: false,
                          
                          // draggableWaypoints: true,
                          createMarker: (i : number, wp : any, nWps : any) => {
                              return L.marker(wp.latLng, {
                                icon: L.icon({
                                  iconUrl: i === 0 ?peopleIconUrl: schoolIconUrl,
                                  iconSize: [32, 32],
                                  iconAnchor: [16, 32],
                                }),
                              });
                            },
                        } as any).addTo(map);
                        const startPoint = { lat: lat, lng: lng };
                        const ordered = sortWaypoints(startPoint, waypoints.value);

                        routingControl.setWaypoints([
                          L.latLng(lat, lng),
                          ...ordered.map(p => L.latLng(p.lat, p.lng))
                        ]);

                        routingControl.on("routesfound", function (e: any) {
                          const route = e.routes[0]; // ambil rute pertama
                          const summary = route.summary;
                          // Konversi jarak ke km
                          const jarakKm = (summary.totalDistance / 1000).toFixed(2);

                          // Konversi waktu ke jam & menit
                          const totalMinutes = Math.floor(summary.totalTime / 60);
                          const hours = Math.floor(totalMinutes / 60);
                          const minutes = totalMinutes % 60;
                          let waktuText = "";
                            if (hours > 0) {
                              waktuText = `${hours} jam ${minutes} menit`;
                            } else {
                              waktuText = `${minutes} menit`;
                            }
                          // jarak (meter), waktu (detik)
                          rute.value = {
                            jarak: jarakKm,
                            waktu: waktuText,
                          };
                          toastStatus(
                            "success",
                            "Rute Ditemukan",
                            `Jarak: ${jarakKm} km, Waktu: ${waktuText}`,
                          )
                          // simpan waypoint terbaru
                          // waypoints.value = routingControl.getWaypoints().map((w: any) => ({
                          //   lat: w.latLng.lat,
                          //   lng: w.latLng.lng,
                          // }));
                          // console.log(`Jarak: ${jarakKm} km`);
                          // console.log(`Waktu: ${waktuText}`);

                          // console.log("Waypoints:", waypoints.value);
                        });

                        //jika waywoints diubah
                        // routingControl.on("waypointschanged", () => {
                        //   const updated = routingControl.getWaypoints().map(wp => ({
                        //     lat: wp.latLng.lat,
                        //     lng: wp.latLng.lng,
                        //   }));
                        //   waypoints.value = updated;
                        // });
                    }else{
                       const marker = L.marker([lat, lng], { icon: asesorIcon })
                          .addTo(map!)
                          .bindPopup(`
                          <b>${selectedAsesor.value?.nama}</b><br>
                          ${selectedAsesor.value?.nomor_induk}<br>
                          ${selectedAsesor.value?.alamat}<br>
                          `)
                      map.on("click", (e: L.LeafletMouseEvent) => {
                      
                      const { lat: clickedLat, lng: clickedLng } = e.latlng;

                      // pindahkan marker
                      marker.setLatLng([clickedLat, clickedLng]);
                      // simpan koordinat ke state
                      selectedAsesor.value?selectedAsesor.value.latitude = clickedLat.toString():"";
                      selectedAsesor.value?selectedAsesor.value.longitude = clickedLng.toString():"";
                      setFieldValue('titik_awal', clickedLat + ", " + clickedLng)
                    });
                    }
                   
                }
                if (allBounds.isValid()) {
                  map?.fitBounds(allBounds, { padding: [20, 20] })
                }
         
        
        })
      }
  }else{
  
    toastStatus(
      'danger',
      'Asesor Belum di Pilih',
      'Silahkan pilih asesor terlebih dahulu',
      2000,
    )

  }

}

function getTodayCalendarDate() {
  const now = new Date()
  return new CalendarDate(
    now.getFullYear(),
    now.getMonth() + 1, // karena bulan di JS mulai dari 0
    now.getDate()
  )
}

const today = getTodayCalendarDate()

const tanggal = ref({
  start: today,
  end: today.add({ days: 0 })
}) as Ref<DateRange>
  
  
// const formTanggal = computed(() => ({
//   start: tanggal.value.start.toString(), // "2025-08-21"
//   end: tanggal.value.end.toString()      // "2025-08-25"
// }))

const formSchema = toTypedSchema(
  z.object({
    nomorInduk: z.string(),
    namaAsesor: z.string(),
    titik_awal: z.string(),
    
    sekolahListValid: z.array(
      z.object({
        id: z.number(),
        npsn: z.string(),
        nama: z.string(),
        alamat: z.string(),
        wilayah: z.object({
          nama: z.string()
        })
      })
    ).min(1, "Minimal pilih 1 tujuan")
  })
)

const { isFieldDirty, handleSubmit,setFieldValue,setFieldError,validateField,resetForm  } = useForm({
  validationSchema: formSchema,
  initialValues: {
    sekolahListValid: []
  }
})

function removeItem(index: number) {
  items.value.splice(index, 1)
  waypoints.value.splice(index, 1)
  const sekolahListValid = items.value.map(item => ({
    id: item.id,
    npsn: item.npsn,
    nama: item.nama,
    alamat: item.alamat,
    wilayah: item.wilayah
  }))
    setFieldValue("sekolahListValid", sekolahListValid)
    validateField("sekolahListValid") 
  
}

const inertiaForm = useInertiaForm<{
  id: number | string,
  lat: string,
  ltd: string
}>({
    id : '',
    lat : '',
    ltd : ''
})

const onSubmit = handleSubmit((values) => {
  console.log(values);
  inertiaForm
    .transform((fieldsLama) => {
      let tujuansekolah =  [] as { id: number; npsn: string; nama: string }[];
      items.value.map((item) => {
        const sekolah = {
          id: item.id,
          npsn: item.npsn,
          nama: item.nama
        }
        tujuansekolah.push(sekolah)
      })
      return {
        ...fieldsLama,
        ...values,
        sekolah : tujuansekolah,
        tanggal_mulai: (tanggal.value.start ?? '').toString(),
        tanggal_akhir: (tanggal.value.end ?? '').toString(),
        jarak: rute.value.jarak,
        waktu: rute.value.waktu,
        keterangan: keterangan.value
      }
    })
    .post(route('post.simpan.penugasan'), {
      onSuccess: () => {
        isDialogKonfirmasi.value = true
        // inertiaForm.reset();
        // toastStatus(
        //   'success',
        //   'Penugasan Berhasil Disimpan',
        //   'Penugasan Berhasil Disimpan',
        //   2000,
        // )
        // rute.value = {
        //   jarak: '',
        //   waktu: ''
        // };
        // items.value = [];
        // waypoints.value = [];
        // resetForm();
      },
      onError: (errors) => {
        // mapping error server Laravel -> vee-validate
        for (const field in errors) {
          setFieldError('nomorInduk', errors[field])
          setFieldError('namaAsesor', errors[field])
          setFieldError('titik_awal', errors[field])
          setFieldError('sekolahListValid', errors[field])
        }
      }
    })



})


async function fetchSekolah(query: string) {
  if (!query) {
    sekolahList.value = []
    return
  }

  loading.value = true
  try {
    const res = await fetch( route("get.data.sekolah", { 'q': query }))
    const data = await res.json()
    sekolahList.value = data.map((item: any) => ({
      id: item.id,
      npsn: item.npsn,
      nama: item.nama,
      alamat: item.alamat,
      latitude: item.latitude,
      longitude: item.longitude,
      wilayah: item.wilayah
    }))
  } catch (error) {
    console.error("Gagal ambil data:", error)
  } finally {
    loading.value = false
  }
}
async function fetchAsesor(query: string) {
  if (!query) {
    asesorList.value = []
    return
  }

  loading.value = true
  try {
    const res = await fetch( route("get.data.asesor", { 'q': query }))
    const data = await res.json()
    asesorList.value = data.map((item: any) => ({
      id: item.id,
      nomor_induk: item.nomor_induk,
      nama: item.nama,
      alamat: item.alamat,
      latitude: item.latitude,
      longitude: item.longitude,
      wilayah: item.wilayah
    }))
  } catch (error) {
    console.error("Gagal ambil data:", error)
  } finally {
    loading.value = false
  }
}


const fetchSekolahDebounced = debounce((val: string) => {
  fetchSekolah(val)
}, 500)
const fetchAsesorDebounced = debounce((val: string) => {
  fetchAsesor(val)
}, 500)


watch(searchTerm, (val) => {
  fetchSekolahDebounced(val)
})
watch(searchAsesor, (val) => {
  fetchAsesorDebounced(val)
})

watch(selected, (val) => {
  if (!val) return // kalau belum ada yang dipilih, keluar
  
  const exists = items.value.some(item => item.id === val.id)
  if (!exists) {
    items.value.push(val)

    waypoints.value.push({
      lat: parseFloat(val.latitude),
      lng: parseFloat(val.longitude)
    })
    rute.value.jarak = ''
    rute.value.waktu = ''
    
  setFieldValue("sekolahListValid", [val])
  }

})
watch(selectedAsesor, (val) => {
  if (!val) return 
  inertiaForm.id = val.id
  inertiaForm.lat = val.latitude
  inertiaForm.ltd = val.longitude
  setFieldValue('namaAsesor', val.nama)
  setFieldValue('nomorInduk', val.nomor_induk)
  setFieldValue('titik_awal', val.latitude + ", " + val.longitude)
  
})
function kembali() {
  router.visit('/banpdm/penugasan')
}
</script>


<template>

    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Form Tambah Data" />
        <MasterLayout>
            
          <Card class="w-full">
            <CardHeader>
            <CardTitle class="uppercase">Form Tambah Penugasan Asesor </CardTitle>
            <!-- <CardDescription>silahkan </CardDescription> -->
            </CardHeader>
            <CardContent>
              <form class="w-full space-y-6" @submit="onSubmit" >
                <div class="grid grid-cols-1 gap-4">
                  <div class="grid grid-cols-2 gap-4">
                    <Combobox v-model="selectedAsesor" by="id" >
                      <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <Button variant="outline" class="justify-between w-full">
                              Pilih Asesor
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </ComboboxTrigger>
                      </ComboboxAnchor>
                        <ComboboxList class=" z-[9999]  w-md">
                          <div class="relative w-full max-w-full items-center">
                              <ComboboxInput 
                              class=" focus-visible:ring-0 border-0 border-b rounded-none h-10 w-full" 
                              placeholder="Cari Asesor..."
                                v-model="searchAsesor"
                                />
                              <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                              <Search class="size-4 text-muted-foreground" />
                              </span>
                          </div>
                        <ComboboxEmpty>
                            Tidak ada asesor ditemukan.
                        </ComboboxEmpty>

                        <ComboboxGroup>
                            <ComboboxItem
                              v-for="item in asesorList"
                              :key="item.id"
                              :value="item"
                            >
                                {{ item.nama }} | {{ item.nomor_induk }} — {{ item.wilayah.nama }}

                            <ComboboxItemIndicator>
                                <Check :class="cn('ml-auto h-4 w-4')" />
                            </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                        </ComboboxList>
                      </Combobox>
                  </div>
                  
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <FormField  v-slot="{ componentField }" name="namaAsesor" :validate-on-blur="!isFieldDirty">
                    <FormItem v-auto-animate>
                      <FormLabel class="data-[error=true]:text-red-600">Nama Asesor</FormLabel>
                      <FormControl>
                          <Input type="text" v-bind="componentField" placeholder="nama asesor" readonly  />
                      </FormControl>
                      <FormMessage class="text-red-500"  />
                    </FormItem>
                  </FormField>
                 
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <FormField  v-slot="{ componentField }" name="nomorInduk" :validate-on-blur="!isFieldDirty">
                    <FormItem v-auto-animate>
                      <FormLabel class="data-[error=true]:text-red-600">Nomor Induk Asesor</FormLabel>
                      <FormControl>
                        <Input type="text" v-bind="componentField" placeholder="nomor induk asesor" readonly  />
                      </FormControl>
                      <FormMessage class="text-red-500"  />
                    </FormItem>
                  </FormField>
                </div>
              
                  <div class="grid grid-cols-2 gap-4">
                  <FormField v-slot="{ componentField }" name="tanggal_penugasan" :validate-on-blur="!isFieldDirty">
                  <FormItem v-auto-animate>
                    <Label for="tanggal_penugasan" class="data-[error=true]:text-red-600" >Tanggal Penugasan</Label>
                    <FormControl>
                      <Popover>
                      <PopoverTrigger as-child>
                        <Button
                          variant="outline"
                          :class="cn(
                            'w-full justify-start text-left font-normal',
                            !tanggal && 'text-muted-foreground',
                          )"
                           id="tanggal_penugasan" 
                        >
                          <CalendarIcon class="mr-2 h-4 w-4" />
                          <template v-if="tanggal.start">
                            <template v-if="tanggal.end">
                              {{ df.format(tanggal.start.toDate(getLocalTimeZone())) }} - {{ df.format(tanggal.end.toDate(getLocalTimeZone())) }}
                            </template>

                            <template v-else>
                              {{ df.format(tanggal.start.toDate(getLocalTimeZone())) }}
                            </template> 
                          </template>
                          <template v-else>
                            Pick a date
                          </template>
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent class="w-auto p-0">
                        <RangeCalendar v-model="tanggal" initial-focus :number-of-months="2" @update:start-value="(startDate) => tanggal.start = startDate" />
                      </PopoverContent>
                    </Popover>
                    </FormControl>
                    <FormMessage class="text-red-500"  />
                  </FormItem>
                </FormField>
                  </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <FormField v-slot="{ componentField }" name="titik_awal" :validate-on-blur="!isFieldDirty">
                    <FormItem v-auto-animate>
                    <FormLabel class="data-[error=true]:text-red-600">Titik Awal</FormLabel>
                    <div class="flex w-full max-w-lg items-center gap-1.5">
                      <FormControl>
                    
                        <Input type="text" readonly  v-bind="componentField" />
                      
                    </FormControl>
                    <Button type="button" @click="openUbahKordinatModal('ubahTitikAwal')">
                          Ubah Kordinat
                    </Button>
                    </div>
                    
                    <FormMessage class="text-red-500"  />
                  </FormItem>
                </FormField>
                </div>
                  <div class="grid grid-cols-2 gap-4">
                  <FormField name="keterangan">
                    <FormItem v-auto-animate>
                      <Label for="keterangan" class="data-[error=true]:text-red-600">Keterangan</Label>
                      <FormControl>
                        <Textarea id="keterangan" v-model="keterangan" placeholder="jika ada keterangan." />
                      </FormControl>
                      <FormMessage class="text-red-500"  />
                    </FormItem>
                  </FormField>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <Combobox v-model="selected" by="id" >
                    <ComboboxAnchor as-child>
                      <ComboboxTrigger as-child>
                        <Button variant="outline" class="justify-between w-full">
                        {{ selected?.nama ?? 'Pilih Tujuan Sekolah' }}
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class=" z-[9999]  w-md">
                    <div class="relative w-full max-w-md items-center">
                        <ComboboxInput 
                        class=" focus-visible:ring-0 border-0 border-b rounded-none h-10" 
                        placeholder="Cari Sekolah..."
                          v-model="searchTerm"
                          />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                        <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <ComboboxEmpty>
                        No framework found.
                    </ComboboxEmpty>

                    <ComboboxGroup>
                        <ComboboxItem
                          v-for="item in sekolahList"
                          :key="item.id"
                          :value="item"
                        >
                            {{ item.nama }} — {{ item.wilayah.nama }}

                        <ComboboxItemIndicator>
                            <Check :class="cn('ml-auto h-4 w-4')" />
                        </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                 
                </div>
                  
                <div class="grid grid-cols-2 gap-4">
                  <FormField name="jarak" >
                    <FormItem v-auto-animate>
                    <FormLabel class="data-[error=true]:text-red-600">Perkiraan jarak dan waktu tempuh</FormLabel>
                    <div class="flex w-full max-w-lg items-center gap-1.5">
                      <FormControl>
                    
                        <Input type="text" v-model="rute.jarak" readonly/>
                        <Input type="text" v-model="rute.waktu" readonly/>
                      
                    </FormControl>
                    <Button type="button" @click="openUbahKordinatModal('lihatRute')">
                      Hitung jarak
                    </Button>
                    </div>
                    
                    <FormMessage class="text-red-500"  />
                  </FormItem>
                </FormField>
                
                </div>
                 <FormField v-slot="{ componentField }" name="sekolahListValid" :validate-on-blur="!isFieldDirty">
                    <FormItem v-auto-animate>
                 
                      <div class="overflow-hidden rounded-md border border-gray-300">
                    
                        <Table class="border-collapse w-full">
                          <TableCaption>List Tujuan Sekolah</TableCaption>
                          <TableHeader>
                            <TableRow>
                              <TableHead class="w-[150px]">NPSN</TableHead>
                              <TableHead>SEKOLAH</TableHead>
                              <TableHead>ALAMAT</TableHead>
                              <TableHead>WILAYAH</TableHead>
                              <TableHead class="w-[100px] text-center">AKSI</TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody v-auto-animate>
                            <TableRow v-for="(item, index) in items" :key="index" >
                              <TableCell class="font-medium">{{ item.npsn }}</TableCell>
                              <TableCell>{{ item.nama }}</TableCell>
                              <TableCell>{{ item.alamat }}</TableCell>
                              <TableCell>{{ item.wilayah.nama }}</TableCell>
                              <TableCell class="text-center" >
                                <button type="button" @click="removeItem(index)" class="text-red-500">Hapus</button>
                              </TableCell>
                            </TableRow>
                          </TableBody>
                        </Table>
                      </div>
                    
                    <FormMessage class="text-red-500"  />
                  </FormItem>
                </FormField>
               
                <div class="flex justify-end">
                    <Button type="button" :variant="'secondary'" :class="cn('mr-2')" @click="kembali">
                      Kembali
                    </Button>
                      <Button >
                      Simpan
                    </Button>
                </div>
                
              </form>

              <Sheet v-model:open="openUbahKordinat">
                  <SheetContent class="max-w-1/2 sm:max-w-1/2">
                    <SheetHeader>
                      <SheetTitle>{{ titleMap }}</SheetTitle>
                      <SheetDescription>
                        {{ descriptionMap }}
                      </SheetDescription>
                    </SheetHeader>
                    <div id="map" class="h-full w-full">
                        
                    </div>
                  </SheetContent>
                </Sheet>
              
            </CardContent>
          </Card>
            <Dialog v-model:open="isDialogKonfirmasi">
              <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                  <DialogTitle class="text-center">Sukses Simpan Data</DialogTitle>
                  <DialogDescription class="text-center">
                    Apakah Anda Ingin Menambahkan Data Baru?
                  </DialogDescription>
                </DialogHeader>
                
                <DialogFooter>
                  <div class="flex w-full justify-center gap-2">
                     <DialogClose as-child>
                        <Button  type="button" @click="closeModalKonfirmasi"> Ya, Tambah Baru </Button>
                    </DialogClose>
                    
                    <Button type="button" @click="kembali" class="bg-red-500">
                      Tidak
                    </Button>
                  </div>
                
                </DialogFooter>
              </DialogContent>
            </Dialog>
        </MasterLayout>
     
    </AppLayout>
</template>
