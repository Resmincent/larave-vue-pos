import { PaginationLink } from './pagination';
import { Product } from './products';

export interface Tax {
    id: number;
    name: string;
    rate: number;
    products: Product[];
}

export interface TaxPagination {
    current_page: number;
    data: Tax[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
