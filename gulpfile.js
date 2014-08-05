var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var phplint = require('phplint');
var phpcs = require('gulp-phpcs');

var phpunitOptions =  {configurationFile: "phpunit.xml"};
var phpcsOptions = {
    bin: 'vendor/bin/phpcs',
    standard: 'PSR2',
    warningSeverity: 0
};

gulp.task('lintsrc', function () {
  return phplint('src/**/*.php');
});
gulp.task('linttests', function () {
  return phplint('tests/**/*.php');
});

gulp.task('phpsrc', ['lintsrc'], function(){
    gulp.src('src/**/*.php').pipe(phpcs(phpcsOptions));
});
gulp.task('phptests', ['linttests'], function(){
    gulp.src('./tests/*.php').pipe(phpcs(phpcsOptions)).pipe(phpunit('./vendor/bin/phpunit', phpunitOptions));
});
gulp.task('watch', function(){
    gulp.watch('src/**/*.php', ['lintsrc', 'phpsrc']);
    gulp.watch('tests/**/*.php', ['linttests', 'phptests']);
});

gulp.task('default', ['phpsrc', 'phptests', 'watch']);
