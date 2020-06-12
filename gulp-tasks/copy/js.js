<<<<<<< HEAD
var gulpCopy = require('gulp-copy');

module.exports = function(gulp, callback) {
	return gulp.src(config.source.js+'/**/*.js')
		.pipe(gulp.dest(config.destination.js));
=======
var gulpCopy = require('gulp-copy');

module.exports = function(gulp, callback) {
	return gulp.src(config.source.js+'/**/*.js')
		.pipe(gulp.dest(config.destination.js));
>>>>>>> 8d76e981e5515d6a93bdf4e69e5483743c47a537
};