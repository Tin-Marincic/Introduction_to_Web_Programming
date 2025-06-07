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
  app.route({ view: 'register', load: 'register.html' });

  app.route({
  view: 'admin_panel',
  load: 'admin_panel.html',
  onReady: function () {
    AdminPanelService.loadServices();
    AdminPanelService.loadInstructors();
    AdminPanelService.loadInstructorBookings();
    AdminPanelService.loadSkiSchoolAvailability();

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

$("#register-form").validate({
  rules: {
    name: { required: true, minlength: 2 },
    surname: { required: true, minlength: 2 },
    username: { required: true, email: true },
    phone: {
      required: true,
      digits: true,
      minlength: 8,
      maxlength: 15
    },
    password: { required: true, minlength: 3, maxlength: 16 },
    "confirm-password": { equalTo: "#password" }
  },
  messages: {
    name: { required: 'Please enter your first name' },
    surname: { required: 'Please enter your last name' },
    username: {
      required: 'Please enter your email',
      email: 'Please enter a valid email address'
    },
    phone: {
      required: 'Please enter your phone number',
      digits: 'Only digits are allowed',
      minlength: 'Phone number must be at least 8 digits',
      maxlength: 'Phone number cannot exceed 15 digits'
    },
    password: {
      required: 'Please enter your password',
      minlength: 'Password must be at least 3 characters long',
      maxlength: 'Password cannot be longer than 16 characters'
    },
    "confirm-password": {
      equalTo: 'Passwords do not match'
    }
  }
});


    $("#register-form").on("submit", function (e) {
      e.preventDefault();
      console.log("[UserService] Register form submitted");

      if (!$("#register-form").valid()) {
        console.warn("[UserService] Register form validation failed");
        return;
      }

      const entity = Object.fromEntries(new FormData(this).entries());
      console.log("Raw registration form data:", entity);

      delete entity["confirm-password"];
      console.log("Cleaned registration entity:", entity);

      UserService.register(entity);
    });
  }
});



  app.run();
});
