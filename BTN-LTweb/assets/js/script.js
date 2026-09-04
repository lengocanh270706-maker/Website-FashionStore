document.addEventListener('DOMContentLoaded', function () {

    // Tự động ẩn thông báo sau 4 giây
    const alerts = document.querySelectorAll('.alert');

    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';

            setTimeout(function () {
                alert.remove();
            }, 500);

        }, 4000);

    });

});
