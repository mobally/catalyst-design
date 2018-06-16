var gulp = require('gulp');
var sass = require("gulp-sass");
var autoprefixer = require("gulp-autoprefixer");
var cleanCSS = require('gulp-clean-css');
var sourcemaps = require('gulp-sourcemaps');
var plumber = require("gulp-plumber");


gulp.task("sass", function() {
    gulp.src("sass/**/*scss")
        .pipe(sourcemaps.init())
        .pipe(plumber())
        .pipe(sass())
        .pipe(autoprefixer())
        .pipe(cleanCSS())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest("./"))
});



gulp.task('default', function () {
    gulp.watch("sass/**/*.scss",["sass"]);
});
