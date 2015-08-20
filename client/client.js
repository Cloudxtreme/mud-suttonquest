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
        for (var i = 0; i < rows.length; i++) {
            var nodes = rows[i].split('');
            for (var j = 0; j < nodes.length; j++) {
                //var temp = '<div class="node" id="' + 1 +'"></div>');
                var nodetype = '';
                switch(nodes[j]) {
                    case 'T': nodetype = 'room'; break;
                    case '-': nodetype = 'opaque'; break;
                    case 'M': nodetype = 'megabeast-spawn'; break;
                    case 'O': nodetype = 'objective'; break;
                    case 'S': nodetype = 'spawn'; break;
                }
                $('<div class="node"></div>').addClass(nodetype).css({top: 35 * i, left: 35 * j}).appendTo('#map');
            }
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
            $('#result').append('<p>' + data + '</p>');
            var height = document.getElementById('result').scrollHeight - $('#result').height();
            $('#result').scrollTop(height);
        }).error( function() {
            $('#result').append('<p>could not reach server</p>');
            var height = document.getElementById('result').scrollHeight - $('#result').height();
            $('#result').scrollTop(height);
        });
        return false; //prevent page reload
    });
});
