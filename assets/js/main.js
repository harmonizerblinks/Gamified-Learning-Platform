// Main JavaScript for Gamified Learning Platform
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

setTimeout(function() {
    var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

console.log('Gamified Learning Platform - JavaScript Loaded');
