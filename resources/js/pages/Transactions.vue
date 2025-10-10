<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    BreadcrumbItem,
    PaginatedResponse,
    Transaction,
    TransactionCreatedEvent,
} from '@/types'
import CreateTransaction from '@/components/transactions/CreateTransaction.vue'
import { PiggyBank } from 'lucide-vue-next'
import { Toaster } from '@/components/ui/sonner'
import 'vue-sonner/style.css'
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
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import Pagination from '@/components/ui/Pagination.vue'

const { formatDateTime } = useDateTimeFormatter()
const { formatCurrency } = useCurrencyFormatter()

interface Props {
    transactions: PaginatedResponse<Transaction>
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
const lastUpdatedAt = ref(page.props.auth.user.updated_at)

useEcho<TransactionCreatedEvent>(
    `App.Models.User.${user.id}`,
    'TransactionCreated',
    (e) => {
        const { sender, receiver, amount, commission_fee } = e.transaction
        const isSender = sender.id === user.id
        const isReceiver = receiver.id === user.id

        if (isSender) {
            balance.value -= amount + commission_fee
            toast(`You sent ${formatCurrency(amount)} to ${receiver.name}`, {
                description: 'The transaction completed successfully.',
            })
        }

        if (isReceiver) {
            balance.value += amount
            toast(`You received ${formatCurrency(amount)} from ${sender.name}`, {
                description: 'The transaction completed successfully.',
            })
        }

        transactions.value.data.unshift(e.transaction)
        lastUpdatedAt.value = e.transaction.created_at
    },
)
</script>

<template>
    <Toaster />

    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-8">
            <div class="rounded-xl border bg-card text-card-foreground shadow">
                <div class="flex items-center space-x-1 p-6 pb-2">
                    <PiggyBank class="text-gray-400" />
                    <h3 class="text-sm font-medium tracking-tight">
                        Your Balance
                    </h3>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(balance) }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Last updated {{ formatDateTime(lastUpdatedAt) }}
                    </p>
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
                        <TableRow v-for="transaction in transactions.data" :key="transaction.id">
                            <TableCell class="font-medium">{{ transaction.id }}</TableCell>
                            <TableCell>{{ transaction.sender.name }}</TableCell>
                            <TableCell>{{ transaction.receiver.name }}</TableCell>
                            <TableCell class="text-right">{{ formatCurrency(transaction.amount) }}</TableCell>
                            <TableCell class="text-right text-gray-600">{{ formatCurrency(transaction.commission_fee) }}</TableCell>
                            <TableCell class="text-right">{{ formatDateTime(transaction.created_at) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination class="mt-6" :links="transactions.links" />
            </div>

            <div class="flex justify-end">
                <CreateTransaction :balance="balance" />
            </div>
        </div>
    </AppLayout>
</template>
