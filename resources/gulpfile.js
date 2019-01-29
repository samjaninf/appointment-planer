var gulp          = require('gulp');
var browserSync   = require('browser-sync').create();
var $             = require('gulp-load-plugins')();
var autoprefixer  = require('autoprefixer');
var named         = require('vinyl-named');
var webpackStream = require('webpack-stream');
var webpack2      = require('webpack');

var sassPaths = [
  'node_modules/foundation-sites/scss',
  'node_modules/motion-ui/src'
];

let webpackConfig = {
  mode: ('production'),
  module: {
    rules: [
      {
        test: /\.js$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [ "@babel/preset-env" ],
            compact: false
          }
        }
      }
    ]
  }
}

function sass() {
  return gulp.src('scss/app.scss')
    .pipe($.sass({
      includePaths: sassPaths,
      outputStyle: 'compressed' // if css compressed **file size**
    })
      .on('error', $.sass.logError))
    .pipe($.postcss([
      autoprefixer({ browsers: ['last 2 versions', 'ie >= 9'] })
    ]))
    .pipe(gulp.dest('../public/assets/css'))
    .pipe(browserSync.stream());
};

function javascript() {
  return gulp.src('js/app.js')
    .pipe(named())
    .pipe(webpackStream(webpackConfig, webpack2))
    .pipe($.uglify()
      .on('error', e => { console.log(e); })
    )
    .pipe(gulp.dest('../public/assets/js'));
}

function serve() {
  browserSync.init({
    proxy: "127.0.0.1:8080"
  });

  gulp.watch("scss/**/*.scss", sass);
  gulp.watch("templates/**/*.html").on('change', browserSync.reload);
  gulp.watch("js/**/*.js").on('change', gulp.series('js', browserSync.reload));
}

gulp.task('sass', sass);
gulp.task('js', javascript);
gulp.task('build', gulp.series('sass', 'js'));
gulp.task('serve', gulp.series('build', serve));
gulp.task('default', gulp.series('build', serve));
