var max;
var data;
var allData;

function setMax(){
    max = allData.length-1;
    localStorage.setItem('max',max);
    return max;
}
function getMax(){
    localStorage.max;
    return max;
}
function change(max){
    $('.holder').each(function() {
        // random_number = getPhoto(max);
        // if (allData[random_number] == undefined) {
        //     console.log(random_number);
        // }
        $(this).attr("src", "./image/"+ allData[getPhoto(max)]);
    });
};

function getPhoto(max){
    var allPhoto = localStorage.data;
    var random = Math.random().toFixed(3) * 1000;
    while(random > max)
    {
        random = Math.random().toFixed(3) * 1000;
    }
    return random;
}


$(document).ready(function() {
    var timer;
    // 特殊抽奖者录入
    var special = [];
    // 特殊概率抽奖者
    var people = [];
    // 特殊抽奖次数
    var frequency = [];
    var people_num;
    // $('.win_num').bind('input', function() {
    //     people_num = $('input').val();
    //         $('.result_box').empty();
    //     for (var i = 0; i < people_num; i++) {
    //         $('.result_box').append('<img class="holder" src="http://dwz.cn/349mam" alt="">')
    //     }
    // });
    $.ajax({
        url: "lottery.php",
        datatype: 'txt',
        type: 'get',
        data: {},
        success: function(resp) {
            data = JSON.parse(resp);
            // console.log(data);
            localStorage.removeItem("data");
            localStorage.setItem('data',JSON.stringify(data));
            allData = JSON.parse(localStorage.data);
            max = setMax();
        },
    });
    var situation = "start";
    $(document).keydown(function(event){ 
        if(event.keyCode == 32 || event.keyCode == 13){
            switch(situation){
                //点击开始
                case "start":
                    $('.holder').show();
                    $("#imgAttr").hide(); 
                    $("#start_stop").text("停止");
                    $('.result_box').empty();

                    if ( parseInt(getMax())  < parseInt($('.win_num').val())){
                        alert("抽奖人数不足");
                        return false;
                    }else{
                        allData = JSON.parse(localStorage.data);
                        max = setMax();
                        function start(max) {
                            timer = setInterval('change(max)',10); //随机数据变换速度，越小变换的越快    
                        };
                        start(max);
                    }
                    situation = "stop";
                    break;
                // 点击停止
                case "stop":
                    $('.holder').hide();
                    $("#start_stop").text("开始");
                    situation = "start";
                    max = getMax();
                    clearInterval(timer);

                    var win_num = $('.win_num').val();
                    for(var i = 0; i < win_num; i++){
                        $('.result_box').append('<img class="result" style="display:none" src="" alt="">');
                    };
                    $('.result').each(function() {
                        var cut =  getPhoto(max);
                        $(this).attr("src", "./image/"+ allData[cut]);
                        var src = this.src;
                        var checkNum = allData.length;
                        for(var i = 0; i < checkNum;i++){
                            // 到.结束
                            var pos = allData[i].indexOf(".");
                            // 如果是副本，到“副”结束
                            if(allData[i].indexOf("副") != -1){
                                pos = allData[i].indexOf("副");
                            }
                            //截图图片名称
                            var del = allData[i].substring(0, pos);

                            if (src.indexOf(del) != -1) {
                                allData.splice(i,1);
                                // localStorage.setItem('data',JSON.stringify(allData));
                                max = setMax();
                                checkNum = allData.length;
                                i--;
                            }
                        }
                    });
                    for(var i = 0; i < win_num; i++){
                        (function(i) {
                            setTimeout(function (){
                                $('.result').eq(i).addClass(' tada animated');
                                $('.result').eq(i).show();
                            }, (i * 1000) );
                        })(i);
                    };
                    localStorage.setItem('data',JSON.stringify(allData));
                    $('.result').on('mouseover', function(event) {
                        var url = this.src;
                        $("#imgAttr").attr("src",url).show(); 
                        // $(this).attr("src", "./image/"+ allData[getPhoto(max)]);
                        // console.log(this.src);
                    });
                    $('.result').on('mouseout', function(event) {
                        $("#imgAttr").hide(); 
                    });
                    break;
            }
        }
    }); 
});
