 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Visitor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- html2canvas for downloading as image -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        * { 
            font-family: 'Inter', sans-serif; 
            box-sizing: border-box;
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            margin: 0;
        }
        
        .registration-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px 25px;
            max-width: 750px;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        
        .registration-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.3); }
            50% { box-shadow: 0 0 40px rgba(102, 126, 234, 0.6); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes confettiFall {
            0% { transform: translateY(-100px) rotate(0deg) scale(1); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg) scale(0); opacity: 0; }
        }
        
        .logo-header { text-align: center; margin-bottom: 25px; }
        .logo-header .icon { font-size: 42px; color: #667eea; }
        .logo-header h1 { color: #1a1a2e; font-weight: 700; margin-top: 8px; font-size: 24px; }
        .logo-header p { color: #6c757d; font-size: 13px; margin: 0; }
        
        .form-label { font-weight: 600; color: #2d3748; font-size: 13px; }
        .required-star { color: #e53e3e; margin-left: 2px; }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .preview-container { margin-top: 10px; display: none; }
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            object-fit: cover;
        }
        
        /* ============================================
           WAITING MESSAGE
           ============================================ */
        .waiting-message {
            display: none;
            text-align: center;
            padding: 30px 15px;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .waiting-message .waiting-icon {
            font-size: 70px;
            color: #f59e0b;
            background: #fffbeb;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }
        
        .waiting-message .spinner-ring {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }
        
        /* ============================================
           APPROVED MESSAGE - MOBILE FRIENDLY
           ============================================ */
        .approved-message {
            display: none;
            text-align: center;
            padding: 20px 10px;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .approved-message .success-header {
            background: var(--success-gradient);
            padding: 20px 15px;
            border-radius: 16px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .approved-message .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .approved-message .success-header .big-check {
            font-size: 56px;
            animation: bounceIn 0.8s ease;
            display: block;
        }
        
        .approved-message .success-header h2 {
            font-weight: 800;
            font-size: 22px;
            margin: 8px 0 3px;
            letter-spacing: 0.5px;
        }
        
        .approved-message .success-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .approved-message .visitor-card {
            background: white;
            border-radius: 16px;
            padding: 20px 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 2px solid #e2e8f0;
            margin: 15px 0;
            transition: all 0.3s ease;
            animation: glow 3s infinite;
        }
        
        .approved-message .visitor-card .qr-icon {
            font-size: 44px;
            color: #667eea;
            margin-bottom: 8px;
            display: block;
        }
        
        .approved-message .visitor-card .id-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #a0aec0;
            letter-spacing: 2px;
            font-weight: 600;
            display: block;
        }
        
        .approved-message .visitor-card .visitor-id {
            font-family: 'Courier New', monospace;
            font-size: 22px;
            font-weight: 800;
            color: #667eea;
            letter-spacing: 2px;
            background: #f7fafc;
            padding: 10px 16px;
            border-radius: 10px;
            display: inline-block;
            border: 2px dashed #667eea;
            word-break: break-all;
            max-width: 100%;
        }
        
        .approved-message .visitor-card .visitor-id span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .approved-message .visitor-card .valid-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 8px;
        }
        
        .approved-message .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 15px 0;
        }
        
        .approved-message .info-grid .info-item {
            background: #f7fafc;
            padding: 10px 12px;
            border-radius: 10px;
            text-align: left;
        }
        
        .approved-message .info-grid .info-item .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #a0aec0;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: block;
        }
        
        .approved-message .info-grid .info-item .value {
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
            margin-top: 2px;
            word-break: break-word;
        }
        
        .approved-message .info-grid .info-item .value i {
            color: #48bb78;
            margin-right: 4px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-approved { background: #d1fae5; color: #065f46; }
        
        .reference-number {
            background: #f3f4f6;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 16px;
            letter-spacing: 1px;
            display: inline-block;
        }
        
        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 15px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4); color: white; }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        
        .btn-reset { 
            border-radius: 10px; 
            padding: 12px; 
            font-weight: 500; 
            width: 100%; 
            font-size: 14px;
        }
        
        .btn-check-status {
            background: #3b82f6;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 15px;
        }
        .btn-check-status:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4); color: white; }
        
        .btn-success-premium {
            background: var(--success-gradient);
            border: none;
            padding: 12px 25px;
            font-weight: 700;
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 15px;
            letter-spacing: 0.5px;
        }
        .btn-success-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.4);
            color: white;
        }
        
        .btn-download {
            background: #1a1a2e;
            border: none;
            padding: 12px 25px;
            font-weight: 700;
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 15px;
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(26, 26, 46, 0.4);
            color: white;
        }
        
        .loading-spinner { display: none; }
        .form-text { font-size: 11px; color: #a0aec0; }
        
        .checkbox-container {
            background: #f7fafc;
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
        }
        .checkbox-container .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        .checkbox-container .form-check-label {
            font-size: 13px;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 12px 0;
            text-align: left;
        }
        .info-box i { color: #3b82f6; font-size: 18px; }
        .info-box ul { padding-left: 18px; margin: 5px 0; }
        .info-box ul li { font-size: 13px; }
        
        /* Confetti */
        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            animation: confettiFall linear forwards;
        }
        
        /* Download wrapper */
        #downloadWrapper {
            background: white;
            padding: 20px;
            border-radius: 16px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        /* Upload buttons */
        .upload-btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .upload-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 576px) {
            .registration-card { 
                padding: 18px 14px; 
                border-radius: 18px;
            }
            .logo-header h1 { font-size: 20px; }
            .logo-header .icon { font-size: 34px; }
            .approved-message .info-grid { grid-template-columns: 1fr; }
            .approved-message .visitor-card .visitor-id { font-size: 18px; padding: 8px 12px; }
            .approved-message .success-header h2 { font-size: 18px; }
            .approved-message .success-header .big-check { font-size: 44px; }
            .btn-submit, .btn-check-status, .btn-success-premium, .btn-download { font-size: 14px; padding: 12px 20px; }
            .row.g-3 { margin: 0 -6px; }
            .row.g-3 .col-md-6 { padding: 0 6px; }
            .form-control, .form-select { font-size: 13px; padding: 8px 12px; }
            .upload-btn { font-size: 13px; padding: 10px; }
        }
        
        @media (max-width: 400px) {
            .registration-card { padding: 14px 10px; }
            .approved-message .visitor-card .visitor-id { font-size: 15px; }
            .approved-message .info-grid .info-item .value { font-size: 12px; }
        }
        
        /* Fix for iOS Safari */
        input, select, textarea {
            -webkit-appearance: none;
            appearance: none;
        }
        
        .form-check-input {
            -webkit-appearance: checkbox;
            appearance: checkbox;
        }
    </style>
</head>
<body>
    <div class="registration-card">
        <!-- Registration Form -->
        <div id="registrationForm">
            <div class="logo-header">
                <div class="icon"><i class="bi bi-shield-check"></i></div>
                <h1>Visitor Registration</h1>
                <p>Complete the form below to register your visit</p>
                <div class="badge bg-warning text-dark mt-2" style="font-size: 11px;">
                    <i class="bi bi-info-circle"></i> All fields marked with * are required
                </div>
            </div>
            
            <form id="visitorForm" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Full Name <span class="required-star">*</span></label>
                        <input type="text" class="form-control" name="visitor_name" placeholder="Enter your full name" required>
                        <div class="invalid-feedback">Please enter your full name</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Contact Number <span class="required-star">*</span></label>
                        <input type="tel" class="form-control" name="contact_number" placeholder="09XX-XXX-XXXX" required pattern="[0-9+\-\s()]{7,20}">
                        <div class="invalid-feedback">Please enter a valid contact number</div>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="you@example.com">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Home Address <span class="required-star">*</span></label>
                        <input type="text" class="form-control" name="home_address" placeholder="Enter your complete address" required>
                        <div class="invalid-feedback">Please enter your home address</div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Purpose of Visit <span class="required-star">*</span></label>
                    <select class="form-select" name="purpose_of_visit" required>
                        <option value="">Select purpose...</option>
                        <option value="official_business">Official Business</option>
                        <option value="interview">Interview</option>
                        <option value="meeting">Meeting</option>
                        <option value="enrollment">Enrollment</option>
                        <option value="delivery">Delivery</option>
                        <option value="maintenance">Maintenance/Repair</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="invalid-feedback">Please select a purpose</div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Office/Department <span class="required-star">*</span></label>
                        <input type="text" class="form-control" name="department" placeholder="e.g. Registrar's Office" required>
                        <div class="invalid-feedback">Please enter the department</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Person to Visit <span class="required-star">*</span></label>
                        <input type="text" class="form-control" name="person_to_visit" placeholder="Name of person you're visiting" required>
                        <div class="invalid-feedback">Please enter the person to visit</div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Vehicle Plate Number</label>
                    <input type="text" class="form-control" name="vehicle_plate" placeholder="Optional - Enter vehicle plate number">
                    <small class="form-text">Leave blank if not applicable</small>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Valid ID Attachment <span class="required-star">*</span></label>
                    <!-- Two upload options -->
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-outline-primary upload-btn flex-fill" onclick="document.getElementById('cameraInput').click()">
                            <i class="bi bi-camera-fill"></i> Take Photo
                        </button>
                        <button type="button" class="btn btn-outline-secondary upload-btn flex-fill" onclick="document.getElementById('galleryInput').click()">
                            <i class="bi bi-image"></i> Choose Photo
                        </button>
                    </div>
                    <!-- Camera capture (opens rear camera on mobile) -->
                    <input type="file" id="cameraInput" class="d-none"
                           accept="image/*" capture="environment">
                    <!-- Gallery / file picker -->
                    <input type="file" id="galleryInput" class="d-none"
                           accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/jpeg,image/png,image/webp,image/heic,image/heif">
                    <!-- The REAL input the form submits (kept in sync) -->
                    <input type="file" class="form-control d-none" name="id_attachment"
                           accept=".jpg,.jpeg,.png,.webp,.heic,.heif,image/jpeg,image/png,image/webp,image/heic,image/heif" required>
                    <small class="form-text">Accepted formats: JPG, JPEG, PNG, WebP, HEIC, HEIF (Max 5MB)</small>
                    <div class="preview-container" id="previewContainer">
                        <img id="idPreview" class="preview-image" alt="ID Preview">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeImage()">
                            <i class="bi bi-x-circle"></i> Remove
                        </button>
                    </div>
                    <div class="invalid-feedback">Please upload a valid ID</div>
                </div>
                
                <div class="mt-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-control" name="additional_notes" rows="2" placeholder="Any additional information..."></textarea>
                </div>
                
                <div class="mt-3 checkbox-container">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="privacy_consent" value="1" required>
                        <label class="form-check-label" style="font-weight: 500;">
                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal" style="color: #667eea;">Privacy Notice</a> <span class="required-star">*</span>
                        </label>
                        <div class="invalid-feedback">You must agree to the Privacy Notice</div>
                    </div>
                </div>
                
                <div class="row g-2 mt-3">
                    <div class="col-4 col-md-4">
                        <button type="button" class="btn btn-outline-secondary btn-reset" onclick="resetForm()">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>
                    <div class="col-8 col-md-8">
                        <button type="submit" class="btn btn-submit">
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status" aria-hidden="true"></span>
                            <i class="bi bi-check-circle"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- ============================================
        WAITING FOR ADMIN APPROVAL
        ============================================ -->
        <div id="waitingMessage" class="waiting-message">
            <div class="waiting-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <h2 class="mt-3" style="color: #1a1a2e; font-size: 22px;">Registration Submitted!</h2>
            <p style="font-size: 16px; color: #d97706; font-weight: 600;">
                ⏳ Waiting for Admin Approval
            </p>
            <p class="text-muted" style="font-size: 14px;">
                Your ID is being reviewed by security personnel.
            </p>
            
            <div class="spinner-ring"></div>
            
            <div class="info-box text-start mt-3">
                <i class="bi bi-info-circle"></i>
                <strong>What happens next?</strong>
                <ul>
                    <li>Security will review your uploaded ID</li>
                    <li>Once approved, your Visitor ID will be generated</li>
                    <li>This page will automatically update</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <div style="background: #f8f9fa; padding: 12px 15px; border-radius: 10px;">
                    <p class="text-muted mb-1" style="font-size: 11px;">YOUR REFERENCE NUMBER</p>
                    <div class="reference-number" id="referenceNumber">Loading...</div>
                </div>
            </div>
            
            <div class="mt-3">
                <span class="status-badge status-pending" id="statusBadge">
                    <i class="bi bi-clock"></i> Pending Approval
                </span>
            </div>
            
            <div class="mt-4">
                <button class="btn btn-check-status" onclick="checkApprovalStatus()">
                    <i class="bi bi-arrow-repeat"></i> Check Status
                </button>
            </div>
            
            <div id="statusCheckResult" class="mt-3"></div>
        </div>
        
        <!-- ============================================
        APPROVED - PREMIUM DESIGN (MOBILE FRIENDLY)
        ============================================ -->
        <div id="approvedMessage" class="approved-message">
            <!-- Success Header -->
            <div class="success-header">
                <span class="big-check">✅</span>
                <h2>REGISTRATION APPROVED!</h2>
                <p>You are cleared to enter the campus</p>
            </div>
            
            <!-- Visitor ID Card - Downloadable -->
            <div id="visitorIdCard">
                <div class="visitor-card">
                    <span class="qr-icon"><i class="bi bi-qr-code"></i></span>
                    <span class="id-label">Your Visitor ID</span>
                    <div class="visitor-id">
                        <span id="visitorQRCode">Loading...</span>
                    </div>
                    <p class="valid-text">
                        <i class="bi bi-shield-check" style="color: #48bb78;"></i> 
                        Present this ID to security upon entry
                    </p>
                </div>
                
                <!-- Info Grid -->
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label"><i class="bi bi-person"></i> Visitor</span>
                        <div class="value" id="visitorNameDisplay">-</div>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-clock"></i> Time In</span>
                        <div class="value"><i class="bi bi-check-circle-fill"></i> <span id="timeInDisplay">-</span></div>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-building"></i> Department</span>
                        <div class="value" id="departmentDisplay">-</div>
                    </div>
                    <div class="info-item">
                        <span class="label"><i class="bi bi-person-badge"></i> Status</span>
                        <div class="value"><span class="status-badge status-approved"><i class="bi bi-check-circle-fill"></i> Approved</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button class="btn-download" onclick="downloadAsImage()">
                    <i class="bi bi-download"></i> Download ID
                </button>
                <button class="btn-success-premium" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button class="btn btn-submit" onclick="location.reload()" style="width: auto; padding: 12px 25px; font-size: 14px;">
                    <i class="bi bi-arrow-repeat"></i> Register Another
                </button>
            </div>
            
            <p class="text-muted mt-3" style="font-size: 11px;">
                <i class="bi bi-clock-history"></i> This ID is valid for today's visit only
            </p>
        </div>
    </div>
    
    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-shield-lock" style="color: #667eea;"></i> Privacy Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Data Collection Purpose</h6>
                    <p>We collect your personal information for the purpose of visitor monitoring and security.</p>
                    
                    <h6 class="mt-3">Information We Collect</h6>
                    <ul>
                        <li>Full Name</li>
                        <li>Contact Information</li>
                        <li>Address</li>
                        <li>ID Information</li>
                        <li>Visit Details</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> Your information will be kept confidential.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background: var(--primary-gradient); border: none; padding: 10px 30px;">
                        <i class="bi bi-check-lg"></i> I Understand
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ============================================
        // CONFIGURATION
        // ============================================
        const API_BASE = window.location.origin + '/modules/monitoring/public/index.php?api=';
        const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
        const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];
        let visitorId = null;
        let checkInterval = null;
        let isApproved = false;

        // ============================================
        // CONFETTI
        // ============================================
        function fireConfetti() {
            const container = document.createElement('div');
            container.className = 'confetti-container';
            document.body.appendChild(container);
            
            const colors = ['#667eea', '#764ba2', '#f7971e', '#ffd200', '#11998e', '#38ef7d', '#f5576c', '#ff6b6b'];
            const shapes = ['■', '●', '▲', '★', '♦', '♥'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.textContent = shapes[Math.floor(Math.random() * shapes.length)];
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.color = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.fontSize = (Math.random() * 20 + 10) + 'px';
                confetti.style.animationDuration = (Math.random() * 2 + 1.5) + 's';
                confetti.style.animationDelay = (Math.random() * 1.5) + 's';
                container.appendChild(confetti);
            }
            
            setTimeout(() => {
                container.remove();
            }, 4000);
        }

        // ============================================
        // DOWNLOAD AS IMAGE
        // ============================================
        function downloadAsImage() {
            const card = document.getElementById('visitorIdCard');
            
            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';
            btn.disabled = true;
            
            html2canvas(card, {
                scale: 2,
                backgroundColor: '#ffffff',
                allowTaint: true,
                useCORS: true,
                logging: false,
                width: card.scrollWidth,
                height: card.scrollHeight,
                windowWidth: card.scrollWidth,
                windowHeight: card.scrollHeight
            }).then(canvas => {
                // Create download link
                const link = document.createElement('a');
                link.download = 'Visitor_ID_' + document.getElementById('visitorQRCode').textContent + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error('Error generating image:', err);
                alert('Failed to generate image. Please try again or use Print.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // ============================================
        // IMAGE COMPRESSION
        // ============================================
        async function compressImageForMobile(file) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const MAX_WIDTH = 1024;
                        const MAX_HEIGHT = 1024;
                        
                        if (width > height) {
                            if (width > MAX_WIDTH) {
                                height *= MAX_WIDTH / width;
                                width = MAX_WIDTH;
                            }
                        } else {
                            if (height > MAX_HEIGHT) {
                                width *= MAX_HEIGHT / height;
                                height = MAX_HEIGHT;
                            }
                        }
                        
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        canvas.toBlob((blob) => {
                            const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                type: 'image/jpeg'
                            });
                            resolve(compressedFile);
                        }, 'image/jpeg', 0.7);
                    };
                };
            });
        }

        // ============================================
        // IMAGE PREVIEW + CAMERA / GALLERY HANDLING
        // ============================================
        const realInput    = document.querySelector('input[name="id_attachment"]');
        const cameraInput  = document.getElementById('cameraInput');
        const galleryInput = document.getElementById('galleryInput');
        
        // When user picks from camera OR gallery, copy the file into the real input
        async function handleSourceInput(sourceInput) {
            if (!sourceInput.files || !sourceInput.files[0]) return;
            
            let file      = sourceInput.files[0];
            const maxSize = 5 * 1024 * 1024;
            const fileType = (file.type || '').toLowerCase();
            const fileExt  = file.name.split('.').pop().toLowerCase();
            const isAllowed = ALLOWED_IMAGE_TYPES.includes(fileType) || ALLOWED_IMAGE_EXTENSIONS.includes(fileExt);
            
            if (!isAllowed) {
                alert('Invalid file type. Please upload JPG, JPEG, PNG, WebP, HEIC, or HEIF.');
                sourceInput.value = '';
                return;
            }
            
            // Compress BEFORE checking the size limit — camera photos are
            // frequently 5MB+, and shrinking them first is what lets a normal
            // phone photo pass the limit instead of being rejected outright.
            if (file.size > 2 * 1024 * 1024) {
                try {
                    file = await compressImageForMobile(file);
                } catch (err) {
                    console.error('Compression failed:', err);
                }
            }
            
            if (file.size > maxSize) {
                alert('File size exceeds 5MB limit. Your file: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                sourceInput.value = '';
                return;
            }
            
            // Transfer the file into the real submitted input
            const dt = new DataTransfer();
            dt.items.add(file);
            realInput.files = dt.files;
            
            showPreview(file);
            
            // Clear the source so re-selecting the same file still fires 'change'
            sourceInput.value = '';
        }
        
        function showPreview(file) {
            const preview   = document.getElementById('idPreview');
            const container = document.getElementById('previewContainer');
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        
        cameraInput.addEventListener('change',  () => handleSourceInput(cameraInput));
        galleryInput.addEventListener('change', () => handleSourceInput(galleryInput));
        
        function removeImage() {
            realInput.value    = '';
            cameraInput.value  = '';
            galleryInput.value = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('idPreview').src = '';
        }

        // ============================================
        // FORM SUBMISSION
        // ============================================
        document.getElementById('visitorForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return;
            }
            
            const fileInput = this.querySelector('input[name="id_attachment"]');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select an ID image to upload.');
                return;
            }
            
            let file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024;
            
            const fileType = (file.type || '').toLowerCase();
            const fileExt = file.name.split('.').pop().toLowerCase();
            const isAllowed = ALLOWED_IMAGE_TYPES.includes(fileType) || ALLOWED_IMAGE_EXTENSIONS.includes(fileExt);
            
            if (!isAllowed) {
                alert('Invalid file type. Please upload JPG, JPEG, PNG, WebP, HEIC, or HEIF images only.\nYour file: ' + fileType + ' (' + fileExt + ')');
                fileInput.value = '';
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                console.log('Compressing image... (Original: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB)');
                try {
                    file = await compressImageForMobile(file);
                    console.log('Compressed: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                } catch (err) {
                    console.error('Compression failed:', err);
                }
            } else if (file.size > maxSize) {
                alert('File size exceeds 5MB limit. Your file: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                fileInput.value = '';
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.loading-spinner');
            const icon = submitBtn.querySelector('i');
            
            spinner.style.display = 'inline-block';
            icon.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Submitting...';
            
            try {
                const formData = new FormData(this);
                formData.set('id_attachment', file);
                
                const apiUrl = API_BASE + '/visitor/register';
                console.log('📡 Sending to:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                console.log('📡 Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('❌ Failed to parse JSON:', e);
                    throw new Error('Server returned invalid response: ' + responseText.substring(0, 200));
                }
                
                console.log('✅ Parsed response:', result);
                
                if (result.success && result.data) {
                    visitorId = result.data.id;
                    console.log('✅ Visitor ID saved:', visitorId);
                    
                    document.getElementById('registrationForm').style.display = 'none';
                    document.getElementById('waitingMessage').style.display = 'block';
                    document.getElementById('referenceNumber').textContent = 'REF-' + String(visitorId).padStart(6, '0');
                    
                    startStatusCheck();
                    
                    if ('vibrate' in navigator) {
                        navigator.vibrate([100, 100, 100]);
                    }
                } else {
                    let errorMsg = 'Registration failed:\n';
                    if (result.errors) {
                        errorMsg += '• ' + result.errors.join('\n• ');
                    } else if (result.error) {
                        errorMsg += '• ' + result.error;
                    } else {
                        errorMsg += '• Unknown error occurred';
                    }
                    alert(errorMsg);
                }
            } catch (error) {
                console.error('❌ Error:', error);
                alert('An error occurred. Please try again.\n' + error.message);
            } finally {
                spinner.style.display = 'none';
                icon.style.display = 'inline-block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Registration';
            }
        });

        // ============================================
        // CHECK APPROVAL STATUS
        // ============================================
        async function checkApprovalStatus() {
            if (!visitorId || isApproved) {
                return;
            }
            
            console.log('🔍 Checking approval for ID:', visitorId);
            
            try {
                const url = API_BASE + '/visitor/' + visitorId;
                console.log('📡 Fetching:', url);
                
                const response = await fetch(url);
                console.log('📡 Response status:', response.status);
                
                if (!response.ok) {
                    console.error('❌ HTTP Error:', response.status);
                    return;
                }
                
                const visitor = await response.json();
                console.log('📊 Visitor data:', visitor);
                
                if (visitor && visitor.id_approved === 1 && visitor.status === 'inside' && visitor.qr_code) {
                    console.log('✅ APPROVED! Visitor ID:', visitor.qr_code);
                    isApproved = true;
                    clearInterval(checkInterval);
                    showApproved(visitor);
                    return;
                }
                
                const statusBadge = document.getElementById('statusBadge');
                if (visitor && visitor.status === 'pending_approval') {
                    statusBadge.innerHTML = '<i class="bi bi-clock"></i> Pending Approval';
                    document.getElementById('statusCheckResult').innerHTML = `
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-clock"></i> <strong>Still pending approval</strong>
                            <br><small>Last checked: ${new Date().toLocaleTimeString()}</small>
                            <br><small>Reference: REF-${String(visitorId).padStart(6, '0')}</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('❌ Error checking status:', error);
                document.getElementById('statusCheckResult').innerHTML = `
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                    </div>
                `;
            }
        }

        // ============================================
        // START STATUS CHECK
        // ============================================
        function startStatusCheck() {
            console.log('🔄 Starting status check every 3 seconds...');
            setTimeout(checkApprovalStatus, 1000);
            checkInterval = setInterval(checkApprovalStatus, 3000);
        }

        // ============================================
        // SHOW APPROVED - NO ALERT POPUP
        // ============================================
        function showApproved(visitor) {
            console.log('🎉 Showing approved message!');
            
            document.getElementById('waitingMessage').style.display = 'none';
            document.getElementById('approvedMessage').style.display = 'block';
            
            const qrCode = visitor.qr_code || 'VIS-' + String(visitor.id).padStart(6, '0');
            document.getElementById('visitorQRCode').textContent = qrCode;
            document.getElementById('visitorNameDisplay').textContent = visitor.visitor_name || 'Visitor';
            document.getElementById('departmentDisplay').textContent = visitor.department || 'N/A';
            document.getElementById('timeInDisplay').textContent = visitor.time_in ? new Date(visitor.time_in).toLocaleString() : 'Just now';
            
            if (checkInterval) {
                clearInterval(checkInterval);
                checkInterval = null;
            }
            
            // 🎊 Fire confetti!
            fireConfetti();
            
            if ('vibrate' in navigator) {
                navigator.vibrate([200, 100, 200, 100, 300]);
            }
        }

        function resetForm() {
            document.getElementById('visitorForm').reset();
            document.getElementById('visitorForm').classList.remove('was-validated');
            removeImage();
        }

        document.querySelector('input[name="visitor_name"]').focus();
    </script>
</body>
</html>