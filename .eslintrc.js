module.exports = {
  extends: ['airbnb-base', 'prettier'],
  plugins: ['prettier'],
  rules: {
    'prettier/prettier': 'error',
    'no-unused-vars': ['error', { 'varsIgnorePattern': '^_' }],
    'no-underscore-dangle': ['error', { allow: ['_scrollSpy'] }],
  },
  env: {
    browser: true,
    node: true,
  },
};
