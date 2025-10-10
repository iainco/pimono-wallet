<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogClose,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Form, usePage } from '@inertiajs/vue3'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldInput,
} from '@/components/ui/number-field'
import { store } from '@/routes/transactions'
import { useCurrencyFormatter } from '@/composables/useCurrencyFormatter'
import { computed } from 'vue'
import { Ban, CheckCircle, LoaderCircle } from 'lucide-vue-next'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'

const { formatCurrency } = useCurrencyFormatter()

interface Props {
    balance: number
}

const props = defineProps<Props>()
const page = usePage()
const maxAmount = computed(() =>
    Math.round(props.balance / (1 + page.props.commissionRate)),
)
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button class="max-w-min">Create Transaction</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <Form
                id="create-transaction"
                :action="store()"
                method="post"
                class="space-y-6"
                #default="{
                    errors,
                    processing,
                    recentlySuccessful,
                }"
                disable-while-processing
                :options="{
                    preserveScroll: true,
                    except: [
                        'auth',
                        'transactions',
                    ],
                }"
            >
                <DialogHeader>
                    <DialogTitle>Create Transaction</DialogTitle>
                    <DialogDescription>
                        Send money to a recipient by providing their e-mail
                        address and an amount.
                    </DialogDescription>
                </DialogHeader>

                <Alert v-if="recentlySuccessful" class="bg-green-50 border-green-100">
                    <CheckCircle class="size-4" />
                    <AlertTitle>Thank you</AlertTitle>
                    <AlertDescription>Your transaction is processing and you will be notified shortly.</AlertDescription>
                </Alert>

                <Alert v-if="balance === 0" variant="destructive" class="bg-red-50 border-red-100">
                    <Ban class="size-4" />
                    <AlertTitle>Sorry</AlertTitle>
                    <AlertDescription>You are currently unable to perform any transactions due to insufficient funds.</AlertDescription>
                </Alert>

                <div class="space-y-2">
                    <Label for="email">Recipient E-mail</Label>

                    <Input
                        id="email"
                        name="email"
                        type="email"
                    />

                    <div v-if="errors.email" class="text-sm text-red-600">
                        {{ errors.email }}
                    </div>
                </div>

                <div class="space-y-1">
                    <NumberField
                        id="amount"
                        name="amount"
                        :default-value="1"
                        :min="0.01"
                        :max="maxAmount / 100"
                        :step="0.01"
                        :format-options="{
                            style: 'currency',
                            currency: 'GBP',
                            currencyDisplay: 'symbol',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }"
                    >
                        <Label for="amount">Amount</Label>
                        <NumberFieldContent>
                            <NumberFieldInput />
                        </NumberFieldContent>
                    </NumberField>

                    <div v-if="balance > 0" class="text-xs text-gray-700">
                        Value must be between {{ formatCurrency(1) }} and {{ formatCurrency(maxAmount) }} (inclusive)
                    </div>

                    <div v-if="errors.amount" class="text-sm text-red-600">
                        {{ errors.amount }}
                    </div>
                </div>

                <DialogFooter class="justify-end">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">Close</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="balance === 0 || processing">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        Submit
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
