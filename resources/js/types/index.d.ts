import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type Project = {
    id: number;
    title: string;
    description?: string;
    deadline?: string;
    creator: User;
    tasks_count: number;
};

type Assignee = {
    id: number;
    name: string;
};

type TaskType = {
    id: number;
    project_id?: number;
    title: string;
    assignee_id: number | null;
    status: string;
    due_date: string;
    completed_at?: string;
};

export type BreadcrumbItemType = BreadcrumbItem;
