const subject = document.getElementById("subject");
const classRoom = document.getElementById("class");
const date = document.getElementById("date");
const session = document.getElementById("session");
const qrCode = document.getElementById("qrCode");

function renderQrImage(value) {
    const encodedValue = encodeURIComponent(value);
    const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodedValue}`;

    qrCode.innerHTML = `
        <img src="${qrImageUrl}" alt="QR diem danh" class="qr-image">
        <strong class="qr-text">${escapeHtml(value)}</strong>
        <p>Ma nay het han sau 1 gio.</p>
    `;
}

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["lecturer"]);
    if (!user) return;

    try {
        const options = await apiRequest("session-options.php");
        subject.innerHTML = `<option value="">Chon mon hoc</option>` + options.courses.map((course) =>
            `<option value="${escapeHtml(course.id)}">${escapeHtml(course.course_name)}</option>`
        ).join("");
        classRoom.innerHTML = `<option value="">Chon lop hoc</option>` + options.classes.map((item) =>
            `<option value="${escapeHtml(item.id)}">${escapeHtml(item.class_name)}</option>`
        ).join("");
    } catch (error) {
        qrCode.textContent = error.message;
    }

    document.querySelector(".btn-generate").addEventListener("click", async () => {
        qrCode.textContent = "Dang tao QR...";

        try {
            const result = await apiRequest("sessions.php", {
                method: "POST",
                body: JSON.stringify({
                    course_id: subject.value,
                    class_id: classRoom.value,
                    session_date: date.value,
                    session_time: session.value.trim()
                })
            });

            renderQrImage(result.section.qr_code);
        } catch (error) {
            qrCode.textContent = error.message;
        }
    });
});
