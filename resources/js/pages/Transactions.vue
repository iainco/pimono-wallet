<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem, User } from '@/types'
import CreateTransaction from '@/components/transactions/CreateTransaction.vue'
import { PiggyBank } from 'lucide-vue-next'
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
import { useEcho } from '@laravel/echo-vue'
import { useCurrencyFormatter } from '@/composables/useCurrencyFormatter'
import { useDateTimeFormatter } from '@/composables/useDateTimeFormatter'
import { ref } from 'vue';

const { formatDateTime } = useDateTimeFormatter()
const { formatCurrency } = useCurrencyFormatter()

interface Transaction {
    id: number
    sender: User
    receiver: User
    amount: number
    commission_fee: number
    created_at: string
}

interface TransactionCreatedEvent {
    transaction: Transaction
}

interface Props {
    transactions: Transaction[]
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: transactionsIndex().url,
    },
]

const page = usePage()
const user = page.props.auth.user
const balance = ref(page.props.auth.user.balance)
const transactions = ref(props.transactions)

useEcho<TransactionCreatedEvent>(
    `App.Models.User.${user.id}`,
    'TransactionCreated',
    (e) => {
        if (e.transaction.sender.id === user.id) {
            balance.value -= e.transaction.amount + e.transaction.commission_fee
        }

        if (e.transaction.receiver.id === user.id) {
            balance.value += e.transaction.amount
        }

        transactions.value.unshift(e.transaction)
    }
)
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-12">
            <div class="rounded-xl border bg-card text-card-foreground shadow">
                <div class="p-6 pb-2 flex items-center space-x-1">
                    <PiggyBank class="text-gray-400" />
                    <h3 class="tracking-tight text-sm font-medium">Your Balance</h3>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">{{ formatCurrency(balance) }}</div>
                    <p class="text-xs text-muted-foreground">Last updated {{ formatDateTime(user.updated_at) }}</p>
                </div>
            </div>

            <div class="rounded-xl border bg-card text-card-foreground shadow">
                <Table>
                    <TableCaption class="pb-4">A list of your recent transactions.</TableCaption>
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
                            <TableCell class="text-right text-gray-600">{{ formatCurrency(transaction.commission_fee) }}</TableCell>
                            <TableCell class="text-right">{{ formatDateTime(transaction.created_at) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex justify-end">
                <CreateTransaction :balance="balance" />
            </div>
        </div>
    </AppLayout>
</template>
