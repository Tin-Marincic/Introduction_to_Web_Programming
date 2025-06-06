let Constants = {
  PROJECT_BASE_URL:
    location.hostname === "localhost" || location.hostname === "127.0.0.1"
      ? "http://localhost/TinMarincic/Introduction_to_Web_Programming/backend/"
      : "https://unisport-9kjwi.ondigitalocean.app/",
  USER_ROLE: "user",
  ADMIN_ROLE: "admin",
  INSTRUCTOR_ROLE: "instructor"
};
