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

const cursor = ref(projectTasksProp.next_cursor);

const tasksUrl = ref<string>(
    route('api.projects.tasks.show', {
        project: project.id,
        cursor: cursor.value,
    }),
);

const taskDateFilter = ref('');
const taskStatusFilter = ref('');

const taskStatusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'done', label: 'Done' },
];

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

const fetchTasks = async (mode: 'filter' | 'scroll') => {
    loadingMore.value = true;

    try {
        const { data } = await axios.get(tasksUrl.value);
        if (mode == 'filter') {
            tasks.value = [];
        }
        tasks.value = tasks.value.concat(data.data);
        tasksUrl.value = data.next_page_url;
        cursor.value = data.next_cursor;
    } finally {
        loadingMore.value = false;
    }
};

const loadMoreTasks = () => {
    if (!cursor.value) return;

    fetchTasks('scroll');
};

const handleScroll = () => {
    const scrollTop = window.scrollY;
    const windowHeight = window.innerHeight;
    const docHeight = document.documentElement.scrollHeight;

    const hasReachedBottom = scrollTop + windowHeight >= docHeight - 200;

    if (hasReachedBottom && !isAtBottom.value) {
        isAtBottom.value = true;
        loadMoreTasks();
    }

    // Reset flag if user scrolls up
    if (!hasReachedBottom && isAtBottom.value) {
        isAtBottom.value = false;
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

watch([taskStatusFilter, taskDateFilter], () => {
    tasksUrl.value = route('api.projects.tasks.show', {
        project: project.id,
        status: taskStatusFilter.value,
        due_date: taskDateFilter.value,
    });
    fetchTasks('filter');
});

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
                    <Input id="deadline" v-model="form.deadline" type="date" :min="today" class="!bg-white text-black" />
                </div>

                <!-- Tasks Section -->
                <div class="mt-16 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium">Tasks</h2>
                        <div>
                            <Label for="dueDate">Filter by Status</Label>
                            <select
                                v-model="taskStatusFilter"
                                class="mt-2 flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                <option v-for="option in taskStatusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Label for="dueDate">Filter by Due Date</Label>
                            <Input id="dueDate" v-model="taskDateFilter" type="date" class="w-full !bg-white text-gray-900" />
                        </div>
                        <Button type="button" size="sm" class="bg-blue-800 text-white hover:bg-blue-800" @click="addTask"> Add New Tasks </Button>
                    </div>

                    <div v-if="tasks.length == 0" class="mt-10 text-center">No tasks found</div>

                    <div v-for="task in tasks" :key="task.id" class="space-y-4 rounded-lg border p-4">
                        <Task :initialTask="task" :showRemoveButton="true" :isEdit="true" @removeTask="removeTask" @addTaskToForm="addTaskToForm" />
                    </div>
                </div>

                <div class="flex justify-end gap-x-2" v-if="tasks.length">
                    <Button type="submit" @click="submit" :disabled="hasFormError"> Save Changes </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
