import * as Websocket from 'websocket';
import * as HostManagement from 'hostManagement';
import 'publicFunctionBinding';
import {bind} from "./classes/publicFunctionBinding";

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap/dist/js/bootstrap.bundle';

import '@forevolve/bootstrap-dark/dist/css/bootstrap-dark.css';
import 'bootstrap-slider/dist/css/bootstrap-slider.css';

import 'jbox/dist/jBox.all.min.css';

import {RConsole} from "./classes/cmd/console";

import {StreamOverlay} from "./classes/stream/streamOverlay";
import {Stream} from "./classes/stream/stream";
import {StreamData} from "./classes/stream/streamData";

import {WsServerPanel} from "./classes/wsServerManagement/wsServerPanel";
import {Log, Err} from "./classes/debugger";
import {HostFileBrowser} from "./classes/hostFileBrowser/hostFileBrowser";
import {StreamKeyGrab} from "./classes/stream/streamKeyGrab";
import * as jBox from "jbox";

export let mainWsContext = Websocket.instance();

export const DEBUG = true;

let hostListTemplate = require('./templates/hostList/hostList.hbs');
let hostControlPanelTemplate = require('./templates/hostControlPanel/mainPanel.hbs');
let hostControlPanelTabsTemplate = require('./templates/hostControlPanel/controlTabs.hbs');
let hostUploadedFilesTemplate = require('./templates/hostControlPanel/filesPanelTable.hbs');

let hostsList = null;
let autoConnectButton = null;
let autoConnect = false;
let serverConnectButton = null;
let serverStatusBadge = null;
let serverLoadingIcon = null;
let hostNameButton = null;
let hostConnectionLoadingIcon = null;
let hostNotRespondingButton = null;
let hostControlPanel = null;
let downloadNewestJarButton = null;

/*TODO podpowiadanie ścieżek w CMD*/
/*TODO Przeglądarka plików hosta*/
/*TODO Przejmowanie kontroli przez strumień*/

let downloadNewestJar = function(){
    fetch(BASE_URL+"getjar")
        .then(res => res.json())
        .then(function(result){
            if(result.filename){
                new jBox('Notice', {
                    content: "Pobieranie pliku: "+result.filename,
                    theme: 'NoticeFancy',
                    color: "green",
                });
                PublicBinding.asyncFileDownload(null, result.url);
            }else{
                new jBox('Notice', {
                    content: "Nie znaleziono żadnej wersji :/",
                    theme: 'NoticeFancy',
                    color: "red",
                });
            }

        console.log(result);
    })
};

let selectTab = (name) => {
    $('#control_tabs').html(
        hostControlPanelTabsTemplate({activeTab: name})
    );
    let activated = $('#tab_'+name);
    activated.removeClass('d-none');
    activated.siblings('div').each((k,e) => {
        $(e).addClass('d-none');
    });
};

let updateStreamInfo = (data) => {
    $('#clipCreateTime').text(data.cst);
    $('#lastClipInterval').text(data.frd);
};

mainWsContext.registerNotification('stream_started', (data) => {
    Log("Stream started");
    updateStreamInfo(data);
    StreamOverlay.switchState(StreamOverlay.STATE_PLAYING);
    Stream.fetchClip().then((result) => {
        if(result){
            Stream.play();
        }
    });
});

mainWsContext.registerNotification('clip_uploaded', (data) => {
    //Log("New clip available");
    updateStreamInfo(data);
    Stream.fetchClip();
});

mainWsContext.registerNotification('console_output', (data) => {
    RConsole.receiveLines(data.lines);
});

mainWsContext.registerNotification('console_autocomplete', (data) => {
    RConsole.autocompleteReceived(data)
});

HostManagement.onConnectionError((occured) => {
    if(occured){
        hostNotRespondingButton.removeClass("d-none");
        hostControlPanel.addClass("disabled");
        RConsole.receiveLines("\nHOST DISCONNECT -> CONSOLE RESET\n");
    } else{
        hostNotRespondingButton.addClass("d-none");
        hostControlPanel.removeClass("disabled");
    }
});

StreamKeyGrab.set.onMouseMove((event, percentPos) => {
   /*TODO on mouse move screen grab
   * Schowanie myszki na pełnym ekranie
   * render podglądu w większej jakości w pozycji myszki
   * */
});
StreamKeyGrab.set.onMouseClick((event, percentPos) => {
/*TODO on mouse click screen grab*/
});
StreamKeyGrab.set.onKeyDown((event) => {
   /*TODO on key down screen grab*/
});

StreamOverlay.setOnPlayingClick(() => {
    Stream.getElement().get(0).requestFullscreen().then(() => {
        Log("Enter full screen");
        StreamKeyGrab.setRunning(true);
    });
});

RConsole.set.onCommand((cmd, signal) => {
    if(signal === undefined){
        signal = false;
    }
    mainWsContext.send('console_command', {
        command: cmd,
        signal: signal
    });
});

RConsole.set.onAutocomplete((cmd) => {
    mainWsContext.send('console_autocomplete', {
        command: cmd
    });
});

bind('setHostCustomName', (inputId, button) => {
    let hostId = HostManagement.getHostData().id;
    let input = $('#'+inputId);
    button = $(button);
    button.attr('disabled', true);

    $.post(BASE_URL+"api/host/"+hostId, {
        customName: input.val().trim()
    }).always(function(res){
        res = JSON.parse(res);
        if(res.success){
            hostNameButton.text(input.val().trim());
        }
        button.attr('disabled', false);
    })
});

bind('browseHostFiles', () => {
    HostFileBrowser.open();
    HostFileBrowser.setTitle(HostManagement.getHostData().name);
});
bind('refreshHostFiles', (button = null) => {
    if(button !== null){
        button = $(button);
        button.prop("disabled", true);
    }

    mainWsContext.request('get_host_uploaded').then((r) => {
        let c = $('#uploadedHostFiles');
        let data = r.map((v) => {
            return {filename: v.file, data: v.data, path: v.path}
        });
        c.html(hostUploadedFilesTemplate({ list: data }));
        if(button !== null){
            button.prop('disabled', false);
        }
    });
});
bind('asyncFileDownload', (button, filepath) => {
    let frame = $('<iframe></iframe>');
    frame.addClass('d-none');
    frame.prop('src', BASE_URL+filepath);

    $(document.body).append(frame);
});
bind('deleteHostUplFile', (button, filename) => {
    $(button).prop('disabled', true);
    mainWsContext.request('delete_host_uploaded', {
        filename: filename
    }).then((r) => {
        if(!r){
            Err("Could not delete host uploaded: "+filename);
        }
        PublicBinding.refreshHostFiles($('#refreshHostUpl'));
    });
});

bind('useHost', (buttonElement, data) => {
    $(buttonElement).prop('disabled', true);
    hostConnectionLoadingIcon.removeClass('d-none');
    hostNameButton.text("CONNECTING...");
    HostManagement.manage(data).then((data) => {
        let hostname = null;
        let hostSoftVersion = 'UNKNOWN';
        let customName = data.custom_name;
        if(data.payload){
            hostSoftVersion = data.payload.version;
            hostname = data.hostname;
        }else{
            hostname = data;
        }

        if(hostname){
            hostConnectionLoadingIcon.addClass('d-none');
            hostNameButton.text(((customName === null) ? hostname : customName));
            hostNameButton.parent().prop('disabled', false);
            hostControlPanel.html(
                hostControlPanelTemplate({
                    host: {
                        version: hostSoftVersion,
                        hostname: hostname,
                        custom_name: customName
                    },
                })
            );

            /*CONSOLE*/
            RConsole.set.container($('#console_container'));
            RConsole.set.window($('#console_window'));
            RConsole.reset();

            /*STREAM*/
            let getFrameSize = (factor) => {
                let f = 1.7777777;
                let baseW = 1920;
                let newW = baseW*factor;
                let w = parseInt(newW);
                let h = parseInt(newW/f);
                return {
                    w: (w % 2 === 0) ? w : w + 1,
                    h: (h % 2 === 0) ? h : h + 1
                };
            };

            let updateSizeLabels = (s) => {
                $('#streamWidth').text(s.w);
                $('#streamHeight').text(s.h);
            };

            let sliderInitValue = 0.1;
            let cmpInitValue = 0.2;
            new Slider('#ex1', {
                formatter: (v) => 'Skala: ' + v,
                tooltip_position: 'top',
                value: sliderInitValue,
                min: 0.1,
                max: 1,
                step: 0.01,
                reversed: true
            }).on('slide', (data) => {
                let size = getFrameSize(data);
                StreamData.setResolution(size.w, size.h);
                updateSizeLabels(size)
            });

            new Slider('#ex2', {
                formatter: (v) => 'Kompresja: ' + v*100+"%",
                tooltip_position: 'top',
                value: cmpInitValue,
                min: 0.01,
                max: 1,
                step: 0.01,
                reversed: true
            }).on('slide', (data) => {
                StreamData.setFrameCompression(data);
                $('#frameCompression').text(parseInt((1-data)*100)+"%");
            });

            let initFrameSize = getFrameSize(sliderInitValue);
            StreamData.setResolution(initFrameSize.w, initFrameSize.h);
            updateSizeLabels(initFrameSize);

            $('#frameCompression').text(parseInt((1-cmpInitValue)*100)+"%");


            $('#framesPerClip').val(StreamData.getFramesPerClip()).on('input', function(e){
                let v = parseInt($(this).val());
                if(isNaN(v) || v <= 0){ v = 20; }
                StreamData.setFPC(v)
            });

            Stream.setElement($('#stream'));
            Stream.setUrl(BASE_URL+"stream/clip");
            Stream.init();

            Stream.getElement().on('fullscreenchange', function(e){
                if(!document.fullscreenElement){
                    StreamKeyGrab.setRunning(false);
                }
            });

            StreamOverlay.setElement($('#streamOverlay'));
            StreamOverlay.resizeTo(Stream.getElement());
            StreamOverlay.switchState(StreamOverlay.STATE_DISABLED);
            StreamOverlay.show();

            selectTab("info");
        }
    });
});

let stream = false;
bind('startStream', (button) => {
    if(stream){
        stream = false;
        PublicBinding.stopStream();
        $(button).text("START STREAM").prop('disabled', false);
        return;
    }
    StreamOverlay.switchState(StreamOverlay.STATE_LOADING);
    $(button).text("LOADING...").prop('disabled', true);

    let data = {
        frameWidth: StreamData.getFrameWidth(),
        frameHeight: StreamData.getFrameHeight(),
        compression: StreamData.getFrameCompression(),
        fpc: StreamData.getFramesPerClip()
    };
    mainWsContext.request('request_stream', data).then((data) => {
        if(!data){
            $(button).text("START STREAM").prop('disabled', false);
            err("Cannot start stream!");
        }else{
            $(button).text("STOP STREAM").prop('disabled', false);
            stream = true;

        }
    });
});

bind('stopStream', () => {
    mainWsContext.request('cancel_stream').then((data) => {
        Log('Stop stream: ',data);
        StreamOverlay.switchState(StreamOverlay.STATE_DISABLED);
    });
});

bind('controlPanelTab', (name) => {
    selectTab(name);
    if(name === 'files'){
        PublicBinding.refreshHostFiles();
    }
});

mainWsContext.onOpen(() => {
    serverConnectButton.addClass('btn-success');
    serverConnectButton.removeClass('btn-danger');
    serverStatusBadge.text("CONNECTED");
    serverLoadingIcon.addClass('d-none');
});

mainWsContext.onClose((errored) => {
    serverConnectButton.removeClass('btn-success');
    serverConnectButton.addClass('btn-danger');
    serverStatusBadge.text("DISCONNECTED");
    serverLoadingIcon.addClass('d-none');
    hostsList.addClass('disabled');

    Log("Disconnected from websocket server "+(errored?"[ERROR]":""));
    disconnectHost();
});

let openConnection = () => {
    if(mainWsContext.isOpen() || mainWsContext.isConnecting()){ return }
    Log("Connecting to websocket server at: "+WEBSOCKET_URL);
    serverLoadingIcon.removeClass('d-none');
    mainWsContext.open(WEBSOCKET_URL);
};



let updateHostsList = () => {
    mainWsContext.request('get_host_list').then((data) => {
        hostsList.html(hostListTemplate({
            hosts: data
        }));
        hostsList.removeClass('disabled');
    });
};

let disconnectHost = function(){
    hostNameButton.text("NOT SELECTED");
    hostNameButton.parent().prop('disabled', true);
    HostManagement.disconnect().then(function(){
        hostNotRespondingButton.addClass("d-none");
        hostControlPanel.html(
            hostControlPanelTemplate({
                host: null
            })
        ).removeClass("disabled")
    });
};

$(document).ready(function(){
    Log("JS INIT");
    Log("JS API URL: "+BASE_URL);

    serverStatusBadge = $('#wsServerStatus');
    serverConnectButton = $('#wsServerToggle');
    autoConnectButton = $('#autoReconnect');
    serverLoadingIcon = $('#serverLoading');
    hostsList = $('#host_list');
    hostNameButton = $('#host_name');
    hostConnectionLoadingIcon = $('#hostConnectionLoading');
    hostNotRespondingButton = $('#hostNotResponding');
    hostControlPanel = $('#host_control_panel');
    downloadNewestJarButton = $('#downloadNewestJarButton');

    hostControlPanel.html(
        hostControlPanelTemplate({
            host: null
        })
    );

    WsServerPanel.setElement($('#wsServerPanel'));
    WsServerPanel.setLoadingIcon($('#wsPanel_loading'));
    WsServerPanel.setContentElement($('#wsPanel_content'));

    HostFileBrowser.setElement($('#hostFileBrowser'));
    HostFileBrowser.setLoadingIcon($('#HFB_loading'));
    HostFileBrowser.setTitleElement($('#HFB_title'));
    HostFileBrowser.setContentElement($('#HFB_content'));

    $('#wsServerPanelButton').click(() => WsServerPanel.open());

    downloadNewestJarButton.click(downloadNewestJar);

    $('#disconnect_host').click(() => disconnectHost());

    serverConnectButton.click(() => {
        if(mainWsContext.isOpen()){
            disconnectHost();
            mainWsContext.disconnect();
        }else{
            openConnection();
        }
    });

    autoConnectButton.click(() => {
        let icon = autoConnectButton.children().first();
        icon.toggleClass('fa-check');
        icon.toggleClass('fa-times');
        autoConnect = !autoConnect;
    });

    setInterval(() => {
        if(mainWsContext.isOpen()) {
            updateHostsList();
        }else if(autoConnect){
            openConnection();
        }
    }, 2000);

    openConnection();

});