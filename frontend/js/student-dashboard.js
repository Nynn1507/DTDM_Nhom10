const studentName = document.getElementById("studentName");
const studentID = document.getElementById("studentID");
const studentClass = document.getElementById("studentClass");
const subjectContainer = document.getElementById("subjectContainer");
const recentAttendance = document.getElementById("recentAttendance");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["student"]);
    if (!user) return;

    const info = user.info || {};
    studentName.textContent = `Xin chao, ${info.full_name || user.username}`;
    studentID.textContent = `MSSV: ${info.student_code || ""}`;
    studentClass.textContent = `Lop: ${info.class_name || info.class_id || ""}`;

    try {
        const sessions = await apiRequest("sessions.php");
        subjectContainer.innerHTML = sessions.length
            ? sessions.map((item) => `
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>${escapeHtml(item.course_name)}</h5>
                            <p>Giang vien: ${escapeHtml(item.lecturer_name)}</p>
                            <p>Ngay: ${escapeHtml(formatDate(item.session_date))}</p>
                            <p>Tiet: ${escapeHtml(item.session_time)}</p>
                        </div>
                    </div>
                </div>
            `).join("")
            : "<p>Hom nay chua co buoi hoc nao.</p>";

        const history = await apiRequest("attendance.php");
        recentAttendance.innerHTML = history.slice(0, 5).map((item) => `
            <p>${escapeHtml(item.course_name)} - ${escapeHtml(formatDate(item.session_date))} - ${escapeHtml(item.status)}</p>
        `).join("") || "Chua co lich su diem danh.";
    } catch (error) {
        subjectContainer.innerHTML = `<p>${escapeHtml(error.message)}</p>`;
    }
});
