'use strict';

var del = require('del');
var gulp = require('gulp');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var less = require('gulp-less');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var config = require('./resources.json');

function clean(done) {
    return del([
        config.less.dest + "*.css",
        config.js.dest + "*.js"
    ], done);
}
clean.description = 'Clean output files.';

function styles() {
    return gulp.src(config.less.src)
        .pipe(sourcemaps.init())
        .pipe(less({ relativeUrls: true, paths: [ config.less.root ] }))
        .pipe(cleanCSS({ processImport: true, relativeTo: config.less.root }))
        .pipe(cleanCSS({ relativeTo: config.less.dest, target: config.less.dest }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.less.dest));
}
styles.description = 'Run less to generate stylesheets.';

function scripts() {
    return gulp.src(config.js.src)
        .pipe(sourcemaps.init())
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest(config.js.dest))
        .pipe(uglify())
        .pipe(rename({ extname: '.min.js' }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.js.dest));
}
scripts.description = 'Combine and minify javascript files.';

exports.clean = clean;
exports.styles = styles;
exports.scripts = scripts;

var build = gulp.series(clean, gulp.parallel(styles, scripts));

gulp.task('build', build);
gulp.task('default', build);