document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
        });
    }

    const treeviewLinks = document.querySelectorAll('.treeview > a');
    treeviewLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            
            const submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('treeview-menu')) {
                submenu.style.display = isExpanded ? 'none' : 'block';
            }
        });
    });

    const modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
            const firstFocusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            }
        });

        modal.addEventListener('hidden.bs.modal', function() {
            const trigger = document.querySelector('[data-target="#' + modal.id + '"]');
            if (trigger) {
                trigger.focus();
            }
        });
    });

    function announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(function() {
            document.body.removeChild(announcement);
        }, 1000);
    }

    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const errors = form.querySelectorAll('.text-danger[role="alert"]');
            if (errors.length > 0) {
                announceToScreenReader('Form has ' + errors.length + ' error' + (errors.length > 1 ? 's' : '') + '. Please review and correct.');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.in, .modal.show');
            openModals.forEach(function(modal) {
                $(modal).modal('hide');
            });
        }
    });
});
