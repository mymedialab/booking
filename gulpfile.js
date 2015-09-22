var gulp = require('gulp');
var phpunit = require('gulp-phpunit');
var phplint = require('phplint');
var phpcs = require('gulp-phpcs');
var codecept = require('gulp-codeception');

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

gulp.task('phpcs', ['lintsrc'], function(){
    gulp.src('src/**/*.php').pipe(phpcs(phpcsOptions));
});
gulp.task('phptests', ['linttests', 'phpcs'], function(){
    gulp.src('tests/*.php').pipe(phpcs(phpcsOptions)).pipe(codecept());
});
gulp.task('watch', function(){
    gulp.watch(['src/**/*.php', 'tests/**/*.php'], ['phptests']);
});

gulp.task('default', ['phpcs', 'phptests', 'watch']);
