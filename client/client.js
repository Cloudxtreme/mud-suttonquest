$(document).ready(function() {
    var playerID;
    var playerName;
    var playerX;
    var playerY;
    var interval;
    var world_grid = [];

    //get updates every second
    function start() {
        interval = setInterval(get_update, 2500);
    }

    function get_update() {
        $.ajax({
            method: 'GET',
            url: 'request.php?cmd=update&playerid=' + playerID,
            dataType: 'json',
            contentType: 'application/json'
        }).success( function(data) {
            if(data) {
                console.log(data);
                var json = JSON.parse(data);
                console.log(json['update_type']);
                //for each update
                $.each(json, function(index, update) {
                    if(update.update_type == "say") {
                        writeToChat('[' + update.time_queued +']' + ':[' + update.name + ']: ' + update.update_body);
                    }
                });
            } else {
                clearInterval(interval);
                writeToChat("Could not connect to server, please refresh the page to try again.");
            }
        }).error( function() {
            clearInterval(interval);
            writeToChat("Could not connect to server, please refresh the page to try again.");
        });
    }

    //perform initial load
    $.ajax({
        method: 'GET',
        url: 'request.php?cmd=init',
        dataType: 'json',
        contentType: 'application/json'
    }).success( function(data) {
        if(data) {
            console.log(data);
            var json = JSON.parse(data);

            //setup our player data, and welcome message
            console.log("playerID" + json.playerID);
            playerID = json.playerID;
            playerName = json.player_name;
            playerX = json.locationX;
            playerY = json.locationY;
            writeToChat(json.welcome_message);

            //generate map
            var rows = json['worldstr'].split('\n');
            for (var i = 0; i < rows.length; i++) {
                var nodes = rows[i].split('');
                world_grid[i] = [];
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
                    world_grid[i][j] = $('<div class="node"></div>');
                    world_grid[i][j].addClass(nodetype).css({top: 30 * i, left: 30 * j}).appendTo('#map');
                }
            }
            //set location in GUI
            world_grid[playerX][playerY].addClass("current-location");

            //start the updates
            start();
        } else {
            writeToChat("Could not connect to server, please refresh the page to try again.");
        }
    }).error( function() {
        writeToChat("Could not connect to server, please refresh the page to try again.");
    });

    $('#submit-button').click( function(e) {
        e.preventDefault(); //prevent page reload

        //split up string after first space, i.e. <command> <command of body>
        var input = $('#command').val();
        $('#scommand').val(''); // clear command
        var command = input.substr(0, input.indexOf(' '));
        var cmd_body = input.substr(input.indexOf(' ') + 1);

        $.ajax({
            method: 'POST',
            url: 'request.php',
            dataType: 'json',
            data: JSON.stringify({
	            cmd: command,
                body: cmd_body,
                playerID: playerID,
                player_name: playerName,
                locationX: playerX,
                locationY: playerY
            }),
            contentType: "application/json"
        }).success( function(data) {
            if(data) {
                console.log(json);
                var json = JSON.parse(data);
                if(json.msg == 'move-success') {
                    writeToChat(json.desc);
                    world_grid[playerX][playerY].removeClass("current-location");
                    playerX = json.locationX;
                    playerY = json.locationY;
                    if(json.others.length > 0) {
                        if (json.others.length == 1) {
                            var others = 'Looking around the room, you see <b>' + json.others[0].name + '</b>. He doesn\'t look pleased to see you.';
                            writeToChat(others);
                        } else {
                            var others = 'Looking around the room, you see the following individuals, ';
                            $.each(json.others, function(index, player) {
                                if(!(index == json.others.length - 1)) {
                                    others += '<b>' + player.name + '</b>, ';
                                } else {
                                    others += 'and <b>' + player.name + '</b>.';
                                }
                            });
                            others += ' They don\'t look pleased to see you.';
                            writeToChat(others);
                        }
                    } else {
                        writeToChat("You don't see any other individuals in the room.");
                    }
                    world_grid[playerX][playerY].addClass("current-location");
                }
                if(json.msg == 'say-success') {
                    writeToChat(json.desc);
                }
                if(json.msg == 'error') {
                    writeToChat(json.desc);
                }
            }
        }).error( function() {
            writeToChat("Could not connect to server, please refresh the page to try again.");
        });
        return false; //prevent page reload
    });

    function writeToChat(string) {
        $('#chat-log').append('<p>' + string + '</p>');
        var height = document.getElementById('chat-log').scrollHeight - $('#chat-log').height();
        $('#chat-log').scrollTop(height);
    }
});
