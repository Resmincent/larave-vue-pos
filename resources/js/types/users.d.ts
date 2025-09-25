import { PaginationLink } from './pagination';
import { Role } from './role';

export interface User {
    id: number;
    custom_id: string;
    name: string;
    email: string;
    password?: string;
    roles: Role[];
    created_at: string;
    updated_at: string;
}

export interface UserPagination {
    current_page: number;
    data: User[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
