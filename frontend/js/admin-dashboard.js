const adminName = document.getElementById("adminName");
const adminID = document.getElementById("adminID");
const totalStudents = document.getElementById("totalStudents");
const totalTeachers = document.getElementById("totalTeachers");
const totalCourses = document.getElementById("totalCourses");

document.addEventListener("DOMContentLoaded", async () => {
    const user = await requirePageRole(["admin"]);
    if (!user) return;

    adminName.textContent = `Xin chao, ${user.username}`;
    adminID.textContent = `Ma Admin: ${user.id}`;

    try {
        const stats = await apiRequest("statistics.php");
        totalStudents.textContent = stats.totalStudents;
        totalTeachers.textContent = stats.totalTeachers;
        totalCourses.textContent = stats.totalCourses;
    } catch (error) {
        totalStudents.textContent = "-";
        totalTeachers.textContent = "-";
        totalCourses.textContent = "-";
    }
});
