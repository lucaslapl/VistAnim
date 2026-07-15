(function() {
    var addBtn = document.getElementById('add_date_btn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            var container = document.getElementById('dates_container');
            var rows = container.querySelectorAll('.date-row');
            if (rows.length >= 10) {
                alert('Maximum 10 dates autorisées.');
                return;
            }
            var now = new Date().toISOString().slice(0, 16);
            var row = document.createElement('div');
            row.className = 'date-row';
            row.innerHTML = '<input type="datetime-local" name="event_dates[]" required style="flex: 1;" min="' + now + '">' +
                '<button type="button" class="btn-remove-date">&times;</button>';
            container.appendChild(row);
        });
    }

    var datesContainer = document.getElementById('dates_container');
    if (datesContainer) {
        datesContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-date')) {
                e.target.parentElement.remove();
            }
        });
    }

    var indicator = document.getElementById('draft-indicator');
    if (!indicator) return;

    var form = document.getElementById('event-form');
    if (!form) return;

    var savedTime = document.getElementById('draft-saved-time');
    var hasChanges = false;
    var isSaving = false;

    form.addEventListener('change', function() { hasChanges = true; });
    form.addEventListener('input', function() { hasChanges = true; });

    function doAutoSave() {
        if (!hasChanges || isSaving) return;
        isSaving = true;
        var formData = new FormData(form);
        formData.set('save_draft', '1');
        formData.set('ajax', '1');
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            isSaving = false;
            if (json.success) {
                var hid = form.querySelector('input[name="draft_id"]');
                if (hid) hid.value = json.draft_id;
                if (savedTime) savedTime.textContent = json.saved_at;
                indicator.style.display = 'block';
                hasChanges = false;
            }
        })
        .catch(function() { isSaving = false; });
    }

    setInterval(doAutoSave, 30000);
})();
