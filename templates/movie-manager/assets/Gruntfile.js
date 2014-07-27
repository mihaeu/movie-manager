/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      clean: {
          build: {
              options: {
                  force: true
              },
              src: ['../../../public_html/css', '../../../public_html/js']
          }
      },

      less: {
          build: {
              files: {
                  '../../../public_html/css/styles.css': 'less/styles.less'
              }
          }
      },

      cssmin: {
          minify: {
              expand: true,
              cwd: '../../../public_html/css/',
              src: ['*.css', '!*.min.css'],
              dest: '../../../public_html/css/',
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
                  '../../../public_html/js/scripts.min.js': [
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
              dest: '../../../public_html/fonts/'
          }
      },

      uncss: {
          options: {
              csspath: '../../public_html/'
          },
          dist: {
              files: {
                  '../../../public_html/css/styles.min.css': ['../index.html.twig']
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
