$(document).ready(function() {
    //post new movie to API
	/*
    $('#submit-button').click( function () {
	    var apiURL = "v1/films";
	    $.ajax({
            type: 'POST',
            url: apiURL,
	        data: JSON.stringify({
	            synopsis: $('#inputSynopsis').val(),
                title: $('#inputTitle').val(),
                year: parseInt($('#inputYear').val())
            }),
	        error: function(e) {
	            console.log(e);
	        },
	        statusCode: {
	            400: function() {
	                $('#intro-help').text("Malformed JSON, record not created.");
	            }
	        },
	        dataType: "json",
            contentType: "application/json"
	    }).done(function (data) {
            //clear form and write ID
	        $('#inputSynopsis').val('');
	            $('#inputTitle').val('');
	            $('#inputYear').val('');
                $('#intro-help').text(data.message + "! ID(" + data.type + ") was created.");
            });
	    return false; //prevent page reload
	});
    */
    /*
	$('#movie-search-button').click( function() {
		//search rotten tomatoes API
		var query = $('#movie-search').val();
		$.ajax({
			method: 'GET',
			url: moviesSearchUrl + '&q=' + encodeURI(query),
			dataType: "jsonp"
		}).success( function(data) {
			//check if we have found any movies
			if (data.total > 0) {
				var movie = data.movies[0];
				$('#results-info').html('<h4>Found ' + data.total + ' results for ' + query
					+ '</h4><p>Showing first result.</p>');

				$('#movie-title-rt').text(movie.title);
				$('#movie-year-rt').text(movie.year);
				$('#critics-score-rt').text('Critics Score: ' + movie.ratings.critics_score);
				$('#audience-score-rt').text('Audience Score: ' + movie.ratings.audience_score);
				$('#synopsis-rt').text(movie.synopsis);
				$('#img-container-rt').html('<img src="' + movie.posters.original + '" />');

				//show the movie info container
				$('#movie-info-header').fadeIn("slow"); //add some transition

                //add data to fields
				$('#inputTitle').val(movie.title);
				$('#inputYear').val(movie.year);
				$('#inputSynopsis').val(movie.synopsis);
            }
		});
	return false; //prevent page reload
	});
    */
    console.log("test");
    $.ajax({
        method: 'GET',
        url: 'get.php',
        dataType: "json",
        contentType: "application/json"
        //dataType: "jsonp"
    }).success( function(data) {
        console.log("got here");
        $('#result').html(data);
    });
});
