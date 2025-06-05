let AdminPanelService = {
  loadServices: function () {
    RestClient.get("api/services", function (services) {
      let rows = "";

      services.forEach(service => {
        let formattedPrice = service.name.toLowerCase().includes("ski school")
          ? `KM ${service.price}`
          : `KM ${service.price} / hour`;

        rows += `
          <tr>
            <td>${service.name}</td>
            <td>${service.description || ""}</td>
            <td>${formattedPrice}</td>
            <td>
              <button class="btn btn-danger btn-sm" 
                onclick="AdminPanelService.editService(${service.id}, '${service.name.replace(/'/g, "\\'")}', '${(service.description || "").replace(/'/g, "\\'")}', ${service.price})">
                Edit
              </button>
              <button class="btn btn-outline-danger btn-sm" 
                onclick="AdminPanelService.deleteService(${service.id})">Delete</button>
            </td>
          </tr>`;
      });

      $("#services-body").html(rows);
    }, function (err) {
      console.error("Failed to load services", err);
      $("#services-body").html("<tr><td colspan='4'>Failed to load services</td></tr>");
    });
  },

  editService: function (id, name, description, price) {
    $("#edit-service-id").val(id);
    $("#edit-service-name").val(name);
    $("#edit-service-description").val(description);
    $("#edit-service-price").val(price);

    const modal = new bootstrap.Modal(document.getElementById("editServiceModal"));
    modal.show();
  },

  deleteService: function (id) {
    if (!confirm("Are you sure you want to delete this service?")) return;

    RestClient.delete(`api/services/${id}`, null, function () {
      toastr.success("Service deleted.");
      AdminPanelService.loadServices();
    }, function (err) {
      toastr.error("Failed to delete service.");
      console.error("Delete error", err);
    });
  },

  loadInstructors: function () {
    RestClient.get("users/instructor", function (instructors) {
      let rows = "";

      instructors.forEach(instructor => {
        rows += `
          <tr>
            <td>${instructor.name} ${instructor.surname}</td>
            <td>${instructor.licence || "-"}</td>
            <td>
              <button class="btn btn-danger btn-sm" onclick="AdminPanelService.editInstructor(${instructor.id})">Edit</button>
              <button class="btn btn-outline-danger btn-sm" onclick="AdminPanelService.deleteInstructor(${instructor.id})">Delete</button>
            </td>
          </tr>`;
      });

      $("#team-body").html(rows);
    }, function (error) {
      console.error("Failed to load instructors:", error);
      $("#team-body").html("<tr><td colspan='3'>Unable to load team members.</td></tr>");
    });
  },


  deleteInstructor: function (id) {
    if (!confirm("Are you sure you want to delete this instructor?")) return;

    RestClient.delete(`instructors/${id}`, null, function () {
      toastr.success("Instructor deleted");
      AdminPanelService.loadInstructors();
    }, function (error) {
      toastr.error("Failed to delete instructor.");
      console.error(error);
    });
  },

  loadInstructorBookings: function () {
    RestClient.get("bookings/detailed", function (data) {
      let html = "";

      for (const instructorName in data) {
        if (!Array.isArray(data[instructorName])) continue;

        const bookings = data[instructorName];
        let rows = "";

      bookings.forEach(booking => {
        rows += `
          <tr>
            <td>${booking.client_name}</td>
            <td>${booking.client_phone || '-'}</td> 
            <td>${booking.date}</td>
            <td>${booking.start_time}</td>
            <td>${booking.session_type}</td>
            <td>${booking.num_of_hours}h</td>
            <td>${booking.status}</td>
          </tr>`;
      });


        html += `
          <div class="instructor-booking">
            <h3>${instructorName}</h3>
            <table class="table table-striped">
              <thead>
              <tr>
                <th>Client Name</th>
                <th>Phone</th> 
                <th>Date</th>
                <th>Time</th>
                <th>Lesson Type</th>
                <th>Duration</th>
                <th>Status</th>
              </tr>
            </thead>
              <tbody>${rows}</tbody>
            </table>
          </div>`;
      }

      $("#booking-tables").html(html);
    }, function (error) {
      console.error("Failed to load instructor bookings", error);
      $("#booking-tables").html("<p>Error loading instructor bookings</p>");
    });
  },

  loadSkiSchoolAvailability: function () {
    RestClient.get("bookings/ski-school", function (data) {
      let rows = "";

      data.forEach(entry => {
        rows += `
          <tr>
            <td>${entry["Week"]}</td>
            <td>${entry["Available Spots"]}</td>
          </tr>`;
      });

      $("#availability-table tbody").html(rows);
    }, function (err) {
      console.error("Failed to load ski school availability", err);
      $("#availability-table tbody").html("<tr><td colspan='2'>Unable to load availability</td></tr>");
    });
  },

openAddInstructorModal: function () {
  $("#add-instructor-form")[0].reset();
  const modal = new bootstrap.Modal(document.getElementById("addInstructorModal"));
  modal.show();
},

editInstructor: function (id, licence) {
  $("#edit-instructor-id").val(id);
  $("#edit-instructor-licence").val(licence);

  const modal = new bootstrap.Modal(document.getElementById("editInstructorModal"));
  modal.show();
}




};