function initFacultyManagementPage() {
    const tabButtons = document.querySelectorAll('[data-fm-tab]');
    const panels = document.querySelectorAll('.fm-panel');

    function bindAddEngagementEmployeeTypeListener() {
        if (document.body.dataset.addEngagementSyncBound === 'true') {
            return;
        }

        const syncEngagementType = (employeeSelect) => {
            const aeTypeInput = document.getElementById('aeType');
            if (!employeeSelect || !aeTypeInput) {
                return;
            }

            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const employmentType = (selectedOption && selectedOption.value)
                ? (selectedOption.dataset.employmentType || '')
                : '';

            aeTypeInput.value = employmentType;
            aeTypeInput.setAttribute('value', employmentType);
            aeTypeInput.readOnly = true;
            aeTypeInput.disabled = true;
        };

        document.addEventListener('change', function (event) {
            const target = event.target;
            if (!target || target.id !== 'aeEmployee') {
                return;
            }

            syncEngagementType(target);
        });

        const initialEmployeeSelect = document.getElementById('aeEmployee');
        const initialTypeInput = document.getElementById('aeType');
        if (initialEmployeeSelect && initialTypeInput) {
            syncEngagementType(initialEmployeeSelect);
        }

        document.body.dataset.addEngagementSyncBound = 'true';
    }

    bindAddEngagementEmployeeTypeListener();

    function activateTab(tabName) {
        tabButtons.forEach(button => {
            button.classList.toggle('active', button.dataset.fmTab === tabName);
        });
        panels.forEach(panel => {
            panel.classList.toggle('active', panel.id === `fm-panel-${tabName}`);
        });
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            activateTab(this.dataset.fmTab);
        });
    });

    function initializeSearch(inputId, tableId, rowFilter) {
        const searchInput = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!searchInput || !table) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const originalRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('no-data'));

        function performSearch() {
            const term = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            tbody.innerHTML = '';
            originalRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                const matches = (!term || rowText.includes(term)) && (!rowFilter || rowFilter(row));
                if (matches) {
                    tbody.appendChild(row);
                    visibleCount += 1;
                }
            });

            if (visibleCount === 0) {
                const noDataRow = document.createElement('tr');
                noDataRow.className = 'no-data';
                const colCount = table.querySelectorAll('thead th').length || 1;
                noDataRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted" style="padding: 20px;">No records match the search criteria.</td>`;
                tbody.appendChild(noDataRow);
            }
        }

        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('change', performSearch);
    }

    initializeSearch('searchCredentials', 'credentialsTable');
    initializeSearch('searchAttainment', 'attainmentTable');
    let trainingFilter = 'all';
    initializeSearch('searchTrainings', 'trainingsTable', row => {
        const status = (row.dataset.recordStatus || '').toLowerCase();
        if (trainingFilter === 'archived') return status === 'archived';
        if (trainingFilter === 'completed') return status === 'completed';
        if (trainingFilter === 'active') return ['pending', 'ongoing', 'for review', 'approved'].includes(status);
        // Archived engagements are shown only when the Archived filter is selected.
        return status !== 'archived';
    });

    const trainingSearchInput = document.getElementById('searchTrainings');
    const trainingTable = document.getElementById('trainingsTable');
    const trainingFilterButtons = document.querySelectorAll('[data-training-filter]');
    if (trainingSearchInput && trainingTable && trainingFilterButtons.length) {
        trainingFilterButtons.forEach(button => {
            button.addEventListener('click', function () {
                trainingFilter = this.dataset.trainingFilter || 'all';
                trainingFilterButtons.forEach(filterButton => filterButton.classList.toggle('active', filterButton === this));
                trainingSearchInput.dispatchEvent(new Event('input'));
            });
        });

        // Apply the default All filter immediately. Archived engagements must
        // never appear in All, Active, or Completed—only in Archived.
        trainingSearchInput.dispatchEvent(new Event('input'));
    }

    function openProfileModal(data) {
        const modal = document.getElementById('fmProfileModal');
        if (!modal) return;

        document.getElementById('fmModalEmployeeId').textContent = data.employeeId || 'N/A';
        document.getElementById('fmModalFacultyName').textContent = data.facultyName || 'N/A';
        document.getElementById('fmModalDepartment').textContent = data.department || 'Not provided';
        document.getElementById('fmModalEmail').textContent = data.email || 'Not provided';
        document.getElementById('fmModalCreatedAt').textContent = data.createdAt || 'Not provided';
        document.getElementById('fmModalPosition').textContent = data.position || 'Not provided';
        document.getElementById('fmModalEmploymentType').textContent = data.employmentType || 'Not provided';
        document.getElementById('fmModalStatus').textContent = data.employmentStatus || 'Not provided';

        modal.classList.add('show');
    }

    function closeProfileModal() {
        const modal = document.getElementById('fmProfileModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function openEducationModal(employeeCode, facultyName, department, records) {
        const modal = document.getElementById('fmEducationModal');
        if (!modal) return;

        const employeeIdElement = document.getElementById('fmEducationEmployeeId');
        const facultyNameElement = document.getElementById('fmEducationFacultyName');
        const departmentElement = document.getElementById('fmEducationDepartment');
        const historyBody = document.getElementById('fmEducationHistoryBody');

        employeeIdElement.textContent = employeeCode || 'N/A';
        facultyNameElement.textContent = facultyName || 'N/A';
        departmentElement.textContent = department || 'Not provided';

        if (!records || records.length === 0) {
            historyBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding: 18px;">No educational attainment records found.</td></tr>';
            modal.classList.add('show');
            return;
        }

        historyBody.innerHTML = records.map(record => {
            const degree = record.level || 'N/A';
            const school = record.school_name || 'N/A';
            const year = record.year_graduated || 'N/A';
            const course = record.course || 'N/A';

            return `
                <tr>
                    <td>${course || degree}</td>
                    <td>${school}</td>
                    <td>${year}</td>
                    <td>${degree}</td>
                </tr>
            `;
        }).join('');

        modal.classList.add('show');
    }

    function closeEducationModal() {
        const modal = document.getElementById('fmEducationModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function openTrainingModal(employeeCode, facultyName, employmentType, employmentStatus, records) {
        const modal = document.getElementById('fmTrainingModal');
        if (!modal) return;

        const employeeIdElement = document.getElementById('fmTrainingEmployeeId');
        const facultyNameElement = document.getElementById('fmTrainingFacultyName');
        const employmentTypeElement = document.getElementById('fmTrainingEmploymentType');
        const employmentStatusElement = document.getElementById('fmTrainingEmploymentStatus');
        const historyBody = document.getElementById('fmTrainingHistoryBody');

        employeeIdElement.textContent = employeeCode || 'N/A';
        facultyNameElement.textContent = facultyName || 'N/A';
        employmentTypeElement.textContent = employmentType || 'N/A';
        employmentStatusElement.textContent = employmentStatus || 'N/A';

        if (!records || records.length === 0) {
            historyBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 18px;">No training or certification records found.</td></tr>';
            modal.classList.add('show');
            return;
        }

        historyBody.innerHTML = records.map(record => {
            const type = record.type || record.engagement_type || 'Training / Certification';
            const title = record.title || record.cert_name || 'N/A';
            const organization = record.organization || record.issuing_organization || 'N/A';
            const datePeriod = record.date_period || record.date_issued || record.start_date || 'N/A';
            const status = record.status || 'Completed';

            return `
                <tr>
                    <td>${type}</td>
                    <td>${title}</td>
                    <td>${organization}</td>
                    <td>${datePeriod}</td>
                    <td>${status}</td>
                </tr>
            `;
        }).join('');

        modal.classList.add('show');
    }

    function closeTrainingModal() {
        const modal = document.getElementById('fmTrainingModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    function formatBreakDuration(value) {
        if (!value || value === '--') return 'N/A';
        const numericValue = Number(value);
        if (!Number.isNaN(numericValue) && numericValue > 0) {
            return `${numericValue} minutes`;
        }
        return String(value);
    }

    function formatTimeDisplay(value) {
        if (!value || value === '--') return '--';

        const str = String(value).trim();
        if (!/^\d{1,2}:\d{2}(:\d{2})?$/.test(str)) {
            return str;
        }

        const [hoursPart, minutesPart] = str.split(':');
        const hours = Number(hoursPart);
        const minutes = Number(minutesPart || 0);
        const period = hours >= 12 ? 'PM' : 'AM';
        const normalizedHours = hours % 12 === 0 ? 12 : hours % 12;
        return `${normalizedHours}:${String(minutes).padStart(2, '0')} ${period}`;
    }

    function openShiftScheduleModal(scheduleData) {
        const modal = document.getElementById('fmShiftScheduleModal');
        const content = document.getElementById('fmShiftScheduleContent');
        const emptyState = document.getElementById('fmShiftScheduleEmpty');
        if (!modal) return;

        if (!scheduleData || !scheduleData.shift_name || scheduleData.shift_name === 'N/A') {
            if (content) content.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
            modal.classList.add('show');
            return;
        }

        if (emptyState) emptyState.style.display = 'none';
        if (content) content.style.display = 'block';

        document.getElementById('fmShiftFacultyName').textContent = scheduleData.faculty_name || 'N/A';
        document.getElementById('fmShiftEmployeeId').textContent = scheduleData.employee_id || 'N/A';
        document.getElementById('fmShiftDepartment').textContent = scheduleData.department || 'Not provided';
        document.getElementById('fmShiftName').textContent = scheduleData.shift_name || 'N/A';
        document.getElementById('fmShiftStartTime').textContent = formatTimeDisplay(scheduleData.shift_start_time || 'N/A');
        document.getElementById('fmShiftEndTime').textContent = formatTimeDisplay(scheduleData.shift_end_time || 'N/A');
        document.getElementById('fmShiftBreakDuration').textContent = formatBreakDuration(scheduleData.break_duration || 'N/A');
        document.getElementById('fmShiftEffectiveFrom').textContent = scheduleData.effective_from || 'N/A';
        document.getElementById('fmShiftEffectiveTo').textContent = scheduleData.effective_to || 'Ongoing';
        document.getElementById('fmShiftStatus').textContent = scheduleData.status || 'Active';

        const weeklyBody = document.getElementById('fmShiftWeeklyBody');
        if (weeklyBody) {
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const weeklySchedule = scheduleData.weekly_schedule || {};
            const hasWeeklyRows = Object.keys(weeklySchedule).length > 0;

            if (!hasWeeklyRows) {
                weeklyBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding: 18px;">No weekly day-specific schedule configured.</td></tr>';
            } else {
                weeklyBody.innerHTML = days.map(day => {
                    const row = weeklySchedule[day] || { start_time: '--', break_start: '--', break_end: '--', end_time: '--' };
                    return `
                        <tr>
                            <td>${day}</td>
                            <td>${formatTimeDisplay(row.start_time)}</td>
                            <td>${formatTimeDisplay(row.break_start)}</td>
                            <td>${formatTimeDisplay(row.break_end)}</td>
                            <td>${formatTimeDisplay(row.end_time)}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        modal.classList.add('show');
    }

    function closeShiftScheduleModal() {
        const modal = document.getElementById('fmShiftScheduleModal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    document.querySelectorAll('.view-profile-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            if (!row) return;

            openProfileModal({
                employeeId: row.dataset.employeeCode || row.cells[0]?.textContent || '',
                facultyName: row.dataset.facultyName || row.cells[1]?.textContent || '',
                department: row.dataset.department || row.cells[2]?.textContent || '',
                email: row.dataset.email || '',
                createdAt: row.dataset.createdAt || '',
                position: row.dataset.position || row.cells[3]?.textContent || '',
                employmentType: row.dataset.employmentType || row.cells[4]?.textContent || '',
                employmentStatus: row.dataset.employmentStatus || row.cells[5]?.textContent || ''
            });
        });
    });

    document.querySelectorAll('.view-attainment-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            if (!row) return;

            let records = [];
            try {
                records = JSON.parse(row.dataset.records || '[]');
            } catch (error) {
                records = [];
            }

            openEducationModal(
                row.dataset.employeeCode || row.cells[0]?.textContent || 'N/A',
                row.dataset.facultyName || row.cells[1]?.textContent || 'N/A',
                row.dataset.department || 'Not provided',
                records
            );
        });
    });

    document.querySelectorAll('.view-training-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            if (!row) return;

            let records = [];
            try {
                records = JSON.parse(row.dataset.records || '[]');
            } catch (error) {
                records = [];
            }

            openTrainingModal(
                row.dataset.employeeCode || row.cells[0]?.textContent || 'N/A',
                row.dataset.facultyName || row.cells[1]?.textContent || 'N/A',
                row.dataset.employmentType || 'N/A',
                row.dataset.employmentStatus || 'N/A',
                records
            );
        });
    });

    document.querySelectorAll('#fmProfileModal .modal-close').forEach(button => {
        button.addEventListener('click', closeProfileModal);
    });

    document.querySelectorAll('#fmEducationModal .modal-close').forEach(button => {
        button.addEventListener('click', closeEducationModal);
    });

    document.querySelectorAll('#fmTrainingModal .modal-close').forEach(button => {
        button.addEventListener('click', closeTrainingModal);
    });

    document.querySelectorAll('#fmShiftScheduleModal .modal-close').forEach(button => {
        button.addEventListener('click', closeShiftScheduleModal);
    });

    // Add Engagement modal handlers
    const addEngagementBtn = document.getElementById('addEngagementBtn');
    const addEngagementModal = document.getElementById('fmAddEngagementModal');
    const addEngagementForm = document.getElementById('addEngagementForm');

    function openAddEngagementModal() {
        if (!addEngagementModal) return;
        addEngagementModal.classList.add('show');
    }

    function closeAddEngagementModal() {
        if (!addEngagementModal) return;
        addEngagementModal.classList.remove('show');
    }

    if (addEngagementBtn) {
        addEngagementBtn.addEventListener('click', openAddEngagementModal);
    }

    document.querySelectorAll('#fmAddEngagementModal .modal-close').forEach(button => {
        button.addEventListener('click', closeAddEngagementModal);
    });

    if (addEngagementModal) {
        addEngagementModal.addEventListener('click', function (event) {
            if (event.target === addEngagementModal) {
                closeAddEngagementModal();
            }
        });
    }

    if (addEngagementForm) {
        addEngagementForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(addEngagementForm);
            const employeeSelect = document.getElementById('aeEmployee');
            const selectedOption = employeeSelect && employeeSelect.selectedIndex >= 0
                ? employeeSelect.options[employeeSelect.selectedIndex]
                : null;
            const employmentType = (selectedOption && selectedOption.dataset.employmentType)
                ? selectedOption.dataset.employmentType
                : '';

            const payload = {
                action: 'add_engagement',
                employee_id: Number(formData.get('employee_id')) || 0,
                engagement_type: employmentType,
                title: (formData.get('title') || '').trim(),
                organization: (formData.get('organization') || '').trim(),
                start_date: formData.get('start_date') || null,
                end_date: formData.get('end_date') || null
            };

            if (!payload.employee_id || !payload.engagement_type || !payload.title || !payload.organization || !payload.start_date || !payload.end_date) {
                alert('Please fill required fields.');
                return;
            }

            if (!confirm('Create this engagement record with status Pending?')) return;

            fetch('/sms/modules/college-coor/pages/faculty-management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    alert(data.message || 'Engagement created');
                    window.location.reload();
                    return;
                }
                alert((data && data.message) || 'Unable to create engagement');
            })
            .catch((err) => {
                console.error('Engagement fetch error:', err);
                alert('Unable to create engagement.');
            });
        });
    }

    const profileModal = document.getElementById('fmProfileModal');
    if (profileModal) {
        profileModal.addEventListener('click', function (event) {
            if (event.target === profileModal) {
                closeProfileModal();
            }
        });
    }

    const educationModal = document.getElementById('fmEducationModal');
    if (educationModal) {
        educationModal.addEventListener('click', function (event) {
            if (event.target === educationModal) {
                closeEducationModal();
            }
        });
    }

    const trainingModal = document.getElementById('fmTrainingModal');
    if (trainingModal) {
        trainingModal.addEventListener('click', function (event) {
            if (event.target === trainingModal) {
                closeTrainingModal();
            }
        });
    }

    function performEngagementWorkflow(action, engagementId, outcome) {
        if (!engagementId) {
            alert('Invalid engagement record.');
            return;
        }

        const payload = {
            action: action,
            engagement_id: Number(engagementId)
        };

        if (action === 'approve_engagement') {
            payload.approved_by = 0;
        }

        if (action === 'mark_completed_engagement') {
            payload.outcome = outcome || 'Not Applicable';
        }

        fetch('/sms/modules/college-coor/pages/faculty-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                alert(data.message || 'Action completed successfully.');
                window.location.reload();
                return;
            }

            alert((data && data.message) || 'The action could not be completed.');
        })
        .catch(() => {
            alert('The action could not be completed. Please try again.');
        });
    }

    document.querySelectorAll('.approve-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const confirmed = window.confirm('Approve this engagement record?');
            if (!confirmed) return;

            performEngagementWorkflow('approve_engagement', engagementId);
        });
    });

    document.querySelectorAll('.complete-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const allowedOutcomes = ['Continue', 'Regularize', 'End Engagement', 'Not Applicable'];
            const outcomeInput = window.prompt('Enter the engagement outcome: Continue, Regularize, End Engagement, or Not Applicable', 'Continue');
            if (outcomeInput === null) return;

            const outcome = outcomeInput.trim();
            if (!allowedOutcomes.includes(outcome)) {
                alert('Invalid outcome. Please use one of: Continue, Regularize, End Engagement, Not Applicable.');
                return;
            }

            const confirmed = window.confirm('Mark this engagement as completed?');
            if (!confirmed) return;

            performEngagementWorkflow('mark_completed_engagement', engagementId, outcome);
        });
    });

    document.querySelectorAll('.generate-certificate-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            // Open certificate preview in a new tab/window. The preview page
            // allows printing and provides a button to mark the certificate
            // as generated (which will call the existing server action).
            const origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
            const url = origin + '/sms/modules/college-coor/pages/engagement_certificate.php?engagement_id=' + encodeURIComponent(engagementId);
            window.open(url, '_blank');
        });
    });

    document.querySelectorAll('.archive-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const confirmed = window.confirm('Archive this engagement record? This will not delete the record.');
            if (!confirmed) return;

            performEngagementWorkflow('archive_engagement', engagementId);
        });
    });

    document.querySelectorAll('.restore-engagement-btn').forEach(button => {
        button.addEventListener('click', function () {
            const engagementId = this.dataset.engagementId;
            if (!engagementId) return;

            const confirmed = window.confirm('Restore this archived engagement record?');
            if (!confirmed) return;

            performEngagementWorkflow('restore_engagement', engagementId);
        });
    });

    document.querySelectorAll('.shift-schedule-btn').forEach(button => {
        button.addEventListener('click', function () {
            const employeeId = Number(this.dataset.employeeId || 0);
            const row = this.closest('tr');
            const facultyName = row && row.dataset.facultyName ? row.dataset.facultyName : 'N/A';
            const department = row && row.dataset.department ? row.dataset.department : 'Not provided';

            if (!employeeId) {
                alert('Invalid faculty selected.');
                return;
            }

            fetch('/sms/modules/college-coor/pages/faculty-management.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'get_faculty_shift_schedule',
                    employee_id: employeeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success && data.schedule) {
                    openShiftScheduleModal({
                        faculty_name: data.schedule.faculty_name || facultyName,
                        employee_id: data.schedule.employee_id || employeeId,
                        department: data.schedule.department || department,
                        shift_name: data.schedule.shift_name || 'N/A',
                        shift_start_time: data.schedule.shift_start_time || 'N/A',
                        shift_end_time: data.schedule.shift_end_time || 'N/A',
                        break_duration: data.schedule.break_duration || 'N/A',
                        effective_from: data.schedule.effective_from || 'N/A',
                        effective_to: data.schedule.effective_to || 'Ongoing',
                        status: data.schedule.status || 'Active',
                        weekly_schedule: data.schedule.weekly_schedule || {}
                    });
                    return;
                }

                openShiftScheduleModal({
                    faculty_name: facultyName,
                    employee_id: employeeId,
                    department: department,
                    shift_name: 'N/A'
                });
            })
            .catch(() => {
                openShiftScheduleModal({
                    faculty_name: facultyName,
                    employee_id: employeeId,
                    department: department,
                    shift_name: 'N/A'
                });
            });
        });
    });
}

function initFacultyManagementWhenReady() {
    if (document.querySelector('[data-fm-tab]')) {
        initFacultyManagementPage();
    }
}

document.addEventListener('DOMContentLoaded', initFacultyManagementWhenReady);
window.addEventListener('page:loaded', function (event) {
    if (event.detail && event.detail.page === 'faculty-management') {
        setTimeout(initFacultyManagementWhenReady, 100);
    }
});
