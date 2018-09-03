const gulp = require('gulp')
const runSequence = require('run-sequence').use(gulp)
const del = require('del')
const nsp = require('gulp-nsp')
const changed = require('gulp-changed')
const notify = require('gulp-notify')
const plumber = require('gulp-plumber')
const autoprefixer = require('gulp-autoprefixer')
const sass = require('gulp-sass')
const sourcemaps = require('gulp-sourcemaps')
const filesize = require('gulp-size')
const uglify = require('gulp-uglify')
const eslint = require('gulp-eslint')
const newer = require('gulp-newer')
const remember = require('gulp-remember')
const cache = require('gulp-cache')
const imgmin = require('gulp-imagemin')
const options = require('gulp-options')
const pngquant = require('imagemin-pngquant')
const browserSync = require('browser-sync')
const browserify = require('browserify')
const source = require('vinyl-source-stream')
const buffer = require('vinyl-buffer')
const ftp = require('vinyl-ftp')
const globby = require('globby')
const through = require('through2')
const path = require('path')
const importer = require('sass-module-importer')
// Config File
const config = require('./.gulpconfig.js')

/** Tasks **/
// Clean
gulp.task('clean', function () {
    let path = options.has('deploy') ? config.paths.stage : config.paths.root
    return del(path, { force: true })
})
// Views
gulp.task('views', function () {
    let paths = config.paths.views
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    return gulp.src(paths.src)
        .pipe(changed(dest))
        .pipe(gulp.dest(dest))
})
// Styles
gulp.task('styles', function () {
    let paths = config.paths.styles
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    return gulp.src(paths.src)
        .pipe(plumber({ errorHandler: notify.onError('Error: <%= error.message %>') }))
        .pipe(sourcemaps.init())
        .pipe(sass({
            cache: false,
            debug: true,
            importer: importer(),
            'import css': true,
            compress: true // Change to be aware of if deploying or not
        }))
        .pipe(autoprefixer())
        .pipe(filesize({ showFiles: true }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dest))
        .pipe(browserSync.reload({ stream: true }))
})
// Script Linting
gulp.task('eslint', function () {
    let paths = config.paths.js
    return gulp.src(paths.src)
        .pipe(eslint({
            'fix': true,
            'extends': 'airbnb',
            'env': {
                'browser': true,
                'node': true
            },
            'parserOptions': {
                'ecmaVersion': 6,
                'sourceType': 'module'
            },
            'rules': {
                'no-console': 1,
                'no-unused-vars': 1
            }
        }))
        .pipe(eslint.format())
})
// Scripts
gulp.task('scripts', ['eslint'], function () {
    let paths = config.paths.js
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    const bundledStream = through()

    bundledStream
        .pipe(source('main.js'))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))

        // Add transformation tasks to the pipeline here.
        .pipe(uglify())
        .on('error', notify.onError({
            title: 'Uglify Error',
            message: '\n<%= error.message %>'
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(filesize({ showFiles: true }))
        .pipe(gulp.dest(dest))

    globby(paths.src).then(function (entries) {
        const b = browserify({
            entries: entries,
            debug: true
        })
        b
            .transform('babelify', {
                presets: ['es2015']
            })
            .bundle()
            .pipe(bundledStream)

    }).catch(function (err) {
    // ensure any errors from globby are handled
        bundledStream.emit('error', err)
    })

    bundledStream.on('error', notify.onError({
        title: 'bundle error',
        message: '\n<%= error.message %>'
    }))

    return bundledStream
})
// PHP
gulp.task('php', function () {
    let paths = config.paths.php
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest

    return gulp.src(paths.src)
        .pipe(changed(dest))
        .pipe(gulp.dest(dest))
})
// Screenshot
gulp.task('screenshot', function () {
    let paths = config.paths.screenshot
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    return gulp.src(paths.src)
        .pipe(gulp.dest(dest))
})
// Images
gulp.task('images', function () {
    let paths = config.paths.media
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    return gulp.src(paths.src)
        .pipe(newer(dest))
        .pipe(imgmin({
            progressive: true,
            svgoPlugins: [{ removeViewBox: false }],
            use: [pngquant()]
        }))
        .pipe(gulp.dest(dest))
})
// Fonts
gulp.task('fonts', function () {
    let paths = config.paths.fonts
    let dest = options.has('deploy') ? config.paths.stage + paths.dest : config.paths.root + paths.dest
    return gulp.src(paths.src)
        .pipe(changed(dest))
        .pipe(gulp.dest(dest))
})
// Reload
gulp.task('reload', function () {
    browserSync.reload()
})

/** Task Groups **/
// Core Build
gulp.task('build', function (cb) {
    runSequence('clean', ['styles', 'scripts', 'php', 'views', 'fonts', 'screenshot', 'images'], cb)
})
// Watch
gulp.task('watch', function () {
    // only watch compiled css file
    // and INJECT when compiled
    // otherwise, HTML/JS changes should trigger full refresh
    const paths = config.paths

    browserSync.init({
        proxy: 'localhost:8000',
        files: `${paths.styles.dest}/style.css`,
        injectChanges: true,
        logPrefix: 'BrowserSync',
        logConnections: true
    })

    gulp.watch(paths.styles.src).on('change', function (event) {
        if (event.type === 'deleted') { // if a file is deleted, forget about it
            delete cache.caches['styles'][event.path]
            remember.forget('styles', event.path)
        }
        runSequence('styles')
    })

    gulp.watch(paths.js.src).on('change', function () {
        runSequence('scripts', 'reload')
    })

    gulp.watch(paths.views.src).on('change', function () {
        runSequence('views', 'reload')
    })

    gulp.watch(paths.php.src).on('change', function () {
        runSequence('php', 'reload')
    })

    gulp.watch(paths.media.src).on('change', function () {
        runSequence('images', 'reload')
    })
})
// Security
gulp.task('nsp', function (cb) {
    nsp({ package: path.resolve('package.json') }, cb)
})
// Default
gulp.task('default', function (cb) {
    runSequence('build', 'watch', cb)
    // 'nsp',
})