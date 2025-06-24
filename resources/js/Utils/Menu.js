export const sidebarItems = [
    // Início
    {
        type: "header",
        label: "Início",
        items: [
            {
                type: "link",
                routeName: "home.index",
                iconClass: "fas fa-home",
                label: "Home",
            },
            {
                type: "link",
                routeName: "dashboard.index",
                iconClass: "fas fa-tachometer-alt",
                label: "Dashboard",
                permission: "dashboard.index",
            },
        ],
    },

    // Cadastros
    {
        type: "header",
        label: "Cadastros",
        items: [
            {
                type: "link",
                routeName: "customers.index",
                iconClass: "fas fa-users",
                label: "Clientes",
                permission: "customers.index",
            },
            {
                type: "link",
                routeName: "suppliers.index",
                iconClass: "fas fa-users",
                label: "Fornecedores",
                permission: "suppliers.index",
            },
            {
                type: "link",
                routeName: "products.index",
                iconClass: "fas fa-box",
                label: "Produtos",
                permission: "products.index",
            },
            {
                type: "collapsible",
                iconClass: "fas fa-tags",
                label: "Atributos",
                subItems: [
                    {
                        type: "link",
                        routeName: "sections.index",
                        iconClass: "fas fa-circle",
                        label: "Seções",
                        permission: "sections.index",
                    },
                    {
                        type: "link",
                        routeName: "groups.index",
                        iconClass: "fas fa-circle",
                        label: "Grupos",
                        permission: "groups.index",
                    },
                    {
                        type: "link",
                        routeName: "brands.index",
                        iconClass: "fas fa-circle",
                        label: "Marcas",
                        permission: "brands.index",
                    },
                ],
            },
        ],
    },

    // Operações
    {
        type: "header",
        label: "Operações",
        items: [
            {
                type: "link",
                routeName: "pos.index",
                iconClass: "fas fa-cash-register",
                label: "PDV",
            },
            {
                type: "collapsible",
                iconClass: "fas fa-shopping-basket",
                label: "Pedidos",
                subItems: [
                    {
                        type: "link",
                        routeName: "orders.create",
                        iconClass: "fas fa-circle",
                        label: "Novo Pedido",
                        permission: "orders.create",
                    },
                    {
                        type: "link",
                        routeName: "orders.index",
                        iconClass: "fas fa-circle",
                        label: "Listar Pedidos",
                        permission: "orders.index",
                    },
                ],
            },
            {
                type: "collapsible",
                iconClass: "fas fa-truck-loading",
                label: "Compras",
                subItems: [
                    {
                        type: "link",
                        routeName: "purchases.create",
                        iconClass: "fas fa-circle",
                        label: "Nova Compra",
                        permission: "purchases.create",
                    },
                    {
                        type: "link",
                        routeName: "purchases.index",
                        iconClass: "fas fa-circle",
                        label: "Listar Compras",
                        permission: "purchases.index",
                    },
                ],
            },
        ],
    },

    // Estoque
    {
        type: "header",
        label: "Estoque",
        items: [
            {
                type: "link",
                routeName: "stocks.index",
                iconClass: "fas fa-boxes",
                label: "Estoque",
                permission: "stocks.index",
            },
            {
                type: "link",
                routeName: "kardex.index",
                iconClass: "fas fa-dolly",
                label: "Kardex",
                permission: "kardex.index",
            },
        ],
    },

    // Financeiro
    {
        type: "header",
        label: "Financeiro",
        items: [
            {
                type: "link",
                routeName: "receivables.index",
                iconClass: "fas fa-arrow-down",
                label: "Recebíveis",
                permission: "receivables.index",
            },
            {
                type: "link",
                routeName: "payables.index",
                iconClass: "fas fa-arrow-up",
                label: "Pagáveis",
                permission: "payables.index",
            },
            {
                type: "link",
                routeName: "cash-flow.index",
                iconClass: "fas fa-chart-area",
                label: "Fluxo de Caixa",
                permission: "cash-flow.index",
            },
            {
                type: "collapsible",
                iconClass: "fas fa-university",
                label: "Contas Bancárias",
                subItems: [
                    {
                        type: "link",
                        routeName: "accounts.index",
                        iconClass: "fas fa-circle",
                        label: "Contas",
                        permission: "accounts.index",
                    },
                    {
                        type: "link",
                        routeName: "account-transfers.index",
                        iconClass: "fas fa-circle",
                        label: "Transferências",
                        permission: "accounts.index",
                    },
                    {
                        type: "link",
                        routeName: "account-reconciliation.select",
                        iconClass: "fas fa-circle",
                        label: "Conciliação Bancária",
                        permission: "accounts.index",
                    },
                ],
            },
            {
                type: "collapsible",
                iconClass: "fas fa-cog",
                label: "Configurações",
                subItems: [
                    {
                        type: "link",
                        routeName: "payment-methods.index",
                        iconClass: "fas fa-circle",
                        label: "Métodos de Pagamento",
                        permission: "payment-methods.index",
                    },
                    {
                        type: "link",
                        routeName: "chart-accounts.index",
                        iconClass: "fas fa-circle",
                        label: "Planos de Contas",
                        permission: "chart-accounts.index",
                    },
                ],
            },
        ],
    },

    // Relatórios
    {
        type: "header",
        label: "Relatórios",
        items: [
            {
                type: "collapsible",
                iconClass: "fas fa-print",
                label: "Relatórios",
                subItems: [
                    {
                        type: "collapsible",
                        iconClass: "fas fa-circle",
                        label: "Pedidos",
                        permission: "reports.orders.index",
                        subItems: [
                            {
                                type: "link",
                                routeName: "reports.orders.analyticals",
                                iconClass: "far fa-circle",
                                label: "Relatório Analítico",
                                permission: "reports.orders.analyticals",
                            },
                            {
                                type: "link",
                                routeName: "reports.orders.synthetics",
                                iconClass: "far fa-circle",
                                label: "Relatório Sintético",
                                permission: "reports.orders.synthetics",
                            },
                        ],
                    },
                    {
                        type: "collapsible",
                        iconClass: "fas fa-circle",
                        label: "Recebíveis",
                        permission: "reports.receivables.index",
                        subItems: [
                            {
                                type: "link",
                                routeName: "reports.receivables.analyticals",
                                iconClass: "far fa-circle",
                                label: "Relatório Analítico",
                                permission: "reports.receivables.analyticals",
                            },
                            {
                                type: "link",
                                routeName: "reports.receivables.synthetics",
                                iconClass: "far fa-circle",
                                label: "Relatório Sintético",
                                permission: "reports.receivables.synthetics",
                            },
                        ],
                    },
                    {
                        type: "collapsible",
                        iconClass: "fas fa-circle",
                        label: "Pagáveis",
                        permission: "reports.payables.index",
                        subItems: [
                            {
                                type: "link",
                                routeName: "reports.payables.analyticals",
                                iconClass: "far fa-circle",
                                label: "Relatório Analítico",
                                permission: "reports.payables.analyticals",
                            },
                            {
                                type: "link",
                                routeName: "reports.payables.synthetics",
                                iconClass: "far fa-circle",
                                label: "Relatório Sintético",
                                permission: "reports.payables.synthetics",
                            },
                        ],
                    },
                    {
                        type: "collapsible",
                        iconClass: "fas fa-circle",
                        label: "Curva ABC",
                        permission: "reports.abc.index",
                        subItems: [
                            {
                                type: "link",
                                routeName: "reports.abc.customers",
                                iconClass: "far fa-circle",
                                label: "Clientes",
                                permission: "reports.abc.customers",
                            },
                            {
                                type: "link",
                                routeName: "reports.abc.products",
                                iconClass: "far fa-circle",
                                label: "Produtos",
                                permission: "reports.abc.products",
                            },
                        ],
                    },
                ],
            },
        ],
    },

    // Configurações
    {
        type: "header",
        label: "Configurações",
        items: [
            {
                type: "link",
                routeName: "users.index",
                iconClass: "fas fa-users",
                label: "Usuários",
                permission: "users.index",
            },
            {
                type: "link",
                routeName: "sellers.index",
                iconClass: "fas fa-users",
                label: "Vendedores",
                permission: "sellers.index",
            },
            {
                type: "link",
                routeName: "roles.index",
                iconClass: "fas fa-user-lock",
                label: "Papéis",
                permission: "roles.index",
            },
            // {
            //     type: "link",
            //     routeName: "configurations.index",
            //     iconClass: "fas fa-cogs",
            //     label: "Configurações",
            //     permission: "configurations.index",
            // }
        ],
    },
];

export const hasPermission = (permission, page) => {
    if (!permission) return true;

    const userRoles = page.props.auth.roles || [];
    const userPermissions = page.props.auth.permissions || [];

    if (userRoles.includes("Administrador")) return true;

    return userPermissions.includes(permission);
};
