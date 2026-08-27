<?php $currentPage = 'mobile-facilities'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Facility Issue Reporting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-navy: #1a1a2e;
            --brand-navy-deep: #16213e;
            --accent: #0b76b8;
            --surface: #ffffff;
            --surface-app: #f2f5f9;
            --line: #e4e8ef;
            --ink: #16213e;
            --ink-muted: #6b7684;
            --danger: #c62828;
            --r-lg: 16px;
            --r-md: 12px;
            --tap: 48px;
        }
        body { background: var(--surface-app); color: var(--ink); font-family: system-ui, -apple-system, sans-serif; margin: 0; }
        .app-shell { max-width: 620px; margin: 0 auto; padding-bottom: 40px; }
        .mobile-header { background: linear-gradient(135deg, var(--brand-navy), var(--brand-navy-deep)); color: #fff; padding: 20px 16px; }
        .mobile-header h1 { font-size: 1.1rem; font-weight: 700; margin: 0; }
        .page-body { padding: 16px; }
        .card-custom { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); padding: 16px; margin-bottom: 16px; }
        .field-label { display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; color: var(--ink-muted); }
        .text-input { width: 100%; min-height: var(--tap); padding: 10px 14px; border: 1.5px solid var(--line); border-radius: var(--r-md); font-size: 16px; margin-bottom: 14px; }
        .btn-submit { width: 100%; min-height: 50px; background: var(--danger); color: #fff; border: none; border-radius: var(--r-md); font-weight: 700; font-size: 1rem; }
        .file-upload-box { border: 2px dashed var(--line); padding: 16px; border-radius: var(--r-md); text-align: center; margin-bottom: 16px; background: #fff; cursor: pointer; }
        .file-upload-box i { font-size: 1.8rem; color: var(--accent); }
        .img-preview { width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--r-md); margin-top: 10px; }
        .btn-remove-photo { margin-top: 8px; width: 100%; border: none; background: #fdecec; color: var(--danger); font-weight: 600; padding: 6px; border-radius: var(--r-md); }
    </style>
</head>
<body>

<div id="app" class="app-shell">
    <header class="mobile-header">
        <h1><i class="bi bi-tools"></i> Report Facility Issue</h1>
        <small class="text-white-50">On-site Monitoring Inspection</small>
    </header>

    <main class="page-body">
        <form @submit.prevent="submitReport" class="card-custom">
            <label class="field-label">Room Number *</label>
            <input type="text" v-model="form.room_number" class="text-input" placeholder="e.g., Room 101, Lab 2" required>

            <label class="field-label">Broken Equipment / Asset *</label>
            <input type="text" v-model="form.broken_equipment" class="text-input" placeholder="e.g., Electric Fan, Projector" required>

            <label class="field-label">Equipment Category *</label>
            <select v-model="form.equipment_type" class="text-input" required>
                <option value="" disabled>Select Type</option>
                <option value="chair">Chair</option>
                <option value="switch">Switch</option>
                <option value="light">Light</option>
                <option value="aircon">Aircon</option>
                <option value="projector">Projector</option>
                <option value="computer">Computer</option>
                <option value="other">Other</option>
            </select>

            <label class="field-label">Quantity Damaged</label>
            <input type="number" min="1" v-model.number="form.quantity_damaged" class="text-input">

            <label class="field-label">Attach Damage Photo</label>
            <div class="file-upload-box" @click="triggerFileInput">
                <i class="bi bi-camera-fill"></i>
                <div class="small fw-bold text-muted mt-1">{{ photoFile ? photoFile.name : 'Tap to take photo' }}</div>
                <input type="file" ref="fileInput" accept="image/*" capture="environment" class="d-none" @change="handleFileChange">
            </div>

            <div v-if="photoPreview">
                <img :src="photoPreview" class="img-preview" alt="Damage Preview">
                <button type="button" class="btn-remove-photo" @click="removePhoto">
                    <i class="bi bi-trash"></i> Remove Photo
                </button>
            </div>

            <label class="field-label mt-3">Issue Description</label>
            <textarea v-model="form.description" class="text-input" rows="3" placeholder="Describe the damage or dysfunction..."></textarea>

            <button type="submit" class="btn-submit" :disabled="isSubmitting">
                <span v-if="isSubmitting">Submitting...</span>
                <span v-else><i class="bi bi-send-fill"></i> Submit Report</span>
            </button>
        </form>
    </main>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script>
const REPORTED_BY = <?php echo json_encode($_SESSION['fullname'] ?? 'Monitoring Staff'); ?>;

Vue.createApp({
    setup() {
        const form = Vue.reactive({
            room_number: '',
            broken_equipment: '',
            equipment_type: '',
            quantity_damaged: 1,
            description: '',
            reported_by: REPORTED_BY
        });
        
        const photoFile = Vue.ref(null);
        const photoPreview = Vue.ref(null);
        const fileInput = Vue.ref(null);
        const isSubmitting = Vue.ref(false);

        function triggerFileInput() {
            if (fileInput.value) fileInput.value.click();
        }

        function handleFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                photoFile.value = file;
                const reader = new FileReader();
                reader.onload = (evt) => { photoPreview.value = evt.target.result; };
                reader.readAsDataURL(file);
            }
        }

        function removePhoto() {
            photoFile.value = null;
            photoPreview.value = null;
            if (fileInput.value) fileInput.value.value = '';
        }

        async function submitReport() {
            isSubmitting.value = true;
            try {
                const formData = new FormData();
                formData.append('room_number', form.room_number);
                formData.append('broken_equipment', form.broken_equipment);
                formData.append('equipment_type', form.equipment_type);
                formData.append('quantity_damaged', form.quantity_damaged);
                formData.append('description', form.description);
                formData.append('reported_by', form.reported_by);
                
                if (photoFile.value) {
                    formData.append('damage_image', photoFile.value);
                }

                const response = await fetch('/api/facility/report', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.id || result.success) {
                    alert('Facility report submitted successfully!');
                    form.room_number = '';
                    form.broken_equipment = '';
                    form.equipment_type = '';
                    form.quantity_damaged = 1;
                    form.description = '';
                    removePhoto();
                } else {
                    alert(result.error || 'Failed to submit report');
                }
            } catch (e) {
                alert('Connection error while submitting report.');
            } finally {
                isSubmitting.value = false;
            }
        }

        return { 
            form, 
            photoFile, 
            photoPreview, 
            fileInput, 
            triggerFileInput, 
            handleFileChange, 
            removePhoto, 
            isSubmitting, 
            submitReport 
        };
    }
}).mount('#app');
</script>
</body>
</html>