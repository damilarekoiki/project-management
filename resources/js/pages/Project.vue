<script lang="ts" setup>
import InputError from '@/components/InputError.vue';
import Task from '@/components/Task.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Project, type BreadcrumbItem, type TaskType } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const projectTasksProp = page.props.projectTasks as any;

const project: Project = page.props.project as Project;
const projectTasks: TaskType[] = projectTasksProp.data;

const isAtBottom = ref(false);

console.log(projectTasksProp);

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

const tasks = ref<TaskType[]>(projectTasks);

console.log('protasks', projectTasks);

const tasksUrl = ref<string>(
    route('api.projects.tasks.show', {
        project: project.id,
        cursor: projectTasksProp.next_cursor,
    }),
);

const loadingMore = ref<boolean>(false);

const form = ref<{
    title: string;
    description?: string;
    deadline?: string;
    tasks: TaskType[];
}>({
    title: project.title,
    description: project.description,
    deadline: project.deadline,
    tasks: [],
});

const hasTaskError = ref<boolean>(false);

const titleError = ref<string>('');

const checkTaskError = () => {
    const error = form.value.tasks.some((task: TaskType): boolean => {
        return task.title == '';
    });
    hasTaskError.value = error;
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

const loadMoreTasks = async () => {
    if (!tasksUrl.value) return;

    loadingMore.value = true;
    console.log('next url', tasksUrl.value);

    try {
        const { data } = await axios.get(tasksUrl.value);
        tasks.value = tasks.value.concat(data.data);
        tasksUrl.value = data.next_page_url;
    } finally {
        loadingMore.value = false;
    }
};

const handleScroll = () => {
    const scrollTop = window.scrollY;
    const windowHeight = window.innerHeight;
    const docHeight = document.documentElement.scrollHeight;

    const hasReachedBottom = scrollTop + windowHeight >= docHeight - 200;

    if (hasReachedBottom && !isAtBottom.value) {
        isAtBottom.value = true;
        console.log('✅ Scrolled to bottom');
        loadMoreTasks();
    }

    // Reset flag if user scrolls up
    if (!hasReachedBottom && isAtBottom.value) {
        isAtBottom.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
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

                <div class="flex justify-end gap-x-2">
                    <Button type="button" variant="outline" :href="route('projects')"> Cancel </Button>
                    <Button type="submit" @click="submit" :disabled="hasFormError"> Create Project </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
