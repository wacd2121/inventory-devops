// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto dismiss alert messages
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-15px)';
            setTimeout(() => {
                alert.remove();
            }, 600);
        }, 5000);
    });

    // 2. Delete confirmations
    const deleteTriggers = document.querySelectorAll('.table-action-link.delete, .btn-confirm-delete');
    deleteTriggers.forEach(trigger => {
        trigger.addEventListener('click', (event) => {
            if (!confirm('Warning: Are you sure you want to permanently delete this item? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });

    // 3. Realtime client-side search filtering
    const searchInput = document.getElementById('search-input');
    const productRows = document.querySelectorAll('.product-row');
    if (searchInput && productRows.length > 0) {
        searchInput.addEventListener('input', (e) => {
            const filter = e.target.value.toLowerCase().trim();
            productRows.forEach(row => {
                const name = row.querySelector('.product-name').textContent.toLowerCase();
                const code = row.querySelector('.product-code').textContent.toLowerCase();
                const category = row.querySelector('.product-category').textContent.toLowerCase();
                
                if (name.includes(filter) || code.includes(filter) || category.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
