(function() {
    document.addEventListener('click', function(e) {
        var target = e.target;

        var confirmMsg = target.getAttribute('data-confirm');
        if (confirmMsg) {
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return;
            }
        }

        if (target.getAttribute('data-action') === 'print') {
            e.preventDefault();
            window.print();
            return;
        }

        if (target.getAttribute('data-action') === 'open-window') {
            e.preventDefault();
            var url = target.getAttribute('data-url');
            if (url) {
                window.open(url, '_blank');
            }
        }
    });

    document.addEventListener('submit', function(e) {
        var form = e.target;
        var confirmMsg = form.getAttribute('data-confirm');
        if (confirmMsg) {
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        }
    });
})();
