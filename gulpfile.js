var gulp = require('gulp');
var babel = require('gulp-babel');
var concat = require('gulp-concat');

gulp.task('default', function() {
    return gulp.src( [
        './assets/js/Styles.js',
        './assets/js/CustomProperty.js',
        './assets/js/customize-preview.js'
    ])
        .pipe(concat('customize-preview.js'))
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe( gulp.dest('./dist/js/') )
});
