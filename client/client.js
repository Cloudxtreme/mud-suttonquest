$(document).ready(function() {
    var playerID;
    var playerName;
    var playerX;
    var playerY;
    var interval;
    var world_grid = [];
    var other_occupants = [];

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
                //process the updates
                if(json.updates.length > 0) {
                    $.each(json.updates, function(index, update) {
                        if(update.update_type == "say") {
                            writeToChat('[<b>' + update.name + '</b>]: ' + update.update_body);
                        }
                    });
                }
                //process the others in the room
                check_others(json.others);
            } else {
                clearInterval(interval);
                writeToChat("Could not connect to server, please refresh the page to try again.");
            }
        }).error( function() {
            clearInterval(interval);
            writeToChat("Could not connect to server, please refresh the page to try again.");
        });
    }

    //inital load
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

    //event handler for submit
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
                    //write new room desc to chat, update location, and erase other occupants array
                    other_occupants.length = 0; //erase
                    writeToChat(json.desc);
                    world_grid[playerX][playerY].removeClass("current-location");
                    playerX = json.locationX;
                    playerY = json.locationY;
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

    //check if others are in the room, and update chat window accordingly
    function check_others(others) {
        //add all others to the array
        var new_player = false;
        $.each(others, function(index, other) {
            if($.inArray(other, other_occupants) != -1) {
                //exists
            } else {
                new_player = true;
                other_occupants.push(other);
            }
        });

        //if others is smaller than other_occupants, means we have lost a player.
        if(others.length < other_occupants.length) {
            other_occupants.length = 0; //clear the array
        }

        //if new player is triggered, write to chat window, i.e. new player entered the room
        if(new_player) {
            if(others.length > 0) {
                if (others.length == 1) {
                    var str = 'Looking around the room, you see <b>' + others[0] + '</b>. He doesn\'t look pleased to see you.';
                    writeToChat(str);
                } else {
                    var str = 'Looking around the room, you see the following individuals, ';
                    $.each(others, function(index, other) {
                        if(!(index == others.length - 1)) {
                            str += '<b>' + other + '</b>, ';
                        } else {
                            str += 'and <b>' + other + '</b>.';
                        }
                    });
                    str += ' They don\'t look pleased to see you.';
                    writeToChat(str);
                }
            } else {
                writeToChat("You don't see any other individuals in this room.");
            }
        }
    }

    function writeToChat(str) {
        $('#chat-log').append('<p>' + str + '</p>');
        var height = document.getElementById('chat-log').scrollHeight - $('#chat-log').height();
        $('#chat-log').scrollTop(height);
    }
});
