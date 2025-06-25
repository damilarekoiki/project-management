<script setup lang="ts">
import ProjectCard from '@/components/ProjectCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects',
    },
];
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mb-12 flex">
                <Button variant="outline" as-child class="cursor-pointer">
                    <Link as="Link" :href="route('projects.create')"> Create Project </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-y-8">
                <ProjectCard />
                <ProjectCard />
                <ProjectCard />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
table {
    width: 100%;
}
</style>
