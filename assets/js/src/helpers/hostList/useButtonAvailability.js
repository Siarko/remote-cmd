module.exports = function(host){
    let date = new Date(host.last_check);
    let now = new Date();
    let timeDiff = parseInt((now-date)/1000);
    let active = (timeDiff < 6 || host.host_ready === "1");
    return (!active || host.user !== null ? 'disabled' : '');
};