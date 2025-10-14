import { Category } from './categories';
import { PaginationLink } from './pagination';
import { Tax } from './tax';

export interface Inventory {
    id: number;
    product_id: number;
    qty: number;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    sku: string;
    name: string;
    category_id: number | null;
    tax_id: number | null;
    sell_price: number;
    cost_price: number;
    unit: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at?: string | null;

    // relations
    category?: Category | null;
    tax?: Tax | null;
    inventory?: Inventory | null;
    stocks?: StockMovement[];
    saleItems?: SaleItem[];
    purchaseItems?: PurchaseItem[];
}

export type StockMovementType = 'SALE' | 'PURCHASE' | 'ADJUSTMENT_IN' | 'ADJUSTMENT_OUT' | 'RETURN_SALE' | 'RETURN_PURCHASE';

export interface StockMovement {
    id: number;
    product_id: number;
    qty_change: number;
    type: StockMovementType;
    source_type?: string | null;
    source_id?: number | null;
    note?: string | null;
    created_at: string;
}

export interface SaleItem {
    id: number;
    sale_id: number;
    product_id: number;
    qty: number;
    price: number;
    discount: number;
    tax_id: number | null;
    line_total: number;

    product: Product;
}

export interface PurchaseItem {
    id: number;
    purchase_id: number;
    product_id: number;
    qty: number;
    cost_price: number;
    discount: number;
    tax_id: number | null;
    line_total: number;

    // relations
    product?: Product;
    tax?: Tax;
    purchase?: any;
}

export interface ProductPagination {
    current_page: number;
    data: Product[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
