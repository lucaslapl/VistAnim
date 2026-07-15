function togglePriceFields() {
    var checkBox = document.getElementById("is_paid");
    var priceZone = document.getElementById("price_details_zone");
    var priceInput = document.getElementById("price_details");
    var priceAmount = document.getElementById("price_amount");
    if (!checkBox || !priceZone) return;
    if (checkBox.checked) {
        priceZone.style.display = "block";
        if (priceInput) priceInput.required = true;
        if (priceAmount) priceAmount.required = true;
    } else {
        priceZone.style.display = "none";
        if (priceInput) { priceInput.required = false; priceInput.value = ""; }
        if (priceAmount) { priceAmount.required = false; priceAmount.value = ""; }
    }
}

function toggleMinParticipantsFields() {
    var checkBox = document.getElementById('toggle_min_participants');
    var container = document.getElementById('min_participants_container');
    var input = document.getElementById('min_participants');
    if (checkBox && checkBox.checked) {
        container.style.display = 'block';
        if (input) input.required = true;
    } else if (container) {
        container.style.display = 'none';
        if (input) { input.required = false; }
    }
}

(function() {
    var isPaid = document.getElementById('is_paid');
    if (isPaid) {
        isPaid.addEventListener('change', togglePriceFields);
        togglePriceFields();
    }

    var toggleMin = document.getElementById('toggle_min_participants');
    if (toggleMin) {
        toggleMin.addEventListener('change', function() {
            var container = document.getElementById('min_participants_container');
            var input = document.getElementById('min_participants');
            if (this.checked) {
                container.style.display = 'block';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                if (input) input.value = '';
            }
        });
        toggleMinParticipantsFields();
    }
})();
