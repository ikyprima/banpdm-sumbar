<script setup lang="ts">
import { Head, Link, useForm, usePage,router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/banpdm/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import DataTable from '@/components/DataTable.vue'
import schoolIconUrl from '../../images/icons/school-svgrepo-com.svg'
import peopleIconUrl from '../../images/icons/people-nearby-svgrepo-com.svg'
import { Checkbox } from "@/components/ui/checkbox"

import { h, ref, onMounted,watch  } from 'vue'
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from "@/components/ui/combobox"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

import { type BreadcrumbItem, type SharedData, type User , type Wilayah, type Sekolah, type Asesor } from '@/types';
import { Check, Search,ChevronsUpDown } from "lucide-vue-next"
// import {
//   Combobox,
//   ComboboxAnchor,
//   ComboboxEmpty,
//   ComboboxGroup,
//   ComboboxInput,
//   ComboboxItem,
//   ComboboxItemIndicator,
//   ComboboxList
// } from "@/components/ui/combobox"

import L, { point } from 'leaflet' // ✅ BENAR

import 'leaflet/dist/leaflet.css'
let map: L.Map | null = null

const schoolMarkers = ref<L.Marker[]>([])
const asesorMarkers = ref<L.Marker[]>([])
const listSekolah = ref<Sekolah[]>([])
const listAsesor = ref<Asesor[]>([])

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dokumen',
    },
    {
        title: 'Penugasan',
        href: '/dokumen/kategori',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const tampilkanSekolah = ref(false) // nilai dari checkbox
const tampilkanAsesor = ref(false) // nilai dari checkbox


// L.Icon.Default.mergeOptions({
//   iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
//   iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
//   shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
// });

const props = defineProps<{
    // sekolahList: Sekolah[],
    polygons: number[][] | number[][][] // bisa 1 polygon atau banyak
    seluruhwilayah: Wilayah[]
}>()
// const selectedWilayah = ref<Wilayah | null>(null)
const selectedWilayah = ref<typeof daerahList[0]>()

const daerahList = props.seluruhwilayah.map(item => {
  const val = item
  return {
    value: val.kode,             // dipakai sebagai value unik
    label: val.nama,             // ditampilkan di dropdown
    // ibukota: val.ibukota          // tambahan data kalau mau dipakai di popup
  }
})
let drawnPolygons: L.Polygon[] = [] // simpan polygon yang sudah digambar

watch(selectedWilayah, (newVal) => {
  // if (!newVal ) return
  if (!map) return // pastikan map sudah siap


  const filtered = props.seluruhwilayah.filter(item => item.kode === newVal?.value)
  // console.log(filtered[0].path);

  
  if (!filtered.length || !filtered[0].path) return

  // Hapus polygon lama
  drawnPolygons.forEach(p => map?.removeLayer(p))
  drawnPolygons = []
  tampilkanSekolah.value = false
  tampilkanAsesor.value = false
  
  schoolMarkers.value.forEach(m => m.remove())
  schoolMarkers.value = []
  asesorMarkers.value.forEach(m => m.remove())
  asesorMarkers.value = []


  // Decode path dari JSON string
  const pathData = typeof filtered[0].path === "string"
    ? JSON.parse(filtered[0].path)
    : filtered[0].path

  const polygonList = Array.isArray(pathData[0][0])
    ? pathData as number[][][]
    : [pathData as number[][]]

  const allBounds = L.latLngBounds([])
  listSekolah.value = filtered[0].satuan_sekolah??[];
  listAsesor.value = filtered[0].asesor??[];

  polygonList.forEach(polygonCoords => {
    const latlngs = polygonCoords.map(([lat, lng]) => L.latLng(lat, lng))
    const polygon = L.polygon(latlngs, { color: 'yellow' }).addTo(map!)
    drawnPolygons.push(polygon)
    allBounds.extend(polygon.getBounds())
  })

  if (allBounds.isValid()) {
    map?.fitBounds(allBounds, { padding: [20, 20] })
  }
})
onMounted(() => {
    map = L.map('map').setView([-0.947083, 100.417181], 7)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map)

        // coords.forEach((polygon) => {
        //     const latlngs = polygon.map(([lat, lng]) => L.latLng(lat, lng));
        //     L.polygon(latlngs, { color: 'blue' }).addTo(map);
        // });

        const polygonList = Array.isArray(props.polygons[0][0])
        ? props.polygons as number[][][] // sudah array of polygons
        : [props.polygons as number[][]] // bungkus jadi array
        
        const allBounds = L.latLngBounds([]) // kosongkan dulu
        polygonList.forEach(polygonCoords => {
            // L.polygon(polygonCoords, {
            // color: 'blue',
            // weight: 2,
            // fillOpacity: 0.2
            // }).addTo(map)
            const latlngs = polygonCoords.map(([lat, lng]) => L.latLng(lat, lng));
            const polygon = L.polygon(latlngs, { color: 'blue' }).addTo(map!);
            allBounds.extend(polygon.getBounds())
        })
        if (allBounds.isValid()) {
            map.fitBounds(allBounds, { padding: [20, 20] }) // padding biar ga mepet
        }
})


// Fungsi toggle marker
const toggleSekolah = () => {
    if (!map) return
  
      const schoolIcon = L.icon({
        iconUrl: schoolIconUrl, // hasil import
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
      })

      schoolMarkers.value.forEach(m => m.remove())
      schoolMarkers.value = []

        if (!tampilkanSekolah.value) return
        listSekolah.value.forEach(sekolah => {
            const lat = parseFloat(sekolah.latitude.toString())
            const lng = parseFloat(sekolah.longitude.toString())

            if (!isNaN(lat) && !isNaN(lng)) {
            const marker = L.marker([lat, lng], { icon: schoolIcon })
                .addTo(map!)
                .bindPopup(`
                <b>${sekolah.nama}</b><br>
                ${sekolah.alamat}<br>
                ${sekolah.kecamatan}, ${sekolah.desa}<br>
                <a href="${sekolah.website}" target="_blank">${sekolah.website}</a>
                `)
                schoolMarkers.value.push(marker)
            }
        })
}
const toggleAsesor = () => {
    if (!map) return
  
      const schoolIcon = L.icon({
        iconUrl: peopleIconUrl, // hasil import
      iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
      })

      asesorMarkers.value.forEach(m => m.remove())
      asesorMarkers.value = []

        if (!tampilkanAsesor.value) return
        listAsesor.value.forEach(asesor => {
            const lat = parseFloat(asesor.latitude.toString())
            const lng = parseFloat(asesor.longitude.toString())

            if (!isNaN(lat) && !isNaN(lng)) {
            const marker = L.marker([lat, lng], { icon: schoolIcon })
                .addTo(map!)
                .bindPopup(`
                <b>${asesor.nama}</b><br>
                ${asesor.nomor_induk}<br>
                ${asesor.alamat}<br>
                <a href="mailto:${asesor.email}" target="_blank">${asesor.email}</a>
                `)
                asesorMarkers.value.push(marker)
            }
        })
}
</script>


<template>
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Master Kategori" />
        <MasterLayout>
          <div class="flex flex-col space-y-6">
            <div class="flex items-center space-x-2">
              <Combobox v-model="selectedWilayah" by="label">
                <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button variant="outline" class="justify-between  w-2xs">
                        {{ selectedWilayah?.label ?? 'Pilih Wilayah' }}
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class=" z-[9999] w-2xs">
                    <div class="relative w-full max-w-sm items-center">
                        <ComboboxInput class=" focus-visible:ring-0 border-0 border-b rounded-none h-10" placeholder="Pilih Wilayah..." />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                        <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <ComboboxEmpty>
                        No framework found.
                    </ComboboxEmpty>

                    <ComboboxGroup>
                        <ComboboxItem
                        v-for="wilayah in daerahList"
                        :key="wilayah.value"
                        :value="wilayah"
                        >
                           {{ wilayah.label }}

                        <ComboboxItemIndicator>
                            <Check :class="cn('ml-auto h-4 w-4')" />
                        </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <Checkbox id="tampilkansekolah"
                  v-model="tampilkanSekolah"
                   @update:checked="toggleSekolah" 
                  @update:model-value="toggleSekolah" />
                <label
                  for="tampilkansekolah"
                  class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 mr-10"
                >
                  Tampilkan Sekolah
                </label>
                <Checkbox id="tampilkanasesor" 
                 v-model="tampilkanAsesor"
                  @update:checked="toggleAsesor" 
                  @update:model-value="toggleAsesor"/>
                <label
                  for="tampilkanasesor"
                  class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                  Tampilkan Asesor
                </label>
              </div>

              
                <Card class="w-full">
                    <CardHeader>
                    <CardTitle class="uppercase">Peta Wilayah Kerja BAN - PDM </CardTitle>
                    <!-- <CardDescription>silahkan </CardDescription> -->
                    </CardHeader>
                    <CardContent>
                        <div id="map" class="h-full w-full">
                        
                        </div>
                    </CardContent>
                
                </Card>
            
               
            </div>
                
        </MasterLayout>
    </AppLayout>
</template>

<style>
/* Tanpa scoped */
#map {
  height: 80vh;
}
.leaflet-pane,
.leaflet-map-pane,
.leaflet-tile-pane,
.leaflet-overlay-pane,
.leaflet-shadow-pane,
.leaflet-marker-pane,
.leaflet-tooltip-pane,
.leaflet-popup-pane {
  z-index: 1 !important;
}
</style>  