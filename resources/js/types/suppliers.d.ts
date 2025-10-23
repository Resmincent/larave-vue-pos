import { User } from '.';
import { PaginationLink } from './pagination';

export interface Supplier {
    id: number;
    user_id: number;
    phone: string;
    is_active: boolean;
    address: string;
    user: User;
    created_at: string;
    updated_at: string;
}

export interface SupplierPagination {
    current_page: number;
    data: Supplier[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
