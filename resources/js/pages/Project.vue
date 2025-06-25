<script setup lang="ts">
import ProjectCard from '@/components/ProjectCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
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
];

// Get initial filter values from props
const page = usePage();
const initialFilters = computed(() => page.props.filters || { status: '', due_date: '' });

// Filter states
const status = ref(initialFilters.value.status || '');
const dueDate = ref(initialFilters.value.due_date || '');

// Status options
// const statusOptions = [
//     { value: '', label: 'All Statuses' },
//     { value: 'pending', label: 'Pending' },
//     { value: 'in_progress', label: 'In Progress' },
//     { value: 'done', label: 'Done' },
// ];

// Apply filters when they change
watch([status, dueDate], () => {
    router.get(
        route('projects'),
        {
            status: status.value,
            due_date: dueDate.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const form = useForm({
    title: '',
    description: '',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="title">Project Title</Label>
                    <Input id="title" v-model="form.title" required />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="4" />
                </div>

                <div class="flex justify-end gap-x-2">
                    <Button type="button" variant="outline" :href="route('projects')"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing"> Create Project </Button>
                </div>
            </form>

            <div>Project's Tasks</div>

            <div class="mb-8 flex justify-between">
                <div class="flex gap-4">
                    <!-- Status Filter -->
                    <div class="w-48">
                        <Label for="status">Filter by status</Label>
                        <!-- <Select v-model="status">
                            <SelectTrigger>
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select> -->
                    </div>

                    <!-- Due Date Filter -->
                    <div class="w-48">
                        <Label for="dueDate">Filter by Due Date</Label>
                        <Input id="dueDate" v-model="dueDate" type="date" class="w-full" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-y-8">
                <ProjectCard />
                <ProjectCard />
                <ProjectCard />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
table {
    width: 100%;
}
</style>
