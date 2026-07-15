(function() {
    function getCellValue(tr, idx) {
        if (!tr.cells[idx]) return '';
        return tr.cells[idx].textContent.trim();
    }

    function compareValues(a, b, sortType, dir) {
        var valA, valB;
        switch (sortType) {
            case 'date':
                valA = a.getAttribute('data-date') || '';
                valB = b.getAttribute('data-date') || '';
                break;
            case 'capacity':
                valA = parseInt(a.getAttribute('data-capacity'), 10) || 0;
                valB = parseInt(b.getAttribute('data-capacity'), 10) || 0;
                break;
            default:
                valA = a.textContent.trim().toLowerCase();
                valB = b.textContent.trim().toLowerCase();
        }
        if (valA < valB) return -1 * dir;
        if (valA > valB) return 1 * dir;
        return 0;
    }

    var tables = document.querySelectorAll('.sortable-table');
    tables.forEach(function(table) {
        var thead = table.querySelector('thead');
        var tbody = table.querySelector('tbody');
        if (!thead || !tbody) return;

        var ths = thead.querySelectorAll('th.sortable');
        ths.forEach(function(th) {
            th.addEventListener('click', function() {
                var idx = Array.prototype.indexOf.call(th.parentNode.children, th);
                var sortType = th.getAttribute('data-sort') || 'text';

                if (th.classList.contains('asc')) {
                    th.classList.remove('asc');
                    th.classList.add('desc');
                } else {
                    th.classList.remove('desc');
                    th.classList.add('asc');
                }
                var dir = th.classList.contains('asc') ? 1 : -1;

                ths.forEach(function(other) {
                    if (other !== th) {
                        other.classList.remove('asc', 'desc');
                    }
                });

                var rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort(function(rowA, rowB) {
                    var cellsA = rowA.querySelectorAll('td');
                    var cellsB = rowB.querySelectorAll('td');
                    if (idx >= cellsA.length || idx >= cellsB.length) return 0;
                    var cellA = cellsA[idx];
                    var cellB = cellsB[idx];
                    return compareValues(cellA, cellB, sortType, dir);
                });

                rows.forEach(function(row) {
                    tbody.appendChild(row);
                });
            });
        });
    });
})();
