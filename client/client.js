$(document).ready(function() {
    $('#submit-button').click( function(e) {
        e.preventDefault(); //prevent page reload

        //split up string after first space, i.e. <command> <command of body>
        var input = $('#command').val();
        var command = input.substr(0, input.indexOf(' '));
        var cmd_body = input.substr(input.indexOf(' ') + 1);

        $.ajax({
            method: 'POST',
            url: 'request.php',
            dataType: "json",
            data: JSON.stringify({
	            cmd: command,
                body: cmd_body
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
