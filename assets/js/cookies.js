// Fonction pour définir un cookie
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Fonction pour obtenir un cookie
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Fonction pour gérer le consentement des cookies
function acceptCookies() {
    // Utiliser sessionStorage au lieu d'un cookie
    sessionStorage.setItem('cookies_accepted', 'true');
    document.getElementById('cookieConsent').style.display = 'none';
}

// Fonction pour refuser les cookies
function refuseCookies() {
    // Utiliser sessionStorage au lieu d'un cookie
    sessionStorage.setItem('cookies_accepted', 'false');
    document.getElementById('cookieConsent').style.display = 'none';
}

// Afficher le popup uniquement sur la page d'accueil et une fois par session
document.addEventListener('DOMContentLoaded', function() {
    // Vérifie si on est sur la page d'accueil
    const isHomePage = window.location.pathname === '/biblio/' || 
                      window.location.pathname === '/biblio/index.php';
    
    // Vérifie si l'utilisateur a déjà fait son choix dans cette session
    const cookieConsent = sessionStorage.getItem('cookies_accepted');
    
    // Si c'est la page d'accueil et que l'utilisateur n'a pas encore fait son choix
    if (isHomePage && cookieConsent === null) {
        setTimeout(() => {
            document.getElementById('cookieConsent').style.display = 'block';
        }, 1000); // Délai de 1 seconde pour une meilleure expérience utilisateur
    }
});
