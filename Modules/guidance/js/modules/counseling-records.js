(function() {
    // ---------- DATA ----------
    let sessions = [];
    function loadData() {
        const stored = localStorage.getItem('counselingSwitched');
        if (stored) { try { sessions = JSON.parse(stored); } catch { sessions = []; } }
        if (!sessions.length) {
            const today = new Date().toISOString().slice(0,10);
            sessions = [
                { id: Date.now()-5000, name: 'Rivera, Alex', year: 'Junior', date: today, concern: 'Thesis anxiety', type: 'Priority guidance', priority: 'yes', referral: 'Self', archived: false },
                { id: Date.now()-4000, name: 'Kim, Dana', year: 'Sophomore', date: '2026-03-16', concern: 'Attendance probation', type: 'Disciplinary', priority: 'no', referral: 'Dean', archived: false },
                { id: Date.now()-3000, name: 'Patel, Riya', year: 'Freshman', date: '2026-03-15', concern: 'Roommate conflict', type: 'Crisis intervention', priority: 'yes', referral: 'Self', archived: false },
                { id: Date.now()-2000, name: 'Smith, John', year: 'Senior', date: '2026-03-14', concern: 'Career planning', type: 'Regular check-in', priority: 'no', referral: 'Faculty', archived: false },
                { id: Date.now()-1000, name: 'Okafor, Chidi', year: 'Junior', date: '2026-03-13', concern: 'Study skills', type: 'Priority guidance', priority: 'yes', referral: 'Self', archived: false }
            ];
        }
        sessions = sessions.map(s => ({ ...s, archived: s.archived || false }));
    }
    loadData();
    // current active tab
    let currentTab = 'counseling';
    // DOM elements
    const tbody = document.getElementById('tableBody');
    const submitBtn = document.getElementById('submitBtn');
    const nameInp = document.getElementById('studentName');
    const yearInp = document.getElementById('studentYear');
    const dateInp = document.getElementById('sessionDate');
    const concernInp = document.getElementById('concern');
    const typeSelect = document.getElementById('sessionType');
    const referralSelect = document.getElementById('referral');
    const priorityYes = document.getElementById('priorityYes');
    const priorityNo = document.getElementById('priorityNo');
    // stats spans
    const todaySpan = document.getElementById('todaySessions');
    const todayPrioritySpan = document.getElementById('todayPriority');
    const priorityTotalSpan = document.getElementById('priorityTotal');
    const totalSessionsSpan = document.getElementById('totalSessions');
    const weekSessionsSpan = document.getElementById('weekSessions');
    const entryCountSpan = document.getElementById('entryCount');
    // set default date
    dateInp.value = new Date().toISOString().slice(0,10);
    function escape(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"]/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;' }[c] || c));
    }
    function renderTable() {
        const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
        let filtered = [];
        if (currentTab === 'archive') {
            filtered = sessions.filter(s => s.archived === true);
        } else {
            filtered = sessions.filter(s => !s.archived);
            if (currentTab === 'disciplinary') {
                filtered = filtered.filter(s => s.type.toLowerCase().includes('disciplinary') || s.type === 'Disciplinary');
            } else if (currentTab === 'priority') {
                filtered = filtered.filter(s => s.priority === 'yes');
            } else if (currentTab === 'disclaimer') {
                filtered = [];
            } else {
                // counseling records: all non-archived
            }
        }
        if (search) {
            filtered = filtered.filter(s => 
                (s.name + ' ' + s.concern + ' ' + s.type + ' ' + s.referral).toLowerCase().includes(search)
            );
        }
        if (filtered.length === 0) {
            let message = 'No sessions';
            if (currentTab === 'disclaimer') message = '📄 Disclaimer: All records are confidential. No sessions shown.';
            else if (currentTab === 'archive') message = '📂 Archive is empty.';
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:2rem;">${message}</td></tr>`;
        } else {
            let html = '';
            filtered.forEach(s => {
                const priorityClass = s.priority === 'yes' ? 'priority-badge' : 'regular-badge';
                const priorityText = s.priority === 'yes' ? '🔴 Priority' : '🟢 Regular';
                let actionButtons = '';
                if (currentTab === 'archive') {
                    actionButtons = `<button class="action-btn restore-btn" onclick="restoreSession(${s.id})" title="Restore"><i class="fas fa-trash-restore"></i></button>`;
                } else {
                    actionButtons = `
                        <button class="action-btn" onclick="editSession(${s.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="action-btn archive-btn" onclick="archiveSession(${s.id})" title="Archive"><i class="fas fa-archive"></i></button>
                    `;
                }
                html += `<tr>
                    <td><strong>${escape(s.name)}</strong></td>
                    <td>${escape(s.year)}</td>
                    <td>${escape(s.date)}</td>
                    <td>${escape(s.concern.substring(0,25))}${s.concern.length>25?'…':''}</td>
                    <td>${escape(s.type)}</td>
                    <td><span class="${priorityClass}">${priorityText}</span></td>
                    <td>${escape(s.referral || '—')}</td>
                    <td>${actionButtons}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }
        entryCountSpan.innerText = `Showing ${filtered.length} entries`;
        updateStats();
    }
    function updateStats() {
        const active = sessions.filter(s => !s.archived);
        totalSessionsSpan.innerText = active.length;
        const today = new Date().toISOString().slice(0,10);
        const todaySessions = active.filter(s => s.date === today);
        todaySpan.innerText = todaySessions.length;
        todayPrioritySpan.innerText = todaySessions.filter(s => s.priority === 'yes').length;
        priorityTotalSpan.innerText = active.filter(s => s.priority === 'yes').length;
        const weekAgo = new Date(Date.now() - 7*86400000).toISOString().slice(0,10);
        weekSessionsSpan.innerText = active.filter(s => s.date >= weekAgo && s.date <= today).length;
    }
    function persist() { localStorage.setItem('counselingSwitched', JSON.stringify(sessions)); }
    // tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const tab = this.getAttribute('data-tab');
            if (tab === 'counseling') currentTab = 'counseling';
            else if (tab === 'disciplinary') currentTab = 'disciplinary';
            else if (tab === 'priority') currentTab = 'priority';
            else if (tab === 'disclaimer') currentTab = 'disclaimer';
            else if (tab === 'archive') currentTab = 'archive';
            renderTable();
        });
    });
    window.filterTable = function() { renderTable(); };
    submitBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const name = nameInp.value.trim();
        const year = yearInp.value.trim();
        const date = dateInp.value;
        const concern = concernInp.value.trim();
        const type = typeSelect.value;
        const referral = referralSelect.value;
        const priority = priorityYes.checked ? 'yes' : 'no';
        if (!name || !year || !date || !concern) { alert('Please fill all fields'); return; }
        const newEntry = { id: Date.now(), name, year, date, concern, type, priority, referral, archived: false };
        sessions.push(newEntry);
        persist();
        nameInp.value = ''; yearInp.value = ''; concernInp.value = ''; typeSelect.value = 'Priority guidance'; referralSelect.value = 'Self'; priorityYes.checked = true; dateInp.value = new Date().toISOString().slice(0,10);
        renderTable();
    });
    window.archiveSession = function(id) {
        if (confirm('Move this session to archive?')) {
            const session = sessions.find(s => s.id === id);
            if (session) { session.archived = true; persist(); renderTable(); }
        }
    };
    window.restoreSession = function(id) {
        if (confirm('Restore this session to active records?')) {
            const session = sessions.find(s => s.id === id);
            if (session) { session.archived = false; persist(); renderTable(); }
        }
    };
    window.editSession = function(id) {
        const session = sessions.find(s => s.id === id);
        if (!session) return;
        document.getElementById('editId').value = session.id;
        document.getElementById('editName').value = session.name;
        document.getElementById('editYear').value = session.year;
        document.getElementById('editDate').value = session.date;
        document.getElementById('editConcern').value = session.concern;
        document.getElementById('editType').value = session.type;
        document.getElementById('editReferral').value = session.referral;
        if (session.priority === 'yes') document.getElementById('editPriorityYes').checked = true;
        else document.getElementById('editPriorityNo').checked = true;
        document.getElementById('editModal').style.display = 'block';
    };
    window.saveEdit = function() {
        const id = parseInt(document.getElementById('editId').value);
        const session = sessions.find(s => s.id === id);
        if (!session) return;
        session.name = document.getElementById('editName').value.trim();
        session.year = document.getElementById('editYear').value.trim();
        session.date = document.getElementById('editDate').value;
        session.concern = document.getElementById('editConcern').value.trim();
        session.type = document.getElementById('editType').value;
        session.referral = document.getElementById('editReferral').value;
        session.priority = document.getElementById('editPriorityYes').checked ? 'yes' : 'no';
        persist();
        closeEditModal();
        renderTable();
    };
    window.closeEditModal = function() { document.getElementById('editModal').style.display = 'none'; };
    window.onclick = function(e) { if (e.target.classList.contains('modal')) closeEditModal(); };
    renderTable();
})();