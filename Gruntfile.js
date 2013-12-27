module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        bootstrap: '../../twbs/bootstrap',
        vendor: 'assets/vendor',
        less_src: 'assets/less',
        app: 'assets/coffee',

        coffee: {

            options: {
                sourceMap: true,
            },

            app: {
                src: [
                    '<%= app %>/init.coffee',

                    '<%= app %>/helpers.coffee',
                    '<%= app %>/factory.coffee',
                    '<%= app %>/attribute.coffee',
                    '<%= app %>/datasource.coffee',
                    '<%= app %>/datagrid.coffee',
                    '<%= app %>/fieldList.coffee',
                    '<%= app %>/filterList.coffee',

                    // Inputs
                    '<%= app %>/inputs/base.coffee',
                    '<%= app %>/inputs/static.coffee',
                    '<%= app %>/inputs/text.coffee',
                    '<%= app %>/inputs/checkbox.coffee',
                    '<%= app %>/inputs/boolean.coffee',
                    '<%= app %>/inputs/entityDropdown.coffee',
                    '<%= app %>/inputs/entitySelector.coffee',

                    // Fields
                    '<%= app %>/fields/field.coffee',
                    '<%= app %>/fields/input.coffee',
                    '<%= app %>/fields/datetime.coffee',
                    '<%= app %>/fields/boolean.coffee',
                    '<%= app %>/fields/relation.coffee',

                    // Columns
                    '<%= app %>/columns/column.coffee',

                    // Entity
                    '<%= app %>/entity/entity.coffee',
                    '<%= app %>/entity/instance.coffee',
                    '<%= app %>/entity/page.coffee',
                    '<%= app %>/entity/form.coffee',
                    '<%= app %>/entity/related.coffee',

                    '<%= app %>/app.coffee',
                ],

                dest: 'public/js/app.js',
            },
        },

        concat: {
            vendor: {
                src: [
                    '<%= vendor %>/jquery-2.0.3.js',
                    '<%= vendor %>/underscore-1.5.2.js',
                    '<%= vendor %>/backbone-1.1.0.js',
                    '<%= vendor %>/moment.js',
                    '<%= vendor %>/moment-ru.js',

                    '<%= bootstrap %>/js/tab.js',
                    '<%= bootstrap %>/js/dropdown.js',
                ],

                dest: 'public/js/vendor.js',
            },
        },

        uglify: {
            all: {
                options: {
                    sourceMap: function (name) {
                        return name + '.map';
                    },

                    sourceMapIn: function (name) {
                        name += '.map';
                        return grunt.file.exists(name) && name || undefined;
                    },
                },

                expand: true,
                cwd:  'public/js',
                src:  ['*.js', '!*.min.js'],
                dest: 'public/js/',
                ext:  '.min.js',
            },
        },

        less: {
            styles: {
                options: {
                    paths: [
                        '<%= bootstrap %>/less',
                    ],

                    sourceMap: true,
                    sourceMapFilename: "public/css/styles.min.css.map",
                    sourceMapBasepath: "public/css/",
                    outputSourceFiles: true,

                    compress: true,
                },

                files: {
                    'public/css/styles.min.css': '<%= less_src %>/styles/styles.less',
                }
            },
        },

        cssmin: {
            styles: {
                files: {
                    'public/css/styles.min.css': [
                        'public/css/bootstrap.css',
                        'public/css/styles.css',
                    ],
                }
            },
        },

        copy: {
            fonts: {
                expand: true,
                cwd: '<%= bootstrap %>/dist/fonts/',
                src: '*',
                dest: 'public/fonts/',
            },
        },

        watch: {

            styles: {
                files: '<%= less_src %>/**/*.less',
                tasks: ['less:styles'],
            },

            coffee: {
                files: [
                    '<%= app %>/**/*.coffee',
                ],

                tasks: ['app-dev'],
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-coffee');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Concat all needed vendor files
    grunt.registerTask('vendor', ['concat:vendor']);

    // Backend scripts
    grunt.registerTask('app-dev', ['coffee:app']);
    grunt.registerTask('app', ['app-dev', 'uglify']);

    grunt.registerTask('css', ['less']);
    grunt.registerTask('scripts', ['app']);

    // Default task
    grunt.registerTask('default', ['css', 'scripts']);

    // Install project
    grunt.registerTask('install', ['copy:fonts', 'vendor', 'default']);
};