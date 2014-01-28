module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        bootstrap: 'assets/vendor/bootstrap',
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
                    '<%= app %>/formData.coffee',
                    '<%= app %>/factory.coffee',
                    '<%= app %>/attribute.coffee',
                    '<%= app %>/datasource.coffee',
                    '<%= app %>/searchDataSource.coffee',
                    '<%= app %>/pagination.coffee',
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
                    '<%= app %>/inputs/fileList.coffee',
                    '<%= app %>/inputs/imageList.coffee',
                    '<%= app %>/inputs/search.coffee',
                    '<%= app %>/inputs/slug.coffee',
                    '<%= app %>/inputs/select.coffee',
                    '<%= app %>/inputs/code.coffee',
                    '<%= app %>/inputs/markdown.coffee',

                    // Fields
                    '<%= app %>/fields/field.coffee',
                    '<%= app %>/fields/input.coffee',
                    '<%= app %>/fields/datetime.coffee',
                    '<%= app %>/fields/boolean.coffee',
                    '<%= app %>/fields/relation.coffee',
                    '<%= app %>/fields/file.coffee',
                    '<%= app %>/fields/image.coffee',
                    '<%= app %>/fields/slug.coffee',
                    '<%= app %>/fields/enum.coffee',
                    '<%= app %>/fields/markdown.coffee',
                    '<%= app %>/fields/code.coffee',

                    // Columns
                    '<%= app %>/columns/column.coffee',

                    // Formatters
                    '<%= app %>/formatters/base.coffee',
                    '<%= app %>/formatters/image.coffee',
                    '<%= app %>/formatters/plain.coffee',

                    // Related
                    '<%= app %>/related/related.coffee',

                    // Entity
                    '<%= app %>/entity/entity.coffee',
                    '<%= app %>/entity/instance.coffee',
                    '<%= app %>/entity/page.coffee',
                    '<%= app %>/entity/form.coffee',

                    '<%= app %>/app.coffee',
                ],

                dest: 'public/js/app.js',
            },
        },

        concat: {
            vendor: {
                src: [
                    // JQuery
                    '<%= vendor %>/jquery/jquery.js',
                    '<%= vendor %>/fancybox/source/jquery.fancybox.js',
                    '<%= vendor %>/jquery-maskedinput/dist/jquery.maskedinput.js',

                    '<%= vendor %>/underscore/underscore.js',
                    '<%= vendor %>/backbone/backbone.js',
                    '<%= vendor %>/moment/moment.js',
                    '<%= vendor %>/moment/lang/ru.js',
                    '<%= vendor %>/markdown/lib/markdown.js',

                    // Ace
                    // '<%= vendor %>/ace-builds/src-noconflict/ace.js',
                    // '<%= vendor %>/ace-builds/src-noconflict/mode-markdown.js',
                    // '<%= vendor %>/ace-builds/src-noconflict/theme-<%= ace_theme %>.js',

                    // Bootstrap components
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

                    sourceMappingURL: function (name) {
                        return name.replace("public/js/", "") + '.map';
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
                    'public/css/styles.min.css': [
                        '<%= vendor %>/fancybox/source/jquery.fancybox.css',
                        '<%= less_src %>/styles/styles.less',
                    ]
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

            fancybox: {
                expand: true,
                cwd: '<%= vendor %>/fancybox/source',
                src: ['*.png', '*.gif'],
                dest: 'public/css',
            },

            ace: {
                expand: true,
                cwd: '<%= vendor %>/ace-builds/src-min-noconflict',
                src: '*.js',
                dest: 'public/js/ace',
            }
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

            reload: {
                files: [
                    'public/css/*.min.css',
                    'public/js/*.js',
                ],

                options: { livereload: true },
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-coffee');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Concat all needed vendor files
    grunt.registerTask('vendor', ['concat:vendor', 'copy:fancybox', 'copy:ace']);

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