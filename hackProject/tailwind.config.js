module.exports = {
  mode: 'jit',
  purge: ['./pages/**/*.{js,ts,jsx,tsx}', './components/**/*.{js,ts,jsx,tsx}'],
  darkMode: true, // or 'media' or 'class'
  theme: {
    extend: {
      colors:{
        'primary':'#081c15',
        'secondary':'#394943',
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
