const courseTable = document.getElementById("courseTable");
const courseCard = document.querySelector(".course-card");
const addButton = document.querySelector(".btn-add");

const courseForm = document.createElement("form");
courseForm.className = "account-form";
courseForm.innerHTML = `
    <input name="course_code" placeholder="Ma mon" required>
    <input name="course_name" placeholder="Ten mon hoc" required>
    <input name="credits" type="number" min="1" max="10" placeholder="So tin chi" required>
    <select name="lecturer_id" required>
        <option value="">Chon giang vien</option>
    </select>
    <input name="description" placeholder="Mo ta">
    <button type="submit">Luu</button>
    <p class="form-message"></p>
`;
courseForm.style.display = "none";
courseCard.insertBefore(courseForm, courseCard.querySelector(".table-responsive"));

addButton.addEventListener("click", () => {
    courseForm.style.display = courseForm.style.display === "none" ? "grid" : "none";
});

async function loadTeacherOptions() {
    const teachers = await apiRequest("teachers.php");
    const select = courseForm.querySelector("select[name='lecturer_id']");
    select.innerHTML = `<option value="">Chon giang vien</option>` + teachers.map((teacher) =>
        `<option value="${escapeHtml(teacher.account_id)}">${escapeHtml(teacher.full_name)}</option>`
    ).join("");
}

async function loadCourses() {
    try {
        const courses = await apiRequest("courses.php");
        courseTable.innerHTML = courses.length
            ? courses.map((course) => `
                <tr>
                    <td>${escapeHtml(course.course_code)}</td>
                    <td>${escapeHtml(course.course_name)}</td>
                    <td>${escapeHtml(course.credits)}</td>
                    <td>${escapeHtml(course.lecturer_name)}</td>
                    <td>${escapeHtml(course.description)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">Chua co mon hoc.</td></tr>`;
    } catch (error) {
        courseTable.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
}

courseForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const message = courseForm.querySelector(".form-message");
    const payload = Object.fromEntries(new FormData(courseForm).entries());

    try {
        await apiRequest("courses.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });
        message.textContent = "Da tao mon hoc.";
        courseForm.reset();
        await loadCourses();
    } catch (error) {
        message.textContent = error.message;
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["admin"]);
    if (!user) return;
    await loadTeacherOptions();
    await loadCourses();
});
