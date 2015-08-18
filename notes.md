#REQUIREMENTS

+Multiuser dungeon made up of Rooms.
+Move either north, south, east, west, up or down in each room. Will need z-levels
+Need chat system, including tell <username> (person), yell <message> (world), say<message> (room). Might be integrated into command system.
+Classes for World, Place/Room, Items?, Player, Action/Command?, Task?

#APPROACH
Build world from text file? Comprise world of 2d (3d?) Array of Rooms, x-y-(z?) adjacent rooms are automatically connected.
Store user stuff in database.
Use WebSockets over AJAX approach - more scalable and less bandwidth than with AJAX requests.

2 Opposing forces, lets call the monsters and humans. You fight to capture a territory, you can capture a room if it's empty, and adjacent block is also owned by your team. A room is automatically captured if all adjacent rooms belong to a team. If an enemy is in the room, you fight. Every now and then, a megabeast spawns somewhere random, a heads towards the team with the most territory/rooms and begins killing players, and recapping rooms (to neutral).

Server will send messages/updates to all connected clients, and will choose what to send accordingly.

#BRIEF MULTIUSER DUNGEON
You are an adventurer in a virtual world made up of unit rooms where each room is either transparent or solid. You can move in any of these 6 directions: north, south, east, west, up, down. Each move is considered to take exactly one room and you can move only through transparent rooms.

When in a room, you can see a description of the room and anything or anyone else in that room. Other adventurers also live in this world and from time to time, you will bump into them.

You can interact with other people by typing "say <dialog>" into a command prompt.

This will send a chat message to everyone in the room you are in. You can alternatively choose to type "tell <person_name> <dialog>".  Moreover, you can type "yell <dialog>" to yell across the entire world. Other commands include "<direction>" to move around, such as "north", "west".

Commands should be flexible enough to extend to more innovative commands at a later time like, "pickup <item>", "fight <person>" or "put <item> <item>", but these commands need not be implemented.

How you display this world is entirely up to you. Text based display with a command prompt to input commands would probably be the default approach.

#THINGS TO CONSIDER
Please Consider any race conditions that might arise with many users in the same room at once(100+). Whether or not you implement the solution to these race conditions, please address them in comments with how you would solve them. Some further thoughts to include are what kinds of problems can you foresee as the number of users in your virtual world scales up? What about if you start interacting with multiple monsters at once?

#WORLD DATA FORMAT
Input can be however you want to describe the world, the only restriction is that it should be both flexible and scalable.

#LAST NOTE
We will mainly look at your approach to the problem, your algorithm, code clarity, and the design choices made. We prefer that you program this in PHP.  Also, your code should all be original. Do not include or rely on any external frameworks in your solution.
