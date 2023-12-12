//ref: https://github.com/cozmo/jsQR
// note 10 issue: https://stackoverflow.com/questions/50460713/samsung-default-browser-or-any-default-browser-in-android-is-not-supporting-back
$(function() {
    $(".snap").on("click", ()=>{
        const fullQuality = canvasElement.toDataURL('image/jpeg', 1.0);
        img_arr.push(fullQuality);
        drawImage(fullQuality);
    });

    $(".upload").on("click", ()=>{
        upload();
    });

});

var recogniseCode = null;
var timeoutId = null;
var flag_photoNear = false;
var flag_photoFar = false;
var flag_photoUploading = false;
var video = document.createElement("video");
var canvasElement = document.getElementById("canvas");
var canvas = canvasElement.getContext("2d");
//var loadingMessage = document.getElementById("loading-message");
var outputContainer = document.getElementById("output");
var outputMessage = document.getElementById("output-message");
var outputData = document.getElementById("output-data");
var hint = document.getElementById("hint");
var img_arr = [];


drawLine = (begin, end, color) => {
    canvas.beginPath();
    canvas.moveTo(begin.x, begin.y);
    canvas.lineTo(end.x, end.y);
    canvas.lineWidth = 4;
    canvas.strokeStyle = color;
    canvas.stroke();
}

// Use facingMode: environment to attemt to get the front camera on phones
/*navigator.mediaDevices.getUserMedia({
    video: true
}).then(function(stream) {
    video.srcObject = stream;
    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
    video.play();
    requestAnimationFrame(tick);
    hint.innerText = "Position your device's QR code in view.";
});*/

navigator.mediaDevices.enumerateDevices()
.then(function(devices) {

    var videoDevices = [];
    var videoDeviceIndex = 0;
    devices.forEach(function(device) {
        if(device.kind == "videoinput") { 
            // console.log(device)
            videoDevices[videoDeviceIndex++] =  device.deviceId;  
        }
    });

    var constraints =  { facingMode: "environment" };
    if(videoDevices.length > 0){
        var isNote10 = !!navigator.userAgent.match(/SM-N975F/);
        if(isNote10){
            // console.log("Note 10")
            constraints =  { deviceId:  videoDevices[videoDevices.length-1] }
        } else {
            // console.log("other browsers")
            // constraints =  { facingMode: "environment" };
            // same for now
            constraints =  { deviceId:  videoDevices[videoDevices.length-1] }
        }
    }
        return navigator.mediaDevices.getUserMedia({ video: constraints });

}).then(function(stream) {
    video.srcObject = stream;
    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
    video.play();
    requestAnimationFrame(tick);
    hint.innerText = "No QR code detected.";
}).catch(function(err) {
    //use this to display the error 
    // console.log(err);
});

tick = () => {
    //loadingMessage.innerText = "Loading video..."
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        //loadingMessage.hidden = true;
        canvasElement.hidden = false;
        //outputContainer.hidden = false;

        canvasElement.height = video.videoHeight;
        canvasElement.width = video.videoWidth;
        canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: "dontInvert",
        });
        if (!flag_photoUploading && code && code.data != "") {
            const d = checkDistance(code.location);
            var fullQuality = canvasElement.toDataURL('image/jpeg', 1.0);

            drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
            drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
            drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
            drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");

            if (!flag_photoNear && !flag_photoFar) {
                hint.innerText = "Position your device a little nearer.";
            }
            
            if (!flag_photoNear && d > 350) {
                hint.innerText = "Position your device a little further.";
                flag_photoNear = true;
                img_arr.push(fullQuality);
                drawImage(fullQuality);
            }
            if (!flag_photoFar && d > 190 && d < 250) {
                hint.innerText = "Position your device a little nearer.";
                flag_photoFar = true;
                img_arr.push(fullQuality);
                drawImage(fullQuality);
            }

            //outputMessage.hidden = true;
            //outputData.parentElement.hidden = false;            
            //outputData.innerText = "d:" + d + " " + code.data;

            if (!flag_photoUploading && flag_photoNear && flag_photoFar) {
                // console.log(code.data);
                // console.log(img_arr);
                hint.innerText = "Processing...";
                flag_photoUploading = true;
                upload(code.data);
            }           


        } else {
            //outputMessage.hidden = false;
            //outputData.parentElement.hidden = true;
        }
    }
    requestAnimationFrame(tick);
}
drawImage = (imageData) => {
    let img = new Image();
    img.src = imageData;
    $('.photo-container').append(img);
}
resetRecogniseCode = () => {
    recogniseCode = null;
}
checkDistance = (location) => {
    const tl = location.topLeftCorner;
    const br = location.bottomRightCorner;
    const a = tl.x - br.x;
    const b = tl.y - br.y;
    const d = Math.sqrt( a*a + b*b );
    return d;
}

upload = (data) => {
    const params = new URLSearchParams(data);
    const mobile_number_full = params.get("m");
    const provisional_token = params.get("t");
    const plan_pool_id = params.get("p");

    let formData = new FormData();
    img_arr.map((img) => {
        const str = Math.random().toString(36).substring(2) + ".jpg";
        formData.append('image_file[]', dataURItoBlob(img), str);
    })

    var jsonData = {"mobile_number_full": mobile_number_full, "provisional_token": provisional_token, "plan_pool_id": plan_pool_id};
    formData.set('json', JSON.stringify(jsonData));
 
    const config = {
        method: 'POST',
        url: apiUrl,
        headers: {'Content-Type': 'multipart/form-data' },
        data: formData
    }

    axios(config)
    .then(function (res) {
        if (res.data.status == 200) {
            hint.innerText = "Success! You can close this page now.";
            alert("Success!");
        } else {
            hint.innerText = "Fail! Please refresh the page and try again.";
            alert("Error!");
            console.log(res);
        }
    })
    .catch(function (err) {
        hint.innerText = "Server error!\nPlease refresh the page and try again.";
        alert("Error!");
        console.log(err);
    });

}


dataURItoBlob = (dataURI) => {
  var byteString = atob(dataURI.split(',')[1]);
  var ab = new ArrayBuffer(byteString.length);
  var ia = new Uint8Array(ab);
  for (var i = 0; i < byteString.length; i++) {
      ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], {type: 'image/jpeg'});
}