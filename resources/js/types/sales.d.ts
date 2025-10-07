// types/sales.ts
import { User } from '.';
import { Customer } from './customers';
import { PaginationLink } from './pagination';
import { SaleItem } from './products';

export type StatusType = 'OPEN' | 'PAID' | 'VOID';

export interface Sale {
    id: number;
    code: string;
    customer_id: number | null;
    user_id: number;
    status: StatusType;

    subtotal: string;
    discount_total: string;
    tax_total: string;
    grand_total: string;
    paid_total: string;
    change_due: string;

    sold_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    note?: string | null;

    customer?: Customer | null;
    user?: User | null;
    saleItems: SaleItem[];
    payments: Payment[];
}

export interface Payment {
    id: number;
    sale_id: number | null;
    purchase_id: number | null;
    payment_method_id: number;
    amount: string;
    paid_at: string;
    cash_session_id: number | null;
    note: string | null;

    payment_method?: PaymentMethod;
}

export interface PaymentMethod {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
}

export interface PaymentMethodPagination {
    current_page: number;
    data: PaymentMethod[];
    from: number | null;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

export interface SalePagination {
    current_page: number;
    data: Sale[];
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
    links: PaginationLink[];
}
