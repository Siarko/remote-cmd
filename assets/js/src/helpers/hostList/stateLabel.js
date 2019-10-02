module.exports = function(host, options){
    let text = '';
    let date = new Date(host.last_check);
    let now = new Date();
    let timeDiff = parseInt((now-date)/1000);
    let active = (timeDiff < 6 || host.host_ready === "1");
    if(active){
        text = "ON";
        if(host.host_ready === "1"){
            text = "WS";
        }
    }else{
        text = "OFF";
    }

    return options.fn({
        class: (active?'label-info':'label-danger'),
        text: text
    });
};