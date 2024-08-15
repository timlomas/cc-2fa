document.addEventListener("DOMContentLoaded", function () {
  const resendContainer = document.getElementById("resend-container");
  const countdownTimer = document.getElementById("countdown-timer");
  const submitButton = document.querySelector('input[type="submit"]');
  let timeLeft = parseInt(cc2fa_vars.time_left, 10); // Get remaining time in seconds

  /**
   * Function to update the countdown timer.
   *
   * Updates the countdown timer every second. If the time runs out,
   * it shows a code expiration message, disables the submit button,
   * and replaces the resend link with a "Send new code" link.
   */
  function updateCountdown() {
    if (timeLeft <= 0) {
      countdownTimer.textContent = cc2fa_vars.code_expired_message; // Show expiration message
      submitButton.disabled = true; // Disable the submit button
      replaceResendWithNewCode(); // Replace the resend link with "Send new code"
    } else {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      countdownTimer.textContent = `Time left: ${minutes}m ${seconds}s`;
      timeLeft -= 1;
      setTimeout(updateCountdown, 1000); // Update every second
    }
  }

  /**
   * Function to replace the resend link with a "Send new code" link.
   *
   * If the "Send new code" link does not already exist, it creates one,
   * hides the existing resend link, and appends the new link to the container.
   */
  function replaceResendWithNewCode() {
    if (document.getElementById("cc2fa-new-code")) {
      return; // Exit if the link already exists to prevent duplicates
    }

    const resendLink = document.getElementById("cc2fa-resend-code");
    resendLink.style.display = "none"; // Hide the resend link

    const newCodeLink = document.createElement("a");
    newCodeLink.href = "#";
    newCodeLink.id = "cc2fa-new-code";
    newCodeLink.textContent = cc2fa_vars.send_new_code_text;

    resendContainer.appendChild(newCodeLink);

    newCodeLink.addEventListener("click", function (event) {
      event.preventDefault();
      sendNewCode(newCodeLink, resendLink);
    });
  }

  /**
   * Function to send a new verification code via AJAX.
   *
   * Sends an AJAX request to generate and send a new verification code to the user.
   * Resets the timer, re-enables the submit button, and restores the resend link.
   */
  function sendNewCode(newCodeLink, resendLink) {
    jQuery.ajax({
      url: cc2fa_vars.ajaxurl, // Use the localized variable
      type: "POST",
      data: {
        action: "cc2fa_resend_code",
      },
      success: function (response) {
        if (response.success) {
          alert(cc2fa_vars.new_code_sent_message); // Display "New code sent" message
          timeLeft = parseInt(cc2fa_vars.time_left, 10); // Reset timer
          updateCountdown(); // Restart countdown
          submitButton.disabled = false; // Re-enable the submit button
          newCodeLink.remove(); // Remove "Send new code" link
          resendLink.style.display = "inline"; // Restore the resend link
        } else {
          alert("Failed to send a new code. Please try again.");
        }
      },
      error: function (xhr, status, error) {
        alert("An error occurred while processing your request.");
      },
    });
  }

  // Start the countdown
  updateCountdown();

  if (resendContainer) {
    const resendLink = document.createElement("a");
    resendLink.href = "#";
    resendLink.id = "cc2fa-resend-code";
    resendLink.textContent = cc2fa_vars.resend_code_text;

    resendContainer.appendChild(resendLink);

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
            alert(cc2fa_vars.resend_code_message); // Display "Code resent" message
            timeLeft = parseInt(cc2fa_vars.time_left, 10); // Reset timer
            updateCountdown(); // Restart countdown
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
