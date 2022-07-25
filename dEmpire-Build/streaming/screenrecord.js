function recordStart(){
    var elementToRecord = document.getElementById('unity-canvas');
    var recorder = RecordRTC(elementToRecord, {
        type: 'canvas',
        showMousePointer: true,
        mimeType: 'video/webm;codecs=vp9',
        getNativeBlob: true
    });
    recorder.startRecording();
    window.stopCallback = function() {
        window.stopCallback = null;
        recorder.stopRecording(async function() {
            var blob = recorder.getBlob();
            let video_url = await uploadVideo(blob);
            await sendVideoUrl(video_url)
            myGameInstance.SendMessage('WarManager', 'recordUploaded');
            console.log(video_url);
        });
    };
}

function makeid(length) {
    var result           = '';
    var characters       = 'abcdefghijklmnopqrstuvwxyz';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

document.addEventListener('DOMContentLoaded', function () {
    let webglCanvas = document.getElementById('unity-canvas');
    window.onresize = ()=>{
        console.log('lol')
        if(webglCanvas.style.height%2 != 0){
            webglCanvas.style.height = webglCanvas.style.height-1;
        }
        if(webglCanvas.style.width %2 != 0){
            webglCanvas.style.width = webglCanvas.style.width-1;
        }
        console.log( webglCanvas.style.height)
        console.log( webglCanvas.style.width)
    }
}, false);


async function uploadVideo(blob){
    let file_name = makeid(8);
    await fetch("https://ny.storage.bunnycdn.com/rtsvideos/videos/"+ file_name +".mp4", {
        method: 'PUT',
        headers: {
            "Content-Type": "video/webm",
            "AccessKey": "b1060b33-9a6e-4e3b-9f882bba1362-41e7-4624"
        },
        body: new File([blob], file_name +".mp4", {type:"video/webm"})
    });
    return "https://rtsvideo.b-cdn.net/videos/" + file_name + ".mp4";
}


async function sendVideoUrl(url){
    await fetch("https://theta.overclockedbrains.co:3030/addVideo", {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ videoUrl: url})
    });
}

function downloadVideo(blob){
    var link = document.createElement("a"); // Or maybe get it from the current document
    link.href = URL.createObjectURL(blob);
    link.download = "test.mp4";
    link.style.display = "none";
    link.id = "temp_video_download_url"
    document.body.appendChild(link); // Or append it whereever you want
    document.getElementById('temp_video_download_url').click() 
}
