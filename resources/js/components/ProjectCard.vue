<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Project } from '@/types';
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps<{
    project: Project;
}>();
</script>

<template>
    <div class="flex justify-between gap-x-4 rounded-md border border-gray-400 p-4">
        <div class="w-8/12">
            <p class="font-bold">{{ project.title }}</p>
            <p class="text-xs text-gray-400">{{ project.description }}</p>
            <div class="flex gap-x-4 pt-4">
                <p>Creator</p>
                <p>Deadline</p>
                <p>Tasks</p>
            </div>
        </div>
        <div class="flex w-4/12 justify-center gap-x-2">
            <Button as-child class="cursor-pointer bg-green-800 text-white hover:bg-green-800">
                <Link :href="route('projects.show', { project: project.id })"> View </Link>
            </Button>
            <Button as-child class="cursor-pointer bg-blue-800 text-white hover:bg-blue-800">
                <Link :href="route('projects.edit', { project: project.id })"> Edit </Link>
            </Button>
            <Button variant="destructive" class="cursor-pointer">Delete</Button>
        </div>
    </div>
</template>

<style></style>
