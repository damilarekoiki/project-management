<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import ProjectCard from '@/components/ProjectCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Project, type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

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

const page = usePage();
const projectsProp = page.props.projects as any;
const projects: Project[] = projectsProp.data;

const deleteProject = (project: Project) => {
    const index = projects.findIndex((p) => p.id === project.id);
    if (index !== -1) {
        projects.splice(index, 1);
    }

    router.reload({ only: ['projects'] });
};
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
                <ProjectCard v-for="(project, index) in projects" :key="index" :project="project" @deleteProject="deleteProject" />
            </div>

            <Pagination :links="projectsProp.links" />
        </div>
    </AppLayout>
</template>

<style scoped>
table {
    width: 100%;
}
</style>
