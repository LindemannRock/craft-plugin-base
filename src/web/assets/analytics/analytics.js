/**
 * LindemannRock Analytics Helpers
 *
 * Provides helper functions for cp-analytics layout.
 * Requires Chart.js to be loaded first.
 *
 * @copyright Copyright (c) 2026 LindemannRock
 */

(function(window) {
    'use strict';

    // Chart colors (consistent across all analytics)
    var chartColors = [
        '#0d78f2', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6', '#06b6d4',
        '#ec4899', '#84cc16', '#f97316', '#6366f1', '#14b8a6', '#f43f5e'
    ];

    // Expose chart colors globally
    window.lrChartColors = chartColors;

    // Chart instances storage (per-plugin prefix)
    window.lrChartInstances = {};

    /**
     * Initialize analytics for a plugin
     *
     * @param {Object} config - Analytics configuration
     */
    window.lrAnalyticsInit = function(config) {
        config = config || {};

        var prefix = config.prefix || 'analytics';

        // Initialize chart storage for this prefix
        if (!window.lrChartInstances[prefix]) {
            window.lrChartInstances[prefix] = {};
        }

        // Store config globally
        window.lrAnalyticsConfig = config;

        // Dispatch init event
        document.dispatchEvent(new CustomEvent('lr:analyticsInit', {
            detail: {
                dateRange: config.dateRange || 'last7days',
                siteId: config.siteId || '',
                customFilters: config.customFilters || {},
                config: config
            }
        }));
    };

    /**
     * Load chart data via AJAX
     *
     * @param {string} type - Chart data type
     * @param {Function} callback - Success callback
     * @param {Object} extraParams - Additional parameters
     */
    window.lrLoadChartData = function(type, callback, extraParams) {
        var config = window.lrAnalyticsConfig || {};

        if (!config.dataEndpoint) {
            console.warn('lrLoadChartData: No dataEndpoint configured');
            return;
        }

        extraParams = extraParams || {};

        var data = Object.assign({
            type: type,
            dateRange: config.dateRange || 'last7days',
            siteId: config.siteId || ''
        }, extraParams);

        // Add CSRF token
        if (config.csrfName && config.csrfToken) {
            data[config.csrfName] = config.csrfToken;
        }

        // Use jQuery if available (Craft includes it)
        if (typeof $ !== 'undefined' && $.ajax) {
            $.ajax({
                url: config.dataEndpoint,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.success && response.data) {
                        callback(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('lrLoadChartData error:', error);
                }
            });
        } else {
            // Fallback to fetch
            var formData = new FormData();
            Object.keys(data).forEach(function(key) {
                formData.append(key, data[key]);
            });

            fetch(config.dataEndpoint, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(response) {
                if (response.success && response.data) {
                    callback(response.data);
                }
            })
            .catch(function(error) {
                console.error('lrLoadChartData error:', error);
            });
        }
    };

    /**
     * Create a chart using Chart.js
     *
     * @param {string} canvasId - Canvas element ID
     * @param {string} type - Chart type (line, bar, doughnut, pie)
     * @param {Object} data - Chart data
     * @param {Object} options - Chart.js options
     * @returns {Chart|null} Chart instance
     */
    window.lrCreateChart = function(canvasId, type, data, options) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.warn('lrCreateChart: Canvas not found:', canvasId);
            return null;
        }

        if (typeof Chart === 'undefined') {
            console.error('lrCreateChart: Chart.js not loaded');
            return null;
        }

        var config = window.lrAnalyticsConfig || {};
        var prefix = config.prefix || 'analytics';
        var chartKey = canvasId.replace(/-/g, '_');

        // Destroy existing chart
        if (window.lrChartInstances[prefix] && window.lrChartInstances[prefix][chartKey]) {
            window.lrChartInstances[prefix][chartKey].destroy();
        }

        // Default options
        var defaultOptions = {
            responsive: true,
            maintainAspectRatio: (type === 'doughnut' || type === 'pie'),
            plugins: {
                legend: {
                    position: (type === 'doughnut' || type === 'pie') ? 'bottom' : 'top'
                }
            }
        };

        // Add scales for line/bar charts
        if (type === 'line' || type === 'bar') {
            defaultOptions.scales = {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 }
                }
            };
        }

        // Merge options
        var mergedOptions = Object.assign({}, defaultOptions, options || {});

        // Create chart
        var chart = new Chart(ctx, {
            type: type,
            data: data,
            options: mergedOptions
        });

        // Store instance
        if (!window.lrChartInstances[prefix]) {
            window.lrChartInstances[prefix] = {};
        }
        window.lrChartInstances[prefix][chartKey] = chart;

        return chart;
    };

    /**
     * Destroy all charts for a prefix
     *
     * @param {string} prefix - Plugin prefix
     */
    window.lrDestroyCharts = function(prefix) {
        prefix = prefix || 'analytics';

        if (window.lrChartInstances[prefix]) {
            Object.values(window.lrChartInstances[prefix]).forEach(function(chart) {
                if (chart && chart.destroy) {
                    chart.destroy();
                }
            });
            window.lrChartInstances[prefix] = {};
        }
    };

    /**
     * Get a chart instance
     *
     * @param {string} canvasId - Canvas element ID
     * @param {string} prefix - Plugin prefix
     * @returns {Chart|null} Chart instance
     */
    window.lrGetChart = function(canvasId, prefix) {
        prefix = prefix || (window.lrAnalyticsConfig ? window.lrAnalyticsConfig.prefix : 'analytics');
        var chartKey = canvasId.replace(/-/g, '_');

        if (window.lrChartInstances[prefix]) {
            return window.lrChartInstances[prefix][chartKey] || null;
        }
        return null;
    };

})(window);
