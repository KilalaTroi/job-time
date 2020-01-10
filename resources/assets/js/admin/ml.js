import Vue from 'vue'
import { MLInstaller, MLCreate, MLanguage } from 'vue-multilanguage'
 
Vue.use(MLInstaller)
 
export default new MLCreate({
  initial: document.querySelector("meta[name='user-language']").getAttribute('content'),
  save: process.env.NODE_ENV === 'production',
  languages: [
    new MLanguage('en').create({
      siteName: 'Job Time',
      sbStatistics: 'Statistics',
      sbTotaling: 'Totaling'
    }),
 
    new MLanguage('ja').create({
      siteName: 'ジョブタイム',
      sbStatistics: '統計',
      sbTotaling: '集計'
    })
  ]
})