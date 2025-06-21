// Modal toggle functionality
const modal = document.getElementById("authModal");
const openModal = document.getElementById("openModal");
const closeModal = document.getElementById("closeModal");
const loginTab = document.getElementById("loginTab");
const registerTab = document.getElementById("registerTab");
const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");

openModal.addEventListener("click", () => {
  modal.classList.remove("hidden");
  showLogin();
});

closeModal.addEventListener("click", () => {
  modal.classList.add("hidden");
});

// Tab Switching
loginTab.addEventListener("click", showLogin);
registerTab.addEventListener("click", showRegister);

function showLogin() {
  loginTab.classList.add("active");
  registerTab.classList.remove("active");
  loginForm.classList.add("active");
  registerForm.classList.remove("active");
}

function showRegister() {
  registerTab.classList.add("active");
  loginTab.classList.remove("active");
  registerForm.classList.add("active");
  loginForm.classList.remove("active");
}

// Close modal on outside click
window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.classList.add("hidden");
  }
});

// Optional: Password Toggle (Add toggle button inside password field if needed)
const passwordToggles = document.querySelectorAll(".toggle-password");

passwordToggles.forEach(toggle => {
  toggle.addEventListener("click", () => {
    const input = toggle.previousElementSibling;
    if (input.type === "password") {
      input.type = "text";
      toggle.textContent = "Hide";
    } else {
      input.type = "password";
      toggle.textContent = "Show";
    }
  });
});

// Optional: Demo error display example for login (use in login.php if session carries error)
const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get("error");
if (error) {
  const errorBox = document.getElementById("loginError");
  errorBox.textContent = decodeURIComponent(error);
  modal.classList.remove("hidden");
  showLogin();
}