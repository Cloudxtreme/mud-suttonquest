$(document).ready(function() {
    //get updates every second
    /*
    setInterval(function() {
        $.ajax({
            method: 'GET',
            url: 'request.php?cmd=update&playerid=1',
            dataType: 'json',
            contentType: 'application/json'
        }).success( function(data) {
            $('#result').append(data);
        }).error( function() {
            $('#result').append("could not reach server");
        });
    }, 1000);
    */

    //perform initial load
    $.ajax({
        method: 'GET',
        url: 'request.php?cmd=init&playerid=1',
        dataType: 'json',
        contentType: 'application/json'
    }).success( function(data) {
        //gets the map string, splits it from newline, then analyses char by char
        var rows = data.split('\n');
        for (row of rows) {
            var nodes = row.split('');
            for (node of nodes) {

                switch(node) {
                    case 'T':
                        $('#map').append('<div class="node"></div>');
                        break;
                    case '-':
                        $('#map').append('<div class="node"></div>');
                        break;
                    case 'M':
                        $('#map').append('<div class="node"></div>');
                        break;
                    case 'O':
                        $('#map').append('<div class="node"></div>');
                        break;
                    case 'S':
                        $('#map').append('<div class="node"></div>');
                        break;
                }
            }
            //$('#result').append('<p>' + level + '</p>');
        }
    }).error( function() {
        $('#result').append("could not reach server");
    });

    $('#submit-button').click( function(e) {
        e.preventDefault(); //prevent page reload

        //split up string after first space, i.e. <command> <command of body>
        var input = $('#command').val();
        var command = input.substr(0, input.indexOf(' '));
        var cmd_body = input.substr(input.indexOf(' ') + 1);

        $.ajax({
            method: 'POST',
            url: 'request.php',
            dataType: 'json',
            data: JSON.stringify({
	            cmd: command,
                body: cmd_body
            }),
            contentType: "application/json"
        }).success( function(data) {
            $('#result').append(data);
        }).error( function() {
            $('#result').append("could not reach server");
        });
        return false; //prevent page reload
    });
});
