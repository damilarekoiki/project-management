<script lang="ts" setup>
import InputError from '@/components/InputError.vue';
import Task from '@/components/Task.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type TaskType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

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
        title: 'Create Project',
        href: '/projects/create',
    },
];

const today = new Date().toISOString().split('T')[0];

const tasks = ref<TaskType[]>([
    {
        id: 0,
        title: '',
        assignee_id: null,
        status: 'pending',
        due_date: '',
    },
]);

type TaskFormState = {
    [taskId: string]: boolean;
};
const formState = ref<TaskFormState>({});
const formChanged = ref<boolean>(true);

const form = ref<{
    title: string;
    description: string;
    deadline: string;
    tasks: TaskType[];
}>({
    title: '',
    description: '',
    deadline: '',
    tasks: [],
});

const hasTaskError = ref<boolean>(false);

const titleError = ref<string>('');

const checkTaskError = () => {
    const error = form.value.tasks.some((task: TaskType): boolean => {
        return task.title == '';
    });
    formChanged.value = Object.values(formState.value).some((state) => state == true);
    hasTaskError.value = error;
};

const updateFormState = (state: boolean, taskId: number) => {
    formState.value[taskId] = state;
    if (state == false) {
        form.value.tasks = form.value.tasks.filter((task: TaskType) => {
            return task.id !== taskId;
        });
    }
    checkTaskError();
};

// Add a new task to the DOM
const addTask = () => {
    tasks.value.push({
        id: (tasks.value[tasks.value.length - 1]?.id ?? 0) + 1,
        title: '',
        assignee_id: null,
        status: 'pending',
        due_date: '',
    });
    checkTaskError();
};

const addTaskToForm = (task: TaskType, taskId: number) => {
    const taskIndex = form.value.tasks.findIndex((task: TaskType) => task.id == taskId);

    if (taskIndex !== -1) {
        form.value.tasks[taskIndex] = task;
    } else {
        form.value.tasks.push(task);
    }
    checkTaskError();
};

// Remove a task from the form and DOM
const removeTask = (taskId: number): void => {
    form.value.tasks = form.value.tasks.filter((task: TaskType) => {
        return task.id !== taskId;
    });
    tasks.value = tasks.value.filter((task: TaskType) => {
        return task.id !== taskId;
    });
    checkTaskError();
};

const hasFormError = computed((): boolean => {
    return hasTaskError.value || form.value.title == '';
});

const submit = async () => {
    try {
        await axios.post(route('api.projects.store'), form.value);
        alert('Project successfully saved');
        setTimeout(() => {
            router.visit(route('projects'));
        }, 200);
    } catch (error) {
        console.error(error);
    }
};

watch(
    () => form.value.title,
    () => {
        if (form.value.title == '') {
            titleError.value = 'The title field is required';
        }
    },
);
</script>

<template>
    <Head title="Create Project" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Create New Project</h1>

            <div class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Project Title</Label>
                    <Input id="title" v-model="form.title" placeholder="Enter title for the project" />
                    <InputError :message="titleError" v-if="titleError" />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="4" />
                </div>

                <div class="space-y-2">
                    <Label for="deadline">Deadline</Label>
                    <Input id="deadline" v-model="form.deadline" type="date" :min="today" class="!bg-white text-black" />
                </div>

                <!-- Tasks Section -->
                <div class="mt-32 space-y-8">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium">Project's Tasks</h2>
                        <Button type="button" size="sm" class="bg-blue-800 text-white hover:bg-blue-800" @click="addTask"> Add Task </Button>
                    </div>

                    <div v-for="task in tasks" :key="task.id" class="space-y-4 rounded-lg border p-4">
                        <Task
                            :initialTask="task"
                            :showRemoveButton="true"
                            :isEdit="false"
                            @removeTask="removeTask"
                            @addTaskToForm="addTaskToForm"
                            @updateFormState="updateFormState"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-x-2">
                    <span class="mt-3 mr-12 inline-block text-sm text-muted-foreground" v-if="hasFormError || !formChanged"
                        >Edit project to continue</span
                    >

                    <Button variant="outline" as-child class="cursor-pointer">
                        <Link as="Link" :href="route('projects')"> Cancel </Link>
                    </Button>
                    <Button @click="submit" :disabled="hasFormError || !formChanged"> Create Project </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
