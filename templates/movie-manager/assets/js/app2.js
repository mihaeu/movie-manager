/* global jQuery, Handlebars */
jQuery(function ($) {
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
                url: '/movies',
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
            console.log(movies);
            var source   = $("#entry-template").html();
            var template = Handlebars.compile(source);
            $('main').html(template(movies));
        },

        bindEvents: function () {
            // ...
        },

        cacheElements: function () {
            // ...
        }
    };

    App.init();
});