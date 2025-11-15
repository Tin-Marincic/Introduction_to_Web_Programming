$(document).ready(function() {
  var app = $.spapp({
    pageNotFound: 'error_404',
    templateDir: 'tpl/',
    defaultView: 'home'
  });

  const backendBaseURL =
  location.hostname === "localhost" || location.hostname === "127.0.0.1"
    ? "http://localhost/TinMarincic/Introduction_to_Web_Programming/backend/"
    : "https://unisport-9kjwi.ondigitalocean.app/";

  
function updateBookingView() {
  if (!localStorage.getItem("user_token")) {
    $("#bookingSection").hide();
    $("#loginReminder").show();

    
    $("#userBookingsContainer").html("");
    $("#userBookingsHeading").hide(); 

    return;
  }

  $("#loginReminder").hide();
  $("#bookingSection").show();
  $("#userBookingsHeading").show(); 
  BookingService.init();
  initFlatpickr();
  loadUserBookings();
}


  app.route({ view: 'home', load: 'home.html' });
  app.route({ view: 'about', load: 'about.html' });
  app.route({ view: 'team', load: 'team.html' });
  app.route({
  view: 'reviews',
  load: 'reviews.html',
  onReady: function () {
    // 1) load reviews on initial render
    ClientLoader.loadReviews();
    ClientLoader.initReviewModal();

    // 2) re-load them if user logs in while still on this page
    $(document)
      .off('loginSuccess.reviews')
      .on('loginSuccess.reviews', function () {
        ClientLoader.loadReviews();
        ClientLoader.initReviewModal();
      });
  }
});

  app.route({ view: 'services', load: 'services.html' });
  app.route({
  view: 'contact',
  load: 'contact.html',
  onReady: function () {
    $(".php-email-form").on("submit", function (e) {
      e.preventDefault();

      const subject = $("input[name='subject']").val();
      const message = $("textarea[name='message']").val();
      const token = localStorage.getItem("user_token");

      // If user is not logged in
      if (!token) {
        $(".error-message").text("You must be logged in to send a message.").show();
        $(".sent-message").hide();
        $(".loading").hide();
        return;
      }

      // Show loading, hide messages
      $(".loading").show();
      $(".error-message").hide();
      $(".sent-message").hide();

      $.ajax({
        url: backendBaseURL + "forms/send_email.php",
        method: "POST",
        data: { subject, message },
        headers: { Authorization: `Bearer ${token}` },
        success: function () {
          $(".loading").hide();
          $(".sent-message").show();
          $(".php-email-form")[0].reset();
        },
        error: function (xhr) {
          $(".loading").hide();
          const res = xhr.responseJSON;
          $(".error-message")
            .text(res && res.error ? res.error : "Sending failed.")
            .show();
        }
      });
    });
  }
});


  app.route({ view: 'sign_in', load: 'sign_in.html' });

  app.route({
  view: 'admin_panel',
  load: 'admin_panel.html',
  onReady: function () {
    AdminPanelService.loadServices();
    AdminPanelService.loadInstructors();
    AdminPanelService.loadInstructorBookings();
    AdminPanelService.loadSkiSchoolAvailability();
    AdminPanelService.loadSkiSchoolBookings();

    $("#edit-service-form").on("submit", function (e) {
      e.preventDefault();

      const id = $("#edit-service-id").val();
      const data = {
        name: $("#edit-service-name").val(),
        description: $("#edit-service-description").val(),
        price: parseFloat($("#edit-service-price").val())
      };

      RestClient.put(`api/services/${id}`, data, function () {
        toastr.success("Service updated!");
        const modal = bootstrap.Modal.getInstance(document.getElementById("editServiceModal"));
        if (modal) modal.hide();
        AdminPanelService.loadServices();
      }, function (err) {
        console.error("Update failed", err);
        toastr.error("Failed to update service.");
      });
    });

    $("#create-service-form").on("submit", function (e) {
  e.preventDefault();

  const data = {
    name: $("#create-service-name").val(),
    description: $("#create-service-description").val(),
    price: parseFloat($("#create-service-price").val())
  };

  RestClient.post("api/services", data, function () {
    toastr.success("Service created!");
    const modal = bootstrap.Modal.getInstance(document.getElementById("createServiceModal"));
    if (modal) modal.hide();
    $("#create-service-form")[0].reset();
    location.reload();

  }, function (err) {
    console.error("Create failed", err);
    toastr.error("Failed to create service.");
  });
});

$("#add-instructor-form").on("submit", function (e) {
  e.preventDefault();

  const data = {
    name: $("#add-instructor-name").val(),
    surname: $("#add-instructor-surname").val(),
    licence: $("#add-instructor-licence").val(),
    username: $("#add-instructor-username").val(),
    password: $("#add-instructor-password").val(),
    role: "instructor"
  };

  RestClient.post("instructors", data, function () {
    toastr.success("Instructor added!");
    const modal = bootstrap.Modal.getInstance(document.getElementById("addInstructorModal"));
    if (modal) modal.hide();
    $("#add-instructor-form")[0].reset();
    AdminPanelService.loadInstructors();
  }, function (err) {
    toastr.error("Failed to add instructor.");
    console.error("Add instructor error:", err);
  });
});


$("#edit-instructor-form").on("submit", function (e) {
  e.preventDefault();
  const id = $("#edit-instructor-id").val();
  const data = {
    licence: $("#edit-instructor-licence").val()
  };

  RestClient.put(`instructors/${id}`, data, function () {
    toastr.success("Instructor licence updated!");
    bootstrap.Modal.getInstance(document.getElementById("editInstructorModal")).hide();
    AdminPanelService.loadInstructors();
  }, function (err) {
    toastr.error("Failed to update instructor.");
    console.error(err);
  });
});




  }
});


app.route({
  view: 'booking',
  load: 'booking.html',
  onReady: function () {

    updateBookingView();
    disableHiddenFields();
    toggleBookingOptions();

        // ⭐ Custom validator for intl-tel-input
    $.validator.addMethod("phoneValid", function (value, element) {
      const instance = intlTelInputGlobals.getInstance(element);
      return instance && instance.isValidNumber();
    }, "Unesite ispravan broj telefona");


    // 🔥 Now the form exists, so validator attaches correctly
    $("#bookingForm").validate({
      ignore: ":hidden",

      rules: {
        sessionType: { required: true },

        // ⭐ SKI SCHOOL RULES
        firstName: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },
        lastName: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },
        phoneNumber: {
          required: function () { return $("#sessionType").val() === "skiSchool"; },
          phoneValid: true
        },
        week: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },
        ageGroup: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },
        skiLevel: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },
        isVegetarian: {
          required: function () { return $("#sessionType").val() === "skiSchool"; }
        },

        // ⭐ PRIVATE INSTRUCTION RULES
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
        sessionType: "Izaberite tip usluge.",

        firstName: "Unesite ime",
        lastName: "Unesite prezime",
        phoneNumber: "Unesite broj telefona",
        week: "Izaberite sedmicu",
        ageGroup: "Izaberite dobnu skupinu",
        skiLevel: "Izaberite nivo",
        isVegetarian: "Odaberite jednu opciju",

        service: "Izaberite vrstu sesije",
        sessionDate: "Izaberite datum",
        instructor: "Izaberite instruktora",
        startTime: "Izaberite početno vrijeme",
        hours: "Izaberite broj sati"
      },

      errorPlacement: function (error, element) {
        // put radio button errors nicely under group
        if (element.attr("name") === "isVegetarian") {
          error.insertAfter(element.closest(".form-group"));
          return;
        }

        // sessionType special case
        if (element.attr("name") === "sessionType") {
          error.insertAfter(element.closest(".form-group"));
          return;
        }

        // default placement
        error.insertAfter(element);
      }
    });


    $(document)
      .off('loginSuccess.booking')
      .on('loginSuccess.booking', updateBookingView);
  }
});



  app.route({
    view: 'instructor_panel',
    load: 'instructor_panel.html',
    onReady: function () {
      InstructorPanelService.loadHeader();
      InstructorPanelService.loadBookings();
      InstructorPanelService.initAvailability();
    }
  });
app.route({
  view: 'register',
  load: 'register.html',
  onReady: function () {

    console.log("[Register Route] Loaded register.html");

    // -------------------------------------------------------------------
    // 0. FULL RESET — VERY IMPORTANT
    // -------------------------------------------------------------------
    const form = $("#register-form");

    if (form.length) {
      form[0].reset();                 // clear inputs
      form.removeClass("error");       // remove any error class

      // reset validator if exists
      if (form.data('validator')) {
        form.validate().resetForm();   // clear error messages
        form.validate().reset();       // reset internal state
      }
    }

    // -------------------------------------------------------------------
    // 1. DESTROY OLD intl-tel-input IF IT EXISTS
    // -------------------------------------------------------------------
    const phoneEl = document.getElementById("phone");

    const oldInstance = window.intlTelInputGlobals.getInstance(phoneEl);
    if (oldInstance) {
      oldInstance.destroy();
      console.log("[Register Route] Old intl-tel-input destroyed");
    }

    // -------------------------------------------------------------------
    // 2. CREATE NEW intl-tel-input INSTANCE
    // -------------------------------------------------------------------
    let iti = window.intlTelInput(phoneEl, {
      initialCountry: "ba",
      preferredCountries: ["ba", "hr", "rs", "si", "de", "at", "ch"],
      separateDialCode: true,
      utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
    });

    console.log("[Register Route] intl-tel-input initialized");

    // -------------------------------------------------------------------
    // 3. Custom validator for phone number
    // -------------------------------------------------------------------
    $.validator.addMethod("phoneValid", function (value, element) {
      const instance = window.intlTelInputGlobals.getInstance(element);
      return instance && instance.isValidNumber();
    }, "Unesite ispravan broj telefona");

    // -------------------------------------------------------------------
    // 4. APPLY FRESH VALIDATION (NO EVENT HANDLERS HERE)
    // -------------------------------------------------------------------
    form.off().validate({
      rules: {
        name: { required: true, minlength: 3 },
        surname: { required: true, minlength: 3 },
        username: { required: true, email: true },
        phone: { required: true, phoneValid: true },
        password: { required: true, minlength: 8, maxlength: 16 }
      },
      messages: {
        name: {
          required: 'Unesite ime',
          minlength: 'Ime mora imati najmanje 3 karaktera'
        },
        surname: {
          required: 'Unesite prezime',
          minlength: 'Prezime mora imati najmanje 3 karaktera'
        },
        username: {
          required: 'Unesite svoj email',
          email: 'Unesite ispravnu email adresu'
        },
        phone: {
          required: 'Unesite broj telefona',
          phoneValid: 'Unesite ispravan broj telefona'
        },
        password: {
          required: 'Unesite lozinku',
          minlength: 'Lozinka mora imati najmanje 8 karaktera',
          maxlength: 'Lozinka ne može imati više od 16 karaktera'
        }
      }
    });

    // -------------------------------------------------------------------
    // 5. 🚫 IMPORTANT: NO SUBMIT HANDLER HERE
    //    Submit is handled ONLY in UserService
    // -------------------------------------------------------------------

    // -------------------------------------------------------------------
// 6. Password Visibility Toggle
// -------------------------------------------------------------------
// -------------------------------------------------------------------
// 6. Password Visibility Toggle (SPA-safe delegated binding)
// -------------------------------------------------------------------
$(document)
  .off("click", "#togglePassword")
  .on("click", "#togglePassword", function () {

    const passwordField = $("#password");
    const type = passwordField.attr("type") === "password" ? "text" : "password";

    passwordField.attr("type", type);

    // toggle icon
    $(this).toggleClass("bi-eye-fill bi-eye-slash-fill");
});

  }
});



  app.run();
});
