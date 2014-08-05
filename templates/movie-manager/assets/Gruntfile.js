/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      config: {
          webRoot: '../../../public_html',
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
                      'bower_components/handlebars/handlebars.runtime.min.js',
                      '<%= config.jsOutput %>/templates.js',
                      'js/app2.js'
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
          dist: {
              files: {
                  '<%= config.cssOutput %>/styles.min.css': ['<%= config.webRoot %>/index.html']
              }
          }
      },

      handlebars: {
          options: {
              namespace: 'MovieManager.Templates',
              processName: function(filePath) {
                  return filePath.replace(/^\.\.\//, '').replace(/\.hbs$/, '');
              }
          },
          all: {
              files: {
                  "<%= config.jsOutput %>/templates.js": ["../**/*.hbs"]
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
              files: ['<%= config.webRoot %>/index.html'],
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
  grunt.loadNpmTasks('grunt-contrib-handlebars');
  grunt.loadNpmTasks('grunt-uncss');

  // Default task.
  grunt.registerTask('css',     ['less', 'cssmin', 'uncss']);
  grunt.registerTask('js',      ['jshint', 'handlebars', 'uglify']);
  grunt.registerTask('default', ['clean', 'css', 'js', 'watch']);

};
