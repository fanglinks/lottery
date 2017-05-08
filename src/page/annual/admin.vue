<style>
    .data-table{
        width: 90%;
        margin: 10px auto;
    }
    .panel{
        width: 50%;
        margin: 10px auto;
    }
</style>
<template>
    <div>
        <div class="panel">
            <el-collapse v-model="activeNames">
                <el-collapse-item title="初始化操作" name="1">
                    <el-button type="danger" size="large" @click="clearWinner">清空中奖者</el-button>
                    <el-button type="danger" size="large" @click="clearRate">初始化中奖概率</el-button>
                </el-collapse-item>
                <el-collapse-item title="修改中奖概率" name="2">
                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-input v-model="rate" placeholder="请输入中奖概率，用小数表示，最多精确到小数点后两位"></el-input>    
                        </el-col>
                        <el-col :span="6">
                            <el-button type="primary" @click="setRate">修改</el-button>
                        </el-col>
                    </el-row>
                    <div>请先选择需要修改的用户，再修改中奖概率</div>
                </el-collapse-item>
                <el-collapse-item title="快捷操作按钮" name="2">
                    <el-button size="large" @click="allPeople">刷新</el-button>
                    <div>刷新会重绘表格</div>
                </el-collapse-item>
            </el-collapse>
        </div>
        <!--<el-button size="large" @click="allPeople">刷新</el-button>-->
        <div class="data-table">
            <el-table
                :data="people"
                border
                stripe
                tooltip-effect="dark"
                style="width: 100%"
                @selection-change="selectLine">
                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>
                <el-table-column
                    prop="name"
                    label="姓名"
                    width="120">
                    <!--<template scope="scope">{{ scope.row.name }}</template>-->
                </el-table-column>
                <el-table-column
                    prop="department"
                    label="所属中心"
                    :filters="[
                    {text: '总经办', value: '总经办' },
                    { text: '商业策略中心', value: '商业策略中心' },
                    { text: '石油零售中心', value: '石油零售中心' },
                    { text: '企业研发中心', value: '企业研发中心' },
                    { text: '用户研发中心', value: '用户研发中心' },
                    { text: '市场运营中心', value: '市场运营中心' },
                    { text: '财务', value: '财务' },
                    { text: '人事行政部', value: '人事行政部' },
                    ]"
                    :filter-method="filterDepartment"
                    show-overflow-tooltip>
                </el-table-column>
                <el-table-column
                    prop="winner"
                    label="是否中奖"
                    :filters="[
                    { text: '否', value: '0' },
                    { text: '已中奖', value: '1' },
                    ]"
                    :filter-method="filterWinner">
                    <template scope="scope">{{ Number(scope.row.winner) === 1 ? '已中奖' : '否' }}</template>                
                </el-table-column>
                <el-table-column
                    prop="rate"
                    label="中奖概率"
                    sortable>
                    <template scope="scope">{{ Number(scope.row.rate.toFixed(2)) * 100  }}%</template>
                </el-table-column>
            </el-table>
        </div>
        
    </div>
</template>
<script>
import qs from 'qs';

export default {
	name: 'admin',
	data () {
		return {
			people: [],
            activeNames: ['2'],
            rate: '',
            willChange: [],
		}
	},
    created () {
        if(this.$route.query.password !== 'UTWNAtvrro0CTK1gQgGwPtT9gPWKhpzcsPkql4bspo0wNxrCu2WOx6UU97ZzILDFNfTKJ0VG8Edsi3Zj'){
            this.$router.replace({ path:'/' });
		}
    },
	mounted () {
		this.allPeople();
        document.getElementsByTagName('body')[0].style.background = 'none';
        document.getElementsByTagName('html')[0].style.background = 'none';
	},
	methods: {
		allPeople() {
            console.log(this.$root.host)
			this.$http.get(this.$root.host + 'front/do-annual.php?action=queryPeople')
			.then((resp) => {
				this.people = resp.data;
			});
		},
        filterDepartment(value, row) {
            return row.department === value;
        },
        filterWinner(value, row) {
            return Number(row.winner) === Number(value);
        },
        clearWinner() {
            this.$confirm('此操作不可逆，请确认已记住所有中奖者', '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            })
            .then(() => {
                this.$http.get(this.$root.host + 'front/do-annual.php?action=clearWinner')
                .then((resp) => {
                    if(resp.data.status === 'ok'){
                        this.$message({
                            type: 'success',
                            message: '清空中奖者成功!'
                        });
                        this.allPeople();
                    }
                });
            }).catch(() => {
                this.$message({
                type: 'info',
                message: '已取消该操作'
                });
            });
        },
        clearRate() {
            this.$confirm('此操作不可逆，请确认：所有人的中奖概率将设置为100%', '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            })
            .then(() => {
                this.$http.get(this.$root.host + 'front/do-annual.php?action=clearRate')
                .then((resp) => {
                    if(resp.data.status === 'ok'){
                        this.$message({
                            type: 'success',
                            message: '初始化中奖概率成功!'
                        });
                        this.allPeople();
                    }
                });
            }).catch(() => {
                this.$message({
                type: 'info',
                message: '已取消该操作'
                });
            });
        },
        selectLine(select_people) {
            this.willChange = select_people;
        },
        setRate() {
            let params = {};
            params.rate = Number(this.rate).toFixed(2);
            // params.users = this.willChange;
            let i = 0;
            params.users = [];
            for(const user of this.willChange){
                params.users[i] = {};
                params.users[i].id = user.id;
                params.users[i].name = user.name;
                i++;
            }
            params = qs.stringify(params);
            if(this.willChange.length === 0){
                this.$message({
                    type: 'info',
                    message: '请在列表中选择要修改参与者'
                });
                return;
            }
            if(this.rate === '' || isNaN(this.rate) || Number(this.rate) > 1){
                this.$message({
                    type: 'info',
                    message: '请填写正确的中奖概率'
                });
                return;
            }
            let users = ''
            for(const user of this.willChange){
                users += ' ' + user.name;
            }
            this.$confirm(`将设置：${users} ，以上${this.willChange.length}人的中奖概率为${Number(this.rate) * 100}%`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            })
            .then(() => {
                this.$http.post(this.$root.host + 'front/do-annual.php?action=changeRate', params)
                .then((resp) => {
                    if(resp.data.status === 'ok'){
                        this.$message({
                            type: 'success',
                            message: '设置成功!'
                        });
                        this.allPeople();
                    }
                });
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消该操作'
                });
            });
        },
	},
};
</script>