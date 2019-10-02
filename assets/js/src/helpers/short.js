module.exports = function(data, maxLen){
    if(data.length > maxLen){
        let s = data.substring(0, maxLen-1);
        return s+"&hellip;"
    }
    return data;
};