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


    form.onsubmit = function (e) {
      e.preventDefault();
      const sessionType = document.getElementById("sessionType").value;

      if (sessionType === "skiSchool") {
        const data = {
          user_id: parseInt(localStorage.getItem("user_id")),
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
          alert("Ski School booking successful!");
          BookingService.init();
        }, function (error) {
          console.error("Ski School booking failed", error);
          alert("Error creating Ski School booking.");
        });
      }

      if (sessionType === "privateInstruction") {
        const booking = {
          user_id: parseInt(localStorage.getItem("user_id")),
          instructor_id: parseInt(document.getElementById("instructor").value),
          service_id: parseInt(document.getElementById("service").value),
          session_type: "Private_instruction",
          date: document.getElementById("sessionDate").value,
          start_time: document.getElementById("startTime").value,
          num_of_hours: parseInt(document.getElementById("hours").value),
          status: "confirmed"
        };

        RestClient.request("bookings", "POST", booking, function () {
          alert("Private instruction booking successful!");
          BookingService.init();
        }, function (error) {
          console.error("Private instruction booking failed", error);
          alert("Error creating private instruction booking.");
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

    allSlots.forEach(hour => {
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

    // If no available slot, show message
    if (!hasAvailableSlot) {
      const alert = document.createElement("div");
      alert.id = "no-available-times";
      alert.textContent = "⚠️ No available time slots for this instructor on the selected date.";
      alert.style.color = "#c00";
      alert.style.fontSize = "0.9em";
      alert.style.marginTop = "0.5em";
      startTimeSelect.parentNode.appendChild(alert);
      return;
    }

    // Adjust hours dropdown based on start time
    startTimeSelect.onchange = function () {
      const selectedHour = parseInt(this.value.split(":")[0], 10);
      hoursSelect.innerHTML = '<option value="" disabled selected>Select number of hours</option>';

      for (let duration = 1; duration <= 6; duration++) {
        const endHour = selectedHour + duration;
        let fits = true;

        // Can't exceed 4 PM (16:00)
        if (endHour > 16) break;

        // Check if any hour in the range is booked
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

  if (delta > 0 && total >= spots) return; // Prevent overfilling

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

  if (delta > 0 && total >= spots) return; // Prevent overfilling

  const newValue = Math.max(0, current + delta);
  input.value = newValue;

}

function updateVegetarian(delta) {
  const input = document.getElementById("vegetarian-count");
  let value = parseInt(input.value, 10) || 0;

  if (delta > 0 && value >= 4) return;  // Currently capped at 4 not good 
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
  const nextSunday = new Date(today);
  const daysUntilNextSunday = 7 - today.getDay() + 7;
  nextSunday.setDate(today.getDate() + daysUntilNextSunday);

  flatpickr("#sessionDate", {
    dateFormat: "Y-m-d",
    minDate: today,
    maxDate: nextSunday,
    defaultDate: today,
    disableMobile: true,
    allowInput: false
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
      section.style.display = "none"; // Hide if no bookings
      return;
    }

    section.style.display = "block"; // Show if there are bookings
    container.innerHTML = "";

    bookings.forEach(booking => {
      const div = document.createElement("div");
      div.className = "booking-card";
      div.innerHTML = `
        <div class="card mb-3 p-3 border">
          <h5>${booking.session_type === "Ski_school" ? `Ski School - ${booking.week}` : "Private Instruction"}</h5>
          <p><strong>Date:</strong> ${booking.date || "-"}</p>
          <p><strong>Start Time:</strong> ${booking.start_time || "-"}</p>
          <p><strong>Duration:</strong> ${booking.num_of_hours || booking.num_of_spots} ${booking.num_of_hours ? "hours" : "spots"}</p>
          <p><strong>Status:</strong> ${booking.status}</p>
        </div>
      `;
      container.appendChild(div);
    });
  }, function (err) {
    console.error("Failed to fetch bookings:", err);
    section.style.display = "none";
  });
}






