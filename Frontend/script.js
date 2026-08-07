const signUpButton = document.getElementById("signUpButton");
const signInButton = document.getElementById("signInButton");
const signInForm = document.getElementById("signIn");
const signUpForm = document.getElementById("signUp");

if (window.location.hash === "#signup") {
  signInForm.style.display = "none";
  signUpForm.style.display = "block";
} else {
  signInForm.style.display = "block";
  signUpForm.style.display = "none";
}

signUpButton.addEventListener("click", function (e) {
  e.preventDefault();
  signInForm.style.display = "none";
  signUpForm.style.display = "block";
  window.location.hash = "signup";
});

signInButton.addEventListener("click", function (e) {
  e.preventDefault();
  signInForm.style.display = "block";
  signUpForm.style.display = "none";
  window.location.hash = "signin";
});

document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.querySelector(
    '#signUp input[name="password"]',
  );
  if (passwordInput) {
    const indicator = document.createElement("div");
    indicator.style.cssText = `
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            display: none;
            transition: all 0.3s;
        `;
    passwordInput.parentNode.appendChild(indicator);

    passwordInput.addEventListener("input", function () {
      const password = this.value;
      indicator.style.display = "block";

      let strength = 0;
      let message = "";
      let color = "";

      if (password.length >= 8) strength += 1;
      if (/[A-Z]/.test(password)) strength += 1;
      if (/[a-z]/.test(password)) strength += 1;
      if (/[0-9]/.test(password)) strength += 1;
      if (/[^A-Za-z0-9]/.test(password)) strength += 1;

      switch (strength) {
        case 0:
        case 1:
          message = "Weak - Add more characters, numbers, and symbols";
          color = "#dc3545";
          break;
        case 2:
        case 3:
          message = "Medium - Add numbers and symbols for better security";
          color = "#ffc107";
          break;
        case 4:
          message = "Strong - Good password!";
          color = "#28a745";
          break;
        case 5:
          message = "Excellent - Very strong password!";
          color = "#20c997";
          break;
      }

      if (password.length === 0) {
        indicator.style.display = "none";
      } else {
        indicator.textContent = "🔒 " + message;
        indicator.style.background = color + "20";
        indicator.style.color = color;
        indicator.style.border = "1px solid " + color + "40";
      }
    });
  }

  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const password = this.querySelector('input[type="password"]');
      const email = this.querySelector('input[type="email"]');

      if (password && password.value.length > 0 && password.value.length < 8) {
        e.preventDefault();
        alert("Password must be at least 8 characters long.");
        password.focus();
        return false;
      }

      if (email && email.value.length > 0) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
          e.preventDefault();
          alert("Please enter a valid email address.");
          email.focus();
          return false;
        }
      }
    });
  });

  const messages = document.querySelectorAll(".message");
  messages.forEach((msg) => {
    setTimeout(() => {
      msg.style.opacity = "0";
      msg.style.transition = "opacity 0.5s ease";
      setTimeout(() => {
        msg.style.display = "none";
      }, 500);
    }, 5000);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const fileName = this.files[0]?.name || "No file selected";
      const fileSize = this.files[0]?.size || 0;

      let sizeLabel = "";
      if (fileSize > 0) {
        if (fileSize < 1024) {
          sizeLabel = fileSize + " B";
        } else if (fileSize < 1048576) {
          sizeLabel = (fileSize / 1024).toFixed(1) + " KB";
        } else {
          sizeLabel = (fileSize / 1048576).toFixed(1) + " MB";
        }
      }

      const infoDiv = this.parentNode.querySelector(".file-info");
      if (infoDiv) {
        infoDiv.innerHTML = `<i class="fa-solid fa-file"></i> ${fileName} ${sizeLabel ? "(" + sizeLabel + ")" : ""}`;
      }
    });
  }
});
