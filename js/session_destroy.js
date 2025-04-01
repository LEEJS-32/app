window.addEventListener("beforeunload", function() {
    if (!document.cookie.includes("remember_me")) {
        fetch("../pages/logout.php", { method: "POST" }); // Logout on tab close
    }
});

