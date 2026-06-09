import type { Config } from 'tailwindcss';

export default {
  content: ['./index.html', './src/**/*.{vue,ts}'],
  theme: {
    extend: {
      colors: {
        ink: '#0c111d',
        mist: '#f4f7f8',
        line: '#dbe3e6',
        energy: '#00a86b',
        ocean: '#1176d2',
      },
      boxShadow: {
        panel: '0 18px 45px rgba(12, 17, 29, 0.08)',
      },
    },
  },
  plugins: [],
} satisfies Config;
