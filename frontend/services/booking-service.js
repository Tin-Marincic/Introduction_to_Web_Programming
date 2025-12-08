// Helper Reset Functions
/*
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
*/
// Disable hidden fields because of error
function disableHiddenFields() {
  const skiSchool = document.getElementById("skiSchoolOptions");
  const privateInstruction = document.getElementById("privateInstructionOptions");
  const sessionType = document.getElementById("sessionType").value;

  const disableElements = (container, disable) => {
    container.querySelectorAll("input, select, textarea").forEach(el => {
      el.disabled = disable;
      if (disable) el.classList.remove("error"); // remove validate error class
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
    document.getElementById("startTime").innerHTML = '<option value="" disabled selected>Izaberite početno vrijeme</option>';
    document.getElementById("hours").innerHTML = '<option value="" disabled selected>Izaberite broj sati</option>';

        // ⭐ Initialize International Phone Input
    const phoneInputField = document.getElementById("phoneNumber");
    if (phoneInputField) {
      const phoneInput = window.intlTelInput(phoneInputField, {
        initialCountry: "ba", // Bosnia default
        preferredCountries: ["ba", "hr", "rs", "me", "de", "at", "si", "ch"],
        separateDialCode: true,
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.3.0/js/utils.js",
      });

      // Save full phone number (+38761234567)
      phoneInputField.addEventListener("blur", function () {
        phoneInputField.dataset.fullNumber = phoneInput.getNumber();
      });
    }

    // Load instructors
    document.getElementById("sessionDate").addEventListener("change", function () {
    const selectedDate = this.value;
    if (!selectedDate) return;

    const instructorSelect = document.getElementById("instructor");
    instructorSelect.innerHTML = '<option value="" disabled selected>Učitavanje Instruktora...</option>';

    RestClient.get(`availability/active?date=${selectedDate}`, function (ids) {
        RestClient.get("users/instructor", function (allInstructors) {
        instructorSelect.innerHTML = '<option value="" disabled selected>Izaberite Instruktora</option>';
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

      serviceSelect.innerHTML = '<option value="" disabled selected>Odaberite tip sesije</option>';

      services.forEach(service => {
        // ❌ Ignore services that start with "Ski Škola"
        // ✅ Include everything else
        if (!service.name.trim().toLowerCase().startsWith("ski škola")) {
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

      if (!$("#bookingForm").valid()) {
        return;
      }

      const userId = parseInt(localStorage.getItem("user_id"));
      if (!userId || isNaN(userId)) {
        toastr.error("Morate biti prijavljeni da biste napravili rezervaciju.");
        return;
      }

      const sessionType = document.getElementById("sessionType").value;

      // 🏫 --- SKI SCHOOL BOOKING (single participant version) ---
      // 🏫 --- SKI SCHOOL BOOKING (single participant version) ---
      if (sessionType === "skiSchool") {

        const iti = intlTelInputGlobals.getInstance(document.getElementById("phoneNumber"));
        const phoneNumber = iti.getNumber(); // sends +387xxxxxxxx

        const firstName    = document.getElementById("firstName").value.trim();
        const lastName     = document.getElementById("lastName").value.trim();
        const week         = document.getElementById("week").value;
        const dateOfBirth  = document.getElementById("dateOfBirth").value;   // NEW
        const skiLevel     = document.getElementById("skiLevel").value;
        const address      = document.getElementById("address").value.trim() || ""; // NEW
        const isVegetarian = document.querySelector("input[name='isVegetarian']:checked")?.value || 0;
        const allergies    = document.getElementById("allergies").value.trim() || "";

        if (!iti.isValidNumber()) {
          toastr.error("Unesite ispravan broj telefona");
          return;
        }

        // Basic validation (extra safety)
        if (!firstName || !lastName || !phoneNumber || !week || !dateOfBirth || !skiLevel) {
          toastr.warning("Molim Vas popunite sva potrebna polja");
          return;
        }

        const data = {
          user_id:      userId,
          service_id:   4,
          session_type: "Ski_school",
          first_name:   firstName,
          last_name:    lastName,
          phone_number: phoneNumber,
          week:         week,
          date_of_birth: dateOfBirth,          // NEW – required, NOT NULL
          ski_level:     skiLevel,
          address:       address || null,      // NEW – optional
          is_vegetarian: parseInt(isVegetarian),
          allergies:     allergies
        };

        RestClient.request("bookings/ski-school", "POST", data,
          function () {
            toastr.success(`Rezervacija za Ski školu za ${firstName} ${lastName} uspješno dodana!`);

            setTimeout(() => {
              window.location.hash = "#_refresh";
              window.location.hash = "#booking";
            }, 10);
          },
          function (error) {
            console.error("Rezervacija za Ski školu neuspješna", error);
            toastr.error("Rezervacija za Ski školu neuspješna");
          }
        );
      }


      // 🧑‍🏫 --- PRIVATE INSTRUCTION BOOKING (unchanged) ---
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

        RestClient.request("bookings", "POST", booking,
          function () {
            toastr.success("Rezervacija za privatnu instrukciju uspješno izvršena!");

            setTimeout(() => {
              window.location.hash = "#_refresh";
              window.location.hash = "#booking";
            }, 10);

          },
          function (error) {
            console.error("Rezervacija za privatnu instrukciju neuspješna", error);
            toastr.error("Rezervacija za privatnu instrukciju neuspješna");
          }
        );
      }
    };

    // Keep your event listeners for updating available times
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
  startTimeSelect.innerHTML = '<option value="" disabled selected>Izaberite Početno vrijeme</option>';
  hoursSelect.innerHTML = '<option value="" disabled selected>Izaberite broj sati</option>';

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

    const allSlots = [9, 10, 11, 12, 13, 14, 15];
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
      alert.textContent = " Nema slobodnih termina za ovog instruktora na odabrani datum ili skijalište ne radi.";
      alert.style.color = "#c00";
      alert.style.fontSize = "0.9em";
      alert.style.marginTop = "0.5em";
      startTimeSelect.parentNode.appendChild(alert);
      return;
    }

    
    startTimeSelect.onchange = function () {
      const selectedHour = parseInt(this.value.split(":")[0], 10);
      hoursSelect.innerHTML = '<option value="" disabled selected>Izaberite broj sati</option>';

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

/*
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

*/
function initFlatpickr() {
  const sessionDate = document.getElementById("sessionDate");
  if (!sessionDate || typeof flatpickr === "undefined") {
    console.error("Flatpickr or #sessionDate not found.");
    return;
  }

  const today = new Date();
  const minDate = today;

  const maxDate = new Date(today);
  const dayOfWeek = today.getDay(); 
  const daysLeftThisWeek = (7 - dayOfWeek) % 7; 
  maxDate.setDate(today.getDate() + daysLeftThisWeek + 7); 

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
      instructorSelect.innerHTML = '<option value="" disabled selected>Ućitavanje Instruktora...</option>';

      RestClient.get(`availability/active?date=${dateStr}`, function (ids) {
        RestClient.get("users/instructor", function (allInstructors) {
          instructorSelect.innerHTML = '<option value="" disabled selected>Izaberite Instruktora</option>';
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

      const cancelMessage = `<p class="text-muted"><em>Molimo da svoju rezervaciju otkažete u razumnom vremenskom roku.</em></p>`;
      const cancelButton = `<button class="btn btn-danger mt-2" onclick="deleteBooking(${booking.id})">Otkažite Rezervaciju</button>`;


      if (booking.session_type === "Ski_school") {
        div.innerHTML = `
          <div class="card mb-3 p-3 border">
            <h5>Škola skijanja - ${booking.week ?? "N/A"}</h5>
            <p><strong>Učesnik:</strong> ${booking.first_name ?? ""} ${booking.last_name ?? ""}</p>
            <p><strong>Broj telefona:</strong> ${booking.phone_number ?? "-"}</p>
            <p><strong>Datum rođenja:</strong> ${booking.date_of_birth ?? "-"}</p>
            <p><strong>Nivo skijanja:</strong> ${booking.ski_level ?? "-"}</p>
            <p><strong>Adresa:</strong> ${booking.address ?? "-"}</p>
            <p><strong>Vegetarijanac:</strong> ${booking.is_vegetarian == 1 ? "Da" : "Ne"}</p>
            <p><strong>Alergije / Ostale napomene:</strong> ${booking.other ?? "-"}</p>
            ${cancelMessage}
            ${cancelButton}
          </div>
        `;
      } else {
        div.innerHTML = `
          <div class="card mb-3 p-3 border">
            <h5>Privatna instrukcija sa ${booking.instructor_name ?? "-"} ${booking.instructor_surname ?? ""}</h5>
            <p><strong>Tip sesije:</strong> ${booking.service_name ?? "-"}</p>
            <p><strong>Datum:</strong> ${booking.date ?? "-"}</p>
            <p><strong>Početak:</strong> ${booking.start_time ?? "-"}</p>
            <p><strong>Trajanje:</strong> ${booking.num_of_hours ?? "N/A"} sat(a)</p>
            ${cancelMessage}
            ${cancelButton}
          </div>
        `;
      }

      container.appendChild(div);
    });
  }, function (err) {
    console.error("Failed to fetch bookings:", err);
    section.style.display = "none";
  });
}

function deleteBooking(id) {
  if (confirm("Da li ste sigurni da želite da otkažete ovu rezervaciju?")) {
    RestClient.delete(
      `bookings/${id}`, 
      {}, 
      function(response) {
        alert(response.message);
        loadUserBookings();
      },
      function(err) {
        console.error("DELETE booking failed:", err);
        alert("Error: " + (err.responseText || "Rezervacija se nije mogla obrisati"));
      }
    );
  }
}




