 document.addEventListener('DOMContentLoaded', function () {

    const editBtn = document.getElementById('editBtn');
    const form = document.getElementById('updateStudentForm');


    editBtn.addEventListener('click',function(){

    const editStudentInfoModal = new bootstrap.Modal(document.getElementById('editStudentInfo'));
 
    editStudentInfoModal.show();

   });




   loadDocuments(applicantId);

    if (!form) {
        return;
    }

    

    form.addEventListener('submit', async function (e) {

        e.preventDefault();

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(form);

        try {

            const response = await fetch(`${BASE_URL}/students/update`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if(result.status == 'success')
            {

                
              const modalElement = document.getElementById('editStudentInfo');
               const modal = bootstrap.Modal.getInstance(modalElement);

                if (modal) {
                    modal.hide();
                }

                Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: result.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                location.reload();
            }); 


            }else{

                 Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Unable to update student information.',
                confirmButtonColor: '#dc3545'
            });
            }

        } catch (error) {

            console.error('Update student error:', error);

            alert(error.message || 'Something went wrong.');

        }

    });

   });





function loadDocuments(applicantId) {
    const tbody = document.getElementById("students-documents-table-body");
    const countBadge = document.getElementById("doc-count-badge");

    
    tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading requirements...
            </td>
        </tr>
    `;

    fetch(`${BASE_URL}/enrollees/${applicantId}/allDocs`)
        .then(response => {
            if (!response.ok) throw new Error("Failed to load records");
            return response.json();
        })
        .then(documents => {
            if (countBadge) countBadge.textContent = `${documents.length} Requirements`;

            if (!documents || documents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No requirements found.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = documents.map(doc => {
                // Evaluates your payload's "is_submitted": 1
                const isSubmitted = Number(doc.is_submitted) === 1;

                const badgeClass = isSubmitted 
                    ? "bg-success-subtle text-success-emphasis border border-success-subtle" 
                    : "bg-warning-subtle text-warning-emphasis border border-warning-subtle";
                
                const badgeLabel = isSubmitted ? "Submitted" : "Pending";
                const badgeIcon = isSubmitted ? "bi-check-circle" : "bi-clock-history";

                const disabledAttr = !isSubmitted ? 'disabled tabindex="-1" aria-disabled="true"' : '';
                const btnDisabledClass = !isSubmitted ? 'disabled opacity-50' : '';

                
                const requirementTitle = doc.requirement_name || `Requirement ID: ${doc.requirement_id}`;

                return `
                    <tr>
                        <!-- Requirement Info -->
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">${requirementTitle}</div>
                            ${doc.notes ? `<small class="text-muted d-block mt-1">${doc.notes}</small>` : ''}
                        </td>

                        <!-- Submission Status -->
                        <td>
                            <span class="badge ${badgeClass} rounded-pill px-2 py-1">
                                <i class="bi ${badgeIcon} me-1"></i>${badgeLabel}
                            </span>
                        </td>

                        <!-- Date Submitted -->
                        <td class="text-muted small">
                            ${isSubmitted && doc.submitted_date ? doc.submitted_date : '<span class="text-body-tertiary">—</span>'}
                        </td>

                        <!-- Actions using student_requirement_id -->
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="${doc.doc_path || '#'}"
                                   data-lightbox="doc-${doc.student_requirement_id}"
                                   data-title="${requirementTitle}"
                                   class="btn btn-outline-primary ${btnDisabledClass}"
                                   ${disabledAttr}>
                                    <i class="bi bi-eye me-1"></i>View
                                </a>

                           

                            
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        })
        .catch(error => {
            console.error("Error loading documents:", error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-octagon me-1"></i> Error loading requirements.
                    </td>
                </tr>
            `;
        });
}