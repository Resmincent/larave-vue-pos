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

    subtotal: number;
    discount_total: number;
    tax_total: number;
    grand_total: number;
    paid_total: number;
    change_due: number;

    sold_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;
    note?: string | null;

    customer?: Customer;
    user?: User;
    sale_items: SaleItem[];
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

export type CreateSalePayload = {
    customer_id: number | null;
    code: string;
    status: StatusType;
    note: string | null;
    items: Array<{
        product_id: number | null;
        qty: number;
        sell_price: number;
        discount: number;
        tax_id: number | null;
        is_active: boolean;
    }>;
    payments: Array<{
        payment_method_id: number | null;
        amount: number;
        note: string | null;
    }>;
};
