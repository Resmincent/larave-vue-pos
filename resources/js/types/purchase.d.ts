import { User } from '.';
import { PaginationLink } from './pagination';
import { PurchaseItem } from './products';

export type statusPurchaseType = 'DRAFT' | 'RECEIVED' | 'CANCELLED';

export interface Purchase {
    id: number;
    supplier_id: number;
    user_id: number;
    code: string;
    status: statusPurchaseType;
    subtotal: number;
    discount_total: number;
    tax_total: number;
    grand_total: number;
    created_at: string;
    updated_at: string;
    received_at?: string | null;
    note?: string | null;

    supplier: Supplier;
    user: User;
    purchaseItems: PurchaseItem[];
}

export type CreatePurchasePayload = {
    supplier_id: number | null;
    code: string;
    status: statusPurchaseType;
    note: string | null;
    items: Array<{
        product_id: number | null;
        qty: number;
        cost_price: number;
        discount: number;
        tax_id: number | null;
    }>;
};

export interface PurchasePagination {
    current_page: number;
    data: Purchase[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
