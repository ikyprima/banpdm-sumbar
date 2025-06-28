<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/dokumen/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dokumen', 
        href: '/dokumen',
    },
    {
        title: 'Dokumen',
        href: '/dokumen',
    }
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Master" />
        <MasterLayout>
            <div class="space-y-6">
                <HeadingSmall title="Silahkan Pilih Menu di Samping" description="Untuk Mengelola Data Master" />
            
            </div>
        </MasterLayout>
    </AppLayout>
</template>
