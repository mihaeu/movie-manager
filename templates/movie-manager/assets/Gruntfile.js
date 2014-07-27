/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      config: {
          fontOutput: '../../../public_html/fonts',
          cssOutput: '../../../public_html/css',
          jsOutput: '../../../public_html/js'
      },

      clean: {
          build: {
              options: {
                  force: true
              },
              src: ['<%= config.cssOutput %>', '../../../public_html/js']
          }
      },

      less: {
          build: {
              files: {
                  '<%= config.cssOutput %>/styles.css': 'less/styles.less'
              }
          }
      },

      cssmin: {
          minify: {
              expand: true,
              cwd: '<%= config.cssOutput %>/',
              src: ['*.css', '!*.min.css'],
              dest: '<%= config.cssOutput %>/',
              ext: '.min.css'
          }
      },

      jshint: {
          all: ['Gruntfile.js', 'js/*.js']
      },

      uglify: {
          build: {
              options: {
                  compress: true
              },
              files: {
                  '<%= config.jsOutput %>/scripts.min.js': [
                      'bower_components/jquery/dist/jquery.min.js',
                      'js/app.js'
                  ]
              }
          }
      },

      copy: {
          main: {
              flatten: true,
              expand: true,
              src: 'bower_components/bootstrap/fonts/*',
              dest: '<%= config.fontOutput %>/'
          }
      },

      uncss: {
          options: {
              csspath: '../../public_html/'
          },
          dist: {
              files: {
                  '<%= config.cssOutput %>/styles.min.css': ['../index.html.twig']
              }
          }
      },

      watch: {
          css: {
              files: ['**/*.less', 'uncss'],
              tasks: ['css'],
              options: {
                  livereload: true
              }
          },
          scripts: {
              files: ['**/*.js'],
              tasks: ['js'],
              options: {
                  livereload: true
              }
          },
          templates: {
              files: ['../*.twig'],
              tasks: [],
              options: {
                  livereload: true
              }
          }
      }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-uncss');

  // Default task.
  grunt.registerTask('css',     ['less', 'cssmin', 'uncss']);
  grunt.registerTask('js',      ['jshint', 'uglify']);
  grunt.registerTask('default', ['clean', 'css', 'js', 'watch']);

};
