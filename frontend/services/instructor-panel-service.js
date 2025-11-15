let InstructorPanelService = {

  loadHeader: function () {
    const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
    if (!user) return;

    const headerHtml = `
      <img src="assets/img/team/default.jpg" alt="Instructor Photo" class="profile-pic">
      <div>
        <h2>Dobrodošli, ${user.name} ${user.surname}</h2>
        <p>${user.licence || "Certifikovani Instruktor"}</p>
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
            <td>${b.phone || '-'}</td>
            <td>${b.date}</td>
            <td>${b.start_time}</td>
            <td>${b.session_type}</td>
            <td>${b.num_of_hours}h</td>
            <td><span class="status ${b.status.toLowerCase()}">${b.status}</span></td>
          </tr>`;
      });

      $("#instructor-bookings-table tbody").html(rows);
      $("#total-hours").text(totalHours);
      $("#upcoming-bookings-count").text(data.length);
    });
  },

  /** =============================
   *  GENERATE DATES FOR THIS WEEK
   *  ============================= */
  getCurrentWeekDates: function () {
    const today = new Date();
    const dayIndex = today.getDay(); // 0 = Sunday, 1 = Monday...

    // Convert JS Sunday=0 → Our index Monday=0
    const convert = {1:0,2:1,3:2,4:3,5:4,6:5,0:6};
    const todayMapped = convert[dayIndex];

    const dates = {};
    const weekdays = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];

    for (let i = todayMapped; i < 7; i++) {
      const d = new Date(today);
      d.setDate(today.getDate() + (i - todayMapped));
      dates[weekdays[i]] = d.toISOString().split("T")[0];
    }

    return dates;
  },

  /** =============================
   *  GENERATE DATES FOR NEXT WEEK
   *  ============================= */
  getNextWeekDates: function () {
    const today = new Date();
    const nextMonday = new Date(today);

    // find next Monday
    const jsDay = today.getDay(); // 0=Sun
    const daysUntilNextMonday = (8 - jsDay) % 7 || 7;
    nextMonday.setDate(today.getDate() + daysUntilNextMonday);

    const dates = {};
    const weekdays = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];

    for (let i = 0; i < 7; i++) {
      const d = new Date(nextMonday);
      d.setDate(nextMonday.getDate() + i);
      dates[weekdays[i]] = d.toISOString().split("T")[0];
    }

    return dates;
  },

  /** =============================
   *  MAIN AVAILABILITY LOGIC
   *  ============================= */
  initAvailability: function () {
    const user = Utils.parseJwt(localStorage.getItem("user_token"))?.user;
    if (!user) return;

    const weekdays = {
      monday: "Ponedjeljak",
      tuesday: "Utorak",
      wednesday: "Srijeda",
      thursday: "Cetvrtak",
      friday: "Petak",
      saturday: "Subota",
      sunday: "Nedjelja"
    };

    const thisWeek = this.getCurrentWeekDates();
    const nextWeek = this.getNextWeekDates();

    $(".this-week").empty();
    $(".next-week").empty();

    // render buttons for this week
    Object.keys(thisWeek).forEach(day => {
      $(".this-week").append(`
        <button class="day-toggle" data-day="${day}" data-date="${thisWeek[day]}">
          ${weekdays[day]}
        </button>
      `);
    });

    // render buttons for next week
    Object.keys(nextWeek).forEach(day => {
      $(".next-week").append(`
        <button class="day-toggle" data-day="${day}" data-date="${nextWeek[day]}">
          ${weekdays[day]}
        </button>
      `);
    });

    const existing = {};
    const bookedDates = new Set();

    // load availability
    RestClient.get(`availability/instructor/${user.id}`, availabilities => {
      availabilities.forEach(av => existing[av.date] = av);

      // load bookings
      RestClient.get(`bookings/instructor/${user.id}/upcoming`, bookings => {
        bookings.forEach(b => bookedDates.add(b.date));

        $(".day-toggle").each(function () {
          const date = $(this).data("date");
          if (existing[date] && existing[date].status === "active") {
            $(this).addClass("available");
          }
        });

        $(".day-toggle").off("click").on("click", function () {
          const date = $(this).data("date");

          // cannot turn off if booked
          if ($(this).hasClass("available") && bookedDates.has(date)) {
            toastr.error("Ne možete ukloniti dostupnost — imate rezervaciju na ovaj dan.");
            return;
          }

          $(this).toggleClass("available");
        });

        $(".save-availability").off("click").on("click", () => {
          const btn = $(".save-availability");
          btn.prop("disabled", true).text("Spasavanje...");

          const allButtons = $(".day-toggle");
          let done = 0;

          const finish = () => {
            done++;
            if (done === allButtons.length) {
              toastr.success("Dostupnost uspješno ažurirana.");
              InstructorPanelService.initAvailability();
              btn.prop("disabled", false).text("Spasite Dostupnost");
            }
          };

          allButtons.each(function () {
            const date = $(this).data("date");
            const isSelected = $(this).hasClass("available");
            const av = existing[date];

            if (isSelected && !av) {
              RestClient.post("availability", {
                instructor_id: user.id,
                date,
                status: "active"
              }, finish, finish);

            } else if (isSelected && av && av.status !== "active") {
              RestClient.put(`availability/${av.id}`, {
                date,
                status: "active"
              }, finish, finish);

            } else if (!isSelected && av && av.status === "active") {
              if (bookedDates.has(date)) {
                finish();
              } else {
                RestClient.put(`availability/${av.id}`, {
                  date,
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
  }
};
