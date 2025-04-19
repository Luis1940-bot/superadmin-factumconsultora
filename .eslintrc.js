module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: ['airbnb-base', 'prettier'],
  settings: {
    'import/resolver': {
      node: {
        extensions: ['.js', '.json'],
      },
    },
  },
  plugins: ['prettier'], // Agrega el plugin de Prettier
  overrides: [
    {
      env: {
        node: true,
      },
      files: ['.eslintrc.{js,cjs}'],
      parserOptions: {
        sourceType: 'script',
      },
    },
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  rules: {
    'prettier/prettier': 'error', // Muestra errores si Prettier no cumple
    'import/no-extraneous-dependencies': ['error', { devDependencies: true }],
    'no-unused-expressions': 'off',
    'no-plusplus': 'off',
    'linebreak-style': ['off'],
    'import/extensions': 'off',
    'no-console': ['warn', { allow: ['time', 'timeEnd', 'error'] }],
    'comma-dangle': ['error', 'only-multiline'],
    'import/no-unresolved': 'off',
    'import/prefer-default-export': 'off',
  },
  ignorePatterns: ['node_modules/', 'dist/', 'build/', 'Libraries/'],
};
