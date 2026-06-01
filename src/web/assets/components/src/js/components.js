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
