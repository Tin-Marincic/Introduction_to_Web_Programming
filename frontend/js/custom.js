// Fix SPApp hash routing for reset password
(function() {
    let h = window.location.hash;

    if (h.startsWith("#reset_password/")) {
        // Force SPApp to load the correct view
        window.location.hash = "#reset_password";
    }
})();



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


app.route({
    view: "sign_in",
    load: "sign_in.html",
    cache: false,
    onReady: function () {

        console.log("[Sign-In Route] Loaded sign_in.html");

        const form = $("#login-form");

        /* --------------------------------------------
           RESET FORM AND VALIDATOR
        ---------------------------------------------*/
        if (form.length) {
            form[0].reset();

            if (form.data("validator")) {
                let v = form.validate();
                v.resetForm();
                v.reset();
            }
        }

        /* --------------------------------------------
           INITIALIZE VALIDATION
        ---------------------------------------------*/
        form.off().validate({
            rules: {
                username: { required: true, email: true },
                password: { required: true, minlength: 3, maxlength: 16 }
            },
            messages: {
                username: {
                    required: "Unesite svoj email",
                    email: "Unesite ispravnu email adresu"
                },
                password: {
                    required: "Unesite svoju lozinku",
                    minlength: "Lozinka mora imati najmanje 3 karaktera",
                    maxlength: "Lozinka ne može imati više od 16 karaktera"
                }
            }
        });

        /* --------------------------------------------
           LOGIN PASSWORD VISIBILITY TOGGLE
        ---------------------------------------------*/
        $(document)
            .off("click.loginToggle")
            .on("click.loginToggle", "#toggleLoginPassword", function () {

                const field = $("#password");
                const hidden = field.attr("type") === "password";

                field.attr("type", hidden ? "text" : "password");

                $(this)
                    .toggleClass("bi-eye-fill", hidden)
                    .toggleClass("bi-eye-slash-fill", !hidden);
            });

    }
});


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
    view: "register",
    load: "register.html",
    cache: false,
    onReady: function () {

        console.log("[Register Route] Loaded register.html");

        // Attach SPA delegated handlers (login, register, logout)
        UserService.init();

        const form = $("#register-form");
        const phoneEl = document.getElementById("phone");

        /* --------------------------------------------
           1) RESET VALIDATOR ONLY (DO NOT RESET FORM!)
        ---------------------------------------------*/
        if (form.data("validator")) {
            const v = form.validate();
            v.resetForm(); // only clears error messages, not values
        }

        /* --------------------------------------------
           2) INIT VALIDATOR FIRST (before phone plugin)
        ---------------------------------------------*/
        initValidator();

        function initValidator() {
            console.log("[Register Route] Validator READY");

            $.validator.addMethod(
                "phoneValid",
                function (value, element) {
                    const inst = window.intlTelInputGlobals.getInstance(element);
                    return inst && inst.isValidNumber();
                },
                "Unesite ispravan broj telefona"
            );

            form.validate({
                rules: {
                    name: { required: true, minlength: 3 },
                    surname: { required: true, minlength: 3 },
                    email: { required: true, email: true }, // FIXED
                    phone: { required: true, phoneValid: true },
                    password: { required: true, minlength: 8, maxlength: 16 }
                },
                messages: {
                    name: { required: "Unesite ime" },
                    surname: { required: "Unesite prezime" },
                    email: { required: "Unesite svoj email" }, // FIXED
                    phone: { required: "Unesite broj telefona" },
                    password: { required: "Unesite lozinku" }
                }
            });
        }

        /* --------------------------------------------
           3) DESTROY OLD intlTelInput INSTANCE
        ---------------------------------------------*/
        const oldInstance = window.intlTelInputGlobals.getInstance(phoneEl);
        if (oldInstance) oldInstance.destroy();

        /* --------------------------------------------
           4) INIT PHONE INPUT LAST (async)
        ---------------------------------------------*/
        const iti = window.intlTelInput(phoneEl, {
            initialCountry: "ba",
            separateDialCode: true,
            preferredCountries: ["ba", "hr", "rs", "si", "de", "at", "ch"],
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
        });

        /* --------------------------------------------
           5) PASSWORD VISIBILITY TOGGLE
        ---------------------------------------------*/
        $(document)
            .off("click.registerToggle")
            .on("click.registerToggle", "#toggleRegisterPassword", function () {

                const field = $("#register-password");
                const hidden = field.attr("type") === "password";

                field.attr("type", hidden ? "text" : "password");

                $(this)
                    .toggleClass("bi-eye-fill", hidden)
                    .toggleClass("bi-eye-slash-fill", !hidden);
            });

    }
});


app.route({
    view: "forgot_password",
    load: "forgot_password.html",
    cache: false,
    onReady: function () {
        console.log("[Forgot Password] Loaded forgot_password.html");

        $("#forgot-form").on("submit", async function (e) {
            e.preventDefault();

            const email = $("#email").val();

            const res = await fetch(backendBaseURL + "auth/forgot-password", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email })
            });

            const data = await res.json();
            alert(data.message || data.error);
        });
    }
});
app.route({
    view: "reset_password",
    load: "reset_password.html",
    cache: false,
    onReady: function () {
        console.log("[Reset Password] Loaded reset_password.html");

        /** -------------------------
         *  TOKEN EXTRACTION
         * ------------------------- */
        let hash = window.location.hash;
        let token = "";

        if (hash.includes("token=")) {
            token = hash.split("token=")[1];
        }

        window.resetToken = token; // store globally
        console.log("Captured token:", token);

        /** -------------------------
         *  PASSWORD VISIBILITY TOGGLE
         * ------------------------- */
        $(document).on("click", "#toggleResetPassword", function () {
            const passwordField = $("#password");
            const isHidden = passwordField.attr("type") === "password";

            if (isHidden) {
                passwordField.attr("type", "text");
                $(this).removeClass("bi-eye-slash-fill").addClass("bi-eye-fill");
            } else {
                passwordField.attr("type", "password");
                $(this).removeClass("bi-eye-fill").addClass("bi-eye-slash-fill");
            }
        });

        /** -------------------------
         *  FORM SUBMIT
         * ------------------------- */
        $("#reset-form").on("submit", async function (e) {
            e.preventDefault();

            const password = $("#password").val();

            const res = await fetch(backendBaseURL + "auth/reset-password", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ token, password })
            });

            const data = await res.json();
            alert(data.message || data.error);
        });
    }
});



  app.run();
});
