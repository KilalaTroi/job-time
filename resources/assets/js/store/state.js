export default {
    translateTexts: null,
    loginUser: {},
    currentLang: '',
    currentTeamOption: JSON.parse(document.querySelector("meta[name='teams']").getAttribute('content')),
    currentMediaTeamOption: JSON.parse(document.querySelector("meta[name='media-teams']").getAttribute('content')),
    currentFullTeamOption: JSON.parse(document.querySelector("meta[name='full-teams']").getAttribute('content')),
    currentTeam: {},
    reportNotify: 0
}