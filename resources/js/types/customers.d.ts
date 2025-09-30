import { PaginationLink } from './pagination';
import { User } from './users';

export interface Customer {
    id: number;
    user_id: number;
    address: string;
    phone: string;
    user: User;
    created_at: string;
    updated_at: string;
}

export interface CustomerPagination {
    current_page: number;
    data: Customer[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
