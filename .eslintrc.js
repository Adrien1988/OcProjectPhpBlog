module.exports = {
  extends: ['airbnb-base', 'prettier'],
  plugins: ['prettier'],
  rules: {
    'prettier/prettier': 'error',
    'no-unused-vars': ['error', { varsIgnorePattern: '^_' }],
    'no-underscore-dangle': ['error', { allow: ['_scrollSpy'] }],
    'import/no-extraneous-dependencies': [
      'error',
      {
      devDependencies: ['**/eslint.config.mjs'],
      optionalDependencies: false,
  },
  ],
  },
  env: {
    browser: true,
    node: true,
  },
  overrides: [
    {
      files: ['eslint.config.js', 'eslint.config.mjs'],
      rules: {
        'import/no-extraneous-dependencies': ['error', { devDependencies: true }],
      },
    },
  ],
};
