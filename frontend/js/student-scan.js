const reader = document.getElementById("reader");
const scanResult = document.getElementById("scanResult");

let html5QrCode = null;
let isSubmittingQr = false;

async function submitQrCode(qrCode) {
    if (!qrCode || isSubmittingQr) return;
    isSubmittingQr = true;
    scanResult.textContent = "Dang diem danh...";

    try {
        const result = await apiRequest("attendance.php", {
            method: "POST",
            body: JSON.stringify({ qr_code: qrCode })
        });
        scanResult.innerHTML = `
            <strong>${escapeHtml(result.message)}</strong><br>
            ${escapeHtml(result.course_name)} - ${escapeHtml(result.class_name)}<br>
            ${escapeHtml(formatDate(result.date))} - ${escapeHtml(result.time)}
        `;

        if (html5QrCode?.isScanning) {
            await html5QrCode.stop();
        }
    } catch (error) {
        scanResult.textContent = error.message;
    } finally {
        isSubmittingQr = false;
    }
}

function renderManualInput() {
    reader.insertAdjacentHTML("beforeend", `
        <div class="manual-qr mt-3">
            <input type="text" id="qrInput" class="form-control mb-3" placeholder="Nhap ma QR neu khong dung camera">
            <button id="submitQr" class="btn btn-primary">Diem danh</button>
        </div>
    `);

    document.getElementById("submitQr").addEventListener("click", () => {
        submitQrCode(document.getElementById("qrInput").value.trim());
    });
}

async function startScanner() {
    if (typeof Html5Qrcode === "undefined") {
        reader.innerHTML = "<p>Khong tai duoc camera scanner. Ban co the nhap ma QR ben duoi.</p>";
        renderManualInput();
        return;
    }

    reader.innerHTML = `<div id="cameraReader"></div>`;
    renderManualInput();
    html5QrCode = new Html5Qrcode("cameraReader");

    try {
        await html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 240, height: 240 } },
            (decodedText) => submitQrCode(decodedText)
        );
    } catch (error) {
        document.getElementById("cameraReader").innerHTML = `
            <p>Khong mo duoc camera. Hay nhap ma QR ben duoi.</p>
        `;
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["student"]);
    if (!user) return;

    await startScanner();
});
