let path = require('path')
let projectName = 'boulders'
let src = './src'
let dest = `./server/wp-content/themes/${projectName}`
let deploy = `./dist/`

const config = {
    port: 3000,
    paths: {
        root: `${dest}`,
        stage: `${deploy}`,
        views: {
            src: `${src}/views/**/**.*`,
            dest: '/templates',
        },
        styles: {
            src: `${src}/styles/**/**.*`,
            dest: ''
        },
        js: {
            src: [`${src}/scripts/**/**.js`, `${src}/scripts/**/**.json`],
            dest: '/js'
        },
        php: {
            src: `${src}/php/**/**.*`,
            dest: ''
        },
        fonts: {
            src: `${src}/fonts/**/**.*`,
            dest: '/fonts'
        },
        media: {
            src: `${src}/media/**/**.*`,
            dest: '/media'
        },
        screenshot: {
            src: `${src}/screenshot.png`,
            dest: ''
        }
    }
}

module.exports = config