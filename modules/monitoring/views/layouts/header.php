<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            padding: 20px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #b3c9d8;
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: #3498db;
            color: #fff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            padding: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.5;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .page-title {
            padding: 15px 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 25px;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .loading.show {
            display: block;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        .nav-brand {
            color: white;
            font-size: 1.5rem;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
        }
        .nav-brand:hover {
            color: #b3c9d8;
        }
        .user-info {
            color: #b3c9d8;
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }
        .user-info a {
            color: #b3c9d8;
            text-decoration: none;
            transition: color 0.3s;
        }
        .user-info a:hover {
            color: #fff;
        }
        .sidebar-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Loading Overlay Styles */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }
        .loading-overlay.show {
            display: flex !important;
        }
        .loading-overlay .spinner-container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            min-width: 200px;
        }
        .loading-overlay .spinner-container .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .loading-overlay .spinner-container h6 {
            margin-top: 15px;
            color: #2c3e50;
            font-weight: 600;
        }
        .loading-overlay .spinner-container small {
            color: #6c757d;
        }

        /* Toast styles */
        .toast-custom {
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
            min-width: 250px;
            max-width: 400px;
            border-left: 4px solid #6c757d;
        }
        .toast-custom.success { border-left-color: #28a745; }
        .toast-custom.error { border-left-color: #dc3545; }
        .toast-custom.warning { border-left-color: #ffc107; }
        .toast-custom.info { border-left-color: #17a2b8; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        #scheduleBody, #attendanceBody {
    transition: opacity 0.3s ease;
}
    </style>
</head>
<body>

<!-- Add jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ============================================
// API CALL HELPER - FIXES UNEXPECTED JSON ERROR
// ============================================
async function apiCall(endpoint, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch('/api/' + endpoint, options);
        
        // Check if response is OK
        if (!response.ok) {
            if (response.status === 401 || response.status === 302) {
                showToast('Session expired. Please login again.', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                return null;
            }
            throw new Error('HTTP error ' + response.status);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            throw new Error('Unexpected response format');
        }

        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API call error:', error);
        showToast('Error: ' + error.message, 'error');
        return null;
    }
}

// ============================================
// TOAST NOTIFICATION HELPER
// ============================================
function showToast(message, type = 'info') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }

    const colors = {
        success: '#28a745',
        error: '#dc3545', 
        warning: '#ffc107',
        info: '#17a2b8'
    };

    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    const toast = document.createElement('div');
    toast.className = `toast-custom ${type}`;
    toast.style.cssText = `
        background: white;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
        min-width: 250px;
        max-width: 400px;
        border-left: 4px solid ${colors[type] || colors.info};
    `;
    
    toast.innerHTML = `
        <span style="color: ${colors[type] || colors.info}; font-size: 1.2rem; font-weight: bold;">
            ${icons[type] || 'ℹ'}
        </span>
        <span style="flex: 1; color: #333;">${message}</span>
        <button onclick="this.parentElement.remove()" style="border: none; background: none; font-size: 1.2rem; cursor: pointer; color: #999;">×</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        }
    }, 4000);
}

// ============================================
// LOADING HELPERS
// ============================================
function showLoading() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
        `;
        overlay.innerHTML = `
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Processing...</h6>
                <small>Please wait</small>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// ============================================
// UTILITY HELPERS
// ============================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(time) {
    if (!time) return '';
    const parts = time.split(':');
    if (parts.length < 2) return time;
    const hours = parseInt(parts[0]);
    const minutes = parts[1];
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const h12 = hours % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function formatDate(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusBadge(status) {
    const badges = {
        'active': 'bg-success',
        'inactive': 'bg-secondary',
        'cancelled': 'bg-danger',
        'rescheduled': 'bg-warning',
        'present': 'bg-success',
        'absent': 'bg-danger',
        'late': 'bg-warning',
        'online': 'bg-info',
        'excused': 'bg-primary',
        'pending': 'bg-secondary'
    };
    return badges[status] || 'bg-secondary';
}
</script>