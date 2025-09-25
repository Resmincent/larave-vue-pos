import type { PaginationLink } from './pagination';

export interface Category {
    id: number;
    name: string;
    parent?: {
        id: number;
        name: string;
    } | null;
    created_at: string;
    updated_at: string;
}

export interface CategoryPagination {
    current_page: number;
    data: Category[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
