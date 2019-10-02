import {Err} from "./debugger";

export function instance(){

    let _onOpen = function(){};
    let _onClose = function(errored){};
    let _onMessage = function(text, event){};
    let context = null;
    let state = false;
    let connecting = false;
    let self = this;

    let requestCounter = 0;
    let pending = [];
    let notifications = {};

    let getCookie = function(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    };

    this.ERROR_DISCONNECTED = 'ERROR_DISCONNECTED';

    this.open = function(url){
        connecting = true;
        context = new WebSocket(url);
        context.onopen = function(){
            state = true;
            connecting = false;
            _onOpen();
        };
        context.onclose = function(){
            state = false;
            connecting = false;

            $.each(Object.keys(pending), function(k,v){
                let resolver = pending[v];
                resolver(self.ERROR_DISCONNECTED);
            });

            _onClose(false);
        };
        context.onerror = function () {
            connecting = false;
            state = false;
            _onClose(true);
        };
        context.onmessage = function(event){
            try{
                let data = JSON.parse(event.data);
                if(data.type === 'response'){
                    let resolver = pending[data.data.id];
                    resolver(data.payload);
                    delete pending[data.data.id];
                }
                if(data.type === 'notification'){
                    if(data.name in notifications){
                        let resolver = notifications[data.name];
                        resolver(data.payload);
                    }else{
                        Err("Unknown notification: "+data.name);
                    }
                }
            }catch (e) {
                Err('[SERVER]> ', event.data);
            }
        }
    };

    this.onOpen = function (action) {
        _onOpen = action;
    };

    this.onClose = function (action) {
        _onClose = action;
    };

    this.onMessage = function (action) {
        _onMessage = action;
    };

    this.disconnect = function(){
        if(context){
            context.close();
        }
    };

    this.isOpen = function(){
        return state;
    };

    this.isConnecting = function(){
        return connecting;
    };

    this.send = function(name, data, type, requestId){

        if(requestId === undefined){ requestId = null;}
        if(type === undefined){ type = 'request';}
        context.send(JSON.stringify({
            PHPSESSID: getCookie('PHPSESSID'),
            client_type: 'client',
            type: type,
            name: name,
            data: {
                id: requestId
            },
            payload:data
        }));
    };

    this.request = function(actionName, data){
        return new Promise(function(resolve){
            if(!self.isOpen()){ resolve(self.ERROR_DISCONNECTED);}
            let requestId = 'r'+requestCounter++;
            pending[requestId] = resolve;
            self.send(actionName, data, 'request', requestId);
        });
    };

    this.registerNotification = function(name, action){
        notifications[name] = action;
    };

    return this;
}