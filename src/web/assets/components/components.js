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
