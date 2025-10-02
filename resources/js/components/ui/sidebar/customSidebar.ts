import {
  LayoutGrid,
  Box,
  Dock,
  WalletCards,
  SquareMenu,
  SquareUser,
  Receipt,
  Truck,
  Layers,
  CreditCard,
  Calculator,
  TicketPercent,
  User,
  IdCard,
} from "lucide-vue-next"

import { dashboard } from "@/routes"
import categories from "@/routes/categories"
import inventories from "@/routes/inventories"
import permissions from "@/routes/permissions"
import products from "@/routes/products"
import roles from "@/routes/roles"
import sale from "@/routes/sales"
import users from "@/routes/users"
import suppliers from "@/routes/suppliers"
import purchases from "@/routes/purchases"
import payments from "@/routes/payments"
import cashSessions from "@/routes/cash-sessions"
import customers from "@/routes/customers"

import type { NavItem } from "@/types"
import taxes from "@/routes/taxes"
import paymentMethods from "@/routes/payment-methods"

export const sidebarMenus: Record<string, { main: NavItem[]; footer: NavItem[] }> = {
  admin: {
    main: [
      { title: "Dashboard", href: dashboard(), icon: LayoutGrid },
      { title: "Products", href: products.index(), icon: Box },
      { title: "Inventory", href: inventories.index(), icon: Dock },
      { title: "Sales", href: sale.index(), icon: WalletCards },
      { title: "Purchases", href: purchases.index(), icon: Receipt },
      { title: "Suppliers", href: suppliers.index(), icon: Truck },
      { title: "Cash Sessions", href: cashSessions.index(), icon: Calculator },
      { title: "Payments", href: payments.index(), icon: CreditCard },
      { title: "Payment Method", href: paymentMethods.index(), icon: IdCard },
      { title: "Taxes", href: taxes.index(), icon:  TicketPercent},

       {
        title: "Roles & Permissions",
        children: [
          { title: "Roles", href: roles.index() },
          { title: "Permissions", href: permissions.index() },
        ],
      },
    ],
    footer: [
      { title: "Categories", href: categories.index(), icon: SquareMenu },
      { title: "Users", href: users.index(), icon: User },
      { title: "Customers", href: customers.index(), icon: SquareUser },
    ],
  },

  cashier: {
    main: [
      { title: "Dashboard", href: dashboard(), icon: LayoutGrid },
      { title: "Sales", href: sale.index(), icon: WalletCards },
      { title: "Cash Sessions", href: cashSessions.index(), icon: Calculator },
      { title: "Payments", href: payments.index(), icon: CreditCard },
    ],
    footer: [
      { title: "Customers", href: customers.index(), icon: SquareUser },
    ],
  },

  inventory: {
    main: [
      { title: "Dashboard", href: dashboard(), icon: LayoutGrid },
      { title: "Products", href: products.index(), icon: Box },
      { title: "Inventory", href: inventories.index(), icon: Dock },
      { title: "Purchases", href: purchases.index(), icon: Receipt },
      { title: "Suppliers", href: suppliers.index(), icon: Truck },
      { title: "Categories", href: categories.index(), icon: Layers },
    ],
    footer: [],
  },

  customer: {
    main: [
      { title: "Dashboard", href: dashboard(), icon: LayoutGrid },
      { title: "My Sales", href: sale.index(), icon: WalletCards },
    ],
    footer: [],
  },

  supplier: {
    main: [
      { title: "Dashboard", href: dashboard(), icon: LayoutGrid },
      { title: "Purchase Orders", href: purchases.index(), icon: Receipt },
    ],
    footer: [],
  },
}
