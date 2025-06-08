let UserService = {
  init: function () {
    console.log("[UserService] Init called");

    // Validation for Login
    $("#login-form").validate({
      rules: {
        username: { required: true, email: true },
        password: { required: true, minlength: 3, maxlength: 16 }
      },
      messages: {
        username: {
          required: 'Please enter your email',
          email: 'Please enter a valid email address'
        },
        password: {
          required: 'Please enter your password',
          minlength: 'Password must be at least 3 characters long',
          maxlength: 'Password cannot be longer than 16 characters'
        }
      }
    });

$("#register-form").validate({
    rules: {
        name: {
            required: true,
            minlength: 3
        },
        surname: {
            required: true,
            minlength: 3
        },
        username: {
            required: true,
            email: true
        },
        phone: {
            required: true,
            digits: true, 
            minlength: 8,
            maxlength: 15
        },
        password: {
            required: true,
            minlength: 8,
            maxlength: 16
        }
    },
    messages: {
        name: {
            required: 'Please enter your first name',
            minlength: 'First name must be at least 3 characters'
        },
        surname: {
            required: 'Please enter your last name',
            minlength: 'Last name must be at least 3 characters'
        },
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
            minlength: 'Password must be at least 8 characters',
            maxlength: 'Password cannot be longer than 16 characters'
        }
    }
});



    this.updateAuthButton();

    // LOGIN FORM SUBMIT
    $("#login-form").on("submit", function (e) {
      e.preventDefault();
      console.log("[UserService] Login form submitted");
      const entity = Object.fromEntries(new FormData(this).entries());
      console.log("Login entity:", entity);
      UserService.login(entity);
    });

    // Auth Button (Sign out)
    $(".btn-getstarted").on("click", function (e) {
      if (localStorage.getItem("user_token")) {
        console.log("[UserService] Logout triggered");
        e.preventDefault();
        UserService.logout();
      }
    });
  },

login: function (entity) {
  console.log("[UserService] Sending login request...");
  RestClient.post("auth/login", entity, function (result) {
    console.log("[UserService] Login successful:", result);

    
    localStorage.setItem("user_token", result.data.token);
    localStorage.setItem("userRole", result.data.role);
    localStorage.setItem("user_id", result.data.id);

    
    $(document).trigger("loginSuccess");

    
    toastr.success("Welcome " + result.data.name + "!");
    $("#login-form")[0].reset();
    UserService.updateAuthButton();

    
    switch (result.data.role) {
      case Constants.ADMIN_ROLE:
        window.location.hash = "#admin_panel";
        break;
      case Constants.INSTRUCTOR_ROLE:
        window.location.hash = "#instructor_panel";
        break;
      default:
        window.location.hash = "#home";
    }
  }, function (err) {
    console.error("[UserService] Login error:", err);
    if (err.status === 401 || err.status === 400) {
      toastr.error("Email or password is incorrect.");
    } else {
      toastr.error("Login failed. Please try again.");
    }
  });
},

  register: function (entity) {
    console.log("[UserService] Sending registration request...");
    RestClient.post("auth/register", entity, function () {
      console.log("[UserService] Registration successful!");
      toastr.success("Registration successful! You can now log in.");

      window.location.hash = "#sign_in"; 

    }, function (err) {
      console.error("[UserService] Registration failed:", err);
      toastr.error(err.responseJSON?.error || "Registration failed.");
    });
  },


  logout: function () {
    console.log("[UserService] Logging out...");
    localStorage.clear();
    UserService.updateAuthButton();
    window.location.href = "index.html#home";
  },

  updateAuthButton: function () {
  const token = localStorage.getItem("user_token");
  const role = localStorage.getItem("userRole");
  const authLink = $(".btn-getstarted");
  const navLinks = $("#nav-links");

  // Remove old role-based links
  navLinks.find(".role-nav").remove();

  
  $("#nav-team, #nav-booking, #footer-team, #footer-booking").show(); // reset visibility

  if (token) {
    authLink.text("Logout");
    authLink.attr("href", "#");

    if (role === Constants.ADMIN_ROLE) {
      navLinks.append(`<li class="role-nav"><a href="#admin_panel">Admin Panel</a></li>`);
    } else if (role === Constants.INSTRUCTOR_ROLE) {
      navLinks.append(`<li class="role-nav"><a href="#instructor_panel">Instructor Panel</a></li>`);

      // HIDE FOR INSTRUCTOR
      $("#nav-team, #nav-booking, #nav-contact").hide();
      $("#footer-team, #footer-booking, #footer-contact").hide();
    }
  } else {
    authLink.text("Sign in");
    authLink.attr("href", "#sign_in");
  }
}
};

