/**
 * @file
 * Provides Gulp configurations and tasks for compiling paragraphs CSS files
 * from SASS files.
 */

'use strict';

// Loading of gulp libs.
var gulp            = require('gulp'),
  autoprefixer      = require('autoprefixer'),
  postcss           = require('gulp-postcss'),
  csslint           = require('gulp-csslint'),
  sass              = require('gulp-sass');

// Main gulp options.
var config = {
  "scssSrc": "./",
  "cssDest": "./",
  "browserSupport": [
    "last 2 versions",
    "ie >= 10",
    "Safari >= 7"
  ]
};

// Post CSS options.
var postCSSOptions = [autoprefixer({ browsers: config.browserSupport })];

gulp.task('sass', function() {
  return gulp
    .src(config.scssSrc + '/*.scss')
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: config.sassIncludePaths
    }))
    .pipe(postcss(postCSSOptions))
    .pipe(csslint())
    .pipe(csslint.formatter())
    .pipe(gulp.dest(config.cssDest));
});

gulp.task('default', ['sass']);
