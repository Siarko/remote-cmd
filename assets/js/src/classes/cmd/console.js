import {getCaretCoordinates} from "./InputCarretPosition";
import {Log} from "../debugger";

export let RConsole = new function(){
    let self = this;
    let consoleElement = null;
    let container = null;

    let cmdHandler = () => {};
    let onAutocomplete = () => {};

    this.set = {
        window: (e) => consoleElement = e,
        container: (e) => container = e,
        onCommand: (h) => cmdHandler = h,
        onAutocomplete: (h) => onAutocomplete = h
    };

    this.autocompleteReceived = (data) => {
        autocomplete.receiveResult(data);
    };

    let history = new function(){
        let set = [];
        let cursor = 0;

        this.add = (line) => {
            if(line.trim().length === 0){ return }
            set.push(line);
            cursor = set.length;
        };

        this.up = () => {
            if(set.length === 0 ){ return null }
            if(cursor > 0){ cursor--; }
            return set[cursor];
        };

        this.down = () => {
            if(set.length === 0 ){ return null }
            if(cursor < set.length){cursor++ }
            if(cursor === set.length){ return '';}
            return set[cursor];
        };

        this.resetCursor = () => cursor = set.length;

        // noinspection JSPotentiallyInvalidUsageOfThis
        this.reset = () => {
            set = [];
            cursor = 0;
        };

        return this;
    };

    let input = new function(){

        let lastLineBlocked = 0;
        let allLen = 0;

        this.replace = function (string) {
            if(string === null){ return }
            let len = consoleElement.val().length;
            let dif = len-allLen;
            consoleElement.val(consoleElement.val().slice(0, len-dif)+string);
        };

        this.append = function(content){
            consoleElement.val(consoleElement.val()+content);
            let last = consoleElement.val().split("\n");
            last = last[last.length-1];
            allLen = consoleElement.val().length;
            lastLineBlocked = last.length;
        };

        this.get = function(){
            let lines = consoleElement.val().split("\n");
            let last = lines[lines.length-1];
            return last.slice(lastLineBlocked);
        };

        this.backspaceAllowed = function(){
            let curPos = consoleElement.prop('selectionStart');
            return curPos > allLen;
        };

        this.modificationAllowed = function(){
            let curPos = consoleElement.prop('selectionStart');
            return curPos >= allLen;
        };

        this.isCtrlComb = function(event, key){
            return (event.ctrlKey && event.key === key);

        };

        // noinspection JSPotentiallyInvalidUsageOfThis
        this.reset = function(){
            lastLineBlocked = 0;
            allLen = 0;
        };

        return this;
    };

    let autocomplete = new function(){

        let self = this;
        let running = false;
        let element = null;

        let anchorContainer = () => {
            if(element !== null){ return }

            element = $('<div></div>');
            element.css({
                padding: '5',
                background: '#525252',
                position: 'absolute',
                border: 'solid 1px gray',
            });

            container.css({position: 'relative'});
            container.append(element);
        };

        this.receiveResult = (data) => {
            if(data.succeed){

            }else{
                element.html(data.reason);
                setTimeout(() => self.hide(), 1000);
            }
            Log(data);
        };

        this.show = () => {
            anchorContainer();
            element.removeClass('d-none');
            element.html('Wyszukiwanie...');
            let s = consoleElement[0].scrollHeight;
            let h = consoleElement.height();
            let dif = (s > h ? s-h : 0);
            let {top: t, left: l} = getCaretCoordinates(consoleElement);
            element.css({top: t-dif, left: l+10});

            onAutocomplete(input.get());
        };

        this.hide = () => {
            if(element === null){ return}
            element.addClass('d-none');
        };

        return this;
    };

    this.receiveLines = function(lines){
        if(consoleElement === null){return;}
        input.append(lines);
        consoleElement.scrollTop(consoleElement[0].scrollHeight);
    };

    this.reset = function(){
        consoleElement.text('');
        input.reset();
        history.reset();

        consoleElement.keydown((e) => {
            //if(e.key === "ArrowLeft"){ e.preventDefault(); }
            if(e.key === "ArrowUp"){
                e.preventDefault();
                input.replace(history.up());
                return;
            }
            if(e.key === "ArrowDown"){
                e.preventDefault();
                input.replace(history.down());
                return;
            }
            history.resetCursor();

            if(e.key === "Backspace"){
                if(!input.backspaceAllowed()){
                    e.preventDefault();
                    return;
                }
            }

            if(!input.modificationAllowed()){
                if(input.isCtrlComb(e, 'c')){
                    return;
                }
                e.preventDefault();
                return;
            }

            if(e.key === "Tab"){
                e.preventDefault();
                autocomplete.show();
            }else{
                autocomplete.hide();
            }

            if(e.ctrlKey && e.key === "c"){
                cmdHandler("SIGKILL", true);
            }
            if(e.key === "Enter"){
                let command = input.get();
                history.add(command);
                cmdHandler(command);
            }

        })
    };

    return this;
};