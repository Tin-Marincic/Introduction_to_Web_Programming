let RestClient = {
  get: function (url, callback, error_callback) {
    console.log("🌐 GET →", Constants.PROJECT_BASE_URL + url);
    $.ajax({
      url: Constants.PROJECT_BASE_URL + url,
      type: "GET",
      beforeSend: function (xhr) {
        const token = localStorage.getItem("user_token");
        if (token) {
          xhr.setRequestHeader("Authorization", "Bearer " + token); // ✅ USE STANDARD HEADER
        }
      },
      success: function (response) {
        if (callback) callback(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error(" GET Error:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          response: jqXHR.responseJSON,
          error: errorThrown
        });
        if (error_callback) error_callback(jqXHR);
      }
    });
  },

  request: function (url, method, data, callback, error_callback) {
    console.log(` ${method} →`, Constants.PROJECT_BASE_URL + url);
    console.log(" Data:", data);

    $.ajax({
      url: Constants.PROJECT_BASE_URL + url,
      type: method,
      contentType: "application/json",
      data: JSON.stringify(data),
      beforeSend: function (xhr) {
        const token = localStorage.getItem("user_token");
        if (token) {
          xhr.setRequestHeader("Authorization", "Bearer " + token); // ✅ FIXED
        }
      },
      success: function (response) {
        console.log(" Success Response:", response);
        if (callback) callback(response);
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error(" Request Error:", {
          status: jqXHR.status,
          statusText: jqXHR.statusText,
          response: jqXHR.responseJSON,
          error: errorThrown
        });
        if (error_callback) {
          error_callback(jqXHR);
        } else {
          toastr.error(jqXHR.responseJSON?.message || "Operation failed");
        }
      }
    });
  },

  post: function (url, data, callback, error_callback) {
    RestClient.request(url, "POST", data, callback, error_callback);
  },

  delete: function (url, data, callback, error_callback) {
    RestClient.request(url, "DELETE", data, callback, error_callback);
  },

  patch: function (url, data, callback, error_callback) {
    RestClient.request(url, "PATCH", data, callback, error_callback);
  },

  put: function (url, data, callback, error_callback) {
    RestClient.request(url, "PUT", data, callback, error_callback);
  }
};
