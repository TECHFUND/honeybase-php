var gulp = require('gulp');
var run = require('gulp-run');
var webserver = require('gulp-webserver');

gulp.task('webserver', function() {
  gulp.src('')
  .pipe(webserver({
    livereload: false,
    directoryListing: false,
    open: false,
    port: 8001
  }));
});

gulp.task("js-task",function(){
  return gulp.src('js/**/*.js');
});

gulp.task("watch",function(){
  return gulp.watch("js/**/*.js",["js-task"]);
});

gulp.task("default", ["webserver", "js-task", "watch"] );
