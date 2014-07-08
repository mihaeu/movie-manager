{{ filedump('bower_components/jquery/jquery.min.js') }}
{{ filedump('bower_components/bootstrap/dist/js/bootstrap.min.js') }}
{{ filedump('bower_components/tinysort/dist/jquery.tinysort.min.js') }}
{{ filedump('bower_components/fancybox/source/jquery.fancybox.js') }}

$(function() {

	// imdb fancybox
	$('.fancybox').fancybox();

	// sort function 
	$("input[type=radio]").bind("change", function() {
		var sortBy = $('input[name=order]:checked').val();
		var order = $('input[name=direction]:checked').val();
		$(".movie").tsort(sortBy, {order: order});
	});

	// filters
	var Filters = new function () {

		this.language = function (selectedLanguage, movieLanguages) {
			if (selectedLanguage === "all") {
				return true;
			}

			var matchFound = false;
			$.map(movieData, function(movie, id) {
				if ($.inArray(selectedLanguage, movieLanguages) !== -1)
				{
					matchFound = true;
				}
			});
			return matchFound;
		};

		this.country = function (selectedCountry, movieCountries) {
			if (selectedCountry === "all") {
				return true;
			}

			var matchFound = false;
			$.map(movieData, function(movie, id) {
				if ($.inArray(selectedCountry, movieCountries) !== -1)
				{
					matchFound = true;
				}
			});
			return matchFound;
		};

		this.genre = function (selectedGenre, movieGenres) {
			if (selectedGenre === "all") {
				return true;
			}
			
			var matchFound = false;
			$.map(movieData, function(movie, id) {
				if ($.inArray(selectedGenre, movieGenres) !== -1)
				{
					matchFound = true;
				}
			});
			return matchFound;
		};

		this.year = function (selectedYearFrom, selectedYearTo, movieYear) {
			return movieYear >= selectedYearFrom 
				&& movieYear <= selectedYearTo;
		};

		this.rating = function (minRating, movieRating) {
			return movieRating >= minRating;
		};

		this.text = function (query, title, directors, cast) {
			// console.log(query);
			if (query.length === 0) {
				return true;
			}

			var regex = '',
				input = '',
				matchFound = false;

			input = query.replace(" ", "");
			input = input.split("");
			input = input.join(".*");
			regex = new RegExp(".*" + input + ".*", "i");

			$.each(cast, function(id, cast) {
				if (regex.test(cast.name)) {
					matchFound = true;
				}
			});

			$.each(directors, function(id, director) {
				if (regex.test(director)) {
					matchFound = true;
				}
			});

			return matchFound || regex.test(title);
		};

		this.all = function (movie) {
			return Filters.language($("#languages").val(), movie.languages)
				&& Filters.genre($("#genre").val(), movie.genre)
				&& Filters.country($("#country").val(), movie.countries)
				&& Filters.rating($("#rating").val(), movie.rating)
				&& Filters.year($("#year-from").val(), $("#year-to").val(), movie.year)
				&& Filters.text($("#filter").val(), movie.title, movie.directors, movie.cast);
		};

	};

	var movieData = $.parseJSON('{{ moviesJson|raw }}');

	// filter function
	$("#languages, #country, #year-from, #year-to, #genre, #rating").change(function() {

		$.each(movieData, function (id, movie) {
			
			if (Filters.all(movie)) {
				$("#" + id).show();
			} else {
				$("#" + id).hide();
			}

		});

	});

	$("#filter").on("keyup", function() {

		$.each(movieData, function (id, movie) {

			if (Filters.all(movie)) {
				$("#" + id).show();
			} else {
				$("#" + id).hide();
			}

		});

	});

});