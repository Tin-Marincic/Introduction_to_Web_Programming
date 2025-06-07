const ClientLoader = {
  loadServices: function () {
    RestClient.get("api/services", function (services) {
      let html = "";
      services.forEach((service, index) => {
        const delay = 100 * (index + 1);
        const formattedPrice = service.name.toLowerCase().includes("ski school")
          ? `<sup>KM</sup>${service.price}<span> / week</span>`
          : `<sup>KM</sup>${service.price}<span> / hour</span>`;
        html += `
          <div class="col-xl-3 col-lg-6" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="pricing-item">
              <h3>${service.name}</h3>
              <h4>${formattedPrice}</h4>
              <ul>
                <li>${service.description || '...'}</li>
                <li>${service.description || '...'}</li>
                <li>${service.description || '...'}</li>
              </ul>
              <div class="btn-wrap">
                <a href="#booking" class="btn-buy">Book Now</a>
              </div>
            </div>
          </div>`;
      });
      $("#pricing-cards").html(html);
    }, function (err) {
      console.error("Failed to load pricing plans", err);
      $("#pricing-cards").html("<p>Error loading services.</p>");
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
      $("#review-list").html("<p class='text-center'>Unable to load reviews.</p>");
    });
  },

  loadInstructors: function () {
    RestClient.get("users/instructor", function (instructors) {
      let html = "";
      const staticImages = {
        "Vedad Saric": "Vedad.jpg",
        "Haris Saric": "Haris.jpg",
        "Ilma Catovic": "Ilma.jpg",
        "Iman Sijercic": "Iman.jpg",
        "Tin Marincic": "Tin.jpg",
        "Muhamed Saric": "Saka.jpg"
      };
      instructors.forEach((instructor, index) => {
        const fullName = `${instructor.name} ${instructor.surname}`;
        const imgFile = staticImages[fullName] || "default.jpg";
        const delay = 100 * (index + 1);
        html += `
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="${delay}">
            <div class="team-member d-flex align-items-start">
              <div class="pic"><img src="assets/img/team/${imgFile}" class="img-fluid" alt=""></div>
              <div class="member-info">
                <h4>${fullName}</h4>
                <span>${instructor.licence} Instructor</span>
                <a href="#booking" class="btn btn-primary">Book Now</a>
              </div>
            </div>
          </div>`;
      });
      $("#team-list").html(html);
    }, function (err) {
      console.error("Failed to load instructors", err);
      $("#team-list").html("<p class='text-center'>Unable to load instructors.</p>");
    });
  },

initReviewModal: function () {
  const userId = localStorage.getItem("user_id");

  // Hide the Add Review button by default
  $(".add-review-container").hide();

  // Check login
  if (!userId) {
    return;
  }

  // Check if user has bookings
  RestClient.get(`users/${userId}/has-bookings`, function (response) {
    if (response.has_booking) {
      $(".add-review-container").show();
    }
  }, function (err) {
    console.error("Failed to check user booking status:", err);
  });

  // Show modal on button click
  $(".review-button").on("click", function (e) {
    e.preventDefault();
    $("#addReviewModal").modal("show");
  });

  // Submit review
  $("#review-form").on("submit", function (e) {
    e.preventDefault();

    const formData = Object.fromEntries(new FormData(this).entries());

    const payload = {
      user_id: parseInt(userId),
      grade: parseInt(formData.grade),
      note: formData.note
    };

    // Include booking_id if valid
    const parsedBookingId = parseInt(formData.booking_id);
    if (!isNaN(parsedBookingId)) {
      payload.booking_id = parsedBookingId;
    }

    RestClient.post("reviews", payload, function () {
      toastr.success("Review submitted successfully!");
      location.reload(); // ✅ Reload page after success
    }, function (err) {
      console.error("Review submission failed:", err);
      toastr.error(err.responseJSON?.error || "Failed to submit review.");
    });
  });
}


};
