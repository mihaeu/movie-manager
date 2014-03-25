$(function() {

    ////////////////////////
    // Index all entries //
    ////////////////////////
    var index = 0;
    $("tr:odd").each(function() {
        $('td:first', this).html(++index);
    });

    /////////////////////////////
    // Check entries and mark //
    /////////////////////////////
    $("tr:odd").each(function() {
        var numberOfRequirementsMet = $('.icon-ok', this).length;
        if (numberOfRequirementsMet !== 5) {
            $(this).addClass('warning');
        } else {
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
            $(this).addClass("btn-success");
        }
    });

    ////////////////////////
    // IMDb Search event //
    ////////////////////////
    $("button.go-imdb").bind("click", function() {
        var chunks = [];
        $(this).parent().parent().find('span.btn-success').each(function() {
            chunks.push($.trim($(this).html()));
        });

        var url = "index.php/imdb",
            postData = { query: chunks.join("%20") },
            suggestions = [],
            current = $(this);

        $.post(url, postData, function(data) {
            suggestions.push(data);
            var suggestionsRow = current.parent().parent().next();
            suggestionsRow.find(".suggestions").html(suggestions);
            suggestionsRow.fadeIn();

            //////////////////////////
            // Movie rename event //
            //////////////////////////
            $(".rename").bind("click", function() {
                var id = $(this).parent().find('.imdb-id').val(),
                    file = $(this).parent().parent().next().val();
                if (id.length < 4 || file.length < 10) {
                    alert(error);
                } else {
                    var parentTr = $(this).parent().parent().parent().parent();
                    parentTr.fadeOut();
                    parentTr.prev().find("td:last").html("<img src='assets/img/ajax-loader.gif'></img>");
                }
                $.post('index.php/movie',
                    { "id": id, "file": file }, function() {
                    parentTr.prev().removeClass("warning").addClass("success");
                    $('.link, .folder, .screenshot, .format, .poster', parentTr.prev())
                        .html("<i class='icon-ok'></i>");
                    parentTr.prev().find("td:last").html("<i class='icon-check'></i>");
                }).fail(function() {
                    alert("error");
                });
            });
        });
    });

});