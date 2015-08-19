$(document).ready(function() {
    $('#submit-button').click( function(e) {
        e.preventDefault();
        console.log("post test");
        $.ajax({
            method: 'POST',
            url: 'get.php',
            dataType: "json",
            data: JSON.stringify({
	            msg: $('#message').val()
            }),
            contentType: "application/json"
        }).success( function(data) {
            console.log("got here");
            $('#result').html(data);
        });
        return false; //prevent page reload
    });

    //get request needs to run in a loop
    console.log("test");
    $.ajax({
        method: 'GET',
        url: 'get.php',
        dataType: "json",
        contentType: "application/json"
    }).success( function(data) {
        console.log("got here");
        $('#result').html(data);
    });
});
