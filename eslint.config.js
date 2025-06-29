import js from '@eslint/js';
import alpine from 'eslint-plugin-alpine';

export default [
  js.configs.recommended,
  {
    files: ['**/*.js'],
    plugins: {
      alpine
    },
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        console: 'readonly',
        document: 'readonly',
        window: 'readonly',
        Alpine: 'readonly',
        Swal: 'readonly'
      }
    },
    rules: {
      'indent': ['error', 2],
      'quotes': ['error', 'single', { 'avoidEscape': true }],
      'semi': ['error', 'always'],
      'no-unused-vars': ['warn', { 'argsIgnorePattern': '^_' }],
      'no-console': 'warn',
      'alpine/no-direct-mutation-state': 'error'
    }
  }
];