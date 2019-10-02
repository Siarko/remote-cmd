import {bind} from "../publicFunctionBinding";
import {Log} from "../debugger";

export let HostFileBrowser = new function(){

    let element = null;
    let loadingIcon = null;
    let container = null;
    let title = null;
    let mainTemplate = require('../../templates/hostFileBrowser/hostFileBrowser.hbs');

    let loadContent = async function(){
        let data = {};
        container.html(mainTemplate(data));
        loadingIcon.addClass('d-none');
    };

    this.setElement = function(e){
        element = e;
        element.on('show.bs.modal', function (e) {
            container.html('');
            loadingIcon.removeClass('d-none');
        })
    };
    this.setTitleElement = function(e){
        title = e;
    };
    this.setTitle = function(text){
        title.text(text);
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