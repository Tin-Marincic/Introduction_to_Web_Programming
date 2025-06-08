// Helper Reset Functions
function resetLevels() {
  document.getElementById("level-beginner").value = 0;
  document.getElementById("level-intermediate").value = 0;
  document.getElementById("level-advanced").value = 0;
}

function resetAgeGroups() {
  document.getElementById("age-child").value = 0;
  document.getElementById("age-teen").value = 0;
  document.getElementById("age-adult").value = 0;
}

function resetVegetarian() {
  document.getElementById("vegetarian-count").value = 0;
}

// Disable hidden fields to avoid "not focusable" errors
function disableHiddenFields() {
  const skiSchool = document.getElementById("skiSchoolOptions");
  const privateInstruction = document.getElementById("privateInstructionOptions");
  const sessionType = document.getElementById("sessionType").value;

  const disableElements = (container, shouldDisable) => {
    container.querySelectorAll("input, select, textarea").forEach(el => {
      el.disabled = shouldDisable;
    });
  };

  disableElements(skiSchool, sessionType !== "skiSchool");
  disableElements(privateInstruction, sessionType !== "privateInstruction");
}

// Main Booking Service
var BookingService = {
  init: function () {
    const form = document.getElementById("bookingForm");
    if (!form) return;

    form.reset();
    document.getElementById("startTime").innerHTML = '<option value="" disabled selected>Select a start time</option>';
    document.getElementById("hours").innerHTML = '<option value="" disabled selected>Select number of hours</option>';

    // Load instructors
    document.getElementById("sessionDate").addEventListener("change", function () {
    const selectedDate = this.value;
    if (!selectedDate) return;

    const instructorSelect = document.getElementById("instructor");
    instructorSelect.innerHTML = '<option value="" disabled selected>Loading instructors...</option>';

    RestClient.get(`availability/active?date=${selectedDate}`, function (ids) {
        RestClient.get("users/instructor", function (allInstructors) {
        instructorSelect.innerHTML = '<option value="" disabled selected>Select an instructor</option>';
        allInstructors.forEach(instructor => {
            if (ids.includes(instructor.id)) {
            const opt = document.createElement("option");
            opt.value = instructor.id;
            opt.text = `${instructor.name} ${instructor.surname}`;
            instructorSelect.appendChild(opt);
            }
        });
        });
    });
    });


    // Load services
    RestClient.get("api/services", function (services) {
      const serviceSelect = document.getElementById("service");
      if (!serviceSelect) return;
      if (!Array.isArray(services)) return;
      serviceSelect.innerHTML = '<option value="" disabled selected>Select a group size</option>';
      services.forEach(service => {
        if (service.name.includes("One on")) {
          const opt = document.createElement("option");
          opt.value = service.id;
          opt.text = service.name;
          serviceSelect.appendChild(opt);
        }
      });
    });

    $.validator.setDefaults({ ignore: [] }); //had to add this so the hidden fields dont get ignored by jquery

    form.onsubmit = function (e) {
      e.preventDefault();

      $("[name='ageValidationTrigger']").val("trigger").valid();
      $("[name='levelValidationTrigger']").val("trigger").valid();


      if (!$("#bookingForm").valid()) {
        return; 
  }

      const userId = parseInt(localStorage.getItem("user_id"));
      if (!userId || isNaN(userId)) {
        toastr.error(" You must be logged in to make a booking.");
        return;
      }

      const sessionType = document.getElementById("sessionType").value;

      if (sessionType === "skiSchool") {
        const data = {
          user_id: userId,
          service_id: 4,
          session_type: "Ski_school",
          num_of_spots: parseInt(document.getElementById("spots").value),
          week: document.getElementById("week").value,
          age_group_child: parseInt(document.getElementById("age-child").value),
          age_group_teen: parseInt(document.getElementById("age-teen").value),
          age_group_adult: parseInt(document.getElementById("age-adult").value),
          ski_level_b: parseInt(document.getElementById("level-beginner").value),
          ski_level_i: parseInt(document.getElementById("level-intermediate").value),
          ski_level_a: parseInt(document.getElementById("level-advanced").value),
          veg_count: parseInt(document.getElementById("vegetarian-count").value) || 0,
          other: document.getElementById("concerns").value || ""
        };

        RestClient.request("bookings/ski-school", "POST", data, function () {
          toastr.success("Ski School booking successful!");
          BookingService.init();
        }, function (error) {
          console.error("Ski School booking failed", error);
          toastr.success("Error creating Ski School booking.");
        });
      }

      if (sessionType === "privateInstruction") {
        const booking = {
          user_id: userId,
          instructor_id: parseInt(document.getElementById("instructor").value),
          service_id: parseInt(document.getElementById("service").value),
          session_type: "Private_instruction",
          date: document.getElementById("sessionDate").value,
          start_time: document.getElementById("startTime").value,
          num_of_hours: parseInt(document.getElementById("hours").value),
          status: "confirmed"
        };

        RestClient.request("bookings", "POST", booking, function () {
          toastr.success("Private instruction booking successful!");

          BookingService.init();
        }, function (error) {
          console.error("Private instruction booking failed", error);
          toastr.error("Error creating private instruction booking.");
        });
      }
    };

    document.getElementById("instructor").addEventListener("change", updateAvailableTimes);
    document.getElementById("sessionDate").addEventListener("change", updateAvailableTimes);
  }
};


function updateAvailableTimes() {
  const instructorId = document.getElementById("instructor").value;
  const date = document.getElementById("sessionDate").value;
  const startTimeSelect = document.getElementById("startTime");
  const hoursSelect = document.getElementById("hours");

  // Clear dropdowns
  startTimeSelect.innerHTML = '<option value="" disabled selected>Select a start time</option>';
  hoursSelect.innerHTML = '<option value="" disabled selected>Select number of hours</option>';

  // Remove any previous alert
  let existingAlert = document.getElementById("no-available-times");
  if (existingAlert) existingAlert.remove();

  if (!instructorId || !date) return;

  RestClient.get(`instructors/${instructorId}/bookings?date=${date}`, function (bookings) {
    const bookedSlots = [];

    bookings.forEach(b => {
      const startHour = parseInt(b.start_time.split(":")[0], 10);
      const endHour = startHour + b.num_of_hours;
      for (let hour = startHour; hour < endHour; hour++) {
        bookedSlots.push(hour);
      }
    });

    const allSlots = [10, 11, 12, 13, 14, 15];
    let hasAvailableSlot = false;

    const selectedDateObj = new Date(date);
    const today = new Date();
    const isToday = selectedDateObj.toDateString() === today.toDateString();
    const currentHour = today.getHours();


    allSlots.forEach(hour => {
      if (isToday && hour <= currentHour) return;
      const hourStr = hour < 10 ? `0${hour}` : `${hour}`;
      const value = `${hourStr}:00`;
      const label = hour < 12 ? `${hour}:00 AM` : `${hour === 12 ? 12 : hour - 12}:00 PM`;

      const opt = document.createElement("option");
      opt.value = value;
      opt.text = label;

      if (bookedSlots.includes(hour)) {
        opt.disabled = true;
        opt.style.color = "#999";
        opt.title = "Unavailable – booked";
      } else {
        hasAvailableSlot = true;
      }

      startTimeSelect.appendChild(opt);
    });

    
    if (!hasAvailableSlot) {
      const alert = document.createElement("div");
      alert.id = "no-available-times";
      alert.textContent = " No available time slots for this instructor on the selected date.";
      alert.style.color = "#c00";
      alert.style.fontSize = "0.9em";
      alert.style.marginTop = "0.5em";
      startTimeSelect.parentNode.appendChild(alert);
      return;
    }

    
    startTimeSelect.onchange = function () {
      const selectedHour = parseInt(this.value.split(":")[0], 10);
      hoursSelect.innerHTML = '<option value="" disabled selected>Select number of hours</option>';

      for (let duration = 1; duration <= 6; duration++) {
        const endHour = selectedHour + duration;
        let fits = true;

        
        if (endHour > 16) break;

        
        for (let i = 0; i < duration; i++) {
          if (bookedSlots.includes(selectedHour + i)) {
            fits = false;
            break;
          }
        }

        if (fits) {
          const opt = document.createElement("option");
          opt.value = duration;
          opt.text = `${duration} hour${duration > 1 ? "s" : ""}`;
          hoursSelect.appendChild(opt);
        }
      }
    };
  });
}

function toggleBookingOptions() {
  const sessionType = document.getElementById("sessionType").value;
  const skiSchoolSection = document.getElementById("skiSchoolOptions");
  const privateInstructionSection = document.getElementById("privateInstructionOptions");
  const container = document.getElementById("bookingDetailsContainer");

  if (sessionType === "skiSchool") {
    container.style.display = "block";
    skiSchoolSection.style.display = "block";
    privateInstructionSection.style.display = "none";
  } else if (sessionType === "privateInstruction") {
    container.style.display = "block";
    skiSchoolSection.style.display = "none";
    privateInstructionSection.style.display = "block";
  } else {
    container.style.display = "none";
    skiSchoolSection.style.display = "none";
    privateInstructionSection.style.display = "none";
  }

  disableHiddenFields();
}


function updateAgeGroup(type, delta) {
  const input = document.getElementById(`age-${type}`);
  const current = parseInt(input.value, 10) || 0;
  const spots = parseInt(document.getElementById("spots").value) || 0;

  const total =
    parseInt(document.getElementById("age-child").value) +
    parseInt(document.getElementById("age-teen").value) +
    parseInt(document.getElementById("age-adult").value);

  if (delta > 0 && total >= spots) return; 

  const newValue = Math.max(0, current + delta);
  input.value = newValue;

}

function updateLevel(level, delta) {
  const input = document.getElementById(`level-${level}`);
  const current = parseInt(input.value, 10) || 0;
  const spots = parseInt(document.getElementById("spots").value) || 0;

  const total =
    parseInt(document.getElementById("level-beginner").value) +
    parseInt(document.getElementById("level-intermediate").value) +
    parseInt(document.getElementById("level-advanced").value);

  if (delta > 0 && total >= spots) return; 

  const newValue = Math.max(0, current + delta);
  input.value = newValue;

}

function updateVegetarian(delta) {
  const input = document.getElementById("vegetarian-count");
  const spots = parseInt(document.getElementById("spots").value) || 0;
  let value = parseInt(input.value, 10) || 0;

  if (delta > 0 && value >= spots) return;  
  value = Math.max(0, value + delta);       

  input.value = value;
}


function initFlatpickr() {
  const sessionDate = document.getElementById("sessionDate");
  if (!sessionDate || typeof flatpickr === "undefined") {
    console.error("Flatpickr or #sessionDate not found.");
    return;
  }

  const today = new Date();
  const dayOfWeek = today.getDay(); // 0 = Sunday

  let minDate = today;
  let maxDate = new Date(today);

  if (dayOfWeek === 0) {
    // If today is Sunday, allow today + next full week
    maxDate.setDate(today.getDate() + 7); // Next Sunday
  } else {
    // Not Sunday: allow from today through next Sunday
    maxDate.setDate(today.getDate() + (7 - dayOfWeek) + 6);
  }

  flatpickr("#sessionDate", {
    dateFormat: "Y-m-d",
    minDate: minDate,
    maxDate: maxDate,
    defaultDate: null,
    disableMobile: true,
    allowInput: false,
    onChange: function (selectedDates, dateStr) {
      if (!dateStr) return;

      const instructorSelect = document.getElementById("instructor");
      instructorSelect.innerHTML = '<option value="" disabled selected>Loading instructors...</option>';

      RestClient.get(`availability/active?date=${dateStr}`, function (ids) {
        RestClient.get("users/instructor", function (allInstructors) {
          instructorSelect.innerHTML = '<option value="" disabled selected>Select an instructor</option>';
          allInstructors.forEach(instructor => {
            if (ids.includes(instructor.id)) {
              const opt = document.createElement("option");
              opt.value = instructor.id;
              opt.text = `${instructor.name} ${instructor.surname}`;
              instructorSelect.appendChild(opt);
            }
          });

          updateAvailableTimes();
        });
      });
    }
  });
}



function loadUserBookings() {
  const userId = localStorage.getItem("user_id");
  const container = document.getElementById("userBookingsContainer");
  const section = container.closest(".container");

  if (!userId) {
    section.style.display = "none";
    return;
  }

  RestClient.get(`bookings/user/${userId}`, function (bookings) {
  if (!Array.isArray(bookings) || bookings.length === 0) {
    section.style.display = "none";
    return;
  }

  const today = new Date().toISOString().split("T")[0];

  const upcomingOnly = bookings.filter(b => {
    if (b.session_type === "Private_instruction") {
      return b.date >= today;
    }
    return true; 
  });

  if (upcomingOnly.length === 0) {
    section.style.display = "none";
    return;
  }

  section.style.display = "block";
  container.innerHTML = "";

  upcomingOnly.forEach(booking => {
    const div = document.createElement("div");
    div.className = "booking-card";

    if (booking.session_type === "Ski_school") {
      div.innerHTML = `
        <div class="card mb-3 p-3 border">
          <h5>Ski School - ${booking.week ?? "N/A"}</h5>
          <p><strong>Spots:</strong> ${booking.num_of_spots ?? "N/A"}</p>
          <p><strong>Ages:</strong> 
            Child: ${booking.age_group_child ?? 0}, 
            Teen: ${booking.age_group_teen ?? 0}, 
            Adult: ${booking.age_group_adult ?? 0}
          </p>
          <p><strong>Ski Levels:</strong> 
            Beginner: ${booking.ski_level_b ?? 0}, 
            Intermediate: ${booking.ski_level_i ?? 0}, 
            Advanced: ${booking.ski_level_a ?? 0}
          </p>
          <p><strong>Vegetarians:</strong> ${booking.veg_count ?? 0}</p>
          <p><strong>Other:</strong> ${booking.other ?? "-"}</p>
        </div>
      `;
    } else {
      div.innerHTML = `
        <div class="card mb-3 p-3 border">
          <h5>Private Instruction with ${booking.instructor_name ?? "-"} ${booking.instructor_surname ?? ""}</h5>
          <p><strong>Session Type:</strong> ${booking.service_name ?? "-"}</p>
          <p><strong>Date:</strong> ${booking.date ?? "-"}</p>
          <p><strong>Start Time:</strong> ${booking.start_time ?? "-"}</p>
          <p><strong>Duration:</strong> ${booking.num_of_hours ?? "N/A"} hour(s)</p>
        </div>
      `;
    }

    container.appendChild(div);
  });
}, function (err) {
  console.error("Failed to fetch bookings:", err);
  section.style.display = "none";
});

$.validator.addMethod("ageGroupTotalMatch", function (_, element) {
  const isSkiSchool = $("#sessionType").val() === "skiSchool";
  const spots = parseInt($("#spots").val(), 10) || 0;
  const totalAge =
    parseInt($("#age-child").val(), 10) +
    parseInt($("#age-teen").val(), 10) +
    parseInt($("#age-adult").val(), 10);

  return !isSkiSchool || totalAge === spots;
}, "Total age group count must equal number of spots.");

$.validator.addMethod("levelTotalMatch", function (_, element) {
  const isSkiSchool = $("#sessionType").val() === "skiSchool";
  const spots = parseInt($("#spots").val(), 10) || 0;
  const totalLevel =
    parseInt($("#level-beginner").val(), 10) +
    parseInt($("#level-intermediate").val(), 10) +
    parseInt($("#level-advanced").val(), 10);

  return !isSkiSchool || totalLevel === spots;
}, "Total skill level count must equal number of spots.");



$("#bookingForm").validate({
  rules: {
    sessionType: { required: true },
    spots: {
      required: function () { return $("#sessionType").val() === "skiSchool"; },
      min: 1,
      max: 20
    },
    week: {
      required: function () { return $("#sessionType").val() === "skiSchool"; }
    },
    ageValidationTrigger: {
      ageGroupTotalMatch: true
    },
    levelValidationTrigger: {
      levelTotalMatch: true
    },
    service: {
      required: function () { return $("#sessionType").val() === "privateInstruction"; }
    },
    sessionDate: {
      required: function () { return $("#sessionType").val() === "privateInstruction"; }
    },
    instructor: {
      required: function () { return $("#sessionType").val() === "privateInstruction"; }
    },
    startTime: {
      required: function () { return $("#sessionType").val() === "privateInstruction"; }
    },
    hours: {
      required: function () { return $("#sessionType").val() === "privateInstruction"; }
    }
  },
  messages: {
    sessionType: "Please choose a service type.",
    spots: {
      required: "Please enter number of spots.",
      min: "Minimum 1 spot is required.",
      max: "Maximum 20 spots allowed."
    },
    week: "Please select a week for Ski School.",
    service: "Please choose session type (1 on 1, etc).",
    sessionDate: "Please select a date for private instruction.",
    instructor: "Please choose an instructor.",
    startTime: "Please select a start time.",
    hours: "Please choose session duration."
  },
  errorPlacement: function (error, element) {
    if (element.attr("name") === "sessionType") {
      error.insertAfter(element.closest(".form-group"));
    } else {
      error.insertAfter(element);
    }
  }
});

}






