const studentTable = document.getElementById("studentTable");
const studentCard = document.querySelector(".student-card");
const addButton = document.querySelector(".btn-add");

const classPanel = document.createElement("div");
classPanel.className = "class-panel";
classPanel.innerHTML = `
    <div class="class-panel-header">
        <h3>Quan ly lop hoc</h3>
        <form id="classForm">
            <input name="class_name" placeholder="Ten lop moi" required>
            <button type="submit">Them lop</button>
        </form>
    </div>
    <div id="classList" class="class-list"></div>
    <p id="classMessage" class="form-message"></p>
`;
studentCard.insertBefore(classPanel, studentCard.querySelector(".header").nextSibling);

const studentForm = document.createElement("form");
studentForm.className = "account-form";
studentForm.innerHTML = `
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Mat khau" required>
    <input name="student_code" placeholder="MSSV" required>
    <input name="full_name" placeholder="Ho ten" required>
    <select name="class_id">
        <option value="">Chon lop</option>
    </select>
    <input name="email" type="email" placeholder="Email">
    <button type="submit">Luu</button>
    <p class="form-message"></p>
`;
studentForm.style.display = "none";
studentCard.insertBefore(studentForm, studentCard.querySelector(".table-responsive"));

addButton.addEventListener("click", () => {
    studentForm.style.display = studentForm.style.display === "none" ? "grid" : "none";
});

async function loadClasses() {
    const classes = await apiRequest("classes.php");
    const select = studentForm.querySelector("select[name='class_id']");
    const classList = document.getElementById("classList");

    select.innerHTML = `<option value="">Chon lop</option>` + classes.map((item) =>
        `<option value="${escapeHtml(item.id)}">${escapeHtml(item.class_name)}</option>`
    ).join("");

    classList.innerHTML = classes.length
        ? classes.map((item) => `<span>${escapeHtml(item.class_name)}</span>`).join("")
        : "Chua co lop hoc.";
}

async function loadStudents() {
    const user = await requirePageRole(["admin"]);
    if (!user) return;

    try {
        const students = await apiRequest("students.php");
        studentTable.innerHTML = students.length
            ? students.map((student) => `
                <tr>
                    <td>${escapeHtml(student.student_code)}</td>
                    <td>${escapeHtml(student.full_name)}</td>
                    <td>${escapeHtml(student.class_name)}</td>
                    <td>${escapeHtml(student.email)}</td>
                    <td>${escapeHtml(student.account_id)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">Chua co sinh vien.</td></tr>`;
    } catch (error) {
        studentTable.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
}

studentForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const message = studentForm.querySelector(".form-message");
    const formData = new FormData(studentForm);
    const payload = Object.fromEntries(formData.entries());

    try {
        await apiRequest("students.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });
        message.textContent = "Da tao tai khoan sinh vien.";
        studentForm.reset();
        await loadStudents();
    } catch (error) {
        message.textContent = error.message;
    }
});

document.getElementById("classForm").addEventListener("submit", async (event) => {
    event.preventDefault();
    const message = document.getElementById("classMessage");
    const form = event.currentTarget;
    const payload = Object.fromEntries(new FormData(form).entries());

    try {
        const result = await apiRequest("classes.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });
        message.textContent = result.message;
        form.reset();
        await loadClasses();
    } catch (error) {
        message.textContent = error.message;
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["admin"]);
    if (!user) return;
    await loadClasses();
    await loadStudents();
});
