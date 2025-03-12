// Key to track number of tabs
const sessionKey = "session_active_tabs";

// Update tab count when a tab is opened
localStorage.setItem(sessionKey, (parseInt(localStorage.getItem(sessionKey) || "0") + 1).toString());

// Listen for tab close
window.addEventListener("beforeunload", function () {
    let count = parseInt(localStorage.getItem(sessionKey) || "0") - 1;
    
    if (count <= 0) {
        // Last tab closed, destroy session
        navigator.sendBeacon("../backend/tab_session_destroy.php", new URLSearchParams({ logout: "true" }));
    }
    
    localStorage.setItem(sessionKey, count.toString());
});
