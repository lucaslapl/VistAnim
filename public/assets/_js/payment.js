(function() {
    var payBtn = document.querySelector('[data-action="open-payment"]');
    if (payBtn) {
        var token = payBtn.getAttribute('data-token');
        var stripeWindow = null;

        function openPayment() {
            stripeWindow = window.open(payBtn.getAttribute('data-url'), '_blank');
            startPolling();
        }

        function startPolling() {
            var verifyUrl = payBtn.getAttribute('data-verify-url');
            setInterval(function() {
                fetch(verifyUrl)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.status === 'paid') {
                            document.getElementById('payment-pending').style.display = 'none';
                            document.getElementById('payment-completed').style.display = 'block';
                            document.getElementById('payment-failed').style.display = 'none';
                        } else if (data.status === 'failed') {
                            document.getElementById('payment-failed').style.display = 'block';
                        }
                    });
            }, 3000);
        }

        payBtn.addEventListener('click', openPayment);

        if (payBtn.getAttribute('data-auto-open') === '1') {
            window.addEventListener('load', openPayment);
        }
    }
})();

(function() {
    var closeBtn = document.querySelector('[data-action="close-window"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            window.close();
        });
    }

    var el = document.querySelector('[data-auto-close]');
    if (el) {
        var delay = parseInt(el.getAttribute('data-auto-close'), 10) || 3000;
        setTimeout(function() {
            window.close();
        }, delay);
    }
})();
