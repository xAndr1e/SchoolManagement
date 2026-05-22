(function() {
        // ---------- DATA (dummy for demo) ----------
        let activeStudents = [
            { id: 1, student_number: '2024-1001', first_name: 'John Michael', last_name: 'Cruz', gender: 'Male', birthdate: '2004-05-12', age: 20, address: '123 Rizal St, Manila', course: 'BSIT', year_level: '2nd Year', email: 'jm.cruz@school.edu.ph', contact_number: '09171234567', emergency_person: 'Ramon Cruz', emergency_number: '09171234567', remarks: 'Regular check-in' },
            { id: 2, student_number: '2024-2045', first_name: 'Angela', last_name: 'Lopez', gender: 'Female', birthdate: '2005-08-22', age: 19, address: '456 Mabini St, Quezon City', course: 'BSBA', year_level: '1st Year', email: 'angela.lopez@school.edu.ph', contact_number: '09189876543', emergency_person: 'Elena Lopez', emergency_number: '09189876543', remarks: 'Needs monitoring' },
            { id: 3, student_number: '2023-3789', first_name: 'Ryan', last_name: 'Santos', gender: 'Male', birthdate: '2003-11-02', age: 21, address: '789 Rizal Blvd, Makati', course: 'BSCS', year_level: '3rd Year', email: 'ryan.santos@school.edu.ph', contact_number: '09221234567', emergency_person: 'Antonio Santos', emergency_number: '09221234567', remarks: 'Academic concerns' }
        ];

        let archivedStudents = [
            { 
                id: 7, 
                student_number: '2022-0012', 
                first_name: 'Maria', 
                last_name: 'Rivera', 
                gender: 'Female', 
                birthdate: '2002-07-15', 
                age: 22, 
                address: 'Old address', 
                course: 'BSIT', 
                year_level: '4th Year', 
                email: 'maria.r@school.edu', 
                contact_number: '09091234567', 
                emergency_person: 'Luz Rivera', 
                emergency_number: '09091234567', 
                remarks: 'Graduated - archived' 
            }
        ];

        let nextId = activeStudents.length ? Math.max(...activeStudents.map(s=>s.id)) + 1 : 100;

        // DOM elements
        const activeTbody = document.getElementById('activeTableBody');
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const saveBtn = document.getElementById('saveStudentBtn');
        const alertMsg = document.getElementById('alertMsg');
        const studentId = document.getElementById('student_id');
        const modal = document.getElementById('archiveModal');
        const openModalBtn = document.getElementById('openArchiveModalBtn');
        const closeModalBtn = document.getElementById('closeArchiveModalBtn');
        const modalBody = document.getElementById('archiveModalBody');

        // stats spans
        const totalActiveSpan = document.getElementById('totalActive');
        const totalArchivedSpan = document.getElementById('totalArchived');
        const courseCountSpan = document.getElementById('courseCount');
        const todaySpan = document.getElementById('todayDate');
        const showingActiveSpan = document.getElementById('showingActive');

        // ---------- DROPDOWN LOGIC (database) ----------
        document.getElementById('studentSelector').addEventListener('change', function(e) {
            const studentNumber = e.target.value;
            if (!studentNumber) {
                clearForm();
                return;
            }

            fetch(`/modules/guidance/api/get-student.php?student_number=${encodeURIComponent(studentNumber)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch details');
                    return res.json();
                })
                .then(student => {
                    // Map database fields to form fields
                    document.getElementById('student_id').value = ''; // DB has no 'id'
                    document.getElementById('student_number').value = student.student_number || '';
                    document.getElementById('first_name').value = student.first_name || '';
                    document.getElementById('last_name').value = student.last_name || '';
                    document.getElementById('gender').value = student.gender || '';
                    document.getElementById('birthdate').value = student.birth_date || '';
                    // Calculate age
                    if (student.birth_date) {
                        const bd = new Date(student.birth_date);
                        const today = new Date();
                        let age = today.getFullYear() - bd.getFullYear();
                        const m = today.getMonth() - bd.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < bd.getDate())) age--;
                        document.getElementById('age').value = age >= 0 ? age : '';
                    } else {
                        document.getElementById('age').value = '';
                    }
                    document.getElementById('address').value = student.address || '';
                    document.getElementById('course').value = student.course || '';
                    document.getElementById('year_level').value = student.year_level || '';
                    document.getElementById('email').value = student.email || '';
                    document.getElementById('contact_number').value = student.phone || '';
                    // Emergency fields not in DB
                    document.getElementById('emergency_person').value = '';
                    document.getElementById('emergency_number').value = '';
                    document.getElementById('remarks').value = '';

                    alertMsg.innerText = `Loaded: ${student.first_name} ${student.last_name}`;
                })
                .catch(err => {
                    console.error(err);
                    alertMsg.innerText = 'Error loading student details';
                });
        });

        // ---------- EXISTING FUNCTIONS (unchanged) ----------
        function refreshStats() {
            totalActiveSpan.innerText = activeStudents.length;
            totalArchivedSpan.innerText = archivedStudents.length;
            const uniqueCourses = new Set(activeStudents.map(s=>s.course).filter(Boolean)).size;
            courseCountSpan.innerText = uniqueCourses || 0;
            const today = new Date().toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
            todaySpan.innerText = today;
        }

        function renderActive(filter = '') {
            const f = filter.toLowerCase();
            const filtered = activeStudents.filter(s => 
                s.first_name.toLowerCase().includes(f) ||
                s.last_name.toLowerCase().includes(f) ||
                s.student_number.toLowerCase().includes(f)
            );

            if (filtered.length === 0) {
                activeTbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:2rem;">No active students</td></tr>`;
                showingActiveSpan.innerText = `0 of ${activeStudents.length}`;
                return;
            }

            let html = '';
            filtered.forEach((s, idx) => {
                html += `<tr>
                    <td>${idx+1}</td>
                    <td>${s.student_number}</td>
                    <td>${s.last_name}, ${s.first_name}</td>
                    <td>${s.course || '—'}</td>
                    <td>${s.year_level || '—'}</td>
                    <td>${s.contact_number || '—'}</td>
                    <td>
                        <button class="action-btn edit-btn" data-id="${s.id}" title="Edit"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button class="action-btn archive-btn" data-id="${s.id}" title="Archive"><i class="fa-regular fa-box-archive"></i></button>
                    </td>
                </tr>`;
            });
            activeTbody.innerHTML = html;
            showingActiveSpan.innerText = `Showing ${filtered.length} of ${activeStudents.length}`;

            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.dataset.id);
                    const student = activeStudents.find(s => s.id === id);
                    if (student) populateForm(student);
                });
            });
            document.querySelectorAll('.archive-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.dataset.id);
                    archiveStudent(id);
                });
            });
        }

        function renderArchiveModal() {
            if (archivedStudents.length === 0) {
                modalBody.innerHTML = `<div style="text-align:center; padding:2rem; color:#5a7997;">Archive is empty</div>`;
                return;
            }
            let html = '';
            archivedStudents.forEach(s => {
                html += `
                    <div class="archive-card" style="margin-bottom: 1.2rem;" data-id="${s.id}">
                        <div class="student-id-large">${s.student_number}</div>
                        <div class="student-detail-row">
                            <span class="student-detail-label">Name</span>
                            <span class="student-detail-value">${s.last_name}, ${s.first_name}</span>
                        </div>
                        <div class="student-detail-row">
                            <span class="student-detail-label">Course</span>
                            <span class="student-detail-value">${s.course || '—'}</span>
                        </div>
                        <div class="student-detail-row">
                            <span class="student-detail-label">Year</span>
                            <span class="student-detail-value">${s.year_level || '—'}</span>
                        </div>
                        <div class="remarks-text">${s.remarks || 'No remarks'}</div>
                        <div style="margin-top: 1.2rem; text-align: right;">
                            <button class="restore-btn" data-id="${s.id}"><i class="fa-regular fa-arrow-rotate-left"></i> Restore</button>
                        </div>
                    </div>
                `;
            });
            modalBody.innerHTML = html;

            document.querySelectorAll('#archiveModalBody .restore-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.dataset.id);
                    restoreStudent(id);
                });
            });
        }

        function archiveStudent(id) {
            const index = activeStudents.findIndex(s => s.id === id);
            if (index !== -1) {
                const [removed] = activeStudents.splice(index, 1);
                archivedStudents.push(removed);
                renderActive(searchInput.value);
                renderArchiveModal();
                refreshStats();
                clearForm();
                alertMsg.innerText = '📦 Student archived';
            }
        }

        function restoreStudent(id) {
            const index = archivedStudents.findIndex(s => s.id === id);
            if (index !== -1) {
                const [removed] = archivedStudents.splice(index, 1);
                activeStudents.push(removed);
                renderActive(searchInput.value);
                renderArchiveModal();
                refreshStats();
                alertMsg.innerText = '↩️ Student restored to active';
            }
        }

        function populateForm(s) {
            studentId.value = s.id;
            document.getElementById('student_number').value = s.student_number || '';
            document.getElementById('first_name').value = s.first_name || '';
            document.getElementById('last_name').value = s.last_name || '';
            document.getElementById('gender').value = s.gender || '';
            document.getElementById('birthdate').value = s.birthdate || '';
            document.getElementById('age').value = s.age || '';
            document.getElementById('address').value = s.address || '';
            document.getElementById('course').value = s.course || '';
            document.getElementById('year_level').value = s.year_level || '';
            document.getElementById('email').value = s.email || '';
            document.getElementById('contact_number').value = s.contact_number || '';
            document.getElementById('emergency_person').value = s.emergency_person || '';
            document.getElementById('emergency_number').value = s.emergency_number || '';
            document.getElementById('remarks').value = s.remarks || '';
            alertMsg.innerText = '✏️ Editing student (ID: ' + s.id + ')';
        }

        function clearForm() {
            studentId.value = '';
            const ids = ['student_number','first_name','last_name','gender','birthdate','age','address','course','year_level','email','contact_number','emergency_person','emergency_number','remarks'];
            ids.forEach(id => document.getElementById(id).value = '');
            alertMsg.innerText = '';
        }

        saveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const required = ['student_number','first_name','last_name','course','year_level','email'];
            for (let f of required) {
                if (!document.getElementById(f).value.trim()) {
                    alertMsg.innerText = '❌ Fill all required fields *';
                    return;
                }
            }
            const studentData = {
                id: studentId.value ? parseInt(studentId.value) : nextId++,
                student_number: document.getElementById('student_number').value.trim(),
                first_name: document.getElementById('first_name').value.trim(),
                last_name: document.getElementById('last_name').value.trim(),
                gender: document.getElementById('gender').value,
                birthdate: document.getElementById('birthdate').value,
                age: document.getElementById('age').value ? parseInt(document.getElementById('age').value) : '',
                address: document.getElementById('address').value,
                course: document.getElementById('course').value.trim(),
                year_level: document.getElementById('year_level').value,
                email: document.getElementById('email').value.trim(),
                contact_number: document.getElementById('contact_number').value,
                emergency_person: document.getElementById('emergency_person').value,
                emergency_number: document.getElementById('emergency_number').value,
                remarks: document.getElementById('remarks').value,
            };

            const editingId = studentId.value ? parseInt(studentId.value) : null;
            if (editingId) {
                const idx = activeStudents.findIndex(s => s.id === editingId);
                if (idx !== -1) {
                    activeStudents[idx] = studentData;
                    alertMsg.innerText = '✅ Student updated';
                } else {
                    alertMsg.innerText = '❌ Cannot edit archived student';
                    return;
                }
            } else {
                activeStudents.push(studentData);
                alertMsg.innerText = '✅ New student saved';
            }
            renderActive(searchInput.value);
            renderArchiveModal();
            refreshStats();
            clearForm();
        });

        function handleSearch() { renderActive(searchInput.value); }
        searchBtn.addEventListener('click', handleSearch);
        searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') handleSearch(); });

        document.getElementById('birthdate').addEventListener('change', function() {
            if (this.value) {
                const bd = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - bd.getFullYear();
                const m = today.getMonth() - bd.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < bd.getDate())) age--;
                document.getElementById('age').value = age >= 0 ? age : '';
            }
        });

        openModalBtn.addEventListener('click', () => {
            renderArchiveModal();
            modal.style.display = 'flex';
        });
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        // initial render
        renderActive('');
        renderArchiveModal();
        refreshStats();
    })();