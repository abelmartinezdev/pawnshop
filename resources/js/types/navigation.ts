import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string
    href: string
    icon?: any
    badge?: string | number
    permission?: string // ej "branches.manage"
    children?: NavItem[]
}
