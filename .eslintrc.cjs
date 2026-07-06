/**
 * Aturan penting revamp:
 * Halaman & fitur TIDAK BOLEH import langsung dari `@mui/material`.
 * Semua komponen UI harus lewat wrapper internal di `@/ui` supaya kustomisasi
 * (warna, default props, varian) cukup diubah di satu tempat.
 *
 * Pengecualian: file di dalam `resources/js/ui/**` dan `resources/js/theme/**`
 * memang boleh import langsung dari MUI (mereka adalah lapisan wrapper-nya).
 */
module.exports = {
  root: true,
  env: { browser: true, es2022: true },
  parser: '@typescript-eslint/parser',
  parserOptions: { ecmaVersion: 'latest', sourceType: 'module', ecmaFeatures: { jsx: true } },
  settings: { react: { version: 'detect' } },
  rules: {
    'no-restricted-imports': [
      'error',
      {
        paths: [
          {
            name: '@mui/material',
            message:
              'Jangan import langsung dari @mui/material. Pakai wrapper internal dari "@/ui".',
          },
        ],
        patterns: [
          {
            group: ['@mui/material/*'],
            message:
              'Jangan import langsung dari @mui/material/*. Pakai wrapper internal dari "@/ui".',
          },
        ],
      },
    ],
  },
  overrides: [
    {
      files: ['resources/js/ui/**/*', 'resources/js/theme/**/*', 'resources/js/layouts/**/*'],
      rules: { 'no-restricted-imports': 'off' },
    },
  ],
};
