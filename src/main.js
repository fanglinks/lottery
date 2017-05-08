// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import ElementUI from 'element-ui';
import axios from 'axios';
import 'element-ui/lib/theme-default/index.css';
import router from './router';

Vue.prototype.$http = axios;
Vue.use(ElementUI);

// import Lottery from './App';

Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
	el: '#app',
	router,
	template: `
		<router-view></router-view>
	`,
	data() {
		return {
			host: '',
		};
	},
	created() {
		if (location.href.indexOf('localhost') !== -1) {
			this.host = 'http://localhost/annual_lottery/';
		}
	},
	components: {

	},
});
