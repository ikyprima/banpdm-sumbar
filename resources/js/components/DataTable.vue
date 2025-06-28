<!-- components/DataTable.vue -->
<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table'
import {
  getCoreRowModel,
  getExpandedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
  FlexRender
} from '@tanstack/vue-table'

import { h, ref, watchEffect } from 'vue'
import { valueUpdater } from '@/components/ui/table/utils'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import DropdownAction from '@/components/DropdownAction.vue'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'

import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
  PaginationFirst,
  PaginationLast
} from '@/components/ui/pagination'

const props = defineProps<{
  data: any[],
  fieldsFromDB: any[],
  currentPage: number,
  totalItems: number,
  perPage: number,
  paginationLinks : any[]
  firstPageUrl: string,
  lastPageUrl: string,
//   columns: ColumnDef<any>[]
}>()

interface ColumnMeta {
  size?: string
}

const dynamicColumns: ColumnDef<any>[] = props.fieldsFromDB.map(field => {
  switch (field.key) {
      case 'select':
      return {
          id: 'select',
          header: ({ table }) =>
            h(Checkbox, {
              modelValue: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
              'onUpdate:modelValue': value => table.toggleAllPageRowsSelected(!!value),
            }),
          cell: ({ row }) =>
            h(Checkbox, {
              modelValue: row.getIsSelected(),
              'onUpdate:modelValue': value => row.toggleSelected(!!value),
            }),
          enableSorting: false,
          enableHiding: false,
      }
    case 'status':
      return {
        accessorKey: field.key,
        header: field.sortable
          ? ({ column }) =>
              h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
              }, () => [field.label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
          : () => h('div', { class: 'text-left' }, field.label),
        cell: ({ row }) => h('div', { class: 'capitalize' }, row.getValue(field.key)),
        enableSorting: field.sortable,
      }

    case 'email':
      return {
        accessorKey: field.key,
          header: field.sortable
          ? ({ column }) =>
              h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
              }, () => [field.label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
          : () => h('div', {}, field.label), 
        cell: ({ row }) => h('div', { class: 'lowercase' }, row.getValue(field.key)),
        enableSorting: field.sortable,
      }

    case 'amount':
      return {
        accessorKey: field.key,
        header: field.sortable
        ? ({ column }) =>
            h(Button, {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => [field.label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4 text-right' })])
        : () => h('div', { class: 'text-right' }, field.label),
        
        cell: ({ row }) => {
          const amount = Number(row.getValue(field.key))
          const formatted = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
          }).format(amount)
          return h('div', { class: 'text-right font-medium' }, formatted)
        },
        enableSorting: field.sortable,
      }

    default:
      return {
        accessorKey: field.key,
        header: field.sortable
          ? ({ column }) =>
              h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
              }, () => [field.label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
          :  () => h('div', {}, field.label),
        cell: ({ row }) => h('div', {class: 'break-words max-w-[200px] whitespace-normal'}, row.getValue(field.key)),
        enableSorting: field.sortable, 
      }
  }
})

const columns: ColumnDef<any,ColumnMeta>[] = [
  // {
  //   id: 'select',
  //   size: 270,
  //   header: ({ table }) => h(Checkbox, {
  //     modelValue: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
  //     'onUpdate:modelValue': value => table.toggleAllPageRowsSelected(!!value),
  //     ariaLabel: 'Select all',
  //   }),
  //   cell: ({ row }) => h(Checkbox, {
  //     modelValue: row.getIsSelected(),
  //     'onUpdate:modelValue': value => row.toggleSelected(!!value),
  //     ariaLabel: 'Select rows',
  //   }),
  //   enableSorting: false,
  //   enableHiding: false,
  // },
 
  ...dynamicColumns,
  {
    id: 'actions',
    // cell: ({ row }) => h(DropdownAction, {
    //   payment: row.original,
    //   onExpand: row.toggleExpanded,
    // }),
    cell: ({ row }) => {
      const payment = row.original
      return h(DropdownAction, {
        rowData: row.original,
        onExpand: row.toggleExpanded,
        onEdits: (id: string) => handleEdit(id),
        onDelete: (id: string) => handleDelete(id),
        onPreviews: (id: Object) => handePreview(id),
      })
    },
  },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})
const searchQuery = ref('')

const table = useVueTable({
  data: props.data,
  columns: columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
  onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
    get expanded() { return expanded.value },
  },
})

const isEllipsis = (label: string) => {
  return label.includes('...')
}

const emit = defineEmits(['previews','downloads','edits', 'delete','clickPaging','search'])
function handePreview(id: Object) {
  emit('previews', id) 
}
function handleEdit(id: string) {
   emit('edits', id) 
  // console.log('Edit item with ID:', id)
  // Navigasi, buka dialog, dsb
}

function handleDelete(id: string) {
  console.log('Delete item with ID:', id)
  // Konfirmasi, hapus via API, dsb
}
function handlePaging(link: string) {
  emit('clickPaging', link)  
}
function handleSearch() {
  emit('search', searchQuery.value) // Emit ke parent (jika diperlukan)
  table.setGlobalFilter?.(searchQuery.value) // Jika pakai filtering global TanStack
}

watchEffect(() => {
  if (Array.isArray(props.data)) {
    table.setOptions((prev) => ({
      ...prev,
      data: props.data,
    }))
  }
})

</script>

<template>
  <div class="w-full">
    <div class="flex items-center py-4">
      <Input
        class="max-w-sm"
        placeholder="Pencarian ..."
        v-model="searchQuery"
        @keyup.enter="handleSearch"
      />
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="ml-auto">
            Columns <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
            :key="column.id"
            class="capitalize"
            :model-value="column.getIsVisible()"
            @update:model-value="(value) => column.toggleVisibility(!!value)"
          >
            {{ column.id }}
          </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
    <div class="rounded-md border w-full overflow-x-auto">
      <Table class="">
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <template v-for="row in table.getRowModel().rows" :key="row.id">
              <TableRow :data-state="row.getIsSelected() && 'selected'">
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"  >
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </TableCell>
              </TableRow>
              <TableRow v-if="row.getIsExpanded()">
                <TableCell :colspan="row.getAllCells().length">
                  {{ JSON.stringify(row.original) }}
                </TableCell>
              </TableRow>
            </template>
          </template>
          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
    <div class="flex items-center justify-end space-x-2 py-4">
      <div class="flex-1 text-sm text-muted-foreground">
        {{ table.getFilteredSelectedRowModel().rows.length }} of
        {{ table.getFilteredRowModel().rows.length }} row(s) selected.
      </div>
      <div class="space-x-2">
        <!-- <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">Previous</Button>
        <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">Next</Button> -->
          <Pagination v-slot="{ page }" :items-per-page=props.perPage :total=props.totalItems :default-page=props.currentPage>

            <PaginationContent>
              <PaginationFirst 
                :disabled="props.currentPage === 1 || !props.firstPageUrl"
                @click="handlePaging(props.firstPageUrl)"/>
              <template v-for="(link, index) in props.paginationLinks" :key="index">
                <!-- Previous -->
                <PaginationPrevious
                  v-if="index === 0"
                  :disabled="!link.url"
                  @click="handlePaging(link.url)"
                />
                <!-- Next -->
                <PaginationNext
                  v-else-if="index === props.paginationLinks.length - 1"
                  :disabled="!link.url"
                  @click="handlePaging(link.url)"
                />
                <!-- Ellipsis -->
                <PaginationEllipsis
                  v-else-if="isEllipsis(link.label)"
                />

                <!-- Page Number -->
                <PaginationItem
                  v-else
                  :is-active="link.active"
                  :value=parseInt(link.label)
                  @click="handlePaging(link.url)"
                >
                
                  {{ link.label }}
                </PaginationItem>
              </template>
              <PaginationLast @click="handlePaging(props.lastPageUrl)"/> 
            </PaginationContent>
          </Pagination>
      </div>
    </div>
  </div>
</template>
