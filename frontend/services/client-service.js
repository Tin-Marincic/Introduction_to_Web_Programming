const ClientLoader = {
  loadServices: function () {
    RestClient.get("api/services", function (services) {
      let html = "";

      // Dinamičke usluge iz API-ja
      services.forEach((service, index) => {
        const delay = 100 * (index + 1);
        const formattedPrice = service.name.toLowerCase().includes("ski škola")
          ? `<sup>KM</sup>${service.price || '...'}<span> / sedmica</span>`
          : `<sup>KM</sup>${service.price || '...'}<span> / sat</span>`;

        html += `
          <div class="col-xl-3 col-lg-6" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="pricing-item">
              <h3>${service.name}</h3>
              <h4>${formattedPrice}</h4>
              <ul>
                <li>${service.description || '...'}</li>
              </ul>
              <div class="btn-wrap">
                <a href="#booking" class="btn-buy">Rezerviši sada</a>
              </div>
            </div>
          </div>`;
      });

      // 🔹 Staticka usluga: Noćno skijanje instrukcije (bez dugmeta)
     // 🔹 Static service: Noćno skijanje instrukcije
    const staticDelay = 100 * (services.length + 1);
    html += `
      <div class="col-xl-3 col-lg-6" data-aos="fade-up" data-aos-delay="${staticDelay}">
        <div class="pricing-item">
          <h3>Noćno skijanje instrukcije</h3>
          <h4><sup>KM</sup>...<span> / sat</span></h4>
          <ul>
            <li>Minimalno 2 sata – super iskustvo pod reflektorima i idealno za brzi napredak!</li>
            <li>Ovu uslugu nije moguće rezervisati putem sistema.</li>
            <li>Rezervacije isključivo putem telefona:</li>
            <li><strong><a href="tel:+38761337548">+387 61 337 548</a></strong></li>
          </ul>

          <!-- Empty block so styling stays the same -->
          <div class="btn-wrap" style="height:52px;"></div>
        </div>
      </div>
    `;


      $("#pricing-cards").html(html);
    }, function (err) {
      console.error("Failed to load pricing plans", err);
      $("#pricing-cards").html("<p>Greška pri učitavanju usluga.</p>");
    });
  },

  loadReviews: function () {
    RestClient.get("reviews", function (reviews) {
      let html = "";
      reviews.forEach((review, index) => {
        const delay = 100 * (index + 1);
        html += `
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="review-item">
              <h3>${review.user_full_name || "Anonymous"}</h3>
              <div class="stars">
                ${'<i class="bi bi-star-fill"></i>'.repeat(review.grade || 0)}
              </div>
              <p>
                <i class="bi bi-quote quote-icon-left"></i>
                <span>${review.note}</span>
                <i class="bi bi-quote quote-icon-right"></i>
              </p>
            </div>
          </div>`;
      });
      $("#review-list").html(html);
    }, function () {
      $("#review-list").html("<p class='text-center'>Nije moguće učitati recenzije.</p>");
    });
  },

  loadInstructors: function () {
    RestClient.get("users/instructor", function (instructors) {
      let html = "";
      instructors.forEach((instructor, index) => {
        const fullName = `${instructor.name} ${instructor.surname}`;
        const delay = 100 * (index + 1);

        const imgFile = instructor.image_url ? instructor.image_url : "default.jpg";

        html += `
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="team-member d-flex align-items-start">
              <div class="pic">
                <img src="assets/img/team/${imgFile}" class="img-fluid" alt="">
              </div>
              <div class="member-info">
                <h4>${fullName}</h4>
                <span>${instructor.licence}</span>
                <h6>instruktor</h6>
                <a href="#booking" class="btn btn-primary">Rezerviši sada</a>
              </div>
            </div>
          </div>`;
      });
      $("#team-list").html(html);
    });
  },


  initReviewModal: function () {
    const userId = localStorage.getItem("user_id");

    $(".add-review-container").hide();

    if (!userId) {
      return;
    }

    RestClient.get(`users/${userId}/has-bookings`, function (response) {
      if (response.has_booking) {
        $(".add-review-container").show();
      }
    }, function (err) {
      console.error("Failed to check user booking status:", err);
    });

    $(".review-button").on("click", function (e) {
      e.preventDefault();
      $("#addReviewModal").modal("show");
    });

    $("#review-form").off("submit").on("submit", function (e) {
      e.preventDefault();

      const formData = Object.fromEntries(new FormData(this).entries());

      const payload = {
        user_id: parseInt(userId),
        grade: parseInt(formData.grade),
        note: formData.note
      };

      const parsedBookingId = parseInt(formData.booking_id);
      if (!isNaN(parsedBookingId)) {
        payload.booking_id = parsedBookingId;
      }

      RestClient.post("reviews", payload, function () {
        toastr.success("Recenzija je uspješno poslana!");
        location.reload();
      }, function (err) {
        console.error("Review submission failed:", err);
        toastr.error(err.responseJSON?.error || "Nije uspjelo slanje recenzije.");
      });
    });
  }
};
