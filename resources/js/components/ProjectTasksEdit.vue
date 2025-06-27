<script lang="ts" setup>
import Task from '@/components/Task.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Project, type TaskType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const projectTasksProp = page.props.projectTasks as any;

const project: Project = page.props.project as Project;
const projectTasks: TaskType[] = projectTasksProp.data;

const isAtBottom = ref(false);

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
    tasks: TaskType[];
}>({
    tasks: [],
});

const hasTaskError = ref<boolean>(false);

type TaskFormState = {
    [taskId: string]: boolean;
};
const formState = ref<TaskFormState>({});
const formChanged = ref<boolean>(false);

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
    return hasTaskError.value;
});

const updateTasks = async () => {
    try {
        await axios.patch(route('api.projects.tasks.update', { project: project.id }), form.value);
        alert('Tasks successfully updated');
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
    <!-- Tasks Section -->
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <div class="w-5/12">
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
            <div class="w-5/12">
                <Label for="dueDate">Filter by Due Date</Label>
                <Input id="dueDate" v-model="taskDateFilter" type="date" class="w-full !bg-white text-gray-900" />
            </div>
        </div>

        <div v-if="tasks.length == 0" class="mt-10 text-center">No tasks found</div>

        <div v-for="task in tasks" :key="task.id" class="space-y-4 rounded-lg border p-4">
            <Task
                :initialTask="task"
                :showRemoveButton="true"
                :isEdit="true"
                @removeTask="removeTask"
                @addTaskToForm="addTaskToForm"
                @updateFormState="updateFormState"
            />
        </div>
    </div>

    <div class="flex justify-end gap-x-2" v-if="tasks.length">
        <span class="mt-3 mr-12 inline-block text-sm text-muted-foreground" v-if="hasFormError || !formChanged">Edit a task to continue</span>
        <Button @click="updateTasks" :disabled="hasFormError || !formChanged"> Update Tasks </Button>
    </div>
</template>
