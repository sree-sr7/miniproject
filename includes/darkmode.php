<?php
function getDarkModePreference() {
    return isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';
}

function getDarkModeClasses($darkMode) {
    return [
        'html_class' => $darkMode ? 'dark' : 'light',
        'body_class' => $darkMode ? 'dark-mode' : '',
        'navbar_class' => $darkMode ? 'navbar-dark bg-dark' : 'navbar-light bg-light'
    ];
}
?>