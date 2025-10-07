import { User } from '.';
import { Customer } from './customers';
import { PaginationLink } from './pagination';
import { SaleItem } from './products';

export interface Sale {
    id: number;
    customer_id: number;
    status: string;
    subtotal: StatusType;
    discount_total: string;
    tax_total: string;
    grand_total: string;
    paid_total: string;
    change_due: string;
    sold_at: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    note?: string | null;

    customer: Customer | null;
    user: User | null;
    saleItems: SaleItem[];
}

export type StatusType = 'OPEN' | 'PAID' | 'VOID';

export interface SalePagination {
    current_page: number;
    data: Sale[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
