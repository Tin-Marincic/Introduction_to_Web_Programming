$(document).ready(function() {
  var app = $.spapp({
      pageNotFound: 'error_404',
      templateDir: 'tpl/',
      defaultView: 'home'
  });
  
  app.route({ view: 'home', load: 'home.html' });
  app.route({ view: 'about', load: 'about.html' });
  app.route({ view: 'team', load: 'team.html' });
  app.route({ view: 'reviews', load: 'reviews.html' });
  app.route({ view: 'services', load: 'services.html' });
  app.route({ view: 'contact', load: 'contact.html' });
  app.route({ view: 'sign_in', load: 'sign_in.html' });
  app.route({ view: 'register', load: 'register.html' });
  app.route({ view: 'admin_panel', load: 'admin_panel.html' });
  app.route({
    view: 'instructor_panel',
    load: 'instructor_panel.html',
    onReady: function() {
      document.querySelectorAll('.day-toggle').forEach(function(button) {
        button.addEventListener('click', function() {
          button.classList.toggle('available');
        });
      });
    }
  });
  app.route({
      view: 'booking',
      load: 'booking.html',
      onReady: function() {
          console.log("Booking page loaded, initializing form...");
          window.initBookingForm();
          window.initDateSelector();
      }
  });
  
  window.initBookingForm = function () {
      console.log("Initializing booking form...");
  
      var sessionTypeEl = document.getElementById("sessionType");
      var spotsEl = document.getElementById("spots");
  
      if (sessionTypeEl) {
          sessionTypeEl.addEventListener("change", window.toggleBookingOptions);
      } else {
          console.error("sessionType element not found");
      }
  
      if (spotsEl) {
          spotsEl.addEventListener("change", window.updateVegetarianOptions);
      } else {
          console.error("spots element not found");
      }
  };
  
  window.initDateSelector = function() {
      var sessionDateInput = document.getElementById('sessionDate');
      if (!sessionDateInput) {
          console.error("sessionDate element not found");
          return;
      }
      var today = new Date();
      today.setHours(0, 0, 0, 0);
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      var formattedToday = yyyy + '-' + mm + '-' + dd;
  
      var dayOfWeek = today.getDay(); 
      var daysUntilSunday = (7 - dayOfWeek) % 7;
      var sunday = new Date(today);
      sunday.setDate(today.getDate() + daysUntilSunday);
      var sunday_dd = String(sunday.getDate()).padStart(2, '0');
      var sunday_mm = String(sunday.getMonth() + 1).padStart(2, '0');
      var sunday_yyyy = sunday.getFullYear();
      var formattedSunday = sunday_yyyy + '-' + sunday_mm + '-' + sunday_dd;
  
      sessionDateInput.setAttribute('min', formattedToday);
      sessionDateInput.setAttribute('max', formattedSunday);
  };
  
  window.updateVegetarianOptions = function() {
      window.resetVegetarian();
  };
  
  window.toggleBookingOptions = function () {
      var sessionTypeEl = document.getElementById("sessionType");
      var skiSchoolOptions = document.getElementById("skiSchoolOptions");
      var privateInstructionOptions = document.getElementById("privateInstructionOptions");
    
      var disableFields = function(section) {
        var fields = section.querySelectorAll("input, select, textarea");
        fields.forEach(function(el) {
          el.disabled = true;
        });
      };
    
      var enableFields = function(section) {
        var fields = section.querySelectorAll("input, select, textarea");
        fields.forEach(function(el) {
          el.disabled = false;
        });
      };
    
      if (skiSchoolOptions) {
          skiSchoolOptions.style.display = "none";
          disableFields(skiSchoolOptions);
      }
      if (privateInstructionOptions) {
          privateInstructionOptions.style.display = "none";
          disableFields(privateInstructionOptions);
      }
    
      var sessionType = sessionTypeEl.value;
      if (sessionType === "skiSchool") {
        if (skiSchoolOptions) {
          skiSchoolOptions.style.display = "block";
          enableFields(skiSchoolOptions);
        }
      } else if (sessionType === "privateInstruction") {
        if (privateInstructionOptions) {
          privateInstructionOptions.style.display = "block";
          enableFields(privateInstructionOptions);
        }
      }
  };
  
  window.updateLevel = function(level, change) {
      var input = document.getElementById('level-' + level);
      var current = parseInt(input.value, 10) || 0;
      var spots = parseInt(document.getElementById('spots').value, 10) || Infinity;
      var total = (parseInt(document.getElementById('level-beginner').value, 10) || 0) +
                  (parseInt(document.getElementById('level-intermediate').value, 10) || 0) +
                  (parseInt(document.getElementById('level-advanced').value, 10) || 0);
      
      if (change > 0 && total >= spots) {
        return;
      }
      
      var newValue = current + change;
      if (newValue < 0) newValue = 0;
      
      input.value = newValue;
  };
  
  window.updateVegetarian = function(change) {
      var vegInput = document.getElementById("vegetarian-count");
      var spotsInput = document.getElementById("spots");
      var spots = parseInt(spotsInput.value, 10) || 0;
      var current = parseInt(vegInput.value, 10) || 0;
      var newValue = current + change;
  
      if (newValue < 0) {
          newValue = 0;
      }
      if (newValue > spots) {
          newValue = spots;
      }
      
      vegInput.value = newValue;
  };
  
  window.resetLevels = function () {
      document.getElementById('level-beginner').value = 0;
      document.getElementById('level-intermediate').value = 0;
      document.getElementById('level-advanced').value = 0;
  };
  
  window.resetVegetarian = function () {
      var spots = parseInt(document.getElementById("spots").value, 10) || 0;
      var vegInput = document.getElementById("vegetarian-count");
      if (parseInt(vegInput.value, 10) > spots) {
          vegInput.value = spots;
      } else {
          vegInput.value = 0;
      }
  };
  
  window.updateAgeGroup = function(group, change) {
      var input = document.getElementById('age-' + group);
      var current = parseInt(input.value, 10) || 0;
      var spots = parseInt(document.getElementById('spots').value, 10) || Infinity;
      var total = (parseInt(document.getElementById('age-child').value, 10) || 0) +
                  (parseInt(document.getElementById('age-teen').value, 10) || 0) +
                  (parseInt(document.getElementById('age-adult').value, 10) || 0);
      if (change > 0 && total >= spots) {
        return;
      }
      var newValue = current + change;
      if (newValue < 0) newValue = 0;
      input.value = newValue;
  };

  window.resetAgeGroups = function() {
      document.getElementById('age-child').value = 0;
      document.getElementById('age-teen').value = 0;
      document.getElementById('age-adult').value = 0;
  };

  window.updateHoursOptions = function() {
      var startTimeEl = document.getElementById("startTime");
      var hoursSelect = document.getElementById("hours");
    
      hoursSelect.innerHTML = '<option value="" disabled selected>Select number of hours</option>';
    
      if (!startTimeEl.value) {
        return;
      }
    
      var timeParts = startTimeEl.value.split(':');
      var startHour = parseInt(timeParts[0], 10);
      var maxHours = 16 - startHour;
      if (maxHours < 1) {
        maxHours = 1; 
      }
    
      for (var i = 1; i <= maxHours; i++) {
        var option = document.createElement("option");
        option.value = i;
        option.text = i + (i === 1 ? " hour" : " hours");
        hoursSelect.appendChild(option);
      }
  };

  window.validateBookingForm = function() {
      var sessionDateInput = document.getElementById("sessionDate");
      var sessionDateValue = sessionDateInput ? sessionDateInput.value : "";
      if (!sessionDateValue) {
          alert("Please select a date for your session.");
          return false;
      }
      var selectedDate = new Date(sessionDateValue);
      var today = new Date();
      today.setHours(0, 0, 0, 0);
      if (selectedDate < today) {
          alert("You cannot select a date in the past.");
          return false;
      }
      var dayOfWeek = today.getDay();
      var daysUntilSunday = (7 - dayOfWeek) % 7;
      var sunday = new Date(today);
      sunday.setDate(today.getDate() + daysUntilSunday);
      if (selectedDate > sunday) {
          alert("Please select a date within the current week (Monday to Sunday).");
          return false;
      }
  
      var sessionType = document.getElementById("sessionType").value;
      if (!sessionType) {
          alert("Please select a session type.");
          return false;
      }
      
      if (sessionType === "skiSchool") {
          var spots = document.getElementById("spots").value;
          var week = document.getElementById("week").value;
          var totalAgeGroups = (parseInt(document.getElementById('age-child').value, 10) || 0) +
                               (parseInt(document.getElementById('age-teen').value, 10) || 0) +
                               (parseInt(document.getElementById('age-adult').value, 10) || 0);
          var totalLevels = (parseInt(document.getElementById('level-beginner').value, 10) || 0) +
                            (parseInt(document.getElementById('level-intermediate').value, 10) || 0) +
                            (parseInt(document.getElementById('level-advanced').value, 10) || 0);
          if (!spots || !week) {
              alert("Please fill out all required fields for Ski School.");
              return false;
          }
          if (totalAgeGroups != spots) {
              alert("Please allocate the correct number of participants into age groups.");
              return false;
          }
          if (totalLevels != spots) {
              alert("Please allocate the correct number of participants into skiing levels.");
              return false;
          }
      } else if (sessionType === "privateInstruction") {
          var groupSize = document.getElementById("groupSize").value;
          var instructor = document.getElementById("instructor").value;
          var skiLevelPI = document.getElementById("skiLevelPI").value;
          var startTime = document.getElementById("startTime").value;
          var hours = document.getElementById("hours").value;
          if (!groupSize || !instructor || !skiLevelPI || !startTime || !hours) {
              alert("Please fill out all required fields for Private Instruction.");
              return false;
          }
      }
      return true;
  };

  document.querySelectorAll('.day-toggle').forEach(function(button) {
    button.addEventListener('click', function() {
      button.classList.toggle('available');
    });
  });
    
  app.run();
});
