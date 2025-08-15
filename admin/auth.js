// Authentication helper for admin pages
class AuthManager {
    constructor() {
        this.sessionKey = 'adminSession';
    }
    
    // Check if user is logged in
    isLoggedIn() {
        const sessionData = localStorage.getItem(this.sessionKey) || sessionStorage.getItem(this.sessionKey);
        if (!sessionData) return false;
        
        try {
            const session = JSON.parse(sessionData);
            // Check if session is valid (less than 24 hours for remember me, 2 hours for regular)
            const loginTime = new Date(session.loginTime);
            const now = new Date();
            const maxAge = session.remember ? 24 * 60 * 60 * 1000 : 2 * 60 * 60 * 1000; // 24h or 2h
            
            if (now - loginTime > maxAge) {
                this.logout();
                return false;
            }
            
            return true;
        } catch (error) {
            return false;
        }
    }
    
    // Get current user session
    getSession() {
        const sessionData = localStorage.getItem(this.sessionKey) || sessionStorage.getItem(this.sessionKey);
        if (!sessionData) return null;
        
        try {
            return JSON.parse(sessionData);
        } catch (error) {
            return null;
        }
    }
    
    // Logout user
    logout() {
        localStorage.removeItem(this.sessionKey);
        sessionStorage.removeItem(this.sessionKey);
        window.location.href = 'login.html';
    }
    
    // Protect page - redirect to login if not authenticated
    requireAuth() {
        if (!this.isLoggedIn()) {
            window.location.href = 'login.html';
            return false;
        }
        return true;
    }
    
    // Update user info in UI
    updateUserInfo() {
        const session = this.getSession();
        if (session) {
            // Update username in UI if element exists
            const userElements = document.querySelectorAll('.admin-username');
            userElements.forEach(el => {
                el.textContent = session.username;
            });
        }
    }
}

// Global auth manager instance
const authManager = new AuthManager();

// Auto-protect admin pages (except login page)
document.addEventListener('DOMContentLoaded', function() {
    // Don't protect login page
    if (window.location.pathname.includes('login.html')) {
        return;
    }
    
    // Protect all other admin pages
    if (!authManager.requireAuth()) {
        return;
    }
    
    // Update user info in UI
    authManager.updateUserInfo();
    
    // Add logout functionality to logout buttons
    const logoutButtons = document.querySelectorAll('.logout-btn, [data-action="logout"]');
    logoutButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin logout?')) {
                authManager.logout();
            }
        });
    });
});

