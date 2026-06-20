const totalSessions = document.getElementById("totalSessions");
const totalStudents = document.getElementById("totalStudents");
const attendanceRate = document.getElementById("attendanceRate");
const chartContainer = document.getElementById("chartContainer");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["lecturer"]);
    if (!user) return;

    try {
        const stats = await apiRequest("statistics.php");
        totalSessions.textContent = stats.totalSessions;
        totalStudents.textContent = stats.totalStudents;
        attendanceRate.textContent = `${stats.attendanceRate}%`;

        chartContainer.innerHTML = stats.byCourse?.length
            ? stats.byCourse.map((item) => `
                <div class="stat-row">
                    <span>${escapeHtml(item.course_name)}</span>
                    <strong>${escapeHtml(item.total)}</strong>
                </div>
            `).join("")
            : "Chua co du lieu thong ke.";
    } catch (error) {
        chartContainer.textContent = error.message;
    }
});
