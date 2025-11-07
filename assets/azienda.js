(function () {
    const initTabs = () => {
        const tabNavs = document.querySelectorAll('[data-ap-tabs]');
        if (!tabNavs.length) {
            return;
        }

        tabNavs.forEach((nav) => {
            const buttons = Array.from(nav.querySelectorAll('[data-ap-tab]'));
            if (!buttons.length) {
                return;
            }

            const scope = nav.closest('.ap-shell') || document;
            const panels = Array.from(scope.querySelectorAll('[data-ap-panel]'));

            const activateTab = (slug) => {
                buttons.forEach((button) => {
                    const isActive = button.getAttribute('data-ap-tab') === slug;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const matches = panel.id === `tab-${slug}`;
                    panel.classList.toggle('is-active', matches);
                    panel.hidden = !matches;
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    activateTab(button.getAttribute('data-ap-tab'));
                });

                button.addEventListener('keydown', (event) => {
                    const currentIndex = buttons.indexOf(button);
                    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                        event.preventDefault();
                        const next = buttons[(currentIndex + 1) % buttons.length];
                        next.focus();
                        activateTab(next.getAttribute('data-ap-tab'));
                    }
                    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        const prev = buttons[(currentIndex - 1 + buttons.length) % buttons.length];
                        prev.focus();
                        activateTab(prev.getAttribute('data-ap-tab'));
                    }
                    if (event.key === 'Home') {
                        event.preventDefault();
                        buttons[0].focus();
                        activateTab(buttons[0].getAttribute('data-ap-tab'));
                    }
                    if (event.key === 'End') {
                        event.preventDefault();
                        const last = buttons[buttons.length - 1];
                        last.focus();
                        activateTab(last.getAttribute('data-ap-tab'));
                    }
                });
            });

            const activeButton = buttons.find((btn) => btn.classList.contains('is-active'));
            activateTab((activeButton || buttons[0]).getAttribute('data-ap-tab'));
        });
    };

    document.addEventListener('DOMContentLoaded', initTabs);
})();

// Accordion Toggle Function
function toggleAccordion(button) {
    const accordionItem = button.closest('.accordion-item');
    const isOpen = accordionItem.classList.contains('is-open');

    // Toggle the open state
    accordionItem.classList.toggle('is-open');

    // Update ARIA attributes
    button.setAttribute('aria-expanded', !isOpen);
}
