
document.addEventListener("DOMContentLoaded", function () {

    const menuButton = document.getElementById("menuBtn");
    const sidebar = document.getElementById("sidebar");

    if (menuButton && sidebar) {
        menuButton.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });
    }

    const search = document.getElementById("tableSearch");
    const table = document.getElementById("dataTable");

    if (search && table) {
        search.addEventListener("input", function () {

            const keyword = search.value.toLowerCase();

            const rows = table.querySelectorAll("tbody tr");

            rows.forEach(function (row) {
                row.style.display =
                    row.innerText.toLowerCase().includes(keyword)
                    ? ""
                    : "none";
            });
        });
    }

});
