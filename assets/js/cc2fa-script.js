document.addEventListener("DOMContentLoaded", function () {
  const resendContainer = document.getElementById("resend-container");

  if (resendContainer) {
    // Create the link element
    const resendLink = document.createElement("a");
    resendLink.href = "#";
    resendLink.id = "cc2fa-resend-code";
    resendLink.textContent = cc2fa_vars.resend_code_text; // Use the localized text

    // Append the link to the container
    resendContainer.appendChild(resendLink);

    // Add the click event listener to the link
    resendLink.addEventListener("click", function (event) {
      event.preventDefault();

      jQuery.ajax({
        url: cc2fa_vars.ajaxurl, // Use the localized variable
        type: "POST",
        data: {
          action: "cc2fa_resend_code",
        },
        success: function (response) {
          if (response.success) {
            alert(response.data); // Display success message
          } else {
            alert("Failed to resend the code. Please try again.");
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred while processing your request.");
        },
      });
    });
  }
});
