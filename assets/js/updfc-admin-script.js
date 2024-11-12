// custom-script.js
document.addEventListener('DOMContentLoaded', function () {
    var titles = myPluginData.titles; // Use localized data
    var profilePage = document.getElementById("profile-page");
    if (profilePage) {
        var elems = profilePage.getElementsByTagName("h2");
        while (elems.length > 0) elems[0].remove();
    } else {
        console.error('Element with ID "profile-page" not found.');
    }
});
