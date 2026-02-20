import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
        path: string;
        links: {
            url: string | null;
            page: number | null;
            label: string;
            active: boolean;
        }[];
    };
};

export const paginatedWithDefaults = <T>(): Paginated<T> => {
    return {
        data: [],
        links: {
            first: '',
            last: '',
            prev: null,
            next: '',
        },
        meta: {
            current_page: 1,
            from: 1,
            last_page: 1,
            per_page: 10,
            to: 10,
            total: 0,
            path: '',
            links: [],
        },
    };
};
