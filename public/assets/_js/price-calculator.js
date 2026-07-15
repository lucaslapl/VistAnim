(function() {
    var input = document.getElementById('nb_participants');
    var totalSpan = document.getElementById('price-total');
    if (!input || !totalSpan) return;

    var price = parseFloat(input.getAttribute('data-price')) || 0;

    function updatePriceTotal() {
        var qty = parseInt(input.value) || 1;
        var total = (qty * price).toFixed(2).replace('.', ',');
        totalSpan.innerHTML = 'Total : <strong>' + total + ' &euro;</strong>';
    }

    input.addEventListener('change', updatePriceTotal);
    input.addEventListener('input', updatePriceTotal);
})();
