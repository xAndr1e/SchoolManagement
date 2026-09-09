  
   let filters = {
    course:'',
    year_level:''
    };
  
  
  
  document.addEventListener('DOMContentLoaded', function() {


    const params = new URLSearchParams(window.location.search);
    const examId = params.get('exam_id');

    let currentStatus = 'all';
    let currentLimit = 10;
    let currentPage = 1;



    getData(currentStatus, currentLimit, currentPage ,examId);

    document.getElementById("status").addEventListener('change', (e) => {

        currentStatus = e.target.value;
        currentPage = 1;
        getData(currentStatus, currentLimit,currentPage, examId);

    });


    
    document.getElementById("limit").addEventListener('change', (e) => {


        currentLimit = e.target.value;
        currentPage = 1; 

        getData(currentStatus, currentLimit, currentPage, examId);

    });


    document.getElementById("search").addEventListener("input", function(e) {

    const status = document.getElementById("status").value;
    const limit = document.getElementById("limit").value;
    

    getData(status, limit, 1,examId); 

    });




    const schoolYearLevelSelect = document.getElementById('filter_year_level');
    const courseSelect = document.getElementById('filter_course');

    const defaultCourseSelect = courseSelect.value;
    const defaultSchoolYearLevelSelect = schoolYearLevelSelect.value;



    filterBtn.addEventListener('click',function(){
  
        modal.show();
    
    });


    
    document.getElementById('resetFilter').addEventListener('click', function() {
    
    filters.course = courseSelect.value = defaultCourseSelect;
    filters.year_level = schoolYearLevelSelect.value = defaultSchoolYearLevelSelect;

    

    updateFilterBadge();
    
     getData(currentStatus, currentLimit, currentPage,examId);
  
     modal.hide();
         
    }) 


     document.getElementById("applyFilter").addEventListener("click", () => {

    filters.course = courseSelect.value;
    filters.year_level = schoolYearLevelSelect.value;
    
    updateFilterBadge();

    getData(currentStatus, currentLimit,currentPage,examId);
  
    modal.hide();

   });




    document.getElementById('pdf').addEventListener('click',()=>{

        const status = document.getElementById("status").value;
       getPdf(status);

    });

    document.getElementById('excel').addEventListener('click',()=>{

       const status = document.getElementById("status").value;
       getExcel(status);

    });

    document.getElementById('csv').addEventListener('click',()=>{

       const status = document.getElementById("status").value;
       getCsv(status);

    });



    // buttons  

    document.getElementById("studentsTableBody").addEventListener("click", function(e) {

    if (e.target.classList.contains("view-btn")) {

        const studentId = e.target.dataset.id;

        alert(studentId);
  
    }
    });




    document.addEventListener("click", function (e) {


    const modalElement = document.getElementById('sectionModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);


    const button = e.target.closest(".view-section-btn");

    if (!button) return;

    e.preventDefault();


   

    const examId = button.dataset.examId;
    const examDate = button.dataset.examDate;

    loadSections(examId, examDate);

     modal.show();
     });


     const modalElement = document.getElementById("sectionModal");

    modalElement.addEventListener("hidden.bs.modal", function () {
    const modalElement = document.getElementById("sectionModal");

    modalElement.addEventListener("hidden.bs.modal", function () {

        document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
            backdrop.remove();
        });

        document.body.classList.remove("modal-open");
        document.body.style.removeProperty("padding-right");
        document.body.style.removeProperty("overflow");

    });
   });





    });

     /*  ========================================================================================= 
       |                                                                                         |
       |      End of Dom Content                                                                 |
       |                                                                                         |
       ========================================================================================= 
    */

    function updateFilterBadge() {

    const badge = document.getElementById("filterBadge");

    let count = 0;

    if (filters.year_level) count++;
    if(filters.course) count++;
    if (count > 0) {
        badge.classList.remove("d-none");
        badge.textContent = count;
    } else {
        badge.classList.add("d-none");
    }   
    }

    // pdf data 

    function getPdf(status)
    {
        const search = document.getElementById("search").value;

        //  fetch(`${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`)
        // .then(response => response.blob())
        // .then(blob => {
        //     const url = URL.createObjectURL(blob);
        //     window.open(url); 
        // })
        // .catch(err => console.error(err));
        

        // take this if you dont want redirection confirmation 

        window.open(
        `${BASE_URL}/students/pdf?status=${status}&search=${encodeURIComponent(search)}`,
        "_blank"
        );
    }


    function getExcel(status)
    {

     
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/students/excel?status=${status}&search=${encodeURIComponent(search)}`;

    }


    function getCsv(status)
    {
        const search = document.getElementById("search").value;
        window.location.href = `${BASE_URL}/students/csv?status=${status}&search=${encodeURIComponent(search)}`;
    }


    // get data from students 

    function getData(status, limit, page = 1,examId){

    
    const search = document.getElementById("search").value;
    const tbody = document.getElementById("studentsTableBody");

    fetch(`${BASE_URL}/examinations/${examId}?status=${status}&limit=${limit}&page=${page}&search=${encodeURIComponent(search)}&year_level=${filters.year_level}&course=${filters.course}`)
        .then(response => response.json())
        .then(result => {

            tbody.innerHTML = "";

            if (result.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="10" class="text-center">No students found</td></tr>`;
                return;
            }

            result.data.forEach(exam => {
                tbody.innerHTML += `
                    <tr class="student-row">
                        <td> Day ${exam.day}</td>
                        <td>${exam.exam_name}</td>
                        <td> ${exam.exam_type}</td>
                        <td> ${exam.exam_date}</td>
                        <td> ${exam.status}</td>
                        <td>
                            
                             <div class="dropdown">
        <button class="btn btn-sm btn-primary dropdown-toggle"
               data-bs-toggle="dropdown"
               data-bs-strategy="fixed">
            Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item view-section-btn"
                    href="#"
                    data-exam-id="${exam.exam_id}"
                    data-exam-date="${exam.exam_date}">
                        View Details
                    </a>
                </li>

            <li>
                <a class="dropdown-item" onclick="getPdf(${exam.exam_id}); return false;" target="_blank">
                    Export to PDF
                </a>
            </li>
        </ul>
    </div>
                            
                        </td>
                    </tr>
                `;
            });

            const rows = tbody.querySelectorAll(".student-row");

            if (rows.length > 0) {
                gsap.from(rows, {
                x: -200,          
                opacity: 0,       
                duration: 0.5,
                stagger: 0.3,      
                ease: "power2.out" 
                });
            }

            renderPagination(result.current_page, result.last_page);
            renderResultInfo(result);

        });
}


function renderPagination(current, last) {

        
    const container = document.getElementById("pagination");
    container.innerHTML = "";

    const ul = document.createElement("ul");
    ul.className = "pagination";

    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);



    if (current > 1) {
        const li = document.createElement("li");
        li.className = "page-item";

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.innerHTML = "&laquo;";

        link.addEventListener("click", function(e){
            e.preventDefault();

            const status = document.getElementById("status").value;
            const limit = document.getElementById("limit").value;
            getData(status, limit, current - 1,examId);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }



    for (let i = start; i <= end; i++) {

        const li = document.createElement("li");
        li.className = "page-item " + (i === current ? "active" : "");

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.textContent = i;

        link.addEventListener("click", function(e) {
            e.preventDefault();

            const status = document.getElementById("status").value;
            const limit = document.getElementById("limit").value;

            getData(status, limit, i,examId);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }

    if (current < last) {
        const li = document.createElement("li");
        li.className = "page-item";

        const link = document.createElement("a");
        link.className = "page-link";
        link.href = "#";
        link.innerHTML = "&raquo;";

        link.addEventListener("click", function(e){
            e.preventDefault();

            const status = document.getElementById("status").value;
                const limit = document.getElementById("limit").value;
            getData(status, limit, current + 1,examId);
        });

        li.appendChild(link);
        ul.appendChild(li);
    }


    container.appendChild(ul);


    
}


function renderResultInfo(result) {

    const info = document.getElementById("pageInfo");

    if (result.total === 0) {
        info.textContent = "No results found";
        return;
    }

    const start = (result.current_page - 1) * result.data.length + 1;
    let end = result.current_page * result.data.length;

    if (end > result.total) {
        end = result.total;
    }

    info.textContent = `Showing ${start}–${end} of ${result.total} results`;
}


function loadSections(examId, examDate) {

    const loading = document.getElementById("sectionLoading");
    const sectionList = document.getElementById("sectionList");
    const modalDate = document.getElementById("modalExamDate");

    modalDate.textContent = examDate;

    loading.classList.remove("d-none");
    sectionList.classList.add("d-none");

    sectionList.innerHTML = "";

    fetch(
        `${BASE_URL}/exam-schedules/section?exam_id=${examId}&exam_date=${examDate}`
    )
    .then(response => response.json())
    .then(result => {

        console.log(result);

        loading.classList.add("d-none");
        sectionList.classList.remove("d-none");

        if (result.length === 0) {

            sectionList.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-secondary text-center">
                        No sections found for this day.
                    </div>
                </div>
            `;

            return;
        }

        result.forEach(section => {

            sectionList.innerHTML += `
                <div class="col-md-6">

                    <div class="card h-100">
                        <div class="card-body">

                            <h5 class="card-title mb-1">
                                ${section.section_code}
                            </h5>

                            <p class="text-muted mb-3">
                                Section
                            </p>

                            <a
                                href="${BASE_URL}/exam-schedules?section_id=${section.section_id}&exam_date=${examDate}&exam_id=${examId}"
                                class="btn btn-primary btn-sm view-schedule-btn"
                                  target="_blank" >
                                View Schedule
                            </a>

                        </div>
                    </div>

                </div>
            `;

        });

    })
    .catch(error => {

        console.error(error);

        loading.classList.add("d-none");
        sectionList.classList.remove("d-none");

        sectionList.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    Failed to load sections.
                </div>
            </div>
        `;
    });
}







