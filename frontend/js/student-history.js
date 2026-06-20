const historyTable = document.getElementById("historyTable");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["student"]);
    if (!user) return;

    try {
        const history = await apiRequest("attendance.php");
        historyTable.innerHTML = history.length
            ? history.map((item) => `
                <tr>
                    <td>${escapeHtml(item.course_name)}</td>
                    <td>${escapeHtml(formatDate(item.session_date))}</td>
                    <td>${escapeHtml(item.session_time)}</td>
                    <td>${escapeHtml(item.status)}</td>
                </tr>
            `).join("")
            : `<tr><td colspan="4">Chua co lich su diem danh.</td></tr>`;
    } catch (error) {
        historyTable.innerHTML = `<tr><td colspan="4">${escapeHtml(error.message)}</td></tr>`;
    }
});
