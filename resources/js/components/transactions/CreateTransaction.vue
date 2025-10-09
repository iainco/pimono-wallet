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
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput
} from '@/components/ui/number-field'
import { store } from '@/routes/transactions'
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
                #default="{
                    errors,
                }"
            >
                <div class="space-y-2">
                    <Label for="email">Recipient E-mail</Label>
                    <Input id="email" type="email" name="email" />
                    <div v-if="errors.email" class="text-red-600 text-sm">
                        {{ errors.email }}
                    </div>
                </div>

                <NumberField
                    id="amount"
                    name="amount"
                    :default-value="100"
                    :min="1"
                    :max="999999"
                >
                    <Label for="amount">Amount</Label>
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <NumberFieldInput />
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                    <div v-if="errors.amount" class="text-red-600 text-sm">
                        {{ errors.amount }}
                    </div>
                </NumberField>
            </Form>

            <DialogFooter class="sm:justify-start">
                <DialogClose as-child>
                    <Button type="button" variant="secondary">Close</Button>
                </DialogClose>
                <Button type="submit" form="create-transaction">Submit</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
