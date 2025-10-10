export function formatCurrency(pence: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(pence / 100)
}

export function useCurrencyFormatter() {
    return { formatCurrency }
}
