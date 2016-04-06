//抽奖数据，以英文逗号分隔
var alldata = "001李大,002二,003邓三,004罗四,005欧五,006周六,007陈七,008潘八,009张九,010林十,011朱十一,012赵十二,013陈十三,014王十四";
var alldataarr = alldata.split(",");
var num = alldataarr.length - 1;
var timer;

function change() {
    $(".oknum").val(alldataarr[GetRnd(0, 124)]);
    console.log(alldataarr);
}

function start() {
    clearInterval(timer);
    timer = setInterval('change()', 46); //随机数据变换速度，越小变换的越快     
}

function ok() {
    clearInterval(timer);
    //以下代码表示获得奖的，不能再获奖了。  重置刷新页面即可。  
    alldata = alldata.replace($(".oknum").val(), "").replace(",,", ",");
    // 去掉前置，最末尾的,  
    if (alldata.substr(0, 1) == ",") {
        alldata = alldata.substr(1, alldata.length);
    }
    if (alldata.substr(alldata.length - 1, 1) == ",") {
        alldata = alldata.substring(0, alldata.length - 1);
    }
    alldataarr = alldata.split(",");
    num = alldataarr.length - 1;
}

function GetRnd(min, max, special, percent) {
    var random = Math.random().toFixed(3) * 1000;
    var lucky;
    var count = max * percent;
    if (count > 0 || ) {}
    if (random > max) {
        lucky = GetRnd(min, max, special);
    }else{
        lucky = random;
        console.log(lucky);
    }
    // var ran_num = parseInt(random * (max - min + 1));
    return lucky;
}
$(document).ready(function() {
    console.log(1);
    var people_num;
    $('input').bind('input', function() {
        people_num = $('input').val();
            $('.result_box').empty();

        for (var i = 0; i < people_num; i++) {
            $('.result_box').append('<input type="text" class="oknum" name="oknum" value="照片" />')
        }
    });

});
