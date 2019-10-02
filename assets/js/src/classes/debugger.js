import {DEBUG} from "../index";

export let Log = function (...data) {
    if(DEBUG === true){
        console.log(...data)
    }
};

export let Err = function (...data) {
    console.error(...data)
};