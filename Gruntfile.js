module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        bootstrap: 'resources/assets/vendor/bootstrap',
        vendor: 'resources/assets/vendor',
        less_src: 'resources/assets/less',
        app: 'resources/assets/coffee',
        dist: '../../../public/cruddy',

        coffee: {

            options: { sourceMap: true },

            app: {
                src: [

                    '<%= app %>/init.coffee',
                    '<%= app %>/helpers.coffee',
                    '<%= app %>/factory.coffee',
                    '<%= app %>/cruddy.coffee',
                    '<%= app %>/view.coffee',
                    '<%= app %>/formData.coffee',
                    '<%= app %>/attribute.coffee',
                    '<%= app %>/datasource.coffee',
                    '<%= app %>/searchDataSource.coffee',
                    '<%= app %>/pagination.coffee',
                    '<%= app %>/datagrid.coffee',
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
                    '<%= app %>/inputs/numberFilter.coffee',
                    '<%= app %>/inputs/datetime.coffee',

                    // Layout
                    '<%= app %>/layout/element.coffee',
                    '<%= app %>/layout/container.coffee',
                    '<%= app %>/layout/baseFieldContainer.coffee',
                    '<%= app %>/layout/fieldset.coffee',
                    '<%= app %>/layout/tabPane.coffee',
                    '<%= app %>/layout/row.coffee',
                    '<%= app %>/layout/col.coffee',
                    '<%= app %>/layout/field.coffee',
                    '<%= app %>/layout/text.coffee',
                    '<%= app %>/fieldList.coffee',
                    '<%= app %>/layout/layout.coffee',

                    // Fields
                    '<%= app %>/fields/baseView.coffee',
                    '<%= app %>/fields/inputView.coffee',
                    '<%= app %>/fields/base.coffee',
                    '<%= app %>/fields/input.coffee',
                    '<%= app %>/fields/prependAppendWrapper.coffee',
                    '<%= app %>/fields/text.coffee',
                    '<%= app %>/fields/datetime.coffee',
                    '<%= app %>/fields/boolean.coffee',
                    '<%= app %>/fields/baseRelation.coffee',
                    '<%= app %>/fields/relation.coffee',
                    '<%= app %>/fields/file.coffee',
                    '<%= app %>/fields/image.coffee',
                    '<%= app %>/fields/imageFormatter.coffee',
                    '<%= app %>/fields/slug.coffee',
                    '<%= app %>/fields/enum.coffee',
                    '<%= app %>/fields/embeddedView.coffee',
                    '<%= app %>/fields/embeddedItemView.coffee',
                    '<%= app %>/fields/relatedCollection.coffee',
                    '<%= app %>/fields/embedded.coffee',
                    '<%= app %>/fields/number.coffee',
                    '<%= app %>/fields/computed.coffee',

                    // Columns
                    '<%= app %>/columns/base.coffee',
                    '<%= app %>/columns/proxy.coffee',
                    '<%= app %>/columns/computed.coffee',
                    '<%= app %>/columns/viewButton.coffee',
                    '<%= app %>/columns/deleteButton.coffee',

                    // Filters
                    '<%= app %>/filters/base.coffee',
                    '<%= app %>/filters/proxy.coffee',

                    // Formatters
                    '<%= app %>/formatters/base.coffee',
                    '<%= app %>/formatters/image.coffee',
                    '<%= app %>/formatters/plain.coffee',

                    // Entity
                    '<%= app %>/entity/entity.coffee',
                    '<%= app %>/entity/instance.coffee',
                    '<%= app %>/entity/page.coffee',
                    '<%= app %>/entity/form.coffee',

                    '<%= app %>/app.coffee',
                    '<%= app %>/router.coffee',
                    '<%= app %>/nav.coffee'
                ],

                dest: 'public/js/app.js'
            }
        },

        concat: {
            vendor: {
                src: [
                    // JQuery
                    '<%= vendor %>/jquery/jquery.js',
                    '<%= vendor %>/fancybox/source/jquery.fancybox.js',
                    '<%= vendor %>/jquery-maskedinput/dist/jquery.maskedinput.js',
                    '<%= app %>/jquery.query.js',

                    '<%= vendor %>/underscore/underscore.js',
                    '<%= vendor %>/backbone/backbone.js',
                    '<%= vendor %>/moment/moment.js',
                    '<%= vendor %>/moment/lang/*.js',
                    '<%= vendor %>/marked/lib/marked.js',

                    // Bootstrap components
                    '<%= bootstrap %>/js/tab.js',
                    '<%= bootstrap %>/js/dropdown.js',
                    '<%= bootstrap %>/js/tooltip.js'
                ],

                dest: 'public/js/vendor.js'
            }
        },

        uglify: {
            options: { sourceMap: true },

            app: {
                src:  'public/js/app.js',
                dest: 'public/js/app.min.js',

                options: {
                    sourceMapIn: 'public/js/app.js.map'
                }
            },

            vendor: {
                src:  'public/js/vendor.js',
                dest: 'public/js/vendor.min.js'
            }
        },

        less: {
            styles: {
                options: {
                    sourceMap: true,
                    sourceMapFilename: 'public/css/styles.min.css.map',
                    sourceMapURL: 'styles.min.css.map',
                    sourceMapBasepath: 'assets',
                    outputSourceFiles: true,

                    compress: true
                },

                files: { 'public/css/styles.min.css': '<%= less_src %>/styles.less' }
            }
        },

        copy: {
            fonts: {
                expand: true,
                cwd: '<%= bootstrap %>/dist/fonts/',
                src: '*',
                dest: 'public/fonts/'
            },

            fancybox: {
                expand: true,
                cwd: '<%= vendor %>/fancybox/source',
                src: ['*.png', '*.gif'],
                dest: 'public/css'
            },

            dist_scripts: {
                expand: true,
                cwd: 'public/js',
                src: '*',
                dest: '<%= dist %>/js'
            },

            dist_styles: {
                expand: true,
                cwd: 'public/css',
                src: '*',
                dest: '<%= dist %>/css'
            }
        },

        watch: {

            styles: {
                files: '<%= less_src %>/**/*.less',
                tasks: [ 'less:styles', 'copy:dist_styles' ]
            },

            coffee: {
                files: '<%= app %>/**/*.coffee',

                tasks: [ 'scripts', 'copy:dist_scripts' ]
            },

            reload: {
                files: [
                    'public/css/*.min.css',
                    'public/js/*.js'
                ],

                options: { livereload: true }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-coffee');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Concat all needed vendor files
    grunt.registerTask('vendor', [ 'concat:vendor', 'uglify:vendor', 'copy:fancybox', 'copy:fonts' ]);

    grunt.registerTask('styles', [ 'less' ]);
    grunt.registerTask('scripts', [ 'coffee', 'uglify:app' ]);

    // Default task
    grunt.registerTask('default', [ 'styles', 'scripts' ]);

    // Install project
    grunt.registerTask('install', [ 'vendor', 'default' ]);
};