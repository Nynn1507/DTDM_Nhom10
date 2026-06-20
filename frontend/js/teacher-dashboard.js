const teacherName = document.getElementById("teacherName");
const teacherID = document.getElementById("teacherID");
const faculty = document.getElementById("faculty");
const classContainer = document.getElementById("classContainer");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["lecturer"]);
    if (!user) return;

    const info = user.info || {};
    teacherName.textContent = `Xin chao, ${info.full_name || user.username}`;
    teacherID.textContent = `Ma giang vien: ${info.lecturer_code || ""}`;
    faculty.textContent = `Khoa: ${info.faculty || ""}`;

    try {
        const sessions = await apiRequest("sessions.php");
        classContainer.innerHTML = sessions.length
            ? sessions.map((item) => `
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>${escapeHtml(item.class_name)}</h5>
                            <p>${escapeHtml(item.course_name)}</p>
                            <p>${escapeHtml(formatDate(item.session_date))} - ${escapeHtml(item.session_time)}</p>
                            <p>Trang thai: ${escapeHtml(item.status)}</p>
                        </div>
                    </div>
                </div>
            `).join("")
            : "<p>Chua co buoi hoc nao.</p>";
    } catch (error) {
        classContainer.innerHTML = `<p>${escapeHtml(error.message)}</p>`;
    }
});
