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
          required: 'Unesite svoj email',
          email: 'Unesite ispravnu email adresu'
        },
        password: {
          required: 'Unesite svoju lozinku',
          minlength: 'Lozinka mora imati najmanje 3 karaktera',
          maxlength: 'Lozinka ne može imati više od 16 karaktera'
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

      // Registration form submit
$(document).on("submit", "#register-form", function (e) {
  e.preventDefault();

  console.log("[UserService] Register form submitted");

  if (!$("#register-form").valid()) {
    return;
  }

  const phoneInput = document.getElementById("phone");
  const iti = window.intlTelInputGlobals.getInstance(phoneInput);

  const user = {
    name: $("#name").val().trim(),
    surname: $("#surname").val().trim(),
    username: $("#username").val().trim(),
    phone: iti ? iti.getNumber() : $("#phone").val().trim(),
    password: $("#password").val().trim()
  };

  UserService.register(user);
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

    
    toastr.success("Dobrodošli " + result.data.name + "!");
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
      toastr.error("Email ili Lozinka nisu tačne");
    } else {
      toastr.error("Login neuspješan. Molim Vas probajte ponovo.");
    }
  });
},

  register: function (entity) {
    console.log("[UserService] Sending registration request...");
    RestClient.post("auth/register", entity, function () {
      console.log("[UserService] Registration successful!");
      toastr.success("Registracija uspješna, sada se možete prijaviti!");

      window.location.hash = "#sign_in"; 

    }, function (err) {
      console.error("[UserService] Registracija neuspješna:", err);
      toastr.error(err.responseJSON?.error || "Registracija neuspješna.");
    });
  },



  logout: function () {
    console.log("[UserService] Odjavljivanje...");
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
    authLink.text("Odjava");
    authLink.attr("href", "#");

    if (role === Constants.ADMIN_ROLE) {
      navLinks.append(`<li class="role-nav"><a href="#admin_panel">Admin Panel</a></li>`);
    } else if (role === Constants.INSTRUCTOR_ROLE) {
      navLinks.append(`<li class="role-nav"><a href="#instructor_panel">Instruktor Panel</a></li>`);

      // HIDE FOR INSTRUCTOR
      $("#nav-team, #nav-booking, #nav-contact").hide();
      $("#footer-team, #footer-booking, #footer-contact").hide();
    }
  } else {
    authLink.text("Prijava");
    authLink.attr("href", "#sign_in");
  }
}
};

