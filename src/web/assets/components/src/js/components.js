(() => {
    if (window.lrIdentifiers) return;

    const normalizeSlug = (value, fallback = '') => {
        let slug = String(value ?? '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9_-]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^[-_]+|[-_]+$/g, '');

        if (slug !== '') {
            return slug;
        }

        if (fallback === '') {
            return '';
        }

        slug = normalizeSlug(fallback, '');

        return slug !== '' ? slug : 'item';
    };

    const resolveElement = (element) => {
        if (typeof element === 'string') {
            return document.querySelector(element);
        }

        return element;
    };

    const bindSlugHandle = (sourceInput, targetInput, options = {}) => {
        const source = resolveElement(sourceInput);
        const target = resolveElement(targetInput);

        if (!source || !target || target.dataset.lrSlugHandleBound) {
            return null;
        }

        const isNew = options.isNew !== false;
        const updateExisting = options.updateExisting === true;
        const fallback = options.fallback ?? '';
        let manuallyEdited = options.manuallyEdited ?? (!isNew && !updateExisting);

        const resolveFallback = () => {
            if (typeof fallback === 'function') {
                return fallback();
            }

            return fallback;
        };

        const update = () => {
            if (manuallyEdited) {
                return;
            }

            target.value = normalizeSlug(source.value, resolveFallback());
        };

        source.addEventListener('input', update);
        target.addEventListener('input', () => {
            manuallyEdited = true;
        });

        target.dataset.lrSlugHandleBound = 'true';

        if (options.updateOnBind === true) {
            update();
        }

        return {
            update,
            isManuallyEdited: () => manuallyEdited,
            setManuallyEdited: (value) => {
                manuallyEdited = Boolean(value);
            },
        };
    };

    window.lrIdentifiers = {
        normalizeSlug,
        bindSlugHandle,
    };
})();

(() => {
    if (window.lrConfigTooltipInit) return;
    window.lrConfigTooltipInit = true;

    let activeTooltip = null;

    const removeTooltip = () => {
        if (activeTooltip) {
            activeTooltip.remove();
            activeTooltip = null;
        }
    };

    const positionTooltip = (trigger, tooltip) => {
        const rect = trigger.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        let left = rect.left + window.scrollX;
        let top = rect.bottom + window.scrollY + 8;

        if (left + tooltipRect.width > window.innerWidth - 20) {
            left = window.innerWidth - tooltipRect.width - 20;
        }

        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
    };

    const showTooltip = (trigger) => {
        const config = trigger.dataset.config;
        if (!config) return;

        removeTooltip();

        const tooltip = document.createElement('div');
        tooltip.className = 'lr-config-tooltip';

        const source = trigger.dataset.configSource;
        if (source) {
            const header = document.createElement('span');
            header.className = 'lr-config-tooltip-header';
            header.textContent = source;
            tooltip.appendChild(header);
        }

        const content = document.createElement('pre');
        content.textContent = config;
        content.style.margin = '0';
        content.style.whiteSpace = 'pre';
        tooltip.appendChild(content);

        document.body.appendChild(tooltip);
        activeTooltip = tooltip;
        positionTooltip(trigger, tooltip);
    };

    const bindTooltip = (trigger) => {
        if (trigger.dataset.lrConfigTooltipBound) return;
        trigger.dataset.lrConfigTooltipBound = 'true';
        trigger.addEventListener('mouseenter', () => showTooltip(trigger));
        trigger.addEventListener('mouseleave', removeTooltip);
        trigger.addEventListener('focus', () => showTooltip(trigger));
        trigger.addEventListener('blur', removeTooltip);
    };

    const initTooltips = () => {
        document.querySelectorAll('.lr-config-info-icon').forEach(bindTooltip);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTooltips);
    } else {
        initTooltips();
    }

    window.addEventListener('scroll', removeTooltip, { passive: true });
    window.addEventListener('resize', removeTooltip, { passive: true });
})();

(() => {
    if (window.lrPluginCreditInit) return;
    window.lrPluginCreditInit = true;

    const brightColors = [
        '#E52521', '#3B82F6', '#10B981', '#8B5CF6', '#F59E0B',
        '#EF4444', '#EC4899', '#06B6D4', '#84CC16', '#F97316',
        '#6366F1', '#14B8A6', '#F43F5E', '#A855F7', '#EAB308',
        '#DC2626', '#7C3AED', '#059669', '#0EA5E9', '#D97706',
    ];

    const getLuminance = (hex) => {
        const rgb = parseInt(hex.slice(1), 16);
        const r = (rgb >> 16) & 0xff;
        const g = (rgb >> 8) & 0xff;
        const b = rgb & 0xff;
        const [rs, gs, bs] = [r, g, b].map((c) => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    };

    const getAccessibleTextColor = (bgColor) => {
        const bgLuminance = getLuminance(bgColor);
        const whiteContrast = (1 + 0.05) / (bgLuminance + 0.05);
        return whiteContrast >= 4.5 ? '#ffffff' : '#000000';
    };

    const bindCredit = (credit) => {
        if (credit.dataset.lrBrandCreditBound) return;
        credit.dataset.lrBrandCreditBound = 'true';

        const pill = credit.querySelector('.lr-brand-pill');
        const text = credit.querySelector('.lr-brand-text');
        const svg = credit.querySelector('.lr-brand-logo svg g');

        credit.addEventListener('mouseenter', () => {
            const randomColor = brightColors[Math.floor(Math.random() * brightColors.length)];
            const textColor = getAccessibleTextColor(randomColor);
            if (pill) pill.style.backgroundColor = randomColor;
            if (text) text.style.color = textColor;
            if (svg) svg.style.fill = textColor;
        });

        credit.addEventListener('mouseleave', () => {
            if (pill) pill.style.backgroundColor = '';
            if (text) text.style.color = '';
            if (svg) svg.style.fill = '';
        });
    };

    const initCredits = () => {
        document.querySelectorAll('.lr-brand-credit').forEach(bindCredit);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCredits);
    } else {
        initCredits();
    }
})();

(() => {
    // Integration cards (_partials/integration-card.twig): show/hide a card's
    // body when its header lightswitch is toggled. Garnish fires the switch
    // change via jQuery, which native 'change' listeners miss, so react to the
    // user interaction directly and read aria-checked after Garnish updates it.
    if (window.lrIntegrationCardsInit) return;
    window.lrIntegrationCardsInit = true;

    const toggleBody = (target) => {
        const lightswitch = target.closest('.lightswitch');
        if (!lightswitch || !lightswitch.closest('.lr-integration-card__header')) return;

        const card = lightswitch.closest('.lr-integration-card');
        const body = card ? card.querySelector('.lr-integration-card__body') : null;
        if (!body) return;

        requestAnimationFrame(() => {
            const isOn = lightswitch.getAttribute('aria-checked') === 'true';
            body.classList.toggle('hidden', !isOn);
        });
    };

    document.addEventListener('click', (e) => toggleBody(e.target));
    document.addEventListener('keyup', (e) => {
        if (e.key === ' ' || e.key === 'Enter' || e.key === 'Spacebar') {
            toggleBody(e.target);
        }
    });
})();

(() => {
    if (window.lrChartContainerInit) return;
    window.lrChartContainerInit = true;

    const addPercentageTooltip = (config) => {
        if (!config.percentageTooltip) {
            return config.options || {};
        }

        const options = config.options || {};
        options.plugins = options.plugins || {};
        options.plugins.tooltip = options.plugins.tooltip || {};
        options.plugins.tooltip.callbacks = options.plugins.tooltip.callbacks || {};
        options.plugins.tooltip.callbacks.label = (context) => {
            const values = context.dataset.data || [];
            const total = values.reduce((sum, value) => sum + Number(value || 0), 0);
            const current = Number(context.parsed || 0);
            const percentage = total > 0 ? ((current / total) * 100).toFixed(1) : '0.0';

            return `${context.label}: ${current} (${percentage}%)`;
        };

        return options;
    };

    const initChart = (canvas, attempt = 0) => {
        if (canvas.dataset.lrChartBound) return;

        const rawConfig = canvas.dataset.lrChartConfig;
        if (!rawConfig) return;

        if (typeof Chart === 'undefined') {
            if (attempt < 20) {
                window.setTimeout(() => initChart(canvas, attempt + 1), 50);
            }

            return;
        }

        let config = {};
        try {
            config = JSON.parse(rawConfig);
        } catch (e) {
            console.warn('lrChartContainer: Invalid chart config', e);
            return;
        }

        const type = config.type || 'line';
        const data = config.data || {};
        const options = addPercentageTooltip(config);

        if (typeof window.lrCreateChart === 'function') {
            window.lrCreateChart(canvas.id, type, data, options);
        } else {
            new Chart(canvas, { type, data, options });
        }

        canvas.dataset.lrChartBound = 'true';
    };

    const initCharts = () => {
        document.querySelectorAll('[data-lr-chart-config]').forEach((canvas) => {
            initChart(canvas);
        });
    };

    const observeChartInsertions = () => {
        if (!document.body || !window.MutationObserver) {
            return;
        }

        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.addedNodes.length === 0) {
                    continue;
                }

                for (const node of mutation.addedNodes) {
                    if (!(node instanceof Element)) {
                        continue;
                    }

                    if (node.matches('[data-lr-chart-config]') || node.querySelector('[data-lr-chart-config]')) {
                        initCharts();
                        return;
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            observeChartInsertions();
        });
    } else {
        initCharts();
        observeChartInsertions();
    }

    window.lrInitChartContainers = initCharts;
})();

(() => {
    // Copy input (_components/copy-input): clicking a [data-lr-copy] button
    // writes its value to the clipboard and shows the CP notice from
    // [data-lr-copied]. Uses the async Clipboard API with an execCommand
    // fallback for non-secure contexts / older browsers.
    if (window.lrCopyInputInit) return;
    window.lrCopyInputInit = true;

    const copyText = (text) => {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (e) {
            // no-op
        }
        document.body.removeChild(textarea);
        return Promise.resolve();
    };

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-lr-copy]');
        if (!btn) return;

        copyText(btn.getAttribute('data-lr-copy')).then(() => {
            const message = btn.getAttribute('data-lr-copied');
            if (message && window.Craft && Craft.cp && Craft.cp.displayNotice) {
                Craft.cp.displayNotice(message);
            }
        });
    });
})();

(() => {
    // Error summary (_partials/error-summary): resolve model error keys to
    // rendered fields, including fields inside inactive Craft CP tabs.
    if (window.lrErrorSummaryInit) return;
    window.lrErrorSummaryInit = true;

    const isVisible = (element) => {
        let current = element;

        while (current) {
            if (current.hidden
                || current.classList.contains('hidden')
                || current.getAttribute('aria-hidden') === 'true') {
                return false;
            }

            const style = window.getComputedStyle(current);
            if (style.display === 'none' || style.visibility === 'hidden') {
                return false;
            }

            current = current.parentElement;
        }

        return true;
    };

    const firstVisible = (elements) => (
        elements.find(isVisible) || elements[0] || null
    );

    const elementsWithAttributeValue = (attribute, value) => (
        Array.from(document.querySelectorAll(`[${attribute}]`))
            .filter((element) => element.getAttribute(attribute) === value)
    );

    const resolveField = (key) => {
        // Tier 1: Craft's explicit field-error convention.
        const explicitFields = elementsWithAttributeValue('data-error-key', key);
        if (explicitFields.length > 0) {
            return firstVisible(explicitFields);
        }

        // Tier 2a: conventional field IDs, progressively dropping leading
        // dotted key segments while preserving the original case.
        const segments = key.split('.');
        for (let index = 0; index < segments.length; index += 1) {
            const field = document.getElementById(`${segments.slice(index).join('-')}-field`);
            if (field) {
                return field;
            }
        }

        // Tier 2b: Craft element-editor field layout containers.
        const attributeFields = elementsWithAttributeValue('data-attribute', key)
            .map((element) => element.closest('.field'))
            .filter(Boolean);

        return firstVisible(attributeFields);
    };

    const tabControls = (paneId) => {
        const target = `#${paneId}`;

        return Array.from(
            document.querySelectorAll(
                'a[href], [data-tab-target], [role="tab"][aria-controls], .pane-tabs [data-id], [role="tablist"] [data-id]',
            ),
        ).filter((control) => {
            // Error-summary anchors are field links, never tab controls.
            if (control.closest('.error-summary')) {
                return false;
            }

            if (
                control.getAttribute('data-tab-target') === target ||
                control.getAttribute('aria-controls') === paneId ||
                control.getAttribute('data-id') === paneId
            ) {
                return true;
            }

            const href = control.getAttribute('href');
            if (!href) {
                return false;
            }

            try {
                return href === target || new URL(href, document.baseURI).hash === target;
            } catch (error) {
                return false;
            }
        });
    };

    const revealTabbedAncestors = (field) => {
        const panes = [];
        let ancestor = field.parentElement;

        while (ancestor) {
            if (ancestor.id && !isVisible(ancestor) && tabControls(ancestor.id).length > 0) {
                panes.push(ancestor);
            }
            ancestor = ancestor.parentElement;
        }

        // Activate outer tab systems before nested ones.
        panes.reverse().forEach((pane) => {
            const controls = tabControls(pane.id);
            const control = controls.find((candidate) => candidate.getAttribute('role') === 'tab')
                || firstVisible(controls);

            if (control && control.getAttribute('aria-selected') !== 'true') {
                control.click();
            }
        });
    };

    const focusField = (field) => {
        const focusableSelector = [
            'input:not([type="hidden"]):not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            'button:not([disabled])',
            '[contenteditable="true"]',
            '[tabindex]:not([tabindex="-1"])',
        ].join(', ');

        window.requestAnimationFrame(() => {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });

            const focusable = firstVisible(Array.from(field.querySelectorAll(focusableSelector)));
            if (focusable) {
                focusable.focus({ preventScroll: true });
            }
        });
    };

    // Capture base-owned summary clicks before Craft.ui's direct
    // `.error-summary a` handler. Native Craft summaries do not carry the
    // base marker and remain untouched.
    document.addEventListener(
        'click',
        (event) => {
            const eventTarget = event.target;
            const origin = eventTarget instanceof Element
                ? eventTarget
                : eventTarget?.parentElement || null;
            const link = origin ? origin.closest('a[data-field-error-key]') : null;

            if (!link || !link.closest('[data-lr-error-summary]')) {
                return;
            }

            // Keep Craft's direct summary handler from also processing base-owned
            // links. This does not cancel the native anchor default.
            event.stopPropagation();

            const key = link.getAttribute('data-field-error-key');
            const field = key ? resolveField(key) : null;
            if (!field) {
                return;
            }

            event.preventDefault();
            revealTabbedAncestors(field);
            focusField(field);
        },
        true,
    );
})();
