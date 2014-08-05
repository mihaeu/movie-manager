/* global jQuery, MovieManager.Templates */
(function ($, templates) {
    'use strict';

    var App = {
        /**
         * Initializes the application.
         */
        init: function () {
            this.movies = this.fetchMovies();
            this.cacheElements();
            this.bindEvents();
        },

        /**
         * Fetches movies from the API and renders the result.
         */
        fetchMovies: function () {
            $.ajax({
                url: 'api.php/movies',
                data: {
                    dir: '/media/media/videos/movies'
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
        },

        bindEvents: function () {
            // ...
        },

        cacheElements: function () {
            // ...
        }
    };

    App.init();
})(jQuery, MovieManager.Templates);