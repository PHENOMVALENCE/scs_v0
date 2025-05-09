// Wait for the DOM to be fully loaded
document.addEventListener("DOMContentLoaded", () => {
  // Password confirmation validation for registration form
  const registerForm = document.querySelector('form[action="register_process.php"]')

  if (registerForm) {
    registerForm.addEventListener("submit", (event) => {
      const password = document.getElementById("password").value
      const confirmPassword = document.getElementById("confirm_password").value

      if (password !== confirmPassword) {
        event.preventDefault()
        alert("Passwords do not match!")
      }
    })
  }

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert")

  if (alerts.length > 0) {
    setTimeout(() => {
      alerts.forEach((alert) => {
        alert.style.opacity = "0"
        alert.style.transition = "opacity 0.5s"

        setTimeout(() => {
          alert.style.display = "none"
        }, 500)
      })
    }, 5000)
  }

  // Confirm before deleting a complaint
  const deleteButtons = document.querySelectorAll('a[href^="delete_complaint.php"]')

  deleteButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      if (!confirm("Are you sure you want to delete this complaint?")) {
        event.preventDefault()
      }
    })
  })
})
