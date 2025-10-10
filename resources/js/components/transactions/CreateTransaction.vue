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
import { Form } from '@inertiajs/vue3'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldInput,
} from '@/components/ui/number-field'
import { store } from '@/routes/transactions'
import { useCurrencyFormatter } from '@/composables/useCurrencyFormatter'
import { computed } from 'vue';

const { formatCurrency } = useCurrencyFormatter()

interface Props {
    balance: number
}

const props = defineProps<Props>()
const maxAmount = computed(() => Math.floor(props.balance / 1.015)) // TODO: don't hardcode commission rate
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button class="max-w-min">Create Transaction</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Create Transaction</DialogTitle>
                <DialogDescription>
                    Send money to a recipient by providing their e-mail address and an amount
                </DialogDescription>
            </DialogHeader>

            <Form
                id="create-transaction"
                :action="store()"
                method="post"
                class="space-y-6"
                #default="{ errors }"
                disable-while-processing
                :options="{
                    preserveScroll: true,
                    except: ['auth', 'transactions'],
                }"
            >
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
                        :min="1"
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

                    <div class="text-xs text-gray-700">
                        Value must be between £1.00 and {{ formatCurrency(maxAmount) }} (inclusive)
                    </div>

                    <div v-if="errors.amount" class="text-sm text-red-600">
                        {{ errors.amount }}
                    </div>
                </div>
            </Form>

            <DialogFooter class="justify-end">
                <DialogClose as-child>
                    <Button type="button" variant="secondary">Close</Button>
                </DialogClose>
                <Button type="submit" form="create-transaction">Submit</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
