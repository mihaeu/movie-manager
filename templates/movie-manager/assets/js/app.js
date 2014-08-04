$(function() {

    var app = function() {

    };

    // check movie requirements
    $(".movie").each(function() {
        var checkedItems = $('.glyphicon-ok', this);
        if (null !== checkedItems && checkedItems.length == 5) {
            $(this).addClass('success');
        }
    });

    ////////////////////////////
    // Hide suggesionts even //
    ////////////////////////////
    $(".hide-suggestions").bind('click', function() {
        $(this).parent().parent().parent().fadeOut();
    });

    //////////////////////////////////
    // Highlight chunks on select. //
    //////////////////////////////////
    $(".chunk").bind("click", function() {
        if ($(this).hasClass("btn-success")) {
            $(this).removeClass("btn-success");
        } else {
            $(this).removeClass("btn-default");
            $(this).addClass("btn-success");
        }
    });

    ////////////////////////
    // IMDb Search event //
    ////////////////////////
    $("button.go-imdb").bind("click", function() {
        var chunks = [];
        $(this).parent().parent().find('.btn-success').each(function() {
            chunks.push($.trim($(this).html()));
        });

        var url = "/suggestions",
            getData = { query: chunks.join("%20") },
            suggestions = [],
            current = $(this);

        $.get(url, getData, function(data) {
            var suggestions = [],
                $suggestionsRow = current.parent().parent().next();

            $.each(data, function (index, movie) {
                suggestions.push(
                    "<img src='" + movie.poster + "' alt='poster' />" + "<h3>" + movie.title + " (" + movie.year + ")</h3>" + "<button type='button' class='btn btn-warning col-md-2 rename'>Rename movie</button>" + "<input type='hidden' class='imdb-id' value='" + movie.id + "' />"
                );
            });
            $suggestionsRow
                .find(".suggestions")
                .html("<li>" + suggestions.join("</li><li>") + "</li>");
            $suggestionsRow.fadeIn();

            //////////////////////////
            // Movie rename event //
            //////////////////////////
            $(".rename").bind("click", function() {
                var $suggestionsTr, $movieTr;
                var id   = $(this).next().val(),
                    file = $(this).parent().parent().next().val();

                if (id.length < 1 || file.length < 10) {
                    alert("There's something wrong with the movie id or file.");
                    return;
                }

                $suggestionsTr = $(this).parent().parent().parent().parent();
                $movieTr       = $suggestionsTr.prev();

                $suggestionsTr.fadeOut();
                $movieTr.find("td:last").html("<img src='media/ajax-loader.gif' />");

                $.get('/movie',
                    { "id": id, "file": file },
                    function() {
                        $movieTr
                            .removeClass("warning")
                            .addClass("success");
                        $('.link, .folder, .screenshot, .format, .poster', $movieTr)
                            .html("<i class='icon-ok'></i>");
                        $movieTr
                            .find("td:last")
                            .html("<i class='icon-check'></i>");
                    }).fail(function() {
                        alert("Unable to process movie.");
                    });
            });
        });
    });

});