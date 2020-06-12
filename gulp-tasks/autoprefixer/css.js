<<<<<<< HEAD
var autoprefixer = require('gulp-autoprefixer');

module.exports = function(gulp, callback) {
    return gulp.src(['**/*.css', '!**/*.min.css'], { cwd: config.destination.css })
        .pipe(autoprefixer({
            browsers: config.autoprefixerBrowsers,
            cascade: false
        }))
        .pipe(gulp.dest(config.destination.css));
=======
var autoprefixer = require('gulp-autoprefixer');

module.exports = function(gulp, callback) {
    return gulp.src(['**/*.css', '!**/*.min.css'], { cwd: config.destination.css })
        .pipe(autoprefixer({
            browsers: config.autoprefixerBrowsers,
            cascade: false
        }))
        .pipe(gulp.dest(config.destination.css));
>>>>>>> 8d76e981e5515d6a93bdf4e69e5483743c47a537
};