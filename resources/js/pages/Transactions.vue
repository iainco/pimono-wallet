<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem, User } from '@/types'
import { transactions as transactionsIndex } from '@/routes'
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import CreateTransaction from '@/components/transactions/CreateTransaction.vue'
import { useEcho } from '@laravel/echo-vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: transactionsIndex().url,
    },
]

interface Transaction {
    id: number
    sender: User
    receiver: User
    amount: number
    commission_fee: number
    created_at: string
}

interface Props {
    transactions: Transaction[]
}

defineProps<Props>()

const formatCurrency = (amount: number): string =>
    new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(amount / 100)

const formatDateTime = (date: string): string =>
    new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(new Date(date))

const page = usePage()
const user = page.props.auth.user
useEcho(
    `App.Models.User.${user.id}`,
    'TransactionCreated',
    (e) => {
        console.log(e)
    },
)
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Table>
            <TableCaption>A list of your recent transactions.</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[100px]">Transaction</TableHead>
                    <TableHead>Sender</TableHead>
                    <TableHead>Receiver</TableHead>
                    <TableHead class="text-right">Amount</TableHead>
                    <TableHead class="text-right">Commission</TableHead>
                    <TableHead class="text-right">Date &amp; Time</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="transaction in transactions"
                    :key="transaction.id"
                >
                    <TableCell class="font-medium">{{ transaction.id }}</TableCell>
                    <TableCell>{{ transaction.sender.name }}</TableCell>
                    <TableCell>{{ transaction.receiver.name }}</TableCell>
                    <TableCell class="text-right">{{ formatCurrency(transaction.amount) }}</TableCell>
                    <TableCell class="text-right text-gray-500">{{ formatCurrency(transaction.commission_fee) }}</TableCell>
                    <TableCell class="text-right">{{ formatDateTime(transaction.created_at) }}</TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div class="mt-8 mr-8 flex justify-end">
            <CreateTransaction />
        </div>
    </AppLayout>
</template>
