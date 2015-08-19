$(document).ready(function() {
    $('#submit-button').click( function(e) {
        e.preventDefault(); //prevent page reload
        $.ajax({
            method: 'POST',
            url: 'request.php',
            dataType: "json",
            data: JSON.stringify({
	            msg: $('#message').val()
            }),
            contentType: "application/json"
        }).success( function(data) {
            console.log("got here");
            $('#result').html(data);
        }).error( function() {
            $('#result').html("could not reach server");
        });
        return false; //prevent page reload
    });

    //get request needs to run in a loop
    $.ajax({
        method: 'GET',
        url: 'request.php',
        dataType: "json",
        contentType: "application/json"
    }).success( function(data) {
        $('#result').html(data);
    }).error( function() {
        $('#result').html("could not reach server");
    });
});
