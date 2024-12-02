import globals from 'globals';
import pluginJs from '@eslint/js';

/** @type {import('eslint').Linter.FlatConfig[]} */
export default [
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
    plugins: {
      js: pluginJs,
    },
    rules: {
      // Vos règles personnalisées
      'no-var': 'error',
      'prefer-const': 'error',
      'prefer-arrow-callback': 'error',
      // Vous pouvez ajouter d'autres règles ici
    },
  },
  // Inclure la configuration recommandée
  pluginJs.configs.recommended,
];
