<style>
    body, html{
        margin: 0;
        padding: 0;
        background: black;
        height: 100%;
        width: 100%;
        background-image: url('https://martin-upload.b0.upaiyun.com/web/2017/04/12848feeaea4cec339963559db0866d3.jpg');
        background-size: 100%;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
<style lang="scss" scoped>
body, html{
  margin: 0;
  padding: 0;
  background: black;
  height: 100%;
  width: 100%;
  background-image: url('https://martin-upload.b0.upaiyun.com/web/2017/04/12848feeaea4cec339963559db0866d3.jpg');
  background-size: 100%;
  background-position: center;
  background-repeat: no-repeat;
}
@font-face{
    font-family: "hanti";
    src: url("https://martin-upload.b0.upaiyun.com/web/2017/04/d3da819bcad89bf620a7153a70b2af7f.ttf") format('truetype');
}
*{
    font-family: "hanti";
}
.background{
    width: 100%;
    visibility: hidden;
}
.box{
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    transform: translateY(-50%);
    .background{
        width: 100%;
        visibility: hidden;
    }
    .win-title{
        position: absolute;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
        color: #990000;
    }
    .number{
        position: absolute;
        top: 10%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
        color: #990000;
        input{
            appearance: none;
            border: 0;
            background: transparent;
            font-family: "hanti";
            font-size: 1rem;
            width: 1.5rem;
            text-align: center;
        }
    }
    .begin{
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
        color: black;
        cursor: pointer;
    }
	.stop{
		position: absolute;
        bottom: 10%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
        color: black;
        cursor: pointer;
	}
    .next{
		position: absolute;
        bottom: 10%;
        right: 0%;
        // transform: translateX(-50%);
        font-size: 1rem;
        color: black;
        cursor: pointer;
	}
	.roll{
		position: absolute;
		display: flex;
		flex-wrap:wrap;
        top: 30%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
		width: 60%;
		flex-wrap: wrap;
		justify-content: center;
		.name{
			margin-bottom: 1rem;
			text-align: center;
			width: 20%;
		}
	}
	.result{
		position: absolute;
		display: flex;
		flex-wrap:wrap;
        top: 30%;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
		width: 60%;
		flex-wrap: wrap;
		justify-content: center;
		.name{
			margin-bottom: 1rem;
			text-align: center;
			width: 20%;
		}
	}
}
</style>

<template>
    <div class="">
        <div class="box">
            <img class="background" src="https://martin-upload.b0.upaiyun.com/web/2017/04/12848feeaea4cec339963559db0866d3.jpg" alt="">
            <div class="number" v-show="status === 'waiting'">
                抽
                <input type="tel" v-model="amount">
                人
            </div>
            <div class="roll" v-show="status === 'rolling'">
                <div class="name" v-for="name in rolling">{{ name }}</div>
            </div>
            <div class="begin" v-show="status === 'waiting'" @click="start()">开始</div>
            <div class="stop" v-show="status === 'rolling'" @click="stop()">停止</div>
            <div class="next" v-show="status === 'result'" @click="restart()">开启下轮抽奖</div>

            <div class="win-title" v-show="status === 'result'">中奖名单</div>
			<div class="result" v-show="status === 'result'">
                <div class="name" v-for="person in winner">{{ person.name }}</div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	name: 'app',
	data () {
		return {
			status: 'waiting',
			amount: 10,
			people: [],
			winner: [],
			rolling:[],
			interval: '',
		}
	},
	mounted () {
		this.allPeople();
	},
	methods: {
		allPeople() {
			this.$http.get(this.$root.host + 'front/do-annual.php?action=queryPeople')
			.then((resp) => {
				this.people = resp.data;
			});
		},
		start() {
			this.status = 'rolling';
			const params = new URLSearchParams();
			params.append('amount', this.amount);
			this.interval = setInterval(()=>{
				this.rolling = [];
				const tem = [];
				for(let i = 0; i < this.amount; i++){
					const rand = Math.round(Math.random() *(this.people.length - 1));
					tem.push(this.people[rand].name);
				};
				this.rolling = tem;
			},100);
            this.winner = [{
                name: '等待ing...',
            }];
			this.$http.post(this.$root.host + 'front/do-annual.php?action=lottery', params)
			.then((resp)=>{
				this.winner = resp.data;
			});
		},
		stop() {
            this.rolling = [];
			this.status = 'result';
			clearInterval(this.interval);
		},
        restart() {
            this.status = 'waiting';
        },
	},
};
</script>


