<script lang="ts" setup>
import Task from '@/components/Task.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type TaskType } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

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

// Form with tasks array
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

const taskError = ref<boolean>(false);

const checkTaskError = () => {
    const error = form.value.tasks.some((task: TaskType): boolean => {
        return task.title == '';
    });
    taskError.value = error;
};

// Add a new task to the DOM
const addTask = () => {
    tasks.value.push({
        id: tasks.value[tasks.value.length - 1].id + 1,
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

// Remove a task from the form
const removeTask = (taskId: number): void => {
    // let taskIndex = form.value.tasks.findIndex((task: TaskType) => task.id == taskId);
    // form.value.tasks.splice(taskIndex, 1);
    form.value.tasks = form.value.tasks.filter((task: TaskType) => {
        return task.id !== taskId;
    });
    tasks.value = tasks.value.filter((task: TaskType) => {
        return task.id !== taskId;
    });
    checkTaskError();
};

const formError = computed((): boolean => {
    return taskError.value || form.value.title == '';
});

const submit = async () => {
    try {
        await axios.post(route('api.projects.store'), form.value);
    } catch (error) {
        console.error(error);
    }

    // if(formError) {
    //     return
    // }

    // Remove assignee_search field from each task before submission
    // as it's only used for the UI and not needed in the backend

    // Create a clean version of the form data for submission
    // const cleanForm = useForm({
    //     title: form.title,
    //     description: form.description,
    //     deadline: form.deadline,
    //     tasks: form.tasks.map((task: Task) => ({
    //         title: task.title,
    //         assignee_id: task.assignee_id,
    //         status: task.status,
    //         due_date: task.due_date
    //     }))
    // });

    // cleanForm.post(route('projects.store'));
};
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
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="4" />
                </div>

                <div class="space-y-2">
                    <Label for="deadline">Deadline</Label>
                    <!-- <div class="bg-white"> -->
                    <Input id="deadline" v-model="form.deadline" type="date" :min="today" class="!bg-white text-black" />

                    <!-- </div> -->
                </div>

                <!-- Tasks Section -->
                <div class="mt-16 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium">Tasks</h2>
                        <Button type="button" size="sm" class="bg-blue-800 text-white hover:bg-blue-800" @click="addTask"> Add Task </Button>
                    </div>

                    <div v-for="task in tasks" :key="task.id" class="space-y-4 rounded-lg border p-4">
                        <Task :initialTask="task" :showRemoveButton="true" :isEdit="false" @removeTask="removeTask" @addTaskToForm="addTaskToForm" />
                    </div>
                </div>
                {{ form.tasks }}
                <div class="flex justify-end gap-x-2">
                    <Button type="button" variant="outline" :href="route('projects')"> Cancel </Button>
                    <Button type="submit" @click="submit" :disabled="formError"> Create Project </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
