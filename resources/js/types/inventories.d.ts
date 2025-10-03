import { PaginationLink } from './pagination';
import { Product } from './products';

export interface InventoryPagination {
    current_page: number;
    data: Product[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
