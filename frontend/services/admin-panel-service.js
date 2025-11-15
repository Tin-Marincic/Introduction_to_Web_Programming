let AdminPanelService = {
  loadServices: function () {
    RestClient.get("api/services", function (services) {
      let rows = "";

      services.forEach(service => {
        let formattedPrice = service.name.toLowerCase().includes("ski škola")
          ? `KM ${service.price}`
          : `KM ${service.price} / sat`;

        rows += `
          <tr>
            <td>${service.name}</td>
            <td>${service.description || ""}</td>
            <td>${formattedPrice}</td>
            <td>
              <button class="btn btn-danger btn-sm" 
                onclick="AdminPanelService.editService(${service.id}, '${service.name.replace(/'/g, "\\'")}', decodeURIComponent('${encodeURIComponent(service.description || "")}'), '${service.price || ""}')">
                Edit
              </button>
              <button class="btn btn-outline-danger btn-sm" 
                onclick="AdminPanelService.deleteService(${service.id})">Obriši</button>
            </td>
          </tr>`;
      });

      $("#services-body").html(rows);
    }, function (err) {
      console.error("Failed to load services", err);
      $("#services-body").html("<tr><td colspan='4'>Nije moguće učitati usluge</td></tr>");
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
    if (!confirm("Jeste li sigurni da želite obrisati ovu uslugu?")) return;

    RestClient.delete(`api/services/${id}`, null, function () {
      toastr.success("Usluga obrisana");
      AdminPanelService.loadServices();
    }, function (err) {
      toastr.error("Brisanje usluge nije uspjelo.");
      console.error("Delete error", err);
    });
  },

  deleteBooking: function(id) {
  if (!confirm("Jeste li sigurni da želite obrisati ovu uslugu?")) return;

    RestClient.delete(`bookings/${id}`, {}, function(response) {
        toastr.success(response.message || "Rezervacija uspjesno obrisana.");
        AdminPanelService.loadInstructorBookings();
        AdminPanelService.loadSkiSchoolBookings();
    }, function(err) {
        toastr.error("Brisanje usluge nije uspjelo.");
        console.error("DELETE booking failed:", err);
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
              <button class="btn btn-danger btn-sm" onclick="AdminPanelService.editInstructor(${instructor.id})">Uredi</button>
              <button class="btn btn-outline-danger btn-sm" onclick="AdminPanelService.deleteInstructor(${instructor.id})">Obriši</button>
            </td>
          </tr>`;
      });

      $("#team-body").html(rows);
    }, function (error) {
      console.error("Failed to load instructors:", error);
      $("#team-body").html("<tr><td colspan='3'>Nije moguće učitati članove tima.</td></tr>");
    });
  },


  deleteInstructor: function (id) {
    if (!confirm("Jeste li sigurni da želite obrisati ovu uslugu?")) return;

    RestClient.delete(`instructors/${id}`, null, function () {
      toastr.success("Instruktor obrisan");
      AdminPanelService.loadInstructors();
    }, function (error) {
      toastr.error("Neuspješno brisanje instruktora.");
      console.error(error);
    });
  },
loadInstructorBookings: function () {
    RestClient.get("bookings/detailed", function (data) {
        let html = "";

        for (const instructorName in data) {
            const bookings = data[instructorName];
            if (!Array.isArray(bookings) || bookings.length === 0) continue;

            let rows = "";

            bookings.forEach(booking => {   // <--- USE booking NOT b
                rows += `
                    <tr>
                        <td>${booking.client_name}</td>
                        <td>${booking.client_phone || '-'}</td>
                        <td>${booking.date}</td>
                        <td>${booking.start_time}</td>
                        <td>${booking.session_type}</td>
                        <td>${booking.num_of_hours}h</td>
                        <td>${booking.status}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
                                onclick="AdminPanelService.deleteBooking(${booking.booking_id})">
                                Obriši
                            </button>
                        </td>
                    </tr>`;
            });

            html += `
                <div class="instructor-booking">
                    <h3>${instructorName}</h3>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Klijent</th>
                            <th>Telefon</th>
                            <th>Datum</th>
                            <th>Vrijeme</th>
                            <th>Lekcija</th>
                            <th>Trajanje</th>
                            <th>Status</th>
                            <th>Akcije</th>
                        </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        }

        $("#booking-tables").html(html);
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
      $("#availability-table tbody").html("<tr><td colspan='2'>Nije moguće učitati dostupnost.</td></tr>");
    });
  },

  // ✅ New method to load grouped Ski School bookings (for admin dashboard)
loadSkiSchoolBookings: function () {
    RestClient.get("bookings/ski-school-bookings", function (data) {
        let html = "";

        const sortedWeeks = Object.keys(data).sort((a, b) => {
            const numA = parseInt(a.replace(/\D/g, "")) || 0;
            const numB = parseInt(b.replace(/\D/g, "")) || 0;
            return numA - numB;
        });

        sortedWeeks.forEach(week => {
            const bookings = data[week];

            bookings.sort((a, b) =>
                (a.age_group || "").localeCompare(b.age_group || "")
            );

            let rows = "";

            bookings.forEach(b => {
                rows += `
                    <tr>
                        <td>${b.user_name || ""} ${b.user_surname || ""}</td>
                        <td>${b.phone_number || "-"}</td>
                        <td>${b.child_first_name || ""} ${b.child_last_name || ""}</td>
                        <td>${b.age_group || "-"}</td>
                        <td>${b.ski_level || "-"}</td>
                        <td>${b.allergies || "-"}</td>
                        <td>${b.is_vegetarian ? "Yes" : "No"}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
                                onclick="AdminPanelService.deleteBooking(${b.booking_id})">
                                Obriši
                            </button>
                        </td>
                    </tr>`;
            });

            html += `
                <div class="ski-school-week mb-5">
                    <h3>${week}</h3>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ime roditelja/osobe </th>
                                <th>Telefon</th>
                                <th>Ime djeteta/osobe</th>
                                <th>Dobna grupa</th>
                                <th>Nivo skijanja</th>
                                <th>Alergije</th>
                                <th>Vegetarijanac</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
        });

        $("#ski-school-bookings").html(html);

    }, function (err) {
        console.error("Failed to load ski school bookings", err);
        $("#booking-tables").append("<p>Greška pri učitavanju rezervacija za ski školu.</p>");
    });
},

cancelRange: function () {
    let start = $("#cancel-start").val();
    let end = $("#cancel-end").val();

    if (!start || !end) {
        toastr.error("Molimo odaberite oba datuma.");
        return;
    }

    if (!confirm(`Jeste li sigurni da želite otkazati sve rezervacije od… ${start} do ${end}?`)) return;

    RestClient.delete("bookings/range", { start_date: start, end_date: end }, 
        function (response) {
            toastr.success(response.message);
            AdminPanelService.loadInstructorBookings();
            AdminPanelService.loadSkiSchoolBookings();
        },
        function (err) {
            toastr.error("Greška pri otkazivanju rezervacija.");
            console.error(err);
        }
    );
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