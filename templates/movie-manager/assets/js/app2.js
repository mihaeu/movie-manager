/* global jQuery, Handlebars */
jQuery(function ($) {
    'use strict';

    var App = {
        init: function () {
            this.movies = this.fetchMovies();
            this.cacheElements();
            this.bindEvents();

            console.log(this.movies);
        },
        fetchMovies: function () {
            $.ajax({
                url: '/movies',
                data: {
                    dir: '/media/media/videos/movies'
                },
                success: this.renderMovies
            });
        },
        renderMovies: function (movies) {
            console.log(movies);
            var source   = $("#entry-template").html();
            var template = Handlebars.compile(source);
            $('main').html(template(movies));
        },
        bindEvents: function () {

        },
        cacheElements: function () {

        }
    };

    App.init();
});