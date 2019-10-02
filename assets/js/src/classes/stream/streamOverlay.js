export let StreamOverlay = new function(){
    let element = null;
    let iconLoading = null;
    let iconFullScreen = null;
    let self = this;

    let onPlayingClick = function(){};

    this.STATE_DISABLED = 0;
    this.STATE_LOADING = 1;
    this.STATE_PLAYING = 2;

    let state = this.STATE_DISABLED;
    let stateActions = [];

    stateActions[self.STATE_DISABLED] = {
        load: () => {element.addClass('tvTestScreen')},
        unload: () => { element.removeClass('tvTestScreen')},
    };

    stateActions[self.STATE_LOADING] = {
        load: () => { iconLoading.removeClass('d-none')},
        unload: () => {iconLoading.addClass('d-none')},
    };

    stateActions[self.STATE_PLAYING] = {
        load: () => { element.addClass('playing')},
        unload: () => {element.removeClass('playing')},
    };

    this.setElement = function(e){
        element = e;

        if(iconLoading === null){
            iconLoading = $('<i></i>');
            iconLoading.addClass('fas fa-circle-notch fa-spin d-none');
            iconFullScreen = $('<i></i>');
            iconFullScreen.addClass('fas fa-keyboard d-none');
        }
        element.append(iconLoading);
        element.append(iconFullScreen);
        element.click(function(e){
            if(state === self.STATE_PLAYING){
                onPlayingClick();
            }
        });
    };

    this.show = function(){
        if(element === null){return;}
        element.removeClass('d-none');
    };
    this.hide = function(){
        if(element === null){return;}
        element.addClass('d-none');
    };

    this.switchState = function(newState){
        if(element === null){return;}

        stateActions[state].unload();
        stateActions[newState].load();

        state = newState;
    };

    this.resizeTo = function(streamElement){
        if(element === null){return;}
        element.css({width: streamElement.width(), height: streamElement.height()});
    };

    this.setOnPlayingClick = (a) => onPlayingClick = a;
    return this;
};