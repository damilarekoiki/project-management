<script lang="ts" setup>
import InputError from '@/components/InputError.vue';
import ProjectTasksEdit from '@/components/ProjectTasksEdit.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Project, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';

const page = usePage();

const project: Project = page.props.project as Project;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: 'Project',
        href: '/projects/create',
    },
];

const today = new Date().toISOString().split('T')[0];

const form = ref<{
    title: string;
    description?: string;
    deadline?: string;
}>({
    title: project.title,
    description: project.description,
    deadline: project.deadline,
});

const initialErrors = {
    title: '',
    description: '',
    deadline: '',
};

const formErrors = ref(initialErrors);

// Add a new task to the DOM
// const addTask = () => {
//     tasks.value.push({
//         id: tasks.value[tasks.value.length - 1].id + 1,
//         title: '',
//         assignee_id: null,
//         status: 'pending',
//         due_date: '',
//     });
//     checkTaskError();
// };

const submit = async () => {
    try {
        await axios.patch(route('api.projects.update', { project: project.id }), form.value);

        formErrors.value.title = '';
        formErrors.value.description = '';
        formErrors.value.deadline = '';
        alert('Projected successfully updated');
    } catch (error: any) {
        const errors = error.response.data.errors;
        formErrors.value.title = errors?.title?.[0];
        formErrors.value.description = errors?.description?.[0];
        formErrors.value.deadline = errors?.deadline?.[0];

        console.error(error);
    }
};

watch(
    () => form.value.title,
    () => {
        if (form.value.title == '') {
            formErrors.value.title = 'The title field is required';
        }
    },
);
</script>

<template>
    <Head title="Create Project" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-10 rounded-xl p-4">
            <h1 class="text-2xl font-bold capitalize">Update Project</h1>

            <div class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Project Title</Label>
                    <Input id="title" v-model="form.title" placeholder="Enter title for the project" />
                    <InputError :message="formErrors.title" v-if="formErrors.title" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="4" />
                    <InputError :message="formErrors.description" v-if="formErrors.description" />
                </div>

                <div class="space-y-2">
                    <Label for="deadline">Deadline</Label>
                    <Input id="deadline" v-model="form.deadline" type="date" :min="today" class="!bg-white text-black" />
                    <InputError :message="formErrors.deadline" v-if="formErrors.deadline" />
                </div>

                <div>
                    <Button
                        type="submit"
                        @click="submit"
                        :disabled="form.title == ''"
                        class="cursor-pointer bg-blue-800 text-white hover:bg-blue-700"
                    >
                        Update Project
                    </Button>
                </div>

                <!-- Tasks Section -->
                <div class="mt-32 flex items-center justify-between">
                    <h2 class="text-lg font-medium">Project's Tasks</h2>

                    <Button type="button" size="sm" class="cursor-pointer bg-blue-800 text-white hover:bg-blue-700"> + Create New Tasks </Button>
                </div>
                <ProjectTasksEdit />
            </div>
        </div>
    </AppLayout>
</template>
