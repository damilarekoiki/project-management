<script lang="ts" setup>
import Task from '@/components/Task.vue';
import { Button } from '@/components/ui/button';
import { Project, type TaskType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const page = usePage();
const project: Project = page.props.project as Project;

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
const formChanged = ref<boolean>(false);

const form = ref<{
    tasks: TaskType[];
}>({
    tasks: [],
});

const hasTaskError = ref<boolean>(false);

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
        id: (tasks.value[tasks.value.length - 1].id ?? 0) + 1,
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
    return hasTaskError.value;
});

const addNewTasks = async () => {
    try {
        await axios.put(route('api.projects.tasks.store', { project: project.id }), form.value);
        alert('Tasks successfully added');
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <!-- Tasks Section -->
    <div class="space-y-8">
        <div class="flex items-center justify-end">
            <Button type="button" size="sm" class="bg-blue-800 text-white hover:bg-blue-800" @click="addTask"> Add Task </Button>
        </div>
        <div v-if="tasks.length == 0" class="mt-10 text-center">No tasks found</div>

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

    <div class="flex justify-end gap-x-2" v-if="tasks.length">
        <span class="mt-3 mr-12 inline-block text-sm text-muted-foreground" v-if="hasFormError || !formChanged">Edit a task to continue</span>
        <Button @click="addNewTasks" :disabled="hasFormError || !formChanged"> Create Tasks </Button>
    </div>
</template>
