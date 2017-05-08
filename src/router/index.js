import Vue from 'vue';
import Router from 'vue-router';
import Lottery from '@/page/annual/lottery';
import Admin from '@/page/annual/admin';

Vue.use(Router);

export default new Router({
	routes: [
		{
			path: '/',
			name: 'Lottery',
			component: Lottery,
		},
		{
			path: '/admin',
			name: 'admin',
			component: Admin,
		},
	],
});
