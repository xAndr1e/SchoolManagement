document.addEventListener("submit", function (e) {
  if (!e.target.matches("#addScheduleForm")) {
    return;
  }

  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  fetch(`${BASE_URL}/schedule/create`, {
    method: "POST",
    body: formData,
  })
    .then(async (response) => {
      const text = await response.text();

      console.log("HTTP STATUS:", response.status);
      console.log("SERVER RESPONSE:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        throw new Error("Server returned invalid JSON:\n\n" + text);
      }

      if (!response.ok) {
        throw new Error(data.error || "Server error.");
      }

      return data;
    })
    .then((data) => {
      console.log("DATA:", data);

      if (data.success) {
        alert("Schedule added successfully!");

        location.reload();
      } else {
        alert(data.error || "Failed to add schedule.");
      }
    })
    .catch((error) => {
      console.error("CREATE SCHEDULE ERROR:", error);

      alert(error.message);
    });
});

// ==========================================
// EDIT SCHEDULE - LOAD DATA
// ==========================================

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".editBtn");

  if (!btn) {
    return;
  }

  e.preventDefault();

  const id = btn.dataset.id;

  console.log("Editing Schedule ID:", id);

  // ==========================================
  // GET SCHEDULE DATA
  // ==========================================

  fetch(`${BASE_URL}/schedule/view/${id}`)
    .then(async (response) => {
      const text = await response.text();

      console.log("HTTP STATUS:", response.status);
      console.log("SERVER RESPONSE:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        throw new Error("Server returned invalid JSON:\n\n" + text);
      }

      if (!response.ok) {
        throw new Error(data.error || "Failed to load schedule.");
      }

      return data;
    })

    // ==========================================
    // PUT DATA INTO EDIT FORM
    // ==========================================

    .then((data) => {
      console.log("Schedule data:", data);

      document.getElementById("edit_schedule_id").value = data.schedule_id;

      document.getElementById("edit_lab_id").value = data.lab_id;

      document.getElementById("edit_subject_code").value = data.subject_code;

      document.getElementById("edit_subject_name").value = data.subject_name;

      document.getElementById("edit_instructor").value = data.instructor;

      document.getElementById("edit_section").value = data.section;

      document.getElementById("edit_day").value = data.day;

      document.getElementById("edit_start_time").value = data.start_time;

      document.getElementById("edit_end_time").value = data.end_time;

      document.getElementById("edit_semester").value = data.semester;

      document.getElementById("edit_school_year").value = data.school_year;

      document.getElementById("edit_status").value = data.status;

      document.getElementById("edit_remarks").value = data.remarks || "";

      // ==========================================
      // SHOW EDIT MODAL
      // ==========================================

      const modalElement = document.getElementById("scheduleEditModal");

      if (!modalElement) {
        throw new Error("scheduleEditModal not found.");
      }

      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

      modal.show();
    })

    .catch((error) => {
      console.error("EDIT SCHEDULE ERROR:", error);

      alert(error.message);
    });
});

// ==========================================
// UPDATE SCHEDULE
// ==========================================

document.addEventListener("submit", function (e) {
  if (!e.target.matches("#editScheduleForm")) {
    return;
  }

  e.preventDefault();

  const form = e.target;

  const id = document.getElementById("edit_schedule_id").value;

  if (!id) {
    alert("Schedule ID is missing.");

    return;
  }

  const formData = new FormData(form);

  console.log("Updating Schedule ID:", id);

  // ==========================================
  // SEND UPDATE REQUEST
  // ==========================================

  fetch(`${BASE_URL}/schedule/update/${id}`, {
    method: "POST",

    body: formData,
  })
    .then(async (response) => {
      const text = await response.text();

      console.log("UPDATE HTTP STATUS:", response.status);

      console.log("UPDATE SERVER RESPONSE:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        throw new Error("Server returned invalid JSON:\n\n" + text);
      }

      if (!response.ok) {
        throw new Error(
          data.error || data.message || "Failed to update schedule.",
        );
      }

      return data;
    })

    // ==========================================
    // UPDATE RESULT
    // ==========================================

    .then((data) => {
      console.log("UPDATE DATA:", data);

      if (data.success) {
        alert("Schedule updated successfully!");

        location.reload();
      } else {
        alert(data.error || data.message || "Failed to update schedule.");
      }
    })

    .catch((error) => {
      console.error("UPDATE SCHEDULE ERROR:", error);

      alert(error.message);
    });
});

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".viewBtn");

  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;

  console.log("Viewing schedule ID:", id);

  fetch(`${BASE_URL}/schedule/view/${id}`)
    .then(async (response) => {
      const text = await response.text();

      console.log("HTTP STATUS:", response.status);
      console.log("SERVER RESPONSE:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        throw new Error("Server returned invalid JSON:\n\n" + text);
      }

      if (!response.ok) {
        throw new Error(data.error || "Failed to load schedule.");
      }

      return data;
    })

    .then((data) => {
      console.log("Schedule data:", data);

      document.getElementById("view_laboratory_name").value =
        data.laboratory_name || data.lab_id || "";

      document.getElementById("view_subject_code").value =
        data.subject_code || "";

      document.getElementById("view_subject_name").value =
        data.subject_name || "";

      document.getElementById("view_instructor").value = data.instructor || "";

      document.getElementById("view_section").value = data.section || "";

      document.getElementById("view_day").value = data.day || "";

      document.getElementById("view_start_time").value = formatTime(
        data.start_time,
      );

      document.getElementById("view_end_time").value = formatTime(
        data.end_time,
      );

      document.getElementById("view_semester").value = data.semester || "";

      document.getElementById("view_school_year").value =
        data.school_year || "";

      document.getElementById("view_status").value = data.status || "";

      document.getElementById("view_remarks").value = data.remarks || "";

      const modalElement = document.getElementById("scheduleViewModal");

      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

      modal.show();
    })

    .catch((error) => {
      console.error("VIEW SCHEDULE ERROR:", error);

      alert(error.message);
    });
});

function formatTime(time) {
  if (!time) return "";

  const parts = time.split(":");

  let hours = parseInt(parts[0]);
  const minutes = parts[1];

  const ampm = hours >= 12 ? "PM" : "AM";

  hours = hours % 12;

  if (hours === 0) {
    hours = 12;
  }

  return `${hours}:${minutes} ${ampm}`;
}

//delete schedule
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".deleteBtn");

  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;

  console.log("Deleting schedule ID:", id);

  if (!id) {
    alert("Schedule ID is missing.");
    return;
  }

  if (!confirm("Are you sure you want to delete this schedule?")) {
    return;
  }

  fetch(`${BASE_URL}/schedule/delete/${id}`, {
    method: "POST",
  })
    .then(async (response) => {
      const text = await response.text();

      console.log("HTTP STATUS:", response.status);
      console.log("SERVER RESPONSE:", text);

      let data;

      try {
        data = JSON.parse(text);
      } catch (error) {
        throw new Error("Server returned invalid JSON:\n\n" + text);
      }

      if (!response.ok) {
        throw new Error(data.error || "Failed to delete schedule.");
      }

      return data;
    })
    .then((data) => {
      console.log("DELETE RESPONSE:", data);

      if (data.success) {
        alert("Schedule deleted successfully!");

        location.reload();
      } else {
        alert(data.message || data.error || "Failed to delete schedule.");
      }
    })
    .catch((error) => {
      console.error("DELETE SCHEDULE ERROR:", error);

      alert(error.message);
    });
});
