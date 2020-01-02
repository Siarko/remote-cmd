import {mainWsContext} from "../index";
import {Err, Log} from "./debugger";

let connected = null;
let managedConnectionErr = function(){};

export function manage(hostId){
    return new Promise(function(success){
        mainWsContext.request('authorize_management', hostId);

        mainWsContext.registerNotification('host_ready', function(data){
            Log("Host ready");
            connected = {id: hostId, name: data.hostname};
            managedConnectionErr(false);
            success(data);
        });

        mainWsContext.registerNotification('host_disconnected', function(data){
            Err("Host disconnected");
            managedConnectionErr(true);
        });
    });
}

export function getHostData(){
    return connected
}

export function onConnectionError(callback){
    managedConnectionErr = callback;
}

export function disconnect(){
    return new Promise(function(success){
        if(connected === null){
            success(true);
        }else{
            mainWsContext.request('disconnect_host', connected.id).then(function(data){
                connected = null;
                success(data);
            });
        }

    });

}