export type ValidationRule = 'required' | 'email' | 'numeric'

export interface SelectOption {
    type: 'option'
    value: string
    text: string
}

interface BaseInputField {
    key: string
    label: string
    defaultValue?: string
    validations: ValidationRule[] | null
    cols: string[] | null
}

export interface TextField extends BaseInputField {
    type: 'text-input'
}

export interface TextAreaField extends BaseInputField {
    type: 'textarea-input'
}

export interface SelectField extends BaseInputField {
    type: 'select-input'
    isAutocomplete: boolean
    options: SelectOption[]
}

export interface CheckboxField extends BaseInputField {
    type: 'checkbox-input'
    defaultValue?: string
    isToggle: boolean
}

export interface DisplayTextField {
    type: 'display-text'
    text: string
}

export interface DisplayHeaderField {
    type: 'display-header'
    text: string
}

export type InputFormField = TextField | TextAreaField | SelectField | CheckboxField
export type FormField = InputFormField | DisplayTextField | DisplayHeaderField

export interface FormTemplate {
    uuid: string
    source: string
    targetEmail: string
    formBlocks: FormField[]
    gdprLink?: Record<string, unknown> | null
}

export interface FormBlockConfig {
    ctaText: string
    ctaInline: boolean
    ctaColor: 'white' | 'black' | 'primary' | 'accent'
    debugMode: boolean
    formTemplate: FormTemplate
}
