let UserService = {

  init: function () {
    console.log("[UserService] Init called");

    this.updateAuthButton();

        // HANDLE EMAIL VERIFICATION LINK
    const hash = window.location.hash;

    if (hash.startsWith("#verify_email")) {
      const token = hash.split("token=")[1];

      if (token) {
        RestClient.post(
          "auth/verify-email",
          { token },
          function () {
            toastr.success("Email uspješno verifikovan! Sada se možete prijaviti.");
            window.location.hash = "#sign_in";
          },
          function () {
            toastr.error("Verifikacijski link nije važeći ili je istekao.");
            window.location.hash = "#home";
          }
        );
      }
    }


    /* --------------------------------------------
       LOGIN FORM SUBMIT (SPA SAFE, DELEGATED)
    ---------------------------------------------*/
    $(document)
      .off("submit.loginForm")
      .on("submit.loginForm", "#login-form", function (e) {
        e.preventDefault();
        console.log("[UserService] Login form submitted");

        if (!$("#login-form").valid()) return;

        const entity = Object.fromEntries(new FormData(this).entries());
        UserService.login(entity);
      });


    /* --------------------------------------------
       LOGOUT BUTTON
    ---------------------------------------------*/
    $(document)
      .off("click.logoutBtn")
      .on("click.logoutBtn", ".btn-getstarted", function (e) {
        if (localStorage.getItem("user_token")) {
          e.preventDefault();
          UserService.logout();
        }
      });


    /* --------------------------------------------
       REGISTER FORM SUBMIT (SPA SAFE, DELEGATED)
    ---------------------------------------------*/
    $(document)
      .off("submit.registerForm")
      .on("submit.registerForm", "#register-form", function (e) {
        e.preventDefault();

        console.log("[UserService] Register form submitted");

        if (!$("#register-form").valid()) {
          console.warn("Register form not valid.");
          return;
        }

        const phoneEl = document.getElementById("phone");
        const iti = window.intlTelInputGlobals.getInstance(phoneEl);

        const user = {
          name: $("#name").val().trim(),
          surname: $("#surname").val().trim(),
          username: $("#email").val().trim(),  
          phone: iti ? iti.getNumber() : $("#phone").val().trim(),
          password: $("#register-password").val().trim(),
        };

        UserService.register(user);
      });

  },


  /* --------------------------------------------
     LOGIN API CALL
  ---------------------------------------------*/
  login: function (entity) {
    console.log("[UserService] Sending login request...");

    RestClient.post(
      "auth/login",
      entity,
      function (result) {

        console.log("[UserService] Login successful:", result);

        localStorage.setItem("user_token", result.data.token);
        localStorage.setItem("userRole", result.data.role);
        localStorage.setItem("user_id", result.data.id);

        $(document).trigger("loginSuccess");

        toastr.success("Dobrodošli " + result.data.name + "!");
        $("#login-form")[0]?.reset();

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
      },

      function (err) {
        console.error("[UserService] Login error:", err);

        const msg = err.responseJSON?.error || "Greška prilikom prijave.";
        toastr.error(msg);

        if (msg.includes("verifikujte email")) {
          toastr.info("Provjerite inbox i kliknite na verifikacijski link.");
        }
      }

    );
  },


  /* --------------------------------------------
     REGISTER API CALL
  ---------------------------------------------*/
  register: function (entity) {
    console.log("[UserService] Sending registration request...");

    RestClient.post(
      "auth/register",
      entity,
      function () {
        console.log("[UserService] Registration successful!");
        toastr.success("Registracija uspješna! Provjerite email i potvrdite registraciju.");

        const form = document.getElementById("register-form");
        if (form) form.reset();

        window.location.hash = "#home";
      },
      function (err) {
        console.error("[UserService] Registration error:", err);
        toastr.error(err.responseJSON?.error || "Registracija neuspješna.");
      }
    );
  },



  /* --------------------------------------------
     LOGOUT
  ---------------------------------------------*/
  logout: function () {
    console.log("[UserService] Logging out...");
    localStorage.clear();
    UserService.updateAuthButton();
    window.location.href = "index.html#home";
  },


  /* --------------------------------------------
     UPDATE NAV BUTTONS
  ---------------------------------------------*/
  updateAuthButton: function () {
    const token = localStorage.getItem("user_token");
    const role = localStorage.getItem("userRole");
    const authLink = $(".btn-getstarted");
    const navLinks = $("#nav-links");

    navLinks.find(".role-nav").remove();
    $("#nav-team, #nav-booking, #footer-team, #footer-booking").show();

    if (token) {
      authLink.text("Odjava").attr("href", "#");

      if (role === Constants.ADMIN_ROLE) {
        navLinks.append(`<li class="role-nav"><a href="#admin_panel">Admin Panel</a></li>`);
      } else if (role === Constants.INSTRUCTOR_ROLE) {
        navLinks.append(`<li class="role-nav"><a href="#instructor_panel">Instruktor Panel</a></li>`);

        $("#nav-team, #nav-booking, #nav-contact").hide();
        $("#footer-team, #footer-booking, #footer-contact").hide();
      }
    } else {
      authLink.text("Prijava").attr("href", "#sign_in");
    }
  }
};
