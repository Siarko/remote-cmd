module.exports = function(host){
    let now = new Date();
    let date = new Date(host.last_check);
    let timeDiff = parseInt((now-date)/1000);
    if(host.host_ready === "1"){
        return "CONST";
    }
    return (timeDiff > 100)?'>100 sec' : `${timeDiff} sec`;
};