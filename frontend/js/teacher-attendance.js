const attendanceTable = document.getElementById("attendanceTable");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["lecturer"]);
    if (!user) return;

    try {
        const rows = await apiRequest("teacher-attendance.php");
        attendanceTable.innerHTML = rows.length
            ? rows.map((item) => `
                <tr>
                    <td>${escapeHtml(item.student_code)}</td>
                    <td>${escapeHtml(item.full_name)}</td>
                    <td>${escapeHtml(item.course_name)}</td>
                    <td>${escapeHtml(item.scanned_at)}</td>
                    <td>${escapeHtml(item.status)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="5">Chua co sinh vien diem danh.</td></tr>`;
    } catch (error) {
        attendanceTable.innerHTML = `<tr><td colspan="5">${escapeHtml(error.message)}</td></tr>`;
    }
});
