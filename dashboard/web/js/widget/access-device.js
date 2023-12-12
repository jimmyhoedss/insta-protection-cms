const MSG_ORIGIN_NODE_SERVER	 = "0";
const MSG_ORIGIN_DASHBOARD		 = "1";
const MSG_ORIGIN_DEVICE			 = "10";
const MSG_ESTABLISH_CONNECTION	 = "0";
const MSG_PING					 = "1";
const MSG_PONG					 = "2";
const MSG_SET_UUID				 = "3";
const MSG_SET_DEVICE_ID			 = "4";
const MSG_SET_SOCKET_ID			 = "5";
const MSG_UNLOCK				 = "6";
const MSG_BUTTON_PRESS			 = "7";

const HEARTBEAT_INTERVAL		 = 3 //sec
const DEAD_INTERVAL				 = 12 //sec

const MYSQL_HOST				 = "localhost";
const MYSQL_DBUSER				 = "root";
const MYSQL_PASSWORD			 = "root123";
const MYSQL_DB			 		 = "kayla";

const MONGO_HOST				 = "localhost";
const MONGO_DBUSER				 = "mongo_dbuser";
const MONGO_PASSWORD			 = "mongo123";
const MONGO_DB					 = "kayla";
const MONGO_CONNECTION			 = "mongodb://"+MONGO_DBUSER+":"+MONGO_PASSWORD+"@"+MONGO_HOST+":27017/"+MONGO_DB+"?replicaSet=rs0&authSource=admin"


$(function () {

	console.log('websocket_conn_str: ' + websocket_conn_str);

	var ws = new WebSocket(websocket_conn_str);
	//var ws = new WebSocket('https://admin.kayla.localhost:3000');

	ws.onopen = function(e) {
	  console.log("WebSocket: Connection established");
	};

	$('.access-device').on('click', function(e) {
		e.preventDefault();
		let uuid = $(this).data('uuid');
		console.log('access:', uuid);
		ws.send(MSG_ORIGIN_DASHBOARD+":"+MSG_UNLOCK+":"+uuid);
	});
	/*
	setInterval(checkAlive, 1000);

	var socket = io.connect(socketio_conn_str);
	//var socket = io.connect("http://192.168.1.199:3000");
	

	socket.on('connect', () => {
		socket.emit('command', 'get_all_device_status');
	  	console.log('SocketIO successfully connected!');
	});
	

	socket.on('device_heartbeat', function(msg){
		//console.log(msg);
		//node\models\device
		var device = msg;
		var divId = device.uuid;
		var elem = $("#"+divId);
		elem.attr("last_alive",device.last_alive)
		if (typeof elem.attr("init") !== "undefined") {
			elem.next().html("OK");
			var name = "anim-" + device.uuid;
			bodymovin.stop(name);
			bodymovin.play(name);
		} else {
			elem.attr("init","true");
		}
	});
	*/

});

