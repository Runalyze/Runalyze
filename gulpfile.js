'use strict';

var del = require('del');
var gulp = require('gulp');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var less = require('gulp-less');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var shell = require('gulp-shell');
var phpunit = require('gulp-phpunit');
var config = require('./resources.json');

function clean(done) {
    return del([
        config.less.dest + "*.css",
        config.js.dest + "*.js"
    ], done);
}
clean.description = 'Clean output files.';

function styles() {
    return gulp.src(config.less.main.src)
        .pipe(sourcemaps.init())
        .pipe(less({ relativeUrls: true, paths: [ config.less.main.root ] }))
        .pipe(cleanCSS({ rebase: true, rebaseTo: config.less.dest }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.less.dest));
}
styles.description = 'Run less to generate stylesheets.';

function stylesInstaller() {
    return gulp.src(config.less.installer.src)
        .pipe(sourcemaps.init())
        .pipe(less({ relativeUrls: true, paths: [ config.less.installer.root ] }))
        .pipe(cleanCSS({ rebase: true, rebaseTo: config.less.dest }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.less.dest));
}
stylesInstaller.description = 'Run less to generate stylesheets for the installer.';

function scripts() {
    return gulp.src(config.js.src)
        .pipe(sourcemaps.init())
        .pipe(concat('scripts.min.js'))
        .pipe(gulp.dest(config.js.dest))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(config.js.dest));
}
scripts.description = 'Combine and minify javascript files.';

function tests() {
    return gulp.src('./tests/config.xml')
        .pipe(phpunit('./vendor/bin/phpunit', { bootstrap: './tests/bootstrap.php', statusLine: false }));
}
tests.description = 'Run phpunit.';

function translate() {
  return gulp.src('./vendor/runalyze/translations/gettext/*/*/*.po', {read: false})
    .pipe(shell([
        'cp <%= file.path %> ./vendor/runalyze/translations/gettext', // Needed for symfony
    ]))
    .pipe(shell([
        'msgfmt -v <%= file.path %> -o <%= target(file.path) %>'
    ], {
        templateData: {
            target: function (f) {
		return f.replace(/messages\.(.*)\.po$/, 'runalyze.mo')
            }
        }
    }))
}
translate.description = 'Compile translation files.';

exports.clean = clean;
exports.styles = styles;
exports.stylesInstaller = stylesInstaller;
exports.scripts = scripts;
exports.tests = tests;
exports.translate = translate;

var build = gulp.series(clean, gulp.parallel(styles, stylesInstaller, scripts));

gulp.task('build', build);
gulp.task('default', gulp.parallel(build, translate));
