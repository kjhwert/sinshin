<<<<<<< HEAD
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");

module.exports = function(gulp, callback) {
	return gulp.src( ['**/*.js', '!**/*.min.js', '!**/sweet-alerts.js'], {cwd: config.destination.js} )
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(config.destination.js));
=======
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");

module.exports = function(gulp, callback) {
	return gulp.src( ['**/*.js', '!**/*.min.js', '!**/sweet-alerts.js'], {cwd: config.destination.js} )
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(config.destination.js));
>>>>>>> 8d76e981e5515d6a93bdf4e69e5483743c47a537
};