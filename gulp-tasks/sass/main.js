<<<<<<< HEAD
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');

module.exports = function(gulp, callback) {
	return gulp.src(['app-lite.scss', 'components-lite.scss', 'bootstrap.scss', 'bootstrap-extended.scss', 'colors.scss', 'vendors.scss' ], { cwd: config.source.sass})
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer({
            browsers: config.autoprefixerBrowsers,
            cascade: false
        }))
		.pipe(gulp.dest(config.destination.css));
=======
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');

module.exports = function(gulp, callback) {
	return gulp.src(['app-lite.scss', 'components-lite.scss', 'bootstrap.scss', 'bootstrap-extended.scss', 'colors.scss', 'vendors.scss' ], { cwd: config.source.sass})
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer({
            browsers: config.autoprefixerBrowsers,
            cascade: false
        }))
		.pipe(gulp.dest(config.destination.css));
>>>>>>> 8d76e981e5515d6a93bdf4e69e5483743c47a537
};