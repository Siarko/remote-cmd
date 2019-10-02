import {Log, Err} from "../debugger";
import {StreamData} from "./streamData";

export let StreamKeyGrab = new function(){
    let self = this;
    let running = false;

    let callbacks = {
        keyDown: () => {},
        mouseMove: () => {},
        mouseClick: () => {}
    };

    let getClientScreen = function(){
        return {width: $(document).width(), height: $(document).height()};
    };

    let map = function(x, in_min, in_max, out_min, out_max){
        return (x - in_min) * (out_max - out_min) / (in_max - in_min) + out_min
    };

    let toProcPosition = function(x,y){
        let size = getClientScreen();
        return {
            x: map(x, 0, size.width, 0, 1),
            y: map(y, 0, size.height, 0, 1)
        }
    };

    let onKeyDown = function(event){
        if(!running){return;}
        event.preventDefault();
        callbacks.keyDown(event);
    };

    let onMouseMove = function(event){
        if(!running){return;}
        let {screenX: x, screenY: y} = event;
        callbacks.mouseMove(event, toProcPosition(x,y));
    };
    let onMouseClick = function(event){
        if(!running){return;}
        let {screenX: x, screenY: y} = event;
        callbacks.mouseClick(event, toProcPosition(x,y));
    };

    $(document).keydown(onKeyDown);
    $(document).mousemove(onMouseMove);
    $(document).click(onMouseClick);

    this.set = {
        onKeyDown: (e) => callbacks.keyDown = e,
        onMouseMove: (e) => callbacks.mouseMove = e,
        onMouseClick: (e) => callbacks.mouseClick = e
    };

    this.setRunning = (state) => running = state;

    return this;
};