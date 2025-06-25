<script lang="ts" setup>
import { Button } from '@/components/ui/button';
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
import { Project } from '@/types';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { defineEmits, defineProps, ref } from 'vue';

const props = defineProps<{
    project: Project;
}>();

const emit = defineEmits(['deleteProject']);

const deleting = ref<boolean>(false);

const deleteProject = async () => {
    deleting.value = true;
    try {
        await axios.delete(route('api.projects.delete', { project: props.project.id }));
        deleting.value = false;
        emit('deleteProject', props.project);
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <div class="flex justify-between gap-x-4 rounded-md border border-gray-400 p-4">
        <div class="w-8/12">
            <p class="font-bold">{{ project.title }}</p>
            <p class="text-xs text-gray-400">{{ project.description }}</p>
            <div class="flex gap-x-4 pt-4">
                <p>Creator: {{ project.creator.name }}</p>
                <p>Deadline: {{ project.deadline ?? 'Not set' }}</p>
                <p>Tasks: {{ project.tasks_count }}</p>
            </div>
        </div>
        <div class="flex w-4/12 justify-center gap-x-2">
            <Button as-child class="cursor-pointer bg-green-800 text-white hover:bg-green-800">
                <Link :href="route('projects.show', { project: project.id })"> View </Link>
            </Button>

            <Button as-child class="cursor-pointer bg-blue-800 text-white hover:bg-blue-800">
                <Link :href="route('projects.edit', { project: project.id })"> Edit </Link>
            </Button>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" class="cursor-pointer">Delete</Button>
                </DialogTrigger>

                <DialogContent>
                    <div class="space-y-6">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to delete this project?</DialogTitle>
                            <DialogDescription> Once deleted, all of its resources and data will also be permanently deleted. </DialogDescription>
                        </DialogHeader>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" class="cursor-pointer"> Cancel </Button>
                            </DialogClose>

                            <DialogClose as-child>
                                <Button type="submit" variant="destructive" :disabled="deleting" @click="deleteProject" class="cursor-pointer">
                                    Delete project
                                </Button>
                            </DialogClose>
                        </DialogFooter>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>

<style></style>
