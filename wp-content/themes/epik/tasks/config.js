var sources = {
  code: "**/*.php",
  images: "../assets/images/**/*",
  scripts: "../assets/js/**/*.js",
  styles: "../assets/scss/**/*.scss"
};

module.exports = {

  i18n: {
    src: sources.code,
    textdomain: 'sportport-active-theme',
    dest: '../languages/',
    message: 'i18n tasks complete.'
  },

  images: {
    src: sources.images,
    dest: '../assets/images/',
    message: 'Images task complete.'
  },

  scripts: {
    src: sources.scripts,
    output: 'theme.js',
    dest: '../assets/js/',
    message: 'Javascript tasks complete.'
  },

  server: {
    url: 'sportport.dev'
  },

  styles: {
    src: sources.styles,
    output: 'compressed',
    dest: '../',
    message: 'Stylesheet compiled & saved.'
  },

  watch: {
    code: sources.code,
    images: sources.images,
    scripts: sources.scripts,
    styles: sources.styles
  }

};
