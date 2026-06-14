// Lấy tất cả button vai trò
const roleButtons = document.querySelectorAll(".role-btn");

// Mặc định là sinh viên
let selectedRole = "student";

// Xử lý khi click chọn vai trò
roleButtons.forEach(button => {

    button.addEventListener("click", () => {

        // Xóa active của tất cả button
        roleButtons.forEach(btn => {
            btn.classList.remove("active");
        });

        // Thêm active cho button được chọn
        button.classList.add("active");

        // Cập nhật vai trò hiện tại
        selectedRole = button.dataset.role;

    });

});


// Xử lý nút đăng nhập
document.querySelector(".btn-login").addEventListener("click", (e) => {

    // Ngăn form submit
    e.preventDefault();

    if (selectedRole === "student") {
        window.location.href = "student/dashboard.html";
    }

    else if (selectedRole === "teacher") {
        window.location.href = "teacher/dashboard.html";
    }

    else if (selectedRole === "admin") {
        window.location.href = "admin/dashboard.html";
    }

});