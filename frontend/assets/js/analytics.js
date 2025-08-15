// Analytics tracking functionality

// Generate or get session ID
function getSessionId() {
    let sessionId = sessionStorage.getItem('analytics_session_id');
    if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('analytics_session_id', sessionId);
    }
    return sessionId;
}

// Track page view
function trackPageView(customData = {}) {
    const analyticsData = {
        page_url: window.location.pathname + window.location.search,
        page_title: document.title,
        session_id: getSessionId(),
        ...customData
    };
    
    // Send analytics data to backend
    fetch(`${API_BASE_URL}/api/analytics`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(analyticsData)
    })
    .catch(error => {
        // Silently fail - don't disrupt user experience
        console.debug('Analytics tracking failed:', error);
    });
}

// Track custom events
function trackEvent(eventName, eventData = {}) {
    const analyticsData = {
        page_url: window.location.pathname + window.location.search,
        page_title: document.title + ' - ' + eventName,
        session_id: getSessionId(),
        event_name: eventName,
        event_data: JSON.stringify(eventData)
    };
    
    fetch(`${API_BASE_URL}/api/analytics`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(analyticsData)
    })
    .catch(error => {
        console.debug('Event tracking failed:', error);
    });
}

// Track form submissions
function trackFormSubmission(formName, formData = {}) {
    trackEvent('form_submission', {
        form_name: formName,
        form_data: formData
    });
}

// Track link clicks
function trackLinkClick(linkText, linkUrl) {
    trackEvent('link_click', {
        link_text: linkText,
        link_url: linkUrl
    });
}

// Track search queries
function trackSearch(searchQuery, resultsCount = 0) {
    trackEvent('search', {
        query: searchQuery,
        results_count: resultsCount
    });
}

// Track file downloads
function trackDownload(fileName, fileUrl) {
    trackEvent('download', {
        file_name: fileName,
        file_url: fileUrl
    });
}

// Track scroll depth
let maxScrollDepth = 0;
let scrollDepthTracked = false;

function trackScrollDepth() {
    if (scrollDepthTracked) return;
    
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = Math.round((scrollTop / documentHeight) * 100);
    
    if (scrollPercent > maxScrollDepth) {
        maxScrollDepth = scrollPercent;
        
        // Track milestone scroll depths
        if (maxScrollDepth >= 25 && maxScrollDepth < 50) {
            trackEvent('scroll_depth', { depth: '25%' });
        } else if (maxScrollDepth >= 50 && maxScrollDepth < 75) {
            trackEvent('scroll_depth', { depth: '50%' });
        } else if (maxScrollDepth >= 75 && maxScrollDepth < 90) {
            trackEvent('scroll_depth', { depth: '75%' });
        } else if (maxScrollDepth >= 90) {
            trackEvent('scroll_depth', { depth: '90%' });
            scrollDepthTracked = true; // Stop tracking after 90%
        }
    }
}

// Track time on page
let pageStartTime = Date.now();
let timeOnPageTracked = false;

function trackTimeOnPage() {
    if (timeOnPageTracked) return;
    
    const timeSpent = Math.round((Date.now() - pageStartTime) / 1000); // in seconds
    
    if (timeSpent >= 30) { // Track after 30 seconds
        trackEvent('time_on_page', { 
            time_spent: timeSpent,
            page_url: window.location.pathname
        });
        timeOnPageTracked = true;
    }
}

// Initialize analytics tracking
function initializeAnalytics() {
    // Track initial page view
    trackPageView();
    
    // Track scroll depth
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(trackScrollDepth, 100);
    });
    
    // Track time on page
    setTimeout(trackTimeOnPage, 30000); // Track after 30 seconds
    
    // Track when user leaves the page
    window.addEventListener('beforeunload', function() {
        const timeSpent = Math.round((Date.now() - pageStartTime) / 1000);
        if (timeSpent >= 10) { // Only track if user spent at least 10 seconds
            trackEvent('page_exit', { 
                time_spent: timeSpent,
                page_url: window.location.pathname
            });
        }
    });
    
    // Track external link clicks
    document.addEventListener('click', function(event) {
        const link = event.target.closest('a');
        if (link && link.href) {
            const url = new URL(link.href, window.location.origin);
            
            // Check if it's an external link
            if (url.hostname !== window.location.hostname) {
                trackLinkClick(link.textContent.trim(), link.href);
            }
            
            // Track specific internal actions
            if (link.href.includes('booking') || link.classList.contains('booking-btn')) {
                trackEvent('booking_click', { 
                    link_text: link.textContent.trim(),
                    page_url: window.location.pathname
                });
            }
            
            if (link.href.includes('tracking') || link.classList.contains('tracking-btn')) {
                trackEvent('tracking_click', { 
                    link_text: link.textContent.trim(),
                    page_url: window.location.pathname
                });
            }
        }
    });
    
    // Track form submissions
    document.addEventListener('submit', function(event) {
        const form = event.target;
        if (form.tagName === 'FORM') {
            const formData = new FormData(form);
            const formDataObj = {};
            
            // Convert FormData to object (excluding sensitive data)
            for (let [key, value] of formData.entries()) {
                if (!key.includes('password') && !key.includes('email')) {
                    formDataObj[key] = value;
                }
            }
            
            trackFormSubmission(form.id || form.className || 'unknown_form', formDataObj);
        }
    });
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure other scripts are loaded
    setTimeout(initializeAnalytics, 1000);
});

// Export functions for manual tracking
window.trackAnalytics = trackPageView;
window.trackEvent = trackEvent;
window.trackFormSubmission = trackFormSubmission;
window.trackLinkClick = trackLinkClick;
window.trackSearch = trackSearch;
window.trackDownload = trackDownload;

