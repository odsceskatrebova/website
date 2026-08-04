<script setup lang="ts">
import { ref, toRefs } from 'vue'
import { useI18n } from 'vue-i18n'
import { useForm, Field } from 'vee-validate'
import { useToastStore } from '@common/vue/stores/ToastStore'
import type {
    FormBlockConfig,
    FormField,
    InputFormField,
    ValidationRule,
} from '@common/vue/types/form'

const props = defineProps({
    config: { type: Object as () => FormBlockConfig, required: true },
})

const { config } = toRefs(props)

const { t } = useI18n()
const toastStore = useToastStore()

const loading = ref(false)
const success = ref(false)
const honeypot = ref('')

function isInputField(block: FormField): block is InputFormField {
    return block.type !== 'display-text' && block.type !== 'display-header'
}

const initialValues = Object.fromEntries(
    (config.value.formTemplate.formBlocks ?? [])
        .filter(isInputField)
        .map((b) => {
            const val = b.type === 'checkbox-input' ? (b.defaultValue ?? false) : (b.defaultValue ?? '')
            return [b.key, val]
        }),
)

const { handleSubmit } = useForm({ initialValues })

function makeRules(validations: ValidationRule[] | null | undefined) {
    return (value: unknown): true | string => {
        for (const rule of (validations ?? [])) {
            if (rule === 'required' && (value === null || value === undefined || value === '' || value === false)) {
                return t('formBlock.validation.required')
            }
            if (rule === 'email' && value !== '' && value != null && !String(value).match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                return t('formBlock.validation.email')
            }
            if (rule === 'numeric' && value !== '' && value != null && isNaN(Number(value))) {
                return t('formBlock.validation.numeric')
            }
        }
        return true
    }
}

const submit = handleSubmit(async (values) => {
    loading.value = true
    try {
        const response = await fetch('/api/contact_form', {
            method: 'POST',
            headers: { 'Content-Type': 'application/ld+json' },
            body: JSON.stringify({
                locale: document.documentElement.lang || 'en',
                formUUID: config.value.formTemplate.uuid,
                formData: values,
                hp: honeypot.value,
            }),
        })
        if (response.ok) {
            success.value = true
        } else {
            toastStore.showError(t('formBlock.error'))
        }
    } catch {
        toastStore.showError(t('common.anErrorOccurred'))
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div v-if="success" class="success-text">
        {{ t('formBlock.success') }}
    </div>

    <v-form v-else @submit.prevent="submit">
        <div class="form-fields">
            <template v-for="(block, index) in (config.formTemplate.formBlocks ?? [])" :key="isInputField(block) ? block.key : `display-${index}`">
                <h4 v-if="block.type === 'display-header'" class="display-header">{{ block.text }}</h4>
                <p v-else-if="block.type === 'display-text'" class="display-text">{{ block.text }}</p>

                <div v-else :class="['form-field-wrap', ...(Array.isArray(block.cols) ? block.cols : [])]">
                    <Field
                        v-if="block.type === 'text-input'"
                        v-slot="{ field, errors }"
                        :name="block.key"
                        :rules="makeRules(block.validations)"
                    >
                        <v-text-field
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            @blur="field.onBlur"
                            :label="block.label"
                            :error-messages="errors"
                            variant="outlined"
                            density="comfortable"
                        />
                    </Field>

                    <Field
                        v-else-if="block.type === 'textarea-input'"
                        v-slot="{ field, errors }"
                        :name="block.key"
                        :rules="makeRules(block.validations)"
                    >
                        <v-textarea
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            @blur="field.onBlur"
                            :label="block.label"
                            :error-messages="errors"
                            variant="outlined"
                            density="comfortable"
                            rows="4"
                        />
                    </Field>

                    <Field
                        v-else-if="block.type === 'select-input'"
                        v-slot="{ field, errors }"
                        :name="block.key"
                        :rules="makeRules(block.validations)"
                    >
                        <v-autocomplete
                            v-if="block.isAutocomplete"
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            @blur="field.onBlur"
                            :label="block.label"
                            :items="block.options"
                            item-value="value"
                            item-title="text"
                            :error-messages="errors"
                            variant="outlined"
                            density="comfortable"
                        />
                        <v-select
                            v-else
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            @blur="field.onBlur"
                            :label="block.label"
                            :items="block.options"
                            item-value="value"
                            item-title="text"
                            :error-messages="errors"
                            variant="outlined"
                            density="comfortable"
                        />
                    </Field>

                    <Field
                        v-else-if="block.type === 'checkbox-input'"
                        v-slot="{ field, errors }"
                        :name="block.key"
                        :rules="makeRules(block.validations)"
                    >
                        <v-switch
                            v-if="block.isToggle"
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            :label="block.label"
                            :error-messages="errors"
                            :true-value="true"
                            :false-value="false"
                            hide-details="auto"
                        />
                        <v-checkbox
                            v-else
                            :model-value="field.value"
                            @update:model-value="field.onChange"
                            :label="block.label"
                            :error-messages="errors"
                            :true-value="true"
                            :false-value="false"
                            hide-details="auto"
                        />
                    </Field>
                </div>
            </template>
        </div>

        <p v-if="config.formTemplate.gdprLink" class="gdpr-notice">
            {{ t('formBlock.gdpr') }}
            <a :href="config.formTemplate.gdprLink" target="_blank">
                {{ t('formBlock.gdprLink') }}
            </a>.
        </p>

        <div class="website">
            <input v-model="honeypot" type="text" name="website" autocomplete="off" />
        </div>

        <div :class="['button-space', { 'button-space--inline': config.ctaInline }]">
            <v-btn
                type="submit"
                :loading="loading"
                :color="config.ctaColor"
                variant="flat"
            >
                {{ config.ctaText || t('formBlock.submit') }}
            </v-btn>
        </div>
    </v-form>
</template>
