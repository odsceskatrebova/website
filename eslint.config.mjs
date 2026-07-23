import js from '@eslint/js';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import importPlugin from 'eslint-plugin-import-x';
import importNewlines from 'eslint-plugin-import-newlines';

export default [
  { ignores: ['assets/admin/**'] },
  js.configs.recommended,
  {
    files: ['assets/odsct/**/*.ts', 'assets/common/**/*.ts'],
    plugins: {
      '@typescript-eslint': tsPlugin,
      import: importPlugin,
      'import-newlines': importNewlines,
    },
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        project: true,
        tsconfigRootDir: import.meta.dirname,
      },
    },
    rules: {
      // @typescript-eslint recommended + type-checked
      ...tsPlugin.configs['flat/recommended'][1].rules,
      ...tsPlugin.configs['flat/recommended-type-checked'][1].rules,
      // formatting (replacements for removed @typescript-eslint extension rules)
      'padding-line-between-statements': [
        'error',
        { blankLine: 'never', prev: 'import', next: 'import' },
      ],
      'padded-blocks': ['error', { blocks: 'never', classes: 'never', switches: 'never' }],
      'no-multi-spaces': ['error', { ignoreEOLComments: false }],
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'no-multiple-empty-lines': ['error', { max: 1, maxEOF: 0, maxBOF: 0 }],
      'comma-dangle': ['warn', { arrays: 'always-multiline', objects: 'always-multiline', imports: 'always-multiline', exports: 'always-multiline', functions: 'always-multiline' }],
      'comma-spacing': ['warn', { before: false, after: true }],
      'func-call-spacing': ['warn', 'never'],
      'indent': ['warn', 2, { SwitchCase: 1 }],
      'key-spacing': ['warn', { beforeColon: false, afterColon: true }],
      'keyword-spacing': ['warn', { before: true, after: true }],
      'no-extra-semi': 'warn',
      'object-curly-spacing': ['warn', 'always', { objectsInObjects: false, arraysInObjects: false }],
      'quotes': ['warn', 'single', { avoidEscape: true }],
      'semi': ['warn', 'always'],
      'space-before-blocks': ['warn', 'always'],
      'space-before-function-paren': ['warn', { anonymous: 'always', asyncArrow: 'always', named: 'never' }],
      'space-infix-ops': 'warn',
      'array-element-newline': ['warn', { multiline: true, minItems: 2 }],
      'object-property-newline': ['warn', { allowAllPropertiesOnSameLine: false, allowMultiplePropertiesPerLine: false }],
      'object-curly-newline': ['error', { consistent: true }],
      'array-bracket-newline': ['warn', { multiline: true, minItems: 2 }],
      'array-bracket-spacing': ['warn', 'always', { arraysInArrays: false, objectsInArrays: false }],
      // typescript-specific (no base ESLint equivalent for member-delimiter-style, type-annotation-spacing — removed in v8)
      'no-unused-vars': 'off',
      '@typescript-eslint/no-explicit-any': 'warn',
      '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      '@typescript-eslint/explicit-member-accessibility': ['error', { accessibility: 'explicit' }],
      '@typescript-eslint/explicit-function-return-type': ['error', {
        allowExpressions: false,
        allowTypedFunctionExpressions: true,
        allowHigherOrderFunctions: false,
        allowDirectConstAssertionInArrowFunctions: true,
        allowConciseArrowFunctionExpressionsStartingWithVoid: false,
      }],
      // imports
      'import/no-unresolved': 'error',
      'import/named': 'error',
      'import/namespace': 'error',
      'import/default': 'error',
      'import/export': 'error',
      'import/no-named-as-default': 'error',
      'import/no-named-as-default-member': 'error',
      'import/no-duplicates': 'error',
      'import/no-mutable-exports': 'error',
      'import-newlines/enforce': ['error', { items: 2, 'max-len': 80 }],
    },
    settings: {
      'import-x/resolver': {
        typescript: true,
        node: { extensions: ['.js', '.ts'] },
      },
    },
  },
];
