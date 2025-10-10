export function formatDateTime(dateTime: string): string {
    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(new Date(dateTime))
}

export function useDateTimeFormatter() {
    return { formatDateTime }
}
