function exposeGlobal(name, value) {
    try {
        window[name] = value;
    } catch (e) {
        console.error(`Failed to expose "${name}" to window:`, e);
    }
}

function exportToCSV() {
    try {
        const table = document.getElementById('scheduleTable') || document.querySelector('table');
        if (!table) {
            alert('No schedule data available to export.');
            return false;
        }

        const rows = Array.from(table.querySelectorAll('tr'));
        const csv = rows.map(row => Array.from(row.querySelectorAll('th, td')).map(cell => `"${(cell.textContent || '').replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'schedule.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        return true;
    } catch (error) {
        console.error('exportToCSV error:', error);
        alert('Export failed: ' + error.message);
        return false;
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
}

function validateForm() {
    const form = document.querySelector('form.form-grid');
    if (!form) return true;
    const required = form.querySelectorAll('[required]');
    for (const field of required) {
        if (!field.value || (field.tagName === 'SELECT' && !field.value)) {
            field.focus();
            alert('Please complete all required fields before submitting.');
            return false;
        }
    }
    return true;
}

function validateEditForm() {
    const form = document.getElementById('editForm');
    if (!form) return true;
    const required = form.querySelectorAll('[required]');
    for (const field of required) {
        if (!field.value || (field.tagName === 'SELECT' && !field.value)) {
            field.focus();
            alert('Please complete all required fields before updating the schedule.');
            return false;
        }
    }
    return true;
}

function editSchedule(row) {
    try {
        const modal = document.getElementById('editModal');
        if (!modal) return;

        const scheduleId = row?.schedule_id ?? row?.id ?? '';
        const roomId = row?.room_id ?? '';
        const scheduleType = row?.schedule_type ?? 'Class';
        const startTime = row?.start_time ?? '';
        const endTime = row?.end_time ?? '';
        const dayOfWeek = row?.day_of_week ?? '';
        const subjectId = row?.subject_id ?? '';
        const gradeSectionId = row?.grade_section_id ?? row?.section_id ?? '';
        const facultyLoadId = row?.faculty_load_id ?? '';
        const facultyId = row?.faculty_id ?? '';
        const semesterId = row?.semester_id ?? '';
        const schoolYearId = row?.school_year_id ?? '';

        document.getElementById('edit_schedule_id').value = scheduleId;
        document.getElementById('edit_room').value = roomId;
        document.getElementById('edit_schedule_type').value = scheduleType;
        document.getElementById('edit_start_time').value = startTime ? startTime.slice(0, 5) : '';
        document.getElementById('edit_end_time').value = endTime ? endTime.slice(0, 5) : '';
        document.getElementById('edit_day_of_week').value = dayOfWeek;
        document.getElementById('edit_subject_id').value = subjectId;
        document.getElementById('edit_grade_section_id').value = gradeSectionId;
        document.getElementById('edit_faculty_load_id').value = facultyLoadId;
        document.getElementById('edit_faculty_id').value = facultyId;
        document.getElementById('edit_semester_id').value = semesterId;
        document.getElementById('edit_school_year_id').value = schoolYearId;

        if (typeof updateScheduleTypeFields === 'function') {
            updateScheduleTypeFields();
        }

        modal.style.display = 'block';
        modal.classList.add('show');
    } catch (error) {
        console.error('editSchedule error:', error);
    }
}

// Tab functionality
function showTab(tabName, clickedElement = null) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });

    const target = document.getElementById(tabName + '-tab');
    if (target) target.classList.add('active');

    const classScheduleFilters = document.getElementById('classScheduleFilters');
    if (classScheduleFilters) {
        classScheduleFilters.style.display = tabName === 'proctoring' ? 'none' : '';
    }

    if (clickedElement) {
        clickedElement.classList.add('active');
    }

    const url = new URL(window.location);
    url.searchParams.set('action', tabName);
    window.history.pushState({}, '', url);
}

document.addEventListener('click', function (event) {
    const tabButton = event.target.closest('.tab[data-tab]');
    if (!tabButton) return;

    const tabName = tabButton.getAttribute('data-tab');
    if (!tabName) return;

    event.preventDefault();
    showTab(tabName, tabButton);
});

// Set active tab based on URL
const urlParams = new URLSearchParams(window.location.search);
const actionParam = urlParams.get('action');
const classScheduleFilters = document.getElementById('classScheduleFilters');
if (classScheduleFilters && actionParam === 'proctoring') {
    classScheduleFilters.style.display = 'none';
}
if (actionParam && ['view', 'add', 'proctoring'].includes(actionParam)) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    const matchingTab = document.querySelector('.tab[data-tab="' + actionParam + '"]');
    const matchingContent = document.getElementById(actionParam + '-tab');
    if (matchingContent) matchingContent.classList.add('active');
    if (matchingTab) matchingTab.classList.add('active');
}

const proctorApi = '/sms/modules/college-coor/api/exam_proctoring.php';
let proctorExamSchedules = [];
let allProctorExamSchedules = [];
let proctorFaculty = [];
let proctorExams = [];
let proctorAllSections = [];
let proctorAllSubjects = [];
let proctorCurriculumSubjects = [];

function proctorEscape(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
}

function formatProctorTime(value) {
    const parts = String(value || '').split(':');
    if (parts.length < 2) return '';
    const hour24 = Number(parts[0]);
    const minute = parts[1];
    return `${hour24 % 12 || 12}:${minute} ${hour24 >= 12 ? 'PM' : 'AM'}`;
}

function formatProctorDate(value) {
    if (!value) return '';
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function selectedProctorPeriodParams() {
    return new URLSearchParams({
        semester_id: document.getElementById('proctorSemesterFilter')?.value || '',
        school_year_id: document.getElementById('proctorSchoolYearFilter')?.value || ''
    });
}

async function loadProctorReferenceData() {
    const params = selectedProctorPeriodParams();
    const [scheduleResponse, facultyResponse, examResponse, subjectResponse, sectionResponse, curriculumSubjectResponse, roomResponse] = await Promise.all([
        fetch(`${proctorApi}?action=exam-schedules&${params}`),
        fetch(`${proctorApi}?action=faculties`),
        fetch(`${proctorApi}?action=exams`),
        fetch(`${proctorApi}?action=subjects`),
        fetch(`${proctorApi}?action=sections`),
        fetch(`${proctorApi}?action=curriculum-subjects`),
        fetch(`${proctorApi}?action=rooms`)
    ]);
    const scheduleData = await scheduleResponse.json();
    const facultyData = await facultyResponse.json();
    const examData = await examResponse.json();
    const subjectData = await subjectResponse.json();
    const sectionData = await sectionResponse.json();
    const curriculumSubjectData = await curriculumSubjectResponse.json();
    const roomData = await roomResponse.json();
    if (!scheduleData.success || !facultyData.success || !examData.success || !subjectData.success || !sectionData.success || !curriculumSubjectData.success || !roomData.success) throw new Error('Unable to load proctoring reference data.');
    allProctorExamSchedules = scheduleData.exam_schedules || [];
    proctorExamSchedules = allProctorExamSchedules.filter(item => item.schedule_type === 'Exam');
    proctorFaculty = facultyData.faculties || [];
    proctorExams = examData.exams || [];
    const examFilter = document.getElementById('proctorExamFilter');
    const currentExam = examFilter.value;
    const exams = [...new Map(proctorExamSchedules.map(item => [item.exam_id, item.exam_name])).entries()];
    examFilter.innerHTML = '<option value="">All Exams</option>' + exams.map(([id, name]) => `<option value="${proctorEscape(id)}">${proctorEscape(name)}</option>`).join('');
    if (exams.some(([id]) => String(id) === currentExam)) examFilter.value = currentExam;
    const scheduleSelect = document.getElementById('proctorScheduleId');
    if (scheduleSelect) {
        // Group exam schedules by exam_id + exam_date + room_id + section_id
        const groups = {};
        proctorExamSchedules.forEach(item => {
            const key = [item.exam_id, item.exam_date, item.room_id, item.section_id].join('::');
            if (!groups[key]) groups[key] = { items: [], exam_name: item.exam_name, exam_id: item.exam_id, exam_date: item.exam_date, room_name: item.room_name, section_code: item.section_code };
            groups[key].items.push(item);
        });
        const groupOptions = Object.values(groups).map(group => {
            const count = group.items.length;
            const sampleId = group.items[0].id;
            const label = `${proctorEscape(group.exam_name)} | ${proctorEscape(group.section_code || '')} | ${proctorEscape(formatProctorDate(group.exam_date))} (${count} Exam${count>1?'s':''})`;
            return { id: sampleId, label };
        });
        scheduleSelect.innerHTML = '<option value="">Select Exam Schedule</option>' + groupOptions.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
    }
    const scheduleBody = document.getElementById('examSchedulesBody');
    if (scheduleBody) {
        // Group schedules visually by exam_id + exam_date + room_id + section_id
        const groups = {};
        allProctorExamSchedules.forEach(item => {
            const key = [item.exam_id, item.exam_date, item.room_id, item.section_id].join('::');
            if (!groups[key]) groups[key] = { items: [], exam_name: item.exam_name, exam_id: item.exam_id, exam_date: item.exam_date, room_name: item.room_name, section_code: item.section_code };
            groups[key].items.push(item);
        });
        // Create ordered list of groups using desired sort order
        const orderedGroups = Object.values(groups).sort((a, b) => {
            if (a.exam_date !== b.exam_date) return a.exam_date.localeCompare(b.exam_date);
            if (a.exam_name !== b.exam_name) return a.exam_name.localeCompare(b.exam_name);
            if ((a.room_name || '') !== (b.room_name || '')) return (a.room_name || '').localeCompare(b.room_name || '');
            return (a.section_code || '').localeCompare(b.section_code || '');
        });
        const rows = orderedGroups.map(group => {
            // sort schedules inside group by start_time
            group.items.sort((x, y) => (x.start_time || '').localeCompare(y.start_time || ''));
            const header = `<tr class="group-header"><td colspan="8" style="padding:8px 10px;background:#f4f6f8;font-weight:700;">${proctorEscape(formatProctorDate(group.exam_date))} &nbsp;|&nbsp; ${proctorEscape(group.exam_name)} &nbsp;|&nbsp; ${proctorEscape(group.room_name || '')} &nbsp;|&nbsp; ${proctorEscape(group.section_code || '')}</td></tr>`;
            const itemRows = group.items.map(item => {
                const isBreak = item.schedule_type === 'Break Time';
                return `<tr><td><strong>${proctorEscape(item.schedule_type)}</strong></td><td>${proctorEscape(item.exam_name)}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.subject_code || '')}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.section_code || '')}</td><td>${isBreak ? 'Break Time' : proctorEscape(item.room_name || '')}</td><td>${proctorEscape(formatProctorDate(item.exam_date))}</td><td>${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}</td><td>${proctorEscape(item.status || '')}</td></tr>`;
            }).join('');
            return header + itemRows;
        }).join('');
        scheduleBody.innerHTML = rows || '<tr><td colspan="8" style="text-align:center;">No exam schedules found.</td></tr>';
    }
    const examSelect = document.getElementById('scheduleExamId');
    if (examSelect) {
        examSelect.innerHTML = '<option value="">Select Examination</option>' + proctorExams.map(item => `<option value="${item.id}">${proctorEscape(item.exam_name)} (${proctorEscape(item.school_year_name || '')} / ${proctorEscape(item.semester_name || '')})</option>`).join('');
    }
    const fillSelect = (id, placeholder, items, label) => {
        const select = document.getElementById(id);
        if (select) select.innerHTML = `<option value="">${placeholder}</option>` + items.map(item => `<option value="${item.id}">${proctorEscape(label(item))}</option>`).join('');
    };
    proctorAllSubjects = subjectData.subjects || [];
    proctorCurriculumSubjects = curriculumSubjectData.curriculum_subjects || [];
    updateScheduleSubjectOptions();
    proctorAllSections = sectionData.sections || [];
    updateScheduleSectionOptions();
    fillSelect('scheduleRoomId', 'Select Room', roomData.rooms || [], item => item.room_name);
}

function updateScheduleSectionOptions() {
    const courseId = document.getElementById('scheduleCourseId')?.value || '';
    const yearLevel = document.getElementById('scheduleYearLevel')?.value || '';
    const filtered = proctorAllSections.filter(sec => {
        const matchesCourse = !courseId || String(sec.program_id) === String(courseId);
        const matchesYear = !yearLevel || sec.grade_level === yearLevel;
        return matchesCourse && matchesYear;
    });
    const select = document.getElementById('scheduleSectionId');
    if (!select) return;
    const currentValue = select.value;
    select.innerHTML = '<option value="">Select Section</option>' + filtered.map(item => `<option value="${item.id}">${proctorEscape(item.section_code)}</option>`).join('');
    if (filtered.some(item => String(item.id) === currentValue)) {
        select.value = currentValue;
    }
}

function yearLevelTextToNumber(text) {
    const map = { '1st Year': 1, '2nd Year': 2, '3rd Year': 3, '4th Year': 4 };
    return map[text] || null;
}

function updateScheduleSubjectOptions() {
    const courseId = document.getElementById('scheduleCourseId')?.value || '';
    const yearLevel = document.getElementById('scheduleYearLevel')?.value || '';
    const yearLevelNum = yearLevelTextToNumber(yearLevel);

    let allowedSubjectIds = null;
    if (courseId || yearLevelNum) {
        allowedSubjectIds = new Set(
            proctorCurriculumSubjects
                .filter(cs => (!courseId || String(cs.course_id) === String(courseId)) && (!yearLevelNum || Number(cs.year_level) === yearLevelNum))
                .map(cs => String(cs.subject_id))
        );
    }

    const filtered = allowedSubjectIds ? proctorAllSubjects.filter(s => allowedSubjectIds.has(String(s.id))) : proctorAllSubjects;
    const select = document.getElementById('scheduleSubjectId');
    if (!select) return;
    const currentValue = select.value;
    select.innerHTML = '<option value="">Select Subject</option>' + filtered.map(item => `<option value="${item.id}">${proctorEscape(item.code)} - ${proctorEscape(item.name)}</option>`).join('');
    if (filtered.some(item => String(item.id) === currentValue)) {
        select.value = currentValue;
    }
}

async function printProctorSchedule() {
    const filters = selectedProctorPeriodParams();
    const examId = document.getElementById('proctorExamFilter')?.value || '';
    const status = document.getElementById('proctorStatusFilter')?.value || '';
    if (examId) filters.set('exam_id', examId);
    if (status) filters.set('status', status);
    filters.set('action', 'list');

    const assignmentResponse = await fetch(`${proctorApi}?${filters}`);
    const assignmentData = await assignmentResponse.json();
    if (!assignmentData.success) {
        alert(assignmentData.message || 'Unable to load proctor assignments for printing.');
        return;
    }

    const scheduleFilters = selectedProctorPeriodParams();
    if (examId) scheduleFilters.set('exam_id', examId);
    const scheduleResponse = await fetch(`${proctorApi}?action=exam-schedules&${scheduleFilters}`);
    const scheduleData = await scheduleResponse.json();
    if (!scheduleData.success) {
        alert(scheduleData.message || 'Unable to load exam schedules for printing.');
        return;
    }

    const currentStatuses = ['Assigned', 'Confirmed', 'Completed'];
    const assignments = (assignmentData.assignments || []).filter(item => {
        if (status === 'Cancelled') return item.status === 'Cancelled';
        return currentStatuses.includes(item.status);
    });

    if (!assignments.length) {
        alert('No proctoring assignments available for the selected filters.');
        return;
    }

    const schedules = scheduleData.exam_schedules || [];
    const examSelect = document.getElementById('proctorExamFilter');
    const examName = examSelect ? examSelect.options[examSelect.selectedIndex]?.text || 'All Exams' : 'All Exams';
    const semesterSelect = document.getElementById('proctorSemesterFilter');
    const semesterName = semesterSelect ? semesterSelect.options[semesterSelect.selectedIndex]?.text || 'All Semesters' : 'All Semesters';
    const schoolYearSelect = document.getElementById('proctorSchoolYearFilter');
    const schoolYearName = schoolYearSelect ? schoolYearSelect.options[schoolYearSelect.selectedIndex]?.text || 'All School Years' : 'All School Years';
    const generatedDate = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

    const groupedByDate = {};
    assignments.forEach(item => {
        const dateKey = item.exam_date || 'Unknown';
        if (!groupedByDate[dateKey]) groupedByDate[dateKey] = [];
        groupedByDate[dateKey].push(item);
    });

    const buildTimeSlots = group => {
        const groupSchedules = schedules.filter(item => {
            if (item.exam_id !== group.exam_id || item.exam_date !== group.exam_date) return false;
            const roomMatches = String(item.room_id || '') === String(group.room_id || '');
            const sectionMatches = String(item.section_id || '') === String(group.section_id || '');
            return roomMatches || sectionMatches;
        });

        const unique = [];
        const seen = new Set();
        groupSchedules.sort((a, b) => (a.start_time || '').localeCompare(b.start_time || '')).forEach(s => {
            if (!seen.has(s.id)) {
                seen.add(s.id);
                unique.push(s);
            }
        });

        return unique;
    };

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[ch]));
    const formatProctorDate = value => {
        if (!value) return '';
        const date = new Date(`${value}T00:00:00`);
        return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    };
    const printWindow = window.open('', '', 'width=1400,height=900');
    if (!printWindow) {
        alert('Please allow pop-ups to print the schedule.');
        return;
    }

    // Build rows grouped by date, then by course, then by exam/room/section
    const dateKeys = Object.keys(groupedByDate).sort();
    const rowsHtml = dateKeys.map((dateKey, dateIndex) => {
        const dateItems = groupedByDate[dateKey] || [];

        // Per-day color cycling classes: day-color-0, day-color-1, day-color-2
        const colorClass = `day-color-${dateIndex % 3}`;
        const headerHtml = `<div class="print-section-header ${colorClass}"><div class="print-section-date">${escapeHtml(formatProctorDate(dateKey))}</div></div>`;

        // Group items by course code (prefix before first '-')
        const courses = {};
        dateItems.forEach(item => {
            const raw = String(item.section_code || '');
            const courseCode = raw.split('-')[0] || 'Unknown';
            if (!courses[courseCode]) courses[courseCode] = [];
            courses[courseCode].push(item);
        });

        const orderedCourseKeys = Object.keys(courses).sort((a, b) => a.localeCompare(b));

        const courseBlocks = orderedCourseKeys.map(courseCode => {
            const items = (courses[courseCode] || []).sort((a, b) => {
                if (a.exam_name !== b.exam_name) return a.exam_name.localeCompare(b.exam_name);
                if (a.room_name !== b.room_name) return (a.room_name || '').localeCompare(b.room_name || '');
                return (a.section_code || '').localeCompare(b.section_code || '');
            });

            const groupRows = items.map(group => {
                const slots = buildTimeSlots(group);
                const cells = slots.map(slot => {
                    if (slot.schedule_type === 'Break Time') {
                        return `<td class="break-time-cell">BREAK TIME</td>`;
                    }
                    return `<td>${escapeHtml(slot.subject_code || slot.subject_name || '')}</td>`;
                }).join('');
                const timeHeaders = slots.map(slot => `<th>${escapeHtml(formatProctorTime(slot.start_time))} - ${escapeHtml(formatProctorTime(slot.end_time))}</th>`).join('');
                return `<div class="group-block"><div class="group-title"><strong>${escapeHtml(group.exam_name)}</strong> | ${escapeHtml(group.room_name)} | ${escapeHtml(group.section_code)}</div><table class="print-table"><thead><tr><th>PROCTOR</th><th>ROOM</th><th>SECTION</th>${timeHeaders}</tr></thead><tbody><tr><td>${escapeHtml(`${group.last_name || ''}, ${group.first_name || ''}`)}</td><td>${escapeHtml(group.room_name)}</td><td>${escapeHtml(group.section_code)}</td>${cells}</tr></tbody></table></div>`;
            }).join('');

            return `<div class="print-course-header">${escapeHtml(courseCode)}</div>${groupRows}`;
        }).join('');

        return `${headerHtml}${courseBlocks}`;
    }).join('');

    const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Exam Proctoring Schedule</title><style>
        @page { size: A4 landscape; margin: 10mm; }
        body { margin: 0; padding: 20px; font-family: Arial, sans-serif; color: #111; }
        .document { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: bold; color: #000; margin-bottom: 5px; letter-spacing: 0.5px; }
        .college-name { font-size: 18px; font-weight: bold; color: #000; margin-bottom: 5px; letter-spacing: 0.5px; }
        .college-address { font-size: 11px; color: #333; line-height: 1.3; margin-bottom: 3px; }
        .college-office { font-size: 12px; font-weight: bold; color: #000; margin-top: 8px; }
        .document-title { font-size: 16px; font-weight: bold; margin: 8px 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .report-info { margin: 14px 0 20px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 12px; }
        .report-info div { line-height: 1.4; }
        .print-section-header { margin-top: 24px; margin-bottom: 8px; border-bottom: 1px solid #333; padding: 8px 12px; border-radius: 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-section-date { font-size: 14px; font-weight: bold; }
        /* Day color variants (cycled): 0=Orange, 1=Yellow, 2=Green */
        .print-section-header.day-color-0 { background-color: #fdc568; }
        .print-section-header.day-color-1 { background-color: #fff59d; }
        .print-section-header.day-color-2 { background-color: #a8e6a3; }
        .print-course-header { font-size: 12px; font-weight: 700; padding: 6px 8px; margin: 8px 0 6px; background-color: rgba(0,0,0,0.03); border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .group-block { margin-bottom: 24px; }
        .group-title { font-size: 13px; margin-bottom: 6px; }
        .print-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .print-table th, .print-table td { border: 1px solid #444; padding: 6px 8px; text-align: center; font-size: 11px; }
        .print-table th { background: #f2f2f2; }
        .break-time-cell { background: #fff3cd; color: #856404; font-weight: 700; }
        .signatures { display: flex; justify-content: space-between; gap: 16px; margin-top: 36px; }
        .signature { flex: 1; text-align: center; font-size: 12px; }
        .signature-line { margin-top: 40px; border-top: 1px solid #111; }
        .signature-title { margin-top: 6px; font-weight: bold; }
        .date-prepared { font-size: 10px; color: #666; margin-top: 15px; }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .print-container {
                padding: 20px;
                border-radius: 0;
                box-shadow: none;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style></head><body><div class="document">
        <div class="header"><div class="school-name">BESTLINK COLLEGE OF THE PHILIPPINES</div><div class="document-title">EXAMINATION PROCTORING SCHEDULE</div></div>
        <div class="report-info"><div><strong>Exam:</strong> ${escapeHtml(examName || 'All Exams')}</div><div><strong>School Year:</strong> ${escapeHtml(schoolYearName)}</div><div><strong>Semester:</strong> ${escapeHtml(semesterName)}</div><div><strong>Generated Date:</strong> ${escapeHtml(generatedDate)}</div></div>
        ${rowsHtml}
        <div class="signatures"><div class="signature"><div class="signature-line"></div><div class="signature-title">Prepared by</div></div><div class="signature"><div class="signature-line"></div><div class="signature-title">Checked by</div></div><div class="signature"><div class="signature-line"></div><div class="signature-title">Approved by</div></div></div>
    </div></body></html>`;
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => printWindow.print(), 300);
}

async function printSchedule() {
    const selectedFaculty = document.getElementById('facultySelector').value;
    if (!selectedFaculty) {
        alert('Please select a faculty to print');
        return;
    }

    const semester_id = document.querySelector('select[name="semester_id"]')?.value;
    const school_year_id = document.querySelector('select[name="school_year_id"]')?.value;
    if (!semester_id || !school_year_id) {
        alert('Please select a semester and school year');
        return;
    }

    try {
        const apiUrl = 'api/get_faculty_schedule_timetable.php?faculty_id=' + encodeURIComponent(selectedFaculty) +
                       '&semester_id=' + encodeURIComponent(semester_id) +
                       '&school_year_id=' + encodeURIComponent(school_year_id);
        const response = await fetch(apiUrl);
        const data = await response.json();
        if (!data.success) {
            alert(data.message || 'Failed to load schedule data');
            return;
        }

        const schedules = data.schedules || [];
        const facultyInfo = data.faculty_info || {};
        const semesterName = data.semester_name || semester_id;
        const schoolYearName = data.school_year_name || school_year_id;
        if (schedules.length === 0) {
            alert('No schedules found for selected faculty');
            return;
        }

        const faculty_name = facultyInfo.last_name ? (facultyInfo.first_name + ', ' + facultyInfo.last_name) : 'N/A';
        const faculty_code = facultyInfo.faculty_code || 'N/A';
        const timeToMinutes = (timeStr) => {
            const [hours, minutes] = String(timeStr || '00:00').split(':').map(Number);
            return (hours || 0) * 60 + (minutes || 0);
        };

        const minutesToLabel = minutes => {
            const hour = Math.floor(minutes / 60);
            const minute = minutes % 60;
            const suffix = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${String(minute).padStart(2, '0')} ${suffix}`;
        };

        const earliestMinute = timeToMinutes('07:00');
        const latestMinute = timeToMinutes('17:00');
        const boundaryMinutes = new Set([earliestMinute, latestMinute]);

        schedules.forEach(sch => {
            const startMinutes = timeToMinutes(sch.start_time);
            const endMinutes = timeToMinutes(sch.end_time);
            if (startMinutes >= earliestMinute && endMinutes <= latestMinute) {
                boundaryMinutes.add(startMinutes);
                boundaryMinutes.add(endMinutes);
            }
        });

        const sortedBoundaries = [...boundaryMinutes]
            .filter(minutes => minutes >= earliestMinute && minutes <= latestMinute)
            .sort((a, b) => a - b);

        const timeSlots = [];
        for (let i = 0; i < sortedBoundaries.length - 1; i++) {
            const startMinutes = sortedBoundaries[i];
            const endMinutes = sortedBoundaries[i + 1];
            if (endMinutes <= startMinutes) continue;
            timeSlots.push({
                start: `${String(Math.floor(startMinutes / 60)).padStart(2, '0')}:${String(startMinutes % 60).padStart(2, '0')}`,
                end: `${String(Math.floor(endMinutes / 60)).padStart(2, '0')}:${String(endMinutes % 60).padStart(2, '0')}`,
                label: `${minutesToLabel(startMinutes)} - ${minutesToLabel(endMinutes)}`
            });
        }

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const daySchedules = days.map(() => []);

        schedules.forEach(sch => {
            const dayIndex = days.indexOf(sch.day_of_week);
            if (dayIndex === -1) return;

            const startMinutes = timeToMinutes(sch.start_time);
            const endMinutes = timeToMinutes(sch.end_time);
            if (startMinutes < earliestMinute || endMinutes > latestMinute) return;

            let startIdx = null;
            for (let i = 0; i < timeSlots.length; i++) {
                const slotStart = timeToMinutes(timeSlots[i].start);
                const slotEnd = timeToMinutes(timeSlots[i].end);
                if (startMinutes >= slotStart && startMinutes < slotEnd) {
                    startIdx = i;
                    break;
                }
            }

            let endIdx = null;
            for (let i = timeSlots.length - 1; i >= 0; i--) {
                const slotStart = timeToMinutes(timeSlots[i].start);
                const slotEnd = timeToMinutes(timeSlots[i].end);
                if (endMinutes > slotStart && endMinutes <= slotEnd) {
                    endIdx = i;
                    break;
                }
            }

            if (startIdx === null || endIdx === null || startIdx > endIdx) return;

            daySchedules[dayIndex].push({
                ...sch,
                startIdx,
                endIdx
            });
        });

        daySchedules.forEach(bucket => {
            bucket.sort((a, b) => (a.startIdx - b.startIdx) || (a.endIdx - b.endIdx));
        });

        let totalUnits = 0;
        schedules.forEach(sch => {
            if (sch.schedule_type !== 'Break Time') {
                const s = timeToMinutes(sch.start_time);
                const e = timeToMinutes(sch.end_time);
                const durationHours = (e - s) / 60;
                totalUnits += Math.ceil(durationHours);
            }
        });

        let timetableHtml = '<table class="timetable-grid"><thead><tr><th class="time-header">Time</th>';
        days.forEach(day => { timetableHtml += `<th class="day-header">${day}</th>`; });
        timetableHtml += '</tr></thead><tbody>';

        for (let timeIndex = 0; timeIndex < timeSlots.length; timeIndex++) {
            const timeSlot = timeSlots[timeIndex];
            timetableHtml += `<tr><td class="time-cell">${timeSlot.label}</td>`;
            for (let dayIndex = 0; dayIndex < days.length; dayIndex++) {
                const blocks = daySchedules[dayIndex] || [];
                const startingBlock = blocks.find(b => b.startIdx === timeIndex);

                if (startingBlock) {
                    const rowspan = (startingBlock.endIdx - startingBlock.startIdx) + 1;
                    const blockStartMin = timeToMinutes(timeSlots[startingBlock.startIdx].start);
                    const blockEndMin = timeToMinutes(timeSlots[startingBlock.endIdx].end);
                    const totalMin = blockEndMin - blockStartMin;
                    const totalHeightPx = rowspan * 45;

                    let content = `<div style="position:relative; height:${totalHeightPx}px;">`;
                    const segments = [{
                        type: startingBlock.schedule_type,
                        start_time: startingBlock.start_time,
                        end_time: startingBlock.end_time,
                        subject_code: startingBlock.subject_code,
                        section_code: startingBlock.section_code,
                        room_full: startingBlock.room_full
                    }];

                    segments.forEach(seg => {
                        const segStart = timeToMinutes(seg.start_time);
                        const segEnd = timeToMinutes(seg.end_time);
                        const topPx = totalMin > 0 ? ((segStart - blockStartMin) / totalMin) * totalHeightPx : 0;
                        const heightPx = totalMin > 0 ? ((segEnd - segStart) / totalMin) * totalHeightPx : totalHeightPx;

                        if (seg.type === 'Break Time') {
                            content += `<div class="break-time" style="position:absolute; left:0; right:0; top:${topPx}px; height:${heightPx}px;">BREAK TIME</div>`;
                        } else {
                            const roomHtml = seg.room_full ? seg.room_full.replace(' - ', '<br>') : '';
                            content += `<div class="schedule-entry" style="position:absolute; left:0; right:0; top:${topPx}px; height:${heightPx}px;">
                                <strong>${seg.subject_code || ''}</strong><br>
                                <small>${seg.section_code || ''}</small><br>
                                <small>${roomHtml}</small>
                            </div>`;
                        }
                    });

                    content += '</div>';
                    timetableHtml += `<td class="schedule-cell" rowspan="${rowspan}">${content}</td>`;
                } else {
                    const covered = blocks.some(b => b.startIdx < timeIndex && b.endIdx >= timeIndex);
                    if (!covered) {
                        timetableHtml += '<td class="schedule-cell"></td>';
                    }
                }
            }
            timetableHtml += '</tr>';
        }
        timetableHtml += '</tbody></table>';

        const printWindow = window.open('', '', 'width=1400,height=900');
        const today = new Date();
        const formattedDate = today.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Faculty Class Schedule - ${faculty_name}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Calibri', 'Arial', sans-serif; background: white; color: #333; line-height: 1.4; }
                    .print-container { width: 100%; padding: 20px; background: white; }
                    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
                    .college-name { font-size: 16px; font-weight: bold; color: #000; }
                    .college-address { font-size: 10px; color: #333; line-height: 1.3; }
                    .office-name { font-size: 11px; font-weight: bold; color: #000; margin-top: 3px; }
                    .document-title { font-size: 13px; font-weight: bold; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
                    .faculty-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 30px; margin-bottom: 15px; font-size: 10px; }
                    .info-item { display: flex; }
                    .info-label { font-weight: bold; width: 80px; flex-shrink: 0; }
                    .info-value { flex: 1; }
                    .timetable-grid { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
                    .timetable-grid thead { background: #fff; }
                    .timetable-grid th { border: 1px solid #000; padding: 6px 4px; text-align: center; font-weight: bold; font-size: 9px; }
                    .time-header { width: 80px; }
                    .day-header { width: 13%; }
                    .timetable-grid td { border: 1px solid #000; padding: 4px; height: 45px; vertical-align: top; font-size: 9px; }
                    .time-cell { font-weight: bold; background: #f5f5f5; width: 80px; text-align: center; }
                    .schedule-cell { background: white; overflow: hidden; }
                    .schedule-entry { font-size: 8px; line-height: 1.2; }
                    .schedule-entry strong { display: block; font-weight: bold; }
                    .break-time {
                        background: #fdf3e0;
                        color: #b8860b;
                        font-weight: bold;
                        text-align: center;
                        padding: 8px 2px;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 8px;
                        border: none;
                        box-shadow: none;
                        outline: none;
                    }
                    .summary-section { margin-top: 10px; font-size: 10px; }
                    .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #000; }
                    .footer-row { display: flex; justify-content: space-between; gap: 20px; margin-top: 20px; }
                    .signature-block { flex: 1; text-align: center; font-size: 9px; }
                    .signature-line { border-top: 1px solid #000; margin-top: 35px; padding-top: 3px; font-weight: bold; }
                    .signature-title { font-size: 8px; color: #666; margin-top: 2px; }
                    @media print { body { margin: 0; padding: 0; } .print-container { padding: 15px; } @page { size: A4 landscape; margin: 8mm; } }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <div class="header">
                        <div class="college-name">BESTLINK COLLEGE OF THE PHILIPPINES</div>
                        <div class="college-address">1071 Brgy. Kaligayahan, Quirino Highway, Novaliches<br>Quezon City, Philippines 1116</div>
                        <div class="office-name">College Coordinator Office</div>
                        <div class="document-title">FACULTY CLASS SCHEDULE</div>
                    </div>
                    <div class="faculty-info">
                        <div class="info-item"><div class="info-label">Faculty:</div><div class="info-value">${faculty_name}</div></div>
                        <div class="info-item"><div class="info-label">Faculty Code:</div><div class="info-value">${faculty_code}</div></div>
                        <div class="info-item"><div class="info-label">Semester:</div><div class="info-value">${semesterName}</div></div>
                        <div class="info-item"><div class="info-label">Academic Year:</div><div class="info-value">${schoolYearName}</div></div>
                    </div>
                    ${timetableHtml}
                    <div class="summary-section">Total Units: <strong>${totalUnits} / 15</strong></div>
                    <div class="footer">
                        <div class="footer-row">
                            <div class="signature-block"><div>Prepared by:</div><div class="signature-line"></div><div class="signature-title">College Coordinator</div></div>
                            <div class="signature-block"><div>Noted by:</div><div class="signature-line"></div><div class="signature-title">Dean / Director</div></div>
                            <div class="signature-block"><div>Date:</div><div class="signature-line">${formattedDate}</div></div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `;
        printWindow.document.write(htmlContent);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    } catch (error) {
        console.error('Error loading schedule:', error);
        alert('Error loading schedule data: ' + error.message);
    }
}

function deleteSchedule(scheduleId) {
    if (!confirm('Delete this schedule? This will also delete attendance records.')) {
        return;
    }

    const formData = new FormData();
    formData.append('schedule_id', scheduleId);

    fetch('/sms/modules/college-coor/api/delete_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting schedule: ' + error);
    });
}

function populateFacultySelect(selectedId = '') {
    const select = document.getElementById('proctorFacultyId');
    if (!select) return;
    select.innerHTML = '<option value="">Select Faculty</option>' + proctorFaculty.map(item => `<option value="${item.id}">${proctorEscape(item.last_name)}, ${proctorEscape(item.first_name)} (${proctorEscape(item.faculty_code)})</option>`).join('');
    select.value = selectedId;
}

async function openProctorModal(assignment = null) {
    try {
        await loadProctorReferenceData();
    } catch (error) {
        alert(error.message);
        return;
    }
    document.getElementById('proctorForm')?.reset();
    document.getElementById('proctorAssignmentId').value = assignment?.id || '';
    document.getElementById('proctorModalTitle').innerHTML = assignment ? '<i class="fas fa-edit"></i> Edit Exam Proctor' : '<i class="fas fa-user-shield"></i> Assign Exam Proctor';
    populateFacultySelect(assignment?.faculty_id || '');
    const scheduleSelect = document.getElementById('proctorScheduleId');
    scheduleSelect.disabled = Boolean(assignment);
    if (assignment) {
        scheduleSelect.value = assignment.exam_schedule_id;
        document.getElementById('proctorRole').value = assignment.role;
        document.getElementById('proctorStatus').value = assignment.status;
    }
    updateProctorScheduleInfo();
    document.getElementById('proctorModal').style.display = 'flex';
}

function closeProctorModal() {
    const modal = document.getElementById('proctorModal');
    if (modal) modal.style.display = 'none';
}

function updateProctorScheduleInfo() {
    const item = proctorExamSchedules.find(schedule => String(schedule.id) === String(document.getElementById('proctorScheduleId')?.value));
    const info = document.getElementById('proctorScheduleInfo');
    if (!info) return;
    info.innerHTML = item ? `<strong>Exam:</strong> ${proctorEscape(item.exam_name)}<br><strong>Subject:</strong> ${proctorEscape(item.subject_code || '')} - ${proctorEscape(item.subject_name || '')}<br><strong>Section:</strong> ${proctorEscape(item.section_code || '')}<br><strong>Room:</strong> ${proctorEscape(item.room_name || '')}<br><strong>Date:</strong> ${proctorEscape(item.exam_date)}<br><strong>Time:</strong> ${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}` : '';
}

function editProctor(id) {
    const assignment = (window.proctorAssignments || []).find(item => Number(item.id) === Number(id));
    if (!assignment) return;
    assignment.exam_schedule_id = assignment.sample_schedule_id;
    openProctorModal(assignment);
}

async function submitProctor(event) {
    event.preventDefault();
    const form = new FormData(event.target);
    form.append('action', form.get('id') ? 'update' : 'create');
    try {
        const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
        if (!data.success) throw new Error(data.message);
        alert(data.message);
        closeProctorModal();
        await loadProctorAssignments();
    } catch (error) {
        alert(error.message);
    }
}

async function cancelProctor(id) {
    if (!confirm('Cancel this exam proctor assignment?')) return;
    const form = new FormData();
    form.append('action', 'cancel');
    form.append('id', id);
    try {
        const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
        if (!data.success) throw new Error(data.message);
        alert(data.message);
        await loadProctorAssignments();
    } catch (error) {
        alert(error.message);
    }
}

function clearProctorFilters() {
    const semesterFilter = document.getElementById('proctorSemesterFilter');
    const schoolYearFilter = document.getElementById('proctorSchoolYearFilter');
    const examFilter = document.getElementById('proctorExamFilter');
    const statusFilter = document.getElementById('proctorStatusFilter');
    if (semesterFilter) semesterFilter.value = '';
    if (schoolYearFilter) schoolYearFilter.value = '';
    if (examFilter) examFilter.value = '';
    if (statusFilter) statusFilter.value = 'Assigned';
    loadProctorReferenceData().then(loadProctorAssignments);
}

function openExamModal() {
    const examForm = document.getElementById('examForm');
    if (examForm) examForm.reset();
    const examSchoolYear = document.getElementById('examSchoolYear');
    const examSemester = document.getElementById('examSemester');
    if (examSchoolYear) examSchoolYear.value = document.getElementById('proctorSchoolYearFilter')?.value || '';
    if (examSemester) examSemester.value = document.getElementById('proctorSemesterFilter')?.value || '';
    const modal = document.getElementById('examModal');
    if (modal) modal.style.display = 'flex';
}

function closeExamModal() {
    const modal = document.getElementById('examModal');
    if (modal) modal.style.display = 'none';
}

async function submitExam(event) {
    event.preventDefault();
    const form = new FormData(event.target);
    form.append('action', 'create_exam');
    try {
        const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
        if (!data.success) throw new Error(data.message);
        alert(data.message);
        closeExamModal();
    } catch (error) {
        alert(error.message);
    }
}

function openExamScheduleModal() {
    const form = document.getElementById('examScheduleForm');
    if (form) form.reset();
    updateExamScheduleTypeFields();
    loadProctorReferenceData().then(() => {
        updateExamScheduleTypeFields();
        updateScheduleSectionOptions();
        updateScheduleSubjectOptions();
        const modal = document.getElementById('examScheduleModal');
        if (modal) modal.style.display = 'flex';
    }).catch(error => alert(error.message));
}

function closeExamScheduleModal() {
    const modal = document.getElementById('examScheduleModal');
    if (modal) modal.style.display = 'none';
}

function updateExamScheduleContext() {
    const exam = proctorExams.find(item => String(item.id) === String(document.getElementById('scheduleExamId').value));
    const context = document.getElementById('scheduleExamContext');
    if (!context) return;
    if (!exam) { context.textContent = ''; return; }
    context.textContent = `${exam.school_year_name || ''} / ${exam.semester_name || ''} | Exam dates: ${exam.start_date} to ${exam.end_date}`;
    const scheduleExamDate = document.getElementById('scheduleExamDate');
    if (scheduleExamDate) {
        scheduleExamDate.min = exam.start_date;
        scheduleExamDate.max = exam.end_date;
    }
}

function updateExamScheduleTypeFields() {
    const scheduleTypeElement = document.getElementById('scheduleType');
    const fields = document.getElementById('examScheduleClassFields');
    if (!scheduleTypeElement || !fields) return;
    const type = scheduleTypeElement.value;
    const subjectField = document.getElementById('scheduleSubjectId');
    const sectionField = document.getElementById('scheduleSectionId');
    const roomField = document.getElementById('scheduleRoomId');

    if (type === 'Exam') {
        fields.style.display = 'contents';
        if (subjectField) { subjectField.disabled = false; subjectField.required = true; subjectField.setAttribute('name', 'subject_id'); }
        if (sectionField) { sectionField.disabled = false; sectionField.required = true; sectionField.setAttribute('name', 'section_id'); }
        if (roomField) { roomField.disabled = false; roomField.required = true; roomField.setAttribute('name', 'room_id'); }
    } else if (type === 'Break Time') {
        fields.style.display = 'contents';
        if (subjectField) { subjectField.disabled = true; subjectField.required = false; subjectField.value = ''; subjectField.removeAttribute('name'); }
        if (sectionField) { sectionField.disabled = false; sectionField.required = true; sectionField.setAttribute('name', 'section_id'); }
        if (roomField) { roomField.disabled = false; roomField.required = true; roomField.setAttribute('name', 'room_id'); }
    } else {
        fields.style.display = 'none';
        [subjectField, sectionField, roomField].forEach(f => {
            if (f) { f.disabled = true; f.required = false; f.value = ''; f.removeAttribute('name'); }
        });
    }
}

// Toggle fields in Add Schedule tab based on Schedule Type (Class vs Break Time)
function updateScheduleTypeFields() {
    const scheduleTypeEl = document.getElementById('schedule_type');
    if (!scheduleTypeEl) return;
    const type = scheduleTypeEl.value;

    const wrappers = {
        subject: document.getElementById('add-subject-field'),
        section: document.getElementById('add-section-field'),
        facultyLoad: document.getElementById('add-faculty-load-field')
    };

    const fields = {
        subject: document.getElementById('subject_id'),
        section: document.getElementById('section_id'),
        facultyLoad: document.getElementById('faculty_load_id'),
        faculty: document.getElementById('faculty_id'),
        room: document.getElementById('room_id')
    };

    if (type === 'Break Time') {
        ['subject', 'section', 'facultyLoad'].forEach(k => {
            const wrap = wrappers[k];
            const f = fields[k];
            if (wrap) wrap.style.display = 'none';
            if (f) { f.disabled = true; f.required = false; f.value = ''; f.removeAttribute('name'); }
        });

        if (fields.faculty) { fields.faculty.disabled = false; fields.faculty.required = true; fields.faculty.setAttribute('name', 'faculty_id'); }
        if (fields.room) fields.room.required = false;
    } else {
        ['subject', 'section', 'facultyLoad'].forEach(k => {
            const wrap = wrappers[k];
            const f = fields[k];
            if (wrap) wrap.style.display = '';
            if (f) {
                f.disabled = false;
                f.required = true;
                if (k === 'subject') f.setAttribute('name', 'subject_id');
                if (k === 'section') f.setAttribute('name', 'grade_section_id');
                if (k === 'facultyLoad') f.setAttribute('name', 'faculty_load_id');
            }
        });

        if (fields.faculty) { fields.faculty.disabled = false; fields.faculty.required = true; fields.faculty.setAttribute('name', 'faculty_id'); }
        if (fields.room) fields.room.required = true;
    }
}

async function submitExamSchedule(event) {
    event.preventDefault();
    const form = new FormData(event.target);
    form.append('action', 'create_schedule');
    // TEMPORARY DEBUG - dump FormData before sending
    for (const [key, value] of form.entries()) {
        console.log('FormData:', key, '=', value);
    }
    try {
        const data = await (await fetch(proctorApi, { method: 'POST', body: form })).json();
        if (!data.success) throw new Error(data.message);
        alert(data.message);
        closeExamScheduleModal();
        await loadProctorReferenceData();
        await loadProctorAssignments();
    } catch (error) {
        alert(error.message);
    }
}

window.addEventListener('page:loaded', function (event) {
    if (event.detail?.page !== 'class-scheduling') return;

    try {
        const blob = document.getElementById('preload-scheduleAllFaculty');
        if (blob && blob.textContent) {
            try {
                window.scheduleAllFaculty = JSON.parse(blob.textContent);
                console.log('Loaded preload scheduleAllFaculty:', window.scheduleAllFaculty);
            } catch (err) {
                console.error('Failed to parse preload-scheduleAllFaculty JSON', err);
            }
        }
    } catch (e) {
        // ignore
    }

    const scheduleType = document.getElementById('schedule_type');
    if (scheduleType) scheduleType.dispatchEvent(new Event('change'));

    const editScheduleType = document.getElementById('edit_schedule_type');
    if (editScheduleType) editScheduleType.dispatchEvent(new Event('change'));

    initProctoringTab();
});

function initProctoringTab() {
    const scheduleIdSelect = document.getElementById('proctorScheduleId');
    if (scheduleIdSelect && !scheduleIdSelect.dataset.listenerAttached) {
        scheduleIdSelect.dataset.listenerAttached = 'true';
        scheduleIdSelect.addEventListener('change', updateProctorScheduleInfo);
    }

    const examIdSelect = document.getElementById('scheduleExamId');
    if (examIdSelect && !examIdSelect.dataset.listenerAttached) {
        examIdSelect.dataset.listenerAttached = 'true';
        examIdSelect.addEventListener('change', updateExamScheduleContext);
    }

    const scheduleTypeSelect = document.getElementById('schedule_type');
    if (scheduleTypeSelect && !scheduleTypeSelect.dataset.listenerAttached) {
        scheduleTypeSelect.dataset.listenerAttached = 'true';
        scheduleTypeSelect.addEventListener('change', updateScheduleTypeFields);
    }

    // Ensure the Add Exam Schedule modal's Schedule Type has a change listener
    const scheduleTypeModal = document.getElementById('scheduleType');
    console.log('scheduleType element found:', scheduleTypeModal);
    if (scheduleTypeModal && !scheduleTypeModal.dataset.listenerAttached) {
        scheduleTypeModal.dataset.listenerAttached = 'true';
        scheduleTypeModal.addEventListener('change', updateExamScheduleTypeFields);
    }

    ['scheduleCourseId', 'scheduleYearLevel'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && !el.dataset.listenerAttached) {
            el.dataset.listenerAttached = 'true';
            el.addEventListener('change', function () {
                updateScheduleSectionOptions();
                updateScheduleSubjectOptions();
            });
        }
    });

    ['proctorSemesterFilter', 'proctorSchoolYearFilter'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && !el.dataset.listenerAttached) {
            el.dataset.listenerAttached = 'true';
            el.addEventListener('change', function () {
                loadProctorReferenceData().catch(function (error) {
                    alert(error.message);
                });
            });
        }
    });

    if (document.getElementById('proctoring-tab')) {
        loadProctorReferenceData().then(loadProctorAssignments).catch(console.error);
    }
}

const classSchedulingExports = [
    'showTab',
    'printSchedule',
    'deleteSchedule',
    'openExamModal',
    'closeExamModal',
    'openExamScheduleModal',
    'closeExamScheduleModal',
    'openProctorModal',
    'closeProctorModal',
    'submitExam',
    'submitExamSchedule',
    'submitProctor',
    'editProctor',
    'cancelProctor',
    'clearProctorFilters',
    'printProctorSchedule',
    'loadProctorAssignments',
    'exportToCSV',
    'editSchedule',
    'closeEditModal',
    'validateForm',
    'validateEditForm'
];

classSchedulingExports.forEach((name) => exposeGlobal(name, window[name] ?? eval(name)));

async function loadProctorAssignments() {
    const body = document.getElementById('proctorAssignmentsBody');
    if (!body) return;
    body.innerHTML = '<tr><td colspan="10" style="text-align:center;">Loading assignments...</td></tr>';
    const params = selectedProctorPeriodParams();
    params.set('action', 'list');
    params.set('exam_id', document.getElementById('proctorExamFilter')?.value || '');
    params.set('status', document.getElementById('proctorStatusFilter')?.value || '');
    try {
        const data = await (await fetch(`${proctorApi}?${params}`)).json();
        if (!data.success) throw new Error(data.message);
        body.innerHTML = data.assignments.length ? data.assignments.map(item => `<tr>
            <td>${proctorEscape(item.exam_name)}</td><td>${proctorEscape(item.subject_codes || '')}</td><td>${proctorEscape(item.section_code || '')}</td><td>${proctorEscape(item.room_name || '')}</td>
            <td>${proctorEscape(formatProctorDate(item.exam_date))}</td><td>${proctorEscape(formatProctorTime(item.start_time))} - ${proctorEscape(formatProctorTime(item.end_time))}</td>
            <td>${proctorEscape(`${item.last_name || ''}, ${item.first_name || ''}`)}</td><td>${proctorEscape(item.role)}</td><td>${proctorEscape(item.status)}</td>
            <td><button type="button" class="btn btn-primary btn-sm" onclick="editProctor(${Number(item.id)})"><i class="fas fa-edit"></i></button>
            <button type="button" class="btn btn-danger btn-sm" onclick="cancelProctor(${Number(item.id)})"><i class="fas fa-ban"></i></button></td></tr>`).join('') : '<tr><td colspan="10" style="text-align:center;">No proctor assignments found.</td></tr>';
        window.proctorAssignments = data.assignments;
    } catch (error) { body.innerHTML = `<tr><td colspan="10" style="text-align:center;color:#b00020;">${proctorEscape(error.message)}</td></tr>`; }
}
