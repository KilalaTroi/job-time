import Vue from 'vue'
import { MLInstaller, MLCreate, MLanguage } from 'vue-multilanguage'

Vue.use(MLInstaller)

export default new MLCreate({
	initial: document.querySelector("meta[name='user-language']").getAttribute('content'),
	save: process.env.NODE_ENV === 'production',
	languages: [
	new MLanguage('vi').create({
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
		lblStartTime: 'Start time',
		lblEndTime: 'End time',
		lblTime: 'Time',
		btExportExcel: 'Export Excel',
		txtScheduleTitle: 'Project schedules',
		txtScheduleSearch: 'search...',
		txtCreateProject: 'Create new project',
		txtFilter: 'Filter',
		txtKeyword: 'Keyword',
		txtDepartments: 'Departments',
		txtTypes: 'Types',
		txtType: 'Type',
		txtSelectOne: 'Select one',
		txtSelectAll: 'All',
		txtName: 'Name',
		txtNameVi: 'Name VI',
		txtNameJa: 'Name JA',
		txtNoPeriod: 'No period',
		txtStartDate: 'Start Date',
		txtEndDate: 'End Date',
		txtCreate: 'Create',
		txtCancel: 'Cancel',
		txtProjectsList: 'Projects list',
		txtAddIssue: 'Add new issue',
		txtImportIssue: 'Import Excel',
		txtViewArchive: "View archive",
		txtPage: 'Page',
		txtAdd: 'Add',
		txtSave: 'Save',
		txtDelete: 'Delete',
		txtUpdate: 'Update',
		txtSelectDate: 'Select date',
		txtLineRoomId: 'LINE WORKS Room ID',
		txtBoxUrl: 'Box Base Destination',
		txtUpdateTime: 'Edit Time',
		txtEditIssue: 'Edit issue',
		txtEditProject: 'Edit Project',
		txtPhase: 'Phase',
		txtJobTypeList: 'Job type list',
		txtCreateType: 'Create new job type',
		txtColor: 'Color',
		txtSlug: 'Slug',
		txtDesc: 'Description',
		txtDescVi: 'Description VI',
		txtDescJa: 'Description JA',
		txtEditType: 'Edit Type',
		txtCreateDept: 'Create new department',
		txtDeptList: 'Departments list',
		txtDepts: 'Departments',
		txtEditDept: 'Edit department',
		txtCreateUser: 'Create new user',            
		txtEditUser: 'Edit user',
		txtUsername: 'Username',
		txtEmail: 'Email',
		txtRole: 'Role',
		txtSelectRole: 'Select role',
		txtPassword: 'Password',
		txtRePassword: 'Reconfirm password',
		txtUserList: 'User list',
		txtAction: 'Action',
		txtLang: 'Language',
		txtEditProfile: 'Edit Profile',
		txtUsers: 'Users',
		txtProjects: 'Projects',
		txtExportExcel: 'Export Excel',
		txtAddTime: 'Add Time',
		txtEditTime: 'Update Time',
		txtExcludeLunchBreak: 'Exclude lunch break',
		txtJobsList: 'Jobs list',
		txtShowBySchedule: 'Show by schedule',
		txtShowAll: 'Show all',
		msgOverTime: 'You work over 8 hours!!!',
		txtMyOffDay: 'My off days',
		txtOffDay: 'Off days',
		txtStaffOffDay: 'Staff off days',
		txtWorkedTime: 'Worked Time',
		txtHour: 'hrs',
		txtWorkingTime: 'Working time',
		txtCurrentJobs: 'Current jobs',
		txtNewUser: 'New users',
		txtKilalaTimeAllocation: 'Kilala time allocation',
		txtWelcome: 'Welcome',
		txtProfile: 'Profile',
		txtLogOut: 'Log out',
		msgConfirmDelete: 'Are you sure want to delete this record?',
		msgConfirmChange: 'Are you sure about this change?',
		txtPickSome: 'Pick some',
		txtProcess: 'Process',    
		txtFinish: 'Finish', 
		txtUpload: 'Upload',
		txtUploadList: 'Upload list',
		txtNewUpload: 'New upload',
		txtProjectIssue: 'Project Issue',
		txtMessage:'Message',
		txtBoxDestination: 'BOX Destination',
		txtFile: 'File',
		txtSelect: 'Select',
		txtProcessList: 'Process List',
		txtShow: 'Show',
		txtShowBy: 'Show by',
		txtSend: 'Send',		
		txtRCFullDay: 'Full',
		txtRCAM: 'AM',
		txtRCPM: 'PM',
	}),

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
		lblStartTime: 'Start time',
		lblEndTime: 'End time',
		lblTime: 'Time',
		btExportExcel: 'Export Excel',
		txtScheduleTitle: 'Project schedules',
		txtScheduleSearch: 'search...',
		txtCreateProject: 'Create new project',
		txtFilter: 'Filter',
		txtKeyword: 'Keyword',
		txtDepartments: 'Departments',
		txtTypes: 'Types',
		txtType: 'Type',
		txtSelectOne: 'Select one',
		txtSelectAll: 'All',
		txtName: 'Name',
		txtNameVi: 'Name VI',
		txtNameJa: 'Name JA',
		txtNoPeriod: 'No period',
		txtStartDate: 'Start Date',
		txtEndDate: 'End Date',
		txtCreate: 'Create',
		txtCancel: 'Cancel',
		txtProjectsList: 'Projects list',
		txtAddIssue: 'Add new issue',
		txtImportIssue: 'Import Excel',
		txtViewArchive: "View archive",
		txtPage: 'Page',
		txtAdd: 'Add',
		txtSave: 'Save',
		txtDelete: 'Delete',
		txtUpdate: 'Update',
		txtSelectDate: 'Select date',
		txtLineRoomId: 'LINE WORKS Room ID',
		txtBoxUrl: 'Box Base Destination',
		txtUpdateTime: 'Edit Time',
		txtEditIssue: 'Edit issue',
		txtEditProject: 'Edit Project',
		txtPhase: 'Phase',
		txtJobTypeList: 'Job type list',
		txtCreateType: 'Create new job type',
		txtColor: 'Color',
		txtSlug: 'Slug',
		txtDesc: 'Description',
		txtDescVi: 'Description VI',
		txtDescJa: 'Description JA',
		txtEditType: 'Edit Type',
		txtCreateDept: 'Create new department',
		txtDeptList: 'Departments list',
		txtDepts: 'Departments',
		txtEditDept: 'Edit department',
		txtCreateUser: 'Create new user',            
		txtEditUser: 'Edit user',
		txtUsername: 'Username',
		txtEmail: 'Email',
		txtRole: 'Role',
		txtSelectRole: 'Select role',
		txtPassword: 'Password',
		txtRePassword: 'Reconfirm password',
		txtUserList: 'User list',
		txtAction: 'Action',
		txtLang: 'Language',
		txtEditProfile: 'Edit Profile',
		txtUsers: 'Users',
		txtProjects: 'Projects',
		txtExportExcel: 'Export Excel',
		txtAddTime: 'Add Time',
		txtEditTime: 'Update Time',
		txtExcludeLunchBreak: 'Exclude lunch break',
		txtJobsList: 'Jobs list',
		txtShowBySchedule: 'Show by schedule',
		txtShowAll: 'Show all',
		msgOverTime: 'You work over 8 hours!!!',
		txtMyOffDay: 'My off days',
		txtOffDay: 'Off days',
		txtStaffOffDay: 'Staff off days',
		txtWorkedTime: 'Worked Time',
		txtHour: 'hrs',
		txtWorkingTime: 'Working time',
		txtCurrentJobs: 'Current jobs',
		txtNewUser: 'New users',
		txtKilalaTimeAllocation: 'Kilala time allocation',
		txtWelcome: 'Welcome',
		txtProfile: 'Profile',
		txtLogOut: 'Log out',
		msgConfirmDelete: 'Are you sure want to delete this record?',
		msgConfirmChange: 'Are you sure about this change?',
		txtPickSome: 'Pick some',
		txtProcess: 'Process',    
		txtFinish: 'Finish', 
		txtUpload: 'Upload',
		txtUploadList: 'Upload list',
		txtNewUpload: 'New upload',
		txtProjectIssue: 'Project Issue',
		txtMessage:'Message',
		txtBoxDestination: 'BOX Destination',
		txtFile: 'File',
		txtSelect: 'Select',
		txtProcessList: 'Process List',
		txtShow: 'Show',
		txtSend: 'Send',		
		txtRCFullDay: 'Full',
		txtRCAM: 'AM',
		txtRCPM: 'PM',
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
		sbOffDays: '休みの日',
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
		lblStartTime: '開始時刻',
		lblEndTime: '終了時刻',
		lblTime: '稼働時間',
		btExportExcel: 'Excelへ書き出す',
		txtScheduleTitle: '案件スケジュール',
		txtScheduleSearch: '探す...',
		txtCreateProject: '新プロジェクトの作成',
		txtFilter: 'フィルタ',
		txtKeyword: 'キーワード',
		txtDepartments: '部署',
		txtTypes: 'タイプ',            
		txtType: '種類',
		txtSelectOne: '一つ選択',
		txtSelectAll: 'すべて',
		txtName: '名前',
		txtNameVi: 'ベトナム名',
		txtNameJa: '日本名',
		txtNoPeriod: '期間なし',
		txtStartDate: '開始日',
		txtEndDate: '終了日',
		txtCreate: '作成',
		txtCancel: 'キャンセル',
		txtProjectsList: 'プロジェクト一覧',
		txtAddIssue: '新しい号を追加',
		txtImportIssue: 'Excel読み込み',
		txtViewArchive: "アーカイブを見る",
		txtPage: 'ページ',
		txtAdd: '追加',
		txtSave: 'セーブ',
		txtDelete: '削除する',
		txtUpdate: '更新',
		txtSelectDate: '日付を選択',
		txtLineRoomId: 'LINE WORKSルームID',
		txtBoxUrl: 'Box Base Destination',
		txtUpdateTime: '時間を編集', 
		txtEditIssue: '問題を修正',
		txtEditProject: 'プロジェクトを修正',
		txtPhase: '段階',
		txtJobTypeList: 'ジョブタイム一覧',
		txtCreateType: '新ジョブタイプの作成',
		txtColor: '色',
		txtSlug: 'Slug',
		txtDesc: '説明',
		txtDescVi: '説明 （ベトナム語）',
		txtDescJa: '説明（日本語）',
		txtEditType: '説明（日本語）',
		txtCreateDept: '新部署の作成',
		txtDeptList: '部署一覧',
		txtDepts: '部署',
		txtEditDept: '部署を修正',
		txtCreateUser: '新ユーザーの作成',        
		txtEditUser: 'ユーザーを修正',
		txtUsername: 'ユーザー名',
		txtEmail: 'メール',
		txtRole: '権限',
		txtSelectRole: '権限の選択',
		txtPassword: 'パスワード',
		txtRePassword: 'パスワード（再確認）',
		txtUserList: 'ユーザー一覧',
		txtAction: '編集',
		txtLang: '言語',
		txtEditProfile: 'プロファイルを修正',
		txtUsers: 'ユーザー',
		txtProjects: '案件',            
		txtExportExcel: 'Excelへ書き出す',
		txtAddTime: '時間を追加',
		txtEditTime: '時間を更新',
		txtExcludeLunchBreak: '昼休みを除く',
		txtJobsList: 'ジョブ一覧',
		txtShowBySchedule: 'スケジュールによる表示',
		txtShowAll: '全部で表示',
		msgOverTime: '8時間以上働きました！',
		txtMyOffDay: '私の休みの日',
		txtOffDay: '休みの日',
		txtStaffOffDay: 'スタッフの休みの日',
		txtWorkedTime: '稼働した時間',
		txtHour: '時間',
		txtWorkingTime: '稼働中の時間',
		txtCurrentJobs: '現在のタスク',
		txtNewUser: '新ユーザー',
		txtKilalaTimeAllocation: 'Kilala 稼働内容の時間配分',
		txtWelcome: 'ようこそ',
		txtProfile: 'プロフィール',
		txtLogOut: 'ログアウト',
		msgConfirmDelete: 'このレコードを削除してもよろしいですか？',
		msgConfirmChange: 'この変化に確かですか？',
		txtPickSome: 'いくつかを選択',
		txtProcess: '進行',    
		txtFinish: '終了', 
		txtUpload: 'アップロード',
		txtUploadList: 'アップロードリスト',
		txtNewUpload: '追加アップ',
		txtProjectIssue: '案件・号',
		txtMessage:'申し送り',
		txtBoxDestination: 'BOXのリンク',
		txtFile: 'ファイル',
		txtSelect: '選択',
		txtProcessList: '案件リスト',
		txtShow: '詳細',
		txtShowBy: '表示',
		txtSend: '送信',
		txtRCFullDay: 'Full',
		txtRCAM: 'AM',
		txtRCPM: 'PM',
    
	})
	]
})