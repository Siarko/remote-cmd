import {bind} from "../publicFunctionBinding";
import {Log} from "../debugger";

export let WsServerPanel = new function(){

    let element = null;
    let loadingIcon = null;
    let container = null;
    let mainTemplate = require('../../templates/wsServerManagement/panelMain.hbs');

    bind("toggleWsServerState", function(button){
        $(button).prop('disabled', true);
        fetch(BASE_URL+"api/wsserver/togglestate")
            .then(res => res.json())
            .then(res => {
                Log(res);
                refresh();
            });
    });

    let fetchContent = async function(){
        return new Promise(function(success){
            fetch(BASE_URL+"api/wsserver/state")
                .then(res => res.json())
                .then(res => {
                    let state = res.state;
                    success({
                        serverState: (state !== false),
                        serverPid: state
                    })
                })
        });
    };

    let loadContent = async function(){
        let data = await fetchContent();
        loadingIcon.addClass('d-none');
        container.html(mainTemplate(data));
    };

    let refresh = function(){
        container.html('');
        loadingIcon.removeClass('d-none');
        loadContent();
    };

    this.setElement = function(e){
        element = e;
        element.on('show.bs.modal', function (e) {
            container.html('');
            loadingIcon.removeClass('d-none');
        })
    };
    this.setLoadingIcon = function(e){
        loadingIcon = e;
    };
    this.setContentElement = function(e){
        container = e;
    };

    this.open = function(){
        element.modal('show');
        loadContent();
    };

    return this;
};