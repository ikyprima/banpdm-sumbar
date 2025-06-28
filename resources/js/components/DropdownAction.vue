<template>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="ghost">⋮</Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem @click="onPreviews">Preview</DropdownMenuItem>
        <DropdownMenuItem @click="onDownloads">Download</DropdownMenuItem>
        <DropdownMenuItem @click="onEdits">Edit</DropdownMenuItem>
        <DropdownMenuItem @click="onDelete">Delete</DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </template>
  
  <script setup>
  import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
  } from '@/components/ui/dropdown-menu'
  import { Button } from '@/components/ui/button'
  
  const props = defineProps({
    rowData: Object,
  })
  const emit = defineEmits(['edits', 'delete','previews','downloads'])

  function onEdits() {
    // console.log('Edit', props.rowData)
    emit('edits', props.rowData?.id)
  }
  
  function onDelete() {
    // console.log('Delete', props.rowData)
    emit('delete', props.rowData?.id)
  }
  function onPreviews() {
    
    emit('previews', props.rowData)
  }
  function onDownloads() {
    emit('downloads', props.rowData?.id)
  }
  </script>