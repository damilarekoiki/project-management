<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Assignee, TaskType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { defineEmits, defineProps, onMounted, ref, watch } from 'vue';
import InputError from './InputError.vue';

const props = defineProps<{
    initialTask: TaskType;
    showRemoveButton: boolean;
    isEdit: boolean;
}>();

const page = usePage();

const user = page.props.auth.user;

const emit = defineEmits(['removeTask', 'addTaskToForm', 'updateFormState']);

// Task status options
const taskStatusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'done', label: 'Done' },
];

const assigneeSearch = ref<string>('');

const searchResult = ref<Assignee[]>([]);

const assignee = ref<Assignee | null>();

const today = new Date().toISOString().split('T')[0];

const task = ref<TaskType>({
    id: 0,
    title: '',
    assignee_id: null,
    status: 'pending',
    due_date: '',
});

const titleError = ref('');

// Remove a task from the form
const removeTask = async (taskId: number | undefined): Promise<void> => {
    if (taskId !== undefined) {
        if (props.isEdit) {
            const confirmed = window.confirm('The task will be permantly deleted');
            if (confirmed) {
                try {
                    await axios.delete(
                        route('api.projects.tasks.delete', {
                            project: route().params.project,
                            task: taskId,
                        }),
                    );
                } catch (error) {
                    console.error(error);
                }
            }
        }
        emit('removeTask', taskId);
    }
};

const searchUsers = async (query: string): Promise<void> => {
    if (!query || query.length == 0) {
        searchResult.value = [];
        return;
    }

    try {
        const response = await axios.get('/api/users/search', {
            params: { query },
        });
        searchResult.value = response.data;
    } catch (error) {
        console.error('Error searching users:', error);
        searchResult.value = [];
    }
};

// Select a user from search results
const selectAssignee = (user: Assignee): void => {
    task.value.assignee_id = user.id;
    assignee.value = user;
    assigneeSearch.value = user.name;
    searchResult.value = [];
};

const removeAssignee = () => {
    task.value.assignee_id = null;
    assigneeSearch.value = '';
    assignee.value = null;
};

watch(assigneeSearch, (newValue: string | undefined) => {
    if (newValue !== undefined && assigneeSearch.value !== assignee.value?.name) {
        searchUsers(newValue);
    }
});

const checkTaskValue = (newValue: any, oldValue: any) => {
    if (newValue == oldValue) {
        emit('updateFormState', false, task.value.id);
        return;
    }
    emit('addTaskToForm', task.value, task.value.id);
    emit('updateFormState', true, task.value.id);
};

watch(
    () => task.value.title,
    () => {
        if (task.value.title == '') {
            titleError.value = 'The title field is required';
        }
        checkTaskValue(task.value.title, props.initialTask.title);
    },
);

watch(
    () => task.value.assignee_id,
    () => {
        checkTaskValue(task.value.assignee_id, props.initialTask.assignee_id);
    },
);

watch(
    () => task.value.status,
    () => {
        checkTaskValue(task.value.status, props.initialTask.status);
    },
);

watch(
    () => task.value.due_date,
    () => {
        checkTaskValue(task.value.due_date, props.initialTask.due_date);
    },
);

onMounted(() => {
    task.value = JSON.parse(JSON.stringify(props.initialTask));
    assigneeSearch.value = task.value.assignee?.name ?? '';
    assignee.value = task.value.assignee;
});
</script>

<template>
    <div class="space-y-8 rounded-lg border p-4">
        <div class="flex items-center justify-end" v-if="user.role == 'admin'">
            <Button type="button" variant="destructive" size="sm" @click="removeTask(task.id)" v-if="showRemoveButton"> Remove </Button>
        </div>

        <div class="space-y-2" :class="{ 'opacity-35': user.role == 'non_admin' }">
            <Label :for="`${task.id}-title`">Title</Label>
            <Input :id="`${task.id}-title`" v-model="task.title" placeholder="Enter a title for the task" :readonly="user.role == 'non_admin'" />
            <InputError :message="titleError" v-if="task.title == ''" />
        </div>

        <div class="space-y-2">
            <Label :for="`${task.id}-status`">Status</Label>
            <select
                :id="`${task.id}-status`"
                v-model="task.status"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            >
                <option v-for="option in taskStatusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div class="space-y-2" :class="{ 'opacity-40': user.role == 'non_admin' }">
            <Label :for="`${task.id}-due-date`">Due Date</Label>
            <Input
                :id="`${task.id}-due-date`"
                v-model="task.due_date"
                type="date"
                :min="today"
                class="!bg-white text-black"
                :readonly="user.role == 'non_admin'"
            />
        </div>

        <div class="space-y-2" :class="{ 'opacity-40': user.role == 'non_admin' }">
            <Label :for="`${task.id}-assignee`">Assignee</Label>
            <div class="relative">
                <Input
                    :id="`${task.id}-assignee`"
                    v-model="assigneeSearch"
                    placeholder="Search for user..."
                    autocomplete="off"
                    :readonly="user.role == 'non_admin'"
                />

                <!-- Search Results -->
                <div
                    v-if="searchResult && searchResult.length > 0"
                    class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white text-gray-900 shadow-lg"
                >
                    <div class="space-y-1 p-2">
                        <div
                            v-for="user in searchResult"
                            :key="user.id"
                            class="flex cursor-pointer items-center space-x-2 rounded p-2"
                            @click="selectAssignee(user)"
                        >
                            <input
                                type="radio"
                                :name="`assignee-${task.id}`"
                                :id="`assignee-${task.id}-${user.id}`"
                                :checked="task.assignee_id === user.id"
                                class="h-4 w-4"
                            />
                            <label :for="`assignee-${task.id}-${user.id}`" class="flex-grow cursor-pointer">
                                {{ user.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Selected User Display -->
                <div v-if="task.assignee_id !== null && !searchResult.length" class="mt-2 text-sm">
                    <span class="font-medium">Selected: </span>
                    <span>{{ assignee?.name }}</span>
                    <button
                        type="button"
                        class="ml-2 text-red-500 hover:text-red-700"
                        @click="
                            () => {
                                removeAssignee;
                            }
                        "
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
