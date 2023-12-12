
$(function () {
	//const socketio_conn_str = 'http://$host:$port?token='.$access_token;
	const connection_str = socketio_conn_str;
	console.log('socketio_conn_str: ' + connection_str);

	setInterval(checkAlive, 1000);

	var socket = io.connect(connection_str);
	//var socket = io.connect("http://192.168.1.199:3000");
	

	socket.on('connect', () => {
		socket.emit('command', 'get_all_device_status');
	  	console.log('SocketIO successfully connected!');
	});
	
	socket.on('error', (error) => {
		//console.log(error);
		throw error;
	});

	socket.on('device_heartbeat', function(msg){
		//console.log(msg);
		//node\models\device
		/*
		uuid: { type: String },
		last_alive: { type }
		*/
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

});

function checkAlive() {
	$(".heart").each(function(index) {
		const last_alive = $(this).attr("last_alive");
		//console.log(last_alive);
		if (typeof last_alive !== "undefined") {
			const diff = Date.now()/1000 - last_alive;
			if (diff > 6) {
				const uuid = $(this).attr('id');
				var name = "anim-" + uuid;
				bodymovin.stop(name);
				$(this).next().html("last alive " + timeSince(last_alive) + " ago");
			}
		}
		//console.log(id);
	})
}


function timeSince(date) {
  var seconds = Math.floor((new Date()/1000 - date) );
  var interval = Math.floor(seconds / 31536000);

  if (interval >= 1) {
    return interval + " years";
  }
  interval = Math.floor(seconds / 2592000);
  if (interval >= 1) {
    return interval + " months";
  }
  interval = Math.floor(seconds / 86400);
  if (interval >= 1) {
    return interval + " days";
  }
  interval = Math.floor(seconds / 3600);
  if (interval >= 1) {
    return interval + " hr";
  }
  interval = Math.floor(seconds / 60);
  if (interval >= 1) {
    return interval + " min";
  }
  return Math.floor(seconds) + " sec";
}