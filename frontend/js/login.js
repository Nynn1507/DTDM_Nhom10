const roleButtons = document.querySelectorAll(".role-btn");
const loginForm = document.querySelector("form");
const usernameInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const loginButton = document.querySelector(".btn-login");
const errorBox = document.createElement("div");

let selectedRole = "student";

errorBox.className = "login-error";
errorBox.style.color = "#dc3545";
errorBox.style.marginBottom = "12px";
errorBox.style.minHeight = "20px";
loginButton.parentNode.insertBefore(errorBox, loginButton);

roleButtons.forEach((button) => {
    button.addEventListener("click", () => {
        roleButtons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");
        selectedRole = button.dataset.role;
    });
});

loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorBox.textContent = "";
    loginButton.disabled = true;
    loginButton.textContent = "Dang nhap...";

    try {
        const result = await apiRequest("login.php", {
            method: "POST",
            body: JSON.stringify({
                username: usernameInput.value.trim(),
                password: passwordInput.value
            })
        });

        const actualFolder = roleToFolder(result.user.role);
        if (actualFolder !== selectedRole) {
            throw new Error("Tai khoan khong dung vai tro da chon");
        }

        window.location.href = `${actualFolder}/dashboard.html`;
    } catch (error) {
        errorBox.textContent = error.message;
    } finally {
        loginButton.disabled = false;
        loginButton.textContent = "Dang nhap";
    }
});
