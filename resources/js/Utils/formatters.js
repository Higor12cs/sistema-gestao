export function formatCurrency(value) {
    if (value === null || value === undefined) return 'R$ 0,00';

    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

export function formatNumber(value, decimals = 0) {
    if (value === null || value === undefined) return '0';

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(value);
}

export function formatDate(date) {
    if (!date) return '';

    const d = new Date(date);
    return d.toLocaleDateString('pt-BR');
}

export function formatPercent(value) {
    if (value === null || value === undefined) return '0%';

    return new Intl.NumberFormat('pt-BR', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
}
