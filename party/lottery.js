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
    $("#start_stop").bind('click', function() {
        // alert();
        switch(situation){
            //点击开始
            case "start":
                $('.holder').show();
                if ( parseInt(getMax())  < parseInt($('.win_num').val())){
                    alert("抽奖人数不足");
                    return false;
                };
                $('.result_box').empty();
                allData = JSON.parse(localStorage.data);
                max = setMax();
                $("#start_stop").text("停止");
                situation = "stop";
                function start(max) {
                    timer = setInterval('change(max)',10); //随机数据变换速度，越小变换的越快    
                };
                start(max);
                break;
            // 点击停止
            case "stop":
                $('.holder').hide();
                max = getMax();
                $("#start_stop").text("开始");
                situation = "start";
                clearInterval(timer);
                var win_num = $('.win_num').val();
                var result_pos = 0;
                var delaySecond = 0;
                for(var i = 0; i < win_num; i++){
                    $('.result_box').append('<img class="result" style="opacity:0;" src="" alt="">');
                };
                $('.result').each(function() {
                    var cut =  getPhoto(max);
                    $(this).attr("src", "./image/"+ allData[cut]);
                    var checkStr = allData[cut].split('.')[0];
                    var check = $('.holder').each(function() {
                        if ($(this).attr("src").indexOf(checkStr) != -1) {
                            return true;
                        }else {return false;}
                    });
                    while (check == true) {
                        cut =  getPhoto(max);
                        $(this).attr("src", "./image/"+ allData[cut]);
                    }
                    $('.holder').attr("src", "./image/"+ allData[cut]);
                    var src = this.src;
                    var checkNum = allData.length;
                    for(var i = 0; i < checkNum;i++){
                        var pos = allData[i].indexOf(".");
                        if(allData[i].indexOf("副") != -1){
                            pos = allData[i].indexOf("副");
                        }

                        var del = allData[i].substring(0, pos);

                        if (src.indexOf(del) != -1) {
                            // console.log(del);
                            // console.log(src);

                            allData.splice(i,1);
                            localStorage.setItem('data',JSON.stringify(allData));

                            max = setMax();
                            checkNum = allData.length;
                            // console.log(checkNum);
                            i--;
                        }
                    }
                });
                for(var i = 0; i < win_num; i++){
                    (function(i) {
                        setTimeout(function (){
                            $('.result').eq(i).animate({opacity:'1'}, 2000);
                        }, (i * 200) );
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
    });
    
});
