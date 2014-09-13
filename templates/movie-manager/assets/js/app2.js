/* global jQuery, MovieManager.Templates */
(function ($, templates) {
    'use strict';

    var App = {
        /**
         * Initializes the application.
         */
        init: function () {
            this.movies = this.fetchMovies('/media/media/videos/movies');
            this.cacheElements();
            this.bindEvents();
        },

        /**
         * Fetches movies from the API and renders the result.
         */
        fetchMovies: function (directory) {
            $.ajax({
                url: 'api.php/movies',
                data: {
                    dir: directory
                },
                success: this.renderMovies
            });
        },

        /**
         * Renders movies using handlebars
         *
         * @param movies
         */
        renderMovies: function (movies) {
            $('main').html(templates.list(movies));
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $('.form-search').on('submit', function (event) {
                event.preventDefault();
                self.fetchMovies($('.search-query').val());
            });

            $(".chunk").on("click", function() {
                if ($(this).hasClass("btn-success")) {
                    $(this).removeClass("btn-success");
                } else {
                    $(this).removeClass("btn-default");
                    $(this).addClass("btn-success");
                }
            });
        },

        cacheElements: function () {
            // ...
        }
    };

    App.init();
})(jQuery, MovieManager.Templates);