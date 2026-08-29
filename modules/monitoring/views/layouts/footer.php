<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h6>Processing...</h6>
        <small class="text-muted">Please wait</small>
    </div>
</div>

<script>
// ============================================
// GLOBAL HELPER FUNCTIONS
// ============================================

// Show/Hide Loading
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('show');
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('show');
}

// Toast Notification
function showToast(message, type = 'info') {
    // Remove existing toasts to prevent stacking
    const existingToasts = document.querySelectorAll('.toast-container .toast');
    existingToasts.forEach(t => t.remove());
    
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    const icons = {
        success: 'bi-check-circle',
        error: 'bi-x-circle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    };
    
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '99999';
        document.body.appendChild(container);
    }
    
    const toastHTML = `
        <div class="toast align-items-center text-white border-0 show" role="alert" style="background-color: ${colors[type] || colors.info}; min-width: 250px;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icons[type] || icons.info} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = toastHTML;
    const toastElement = tempDiv.firstElementChild;
    container.appendChild(toastElement);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toastElement.parentNode) {
            toastElement.remove();
        }
    }, 4000);
}

// Format Date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

// Format Time
function formatTime(timeString) {
    if (!timeString) return 'N/A';
    try {
        const [hours, minutes] = timeString.split(':');
        const h = parseInt(hours);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${minutes} ${ampm}`;
    } catch (e) {
        return timeString;
    }
}

// Get Status Badge Class
function getStatusBadge(status) {
    const classes = {
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'online': 'bg-info',
        'excused': 'bg-secondary',
        'active': 'bg-success',
        'cancelled': 'bg-danger',
        'rescheduled': 'bg-warning',
        'reported': 'bg-warning',
        'in_progress': 'bg-info',
        'resolved': 'bg-success',
        'replaced': 'bg-primary',
        'inside': 'bg-success',
        'left': 'bg-secondary',
        'complete': 'bg-success',
        'incomplete': 'bg-warning',
        'needs_repair': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// API HELPER FUNCTION - FIXED
// ============================================
async function apiCall(endpoint, method = 'GET', data = null) {
    // Show loading overlay (except for auto-refresh)
    const overlay = document.getElementById('loadingOverlay');
    const isAutoRefresh = endpoint.includes('auto-refresh') || endpoint.includes('_=');
    if (overlay && !isAutoRefresh) {
        overlay.classList.add('show');
    }
    
    try {
        // Clean endpoint
        let url = endpoint.replace(/^\/+/, '');
        if (!url.startsWith('api/') && !url.startsWith('http')) {
            url = 'api/' + url;
        }
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        // Add cache busting for GET
        if (method === 'GET') {
            const separator = url.includes('?') ? '&' : '?';
            url = url + separator + '_=' + Date.now();
        }
        
        // Get base path
        const basePath = window.location.pathname.replace(/\/[^/]*$/, '/');
        const fullUrl = basePath + url;
        
        const response = await fetch(fullUrl, options);
        
        // Handle non-OK responses
        if (!response.ok) {
            let errorMsg = `HTTP error! status: ${response.status}`;
            try {
                const errorData = await response.json();
                errorMsg = errorData.error || errorData.message || errorMsg;
            } catch (e) {
                errorMsg = response.statusText || errorMsg;
            }
            throw new Error(errorMsg);
        }
        
        const result = await response.json();
        return result;
        
    } catch (error) {
        console.error('API Error:', error);
        // Don't show toast for auto-refresh errors
        if (!isAutoRefresh) {
            showToast(error.message || 'An error occurred. Please try again.', 'error');
        }
        return null;
    } finally {
        // Hide loading overlay
        if (overlay && !isAutoRefresh) {
            overlay.classList.remove('show');
        }
    }
}

// ============================================
// JQUERY READY - Initialize all pages
// ============================================
$(document).ready(function() {
    // Handle AJAX errors globally
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX Error:', error);
        if (jqXHR.status === 401) {
            // Session expired - redirect to login
            window.location.href = '/';
        }
    });
    
    // Close modal on escape key
    $(document).keydown(function(e) {
        if (e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });
});
</script>
</body>
</html>