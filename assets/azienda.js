(function () {
    const initTOC = () => {
        const tocLinks = Array.from(document.querySelectorAll('[data-lg-toc-link]'));
        if (!tocLinks.length) {
            return;
        }

        const sections = tocLinks
            .map((link) => {
                const href = link.getAttribute('href') || '';
                if (!href.startsWith('#')) {
                    return null;
                }
                const target = document.querySelector(href);
                return target instanceof HTMLElement ? target : null;
            })
            .filter(Boolean);

        if (!sections.length) {
            return;
        }

        const activateLink = (id) => {
            if (!id) {
                return;
            }
            tocLinks.forEach((link) => {
                const href = link.getAttribute('href') || '';
                const matches = href === `#${id}`;
                link.classList.toggle('active', matches);
                if (matches) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        const handleClick = (event) => {
            const link = event.currentTarget;
            if (!(link instanceof HTMLElement)) {
                return;
            }
            const href = link.getAttribute('href') || '';
            if (!href.startsWith('#')) {
                return;
            }
            const target = document.querySelector(href);
            if (!(target instanceof HTMLElement)) {
                return;
            }
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (history.replaceState) {
                history.replaceState(null, '', href);
            }
            activateLink(target.id);
        };

        tocLinks.forEach((link) => {
            link.addEventListener('click', handleClick);
        });

        if ('IntersectionObserver' in window) {
            let currentId = '';
            const observer = new IntersectionObserver(
                (entries) => {
                    const visible = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                    if (visible.length > 0) {
                        const nextId = visible[0].target.id;
                        if (nextId && nextId !== currentId) {
                            currentId = nextId;
                            activateLink(currentId);
                        }
                        return;
                    }

                    const topMost = entries
                        .filter((entry) => entry.boundingClientRect.top < 0)
                        .sort((a, b) => b.boundingClientRect.top - a.boundingClientRect.top);

                    if (topMost.length > 0) {
                        const fallbackId = topMost[0].target.id;
                        if (fallbackId && fallbackId !== currentId) {
                            currentId = fallbackId;
                            activateLink(currentId);
                        }
                    }
                },
                {
                    root: null,
                    threshold: [0, 0.25, 0.5, 0.75],
                    rootMargin: '-45% 0px -45% 0px',
                }
            );

            sections.forEach((section) => observer.observe(section));
        } else {
            activateLink(sections[0].id);
        }
    };

    const initTabs = () => {
        const tabContainers = Array.from(document.querySelectorAll('[data-lg-tabs]'));
        if (!tabContainers.length) {
            return;
        }

        const focusTab = (tabs, index) => {
            if (index < 0 || index >= tabs.length) {
                return;
            }
            tabs[index].focus();
        };

        tabContainers.forEach((container) => {
            const tabButtons = Array.from(container.querySelectorAll('[data-lg-tab]'));
            const panels = Array.from(container.querySelectorAll('[data-lg-panel]'));

            if (!tabButtons.length || !panels.length) {
                return;
            }

            const ensureFirstAccordion = (panel) => {
                const accordions = Array.from(panel.querySelectorAll('.lg-azienda__accordion'));
                if (!accordions.length) {
                    return;
                }
                const hasOpen = accordions.some((accordion) => accordion.hasAttribute('open'));
                if (!hasOpen) {
                    accordions[0].setAttribute('open', '');
                }
            };

            const activateTab = (index) => {
                tabButtons.forEach((button, btnIndex) => {
                    const isActive = btnIndex === index;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    button.setAttribute('tabindex', isActive ? '0' : '-1');
                });

                panels.forEach((panel, panelIndex) => {
                    const isActive = panelIndex === index;
                    panel.classList.toggle('is-active', isActive);
                    panel.toggleAttribute('hidden', !isActive);
                    if (isActive) {
                        ensureFirstAccordion(panel);
                    }
                });
            };

            tabButtons.forEach((button, index) => {
                button.addEventListener('click', () => {
                    activateTab(index);
                });

                button.addEventListener('keydown', (event) => {
                    switch (event.key) {
                        case 'ArrowLeft':
                        case 'ArrowUp':
                            event.preventDefault();
                            activateTab((index - 1 + tabButtons.length) % tabButtons.length);
                            focusTab(tabButtons, (index - 1 + tabButtons.length) % tabButtons.length);
                            break;
                        case 'ArrowRight':
                        case 'ArrowDown':
                            event.preventDefault();
                            activateTab((index + 1) % tabButtons.length);
                            focusTab(tabButtons, (index + 1) % tabButtons.length);
                            break;
                        case 'Home':
                            event.preventDefault();
                            activateTab(0);
                            focusTab(tabButtons, 0);
                            break;
                        case 'End':
                            event.preventDefault();
                            activateTab(tabButtons.length - 1);
                            focusTab(tabButtons, tabButtons.length - 1);
                            break;
                        default:
                            break;
                    }
                });
            });

            const initialIndex = tabButtons.findIndex((button) => button.classList.contains('is-active'));
            activateTab(initialIndex >= 0 ? initialIndex : 0);
        });
    };

    initTOC();
    initTabs();
})();
