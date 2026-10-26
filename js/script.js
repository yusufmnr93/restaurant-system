document.addEventListener("DOMContentLoaded", () => {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Simple animations on load
    const elements = document.querySelectorAll('.card, .hero-section, table');
    elements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
        el.classList.add('fade-in');
    });

    // Confirmation dialog for delete actions
    const deleteForms = document.querySelectorAll('form');
    deleteForms.forEach(form => {
        if (form.querySelector('button[name="delete_menu"]')) {
            form.onsubmit = (e) => {
                if (!confirm("Are you sure you want to permanently delete this menu item?")) {
                    e.preventDefault();
                }
            };
        }
    });

    // Auto-dismiss Bootstrap alerts after 4 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-important)');
        alerts.forEach(alert => {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        });
    }, 4000);

    // Search filtering logic for tables (Client-side fast search)
    const tables = document.querySelectorAll('table.table');
    tables.forEach((table, index) => {
        if (!table.querySelector('tbody') || table.previousElementSibling?.classList.contains('search-wrapper')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'search-wrapper d-flex justify-content-end align-items-center mb-3 fade-in';
        wrapper.style.animationDelay = '0.1s';
        wrapper.innerHTML = `<i class="text-muted me-2">🔍</i><input type="text" class="form-control rounded-pill" style="max-width: 300px;" placeholder="Search in table..." data-table-target="${index}">`;
        table.parentNode.insertBefore(wrapper, table);

        const input = wrapper.querySelector('input');
        
        input.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });
});
