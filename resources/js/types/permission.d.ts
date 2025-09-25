import type { PaginationLink } from './pagination';
export interface Permission {
    id: number;
    name: string;
}

export interface PermissionPagination {
    current_page: number;
    data: Role[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
