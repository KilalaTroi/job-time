import Vue from 'vue'
import { MLInstaller, MLCreate, MLanguage } from 'vue-multilanguage'
import langViDict from './language/vi'
import langJaDict from './language/ja'

Vue.use(MLInstaller)

export default new MLCreate({
	initial: document.querySelector("meta[name='user-language']").getAttribute('content'),
	save: process.env.NODE_ENV === 'production',
	languages: [
		new MLanguage('vi').create(langViDict),
		new MLanguage('ja').create(langJaDict)
	]
})