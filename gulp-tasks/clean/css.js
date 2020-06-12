<<<<<<< HEAD
var clean = require('gulp-clean');

module.exports = function(gulp, callback) {
	return gulp.src(config.destination.css, {
			read: false
		})
		.pipe(clean());
=======
var clean = require('gulp-clean');

module.exports = function(gulp, callback) {
	return gulp.src(config.destination.css, {
			read: false
		})
		.pipe(clean());
>>>>>>> 8d76e981e5515d6a93bdf4e69e5483743c47a537
};