const teacherTable = document.getElementById("teacherTable");
const teacherCard = document.querySelector(".teacher-card");
const addButton = document.querySelector(".btn-add");

const teacherForm = document.createElement("form");
teacherForm.className = "account-form";
teacherForm.innerHTML = `
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Mat khau" required>
    <input name="lecturer_code" placeholder="Ma GV" required>
    <input name="full_name" placeholder="Ho ten" required>
    <input name="faculty" placeholder="Khoa" required>
    <input name="email" type="email" placeholder="Email">
    <button type="submit">Luu</button>
    <p class="form-message"></p>
`;
teacherForm.style.display = "none";
teacherCard.insertBefore(teacherForm, teacherCard.querySelector(".table-responsive"));

addButton.addEventListener("click", () => {
    teacherForm.style.display = teacherForm.style.display === "none" ? "grid" : "none";
});

async function loadTeachers() {
    const user = await requirePageRole(["admin"]);
    if (!user) return;

    try {
        const teachers = await apiRequest("teachers.php");
        teacherTable.innerHTML = teachers.length
            ? teachers.map((teacher) => `
                <tr>
                    <td>${escapeHtml(teacher.lecturer_code)}</td>
                    <td>${escapeHtml(teacher.full_name)}</td>
                    <td>${escapeHtml(teacher.faculty)}</td>
                    <td>${escapeHtml(teacher.email)}</td>
                    <td>${escapeHtml(teacher.username)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">Chua co giang vien.</td></tr>`;
    } catch (error) {
        teacherTable.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
}

teacherForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const message = teacherForm.querySelector(".form-message");
    const formData = new FormData(teacherForm);
    const payload = Object.fromEntries(formData.entries());

    try {
        await apiRequest("teachers.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });
        message.textContent = "Da tao tai khoan giang vien.";
        teacherForm.reset();
        await loadTeachers();
    } catch (error) {
        message.textContent = error.message;
    }
});

document.addEventListener("DOMContentLoaded", loadTeachers);
