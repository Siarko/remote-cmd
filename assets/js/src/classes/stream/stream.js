import {Err} from "../debugger";

export let Stream = new function(){
    let self = this;
    let element = null;

    let fetchUrl = null;

    let mimeType = "video/mp4";
    let mediaSource = null;
    let sourceBuffer = null;

    let fetchData = async function(){
        return new Promise(function(success){
            let xhr = new XMLHttpRequest;
            xhr.open('get', fetchUrl);
            xhr.responseType = 'arraybuffer';
            xhr.onload = function () { success(xhr.response); };
            xhr.send();
        });
    };

    let isMimeSupported = function(mime){
        return 'MediaSource' in window && MediaSource.isTypeSupported(mime)
    };

    this.init = function(){
        if(isMimeSupported(mimeType)){
            mediaSource = new MediaSource();
            element.prop('src', URL.createObjectURL(mediaSource));
            mediaSource.addEventListener('sourceopen', function(){
                sourceBuffer = mediaSource.addSourceBuffer(mimeType);
                sourceBuffer.mode = 'sequence';
            })
        }else{
            Err("Media source or mime type not supported ["+mimeType+"]")
        }
    };

    this.fetchClip = async function(){
        return new Promise(async function(success){
            if(sourceBuffer === null){ Err("Stream initialization incorrect!"); success(false);}
            let data = await fetchData();
            if(data.byteLength > 0){
                sourceBuffer.appendBuffer(data);
                success(true);
            }else{
                Err("Fetched 0 bytes from stream source!");
                success(false);
            }
        });
    };

    this.setUrl = function(u){
        fetchUrl = u;
    };


    this.setElement = function(e){
        element = e;
    };
    this.getElement = function(){
        return element;
    };

    this.play = function(){
        element.get(0).play();
    };
    this.pause = function(){
        element.get(0).pause();
    };


    return this;
};