import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { Asesor } from './index.d';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    children?: NavItem[];
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaginationLinks {
  url: string | null
  label: string
  active: boolean
}

export interface PaginatedResponse<T> {
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
export interface Sekolah {
  id: number
  npsn: string
  nama: string
  jenjang_id: number
  status: string
  alamat: string
  desa: string
  kecamatan: string
  kode_daerah: string
  kode_pos: string
  telepon: string
  email: string
  website: string
  kepala_sekolah: string
  latitude: string // atau bisa juga number, tergantung data backend
  longitude: string // atau bisa juga number
  created_at: string // ISO date string
  updated_at: string // ISO date string
}
export interface Asesor {
  id: number
  nomor_induk: string
  nama: string
  email: string
  no_hp: string
  alamat: string
  latitude: string // bisa juga number kalau mau langsung parsing
  longitude: string
  instansi: string | null
  foto: string | null
  id_wilayah: string
  created_at: string | null
  updated_at: string | null
  deleted_at: string | null
}
export interface Wilayah {
  kode: string;
  nama: string;
  ibukota: string;
  path: string;
  satuan_sekolah?: Sekolah[] | null // bisa kosong/null
  asesor?: Asesor[] | null // bisa kosong/null
}





export type BreadcrumbItemType = BreadcrumbItem;
