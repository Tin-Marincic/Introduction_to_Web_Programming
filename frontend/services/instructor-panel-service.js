let InstructorPanelService = {
  loadHeader: function () {
    const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
    if (!user) return;

    const headerHtml = `
      <img src="assets/img/team/default.jpg" alt="Instructor Photo" class="profile-pic">
      <div>
        <h2>Welcome, ${user.name} ${user.surname}</h2>
        <p>${user.licence || "Certified Instructor"}</p>
      </div>`;
    $("#instructor-header").html(headerHtml);
  },

  loadBookings: function () {
    const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
    if (!user) return;

    RestClient.get(`bookings/instructor/${user.id}/upcoming`, function (data) {
      let rows = "";
      let totalHours = 0;

      data.forEach(b => {
        totalHours += b.num_of_hours || 0;
        rows += `
          <tr>
            <td>${b.client_name}</td>
            <td>${b.date}</td>
            <td>${b.start_time}</td>
            <td>${b.session_type}</td>
            <td>${b.num_of_hours}h</td>
            <td><span class="status ${b.status.toLowerCase()}">${b.status}</span></td>
            <td>
              <button class="btn btn-success btn-sm" onclick="InstructorPanelService.markComplete(${b.id})">Mark Complete</button>
              <button class="btn btn-outline-danger btn-sm" onclick="InstructorPanelService.cancel(${b.id})">Cancel</button>
            </td>
          </tr>`;
      });

      $("#instructor-bookings-table tbody").html(rows);
      $("#total-hours").text(totalHours);
      $("#upcoming-bookings-count").text(data.length);
    });
  },

loadTotalHours: function () {
  const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
  if (!user) return;

  RestClient.get(`bookings/instructor/${user.id}/hours`,
    function (data) {
      $("#total-hours").text(data.total_hours || 0);
    },
    function (error) {
      console.error("Failed to load total hours:", error);
      $("#total-hours").text("0");
    });
  },

  markComplete: function (id) {
    RestClient.patch(`bookings/${id}`, { status: "completed" }, function () {
      toastr.success("Booking marked as complete");
      InstructorPanelService.loadBookings();
      InstructorPanelService.loadSummaryStats();
    });
  },

  cancel: function (id) {
    RestClient.patch(`bookings/${id}`, { status: "cancelled" }, function () {
      toastr.success("Booking cancelled");
      InstructorPanelService.loadBookings();
      InstructorPanelService.loadSummaryStats();
    });
  },

initAvailability: function () {
  const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
  if (!user) return;

  const selectedDays = new Set();
  const existingAvailabilities = {};
  const bookedDates = new Set();

  RestClient.get(`availability/instructor/${user.id}`, function (availabilities) {
    availabilities.forEach(av => {
      existingAvailabilities[av.date] = av;
      if (av.status === "active") {
        const day = new Date(av.date).toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
        $(`.day-toggle[data-day="${day}"]`).addClass("available");
        selectedDays.add(day);
      }
    });


    RestClient.get(`bookings/instructor/${user.id}/upcoming`, function (bookings) {
      bookings.forEach(b => bookedDates.add(b.date));

      $(".day-toggle").off("click").on("click", function () {
        const day = $(this).data("day");
        const date = InstructorPanelService.getNextDateFor(day);

        if ($(this).hasClass("available")) {
          if (bookedDates.has(date)) {
            toastr.error(`Can't remove ${day}: a booking exists.`);
            return;
          }
          $(this).removeClass("available");
          selectedDays.delete(day);
        } else {
          $(this).addClass("available");
          selectedDays.add(day);
        }
      });

      $(".save-availability").off("click").on("click", function () {
        const button = $(this);
        button.prop("disabled", true).text("Saving...");
        const days = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];
        let completed = 0;

        const finish = () => {
          completed++;
          if (completed === days.length) {
            toastr.success("Availability updated");
            button.prop("disabled", false).text("Save Availability");

            $(".day-toggle").removeClass("available");
            InstructorPanelService.initAvailability();
          }
        };

        days.forEach(day => {
          const date = InstructorPanelService.getNextDateFor(day);
          const isSelected = selectedDays.has(day);
          const availability = existingAvailabilities[date];

          if (isSelected && !availability) {
            RestClient.post("availability", {
              instructor_id: user.id,
              date: date,
              status: "active"
            }, finish, finish);
          } else if (isSelected && availability && availability.status === "not_active") {
            RestClient.put(`availability/${availability.id}`, {
              date: date,
              status: "active"
            }, finish, finish);
          } else if (!isSelected && availability && availability.status === "active") {
            if (bookedDates.has(date)) {
              console.warn(`Cannot deactivate ${day}: already booked`);
              finish();
            } else {
              RestClient.put(`availability/${availability.id}`, {
                date: date,
                status: "not_active"
              }, finish, finish);
            }
          } else {
            finish(); 
          }
        });
      });
    });
  });
},


getNextDateFor: function (weekday) {
  const dayIndex = ["sunday", "monday", "tuesday", "wednesday", "thursday", "friday", "saturday"].indexOf(weekday);
  const today = new Date();

  const endOfNextWeek = new Date(today);
  const daysUntilNextSunday = 7 - today.getDay() + 7;
  endOfNextWeek.setDate(today.getDate() + daysUntilNextSunday);

  for (let d = new Date(today); d <= endOfNextWeek; d.setDate(d.getDate() + 1)) {
    if (d.getDay() === dayIndex) {
      return d.toISOString().split("T")[0];
    }
  }

  return null; 
}


};
