declare module 'vue3-easy-data-table' {
    import type { DefineComponent } from 'vue';

    export interface DataTableHeader {
        text: string;
        value: string;
        sortable?: boolean;
        width?: number | string;
    }

    export type DataTableItem = Record<string, any>;

    const component: DefineComponent<
        {
            headers: DataTableHeader[];
            items: DataTableItem[];
            rowsPerPage?: number;
            showIndex?: boolean;
            buttonsPagination?: boolean;
        },
        object,
        any
    >;

    export default component;
}
