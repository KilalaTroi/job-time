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
      sbTotaling: 'Totaling',
      sbUsers: 'Users',
      sbDepartments: 'Departments',
      sbJobTypes: 'Job Types',
      sbProjects: 'Projects',
      sbSchedules: 'Schedules',
      sbJobs: 'Jobs',
      sbOffDays: 'Off days',
      msWelcome: 'Welcome',
      mnProfile: 'Profile',
      mnLogOut: 'Log out',
      stWorkedTime: 'Worked time',
      stWorkingTime: 'Working time',
      stCrurrentJobs: 'Current jobs',
      stNewUsers: 'New users',
      txtChartAllocation: 'Kilala time allocation',
      txtTimeRecord: 'Time record',
      txtDepartment: 'Department',
      txtProject: 'Project',
      txtIssue: 'Issue',
      txtJobType: 'Job Type',
      lblStartDate: 'Start date',
      lblEndDate: 'End date',
      lblUsername: 'Username',
      lblDate: 'Date',
      lblStartDate: 'Start time',
      lblEndDate: 'End time',
      lblTime: 'Time',
      btExportExcel: 'Export Excel',
    }),
 
    new MLanguage('ja').create({
      siteName: 'ジョブタイム',
      sbStatistics: '統計',
      sbTotaling: '集計',
      sbUsers: 'ユーザー',
      sbDepartments: '部署',
      sbJobTypes: 'ジョブタイプ',
      sbProjects: 'プロジェクト',
      sbSchedules: 'スケジュール',
      sbJobs: 'ジョブ',
      sbOffDays: 'Off days',
      msWelcome: 'ようこそ',
      mnProfile: 'プロフィール',
      mnLogOut: 'ログアウト',
      stWorkedTime: '稼働した時間',
      stWorkingTime: '稼働中の時間',
      stCrurrentJobs: '現在のタスク',
      stNewUsers: '新ユーザー',
      txtChartAllocation: 'Kilala 稼働内容の時間配分',
      txtTimeRecord: 'タイムレコード',
      txtDepartment: '部署',
      txtProject: '案件',
      txtIssue: '号',
      txtJobType: '案件内容',
      lblStartDate: '開始日',
      lblEndDate: '終了日',
      lblUsername: 'ユーザー名',
      lblDate: '日',
      lblStartDate: '開始時刻',
      lblEndDate: '終了時刻',
      lblTime: '稼働時間',
      btExportExcel: 'Excelへ書き出す',
    })
  ]
})