/**
 * Created by xry on 2016/9/19.
 */
var linkurl = 'http://172.16.4.4/rundong/';



function getUrlParam(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r!=null) return unescape(r[2]);
    return null;
}