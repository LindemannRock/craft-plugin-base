import confetti from 'canvas-confetti';

(function () {
  const rootId = 'lr-install-experience';
  const presetNames = ['spray', 'shower', 'fireworks', 'rain', 'fountains'];

  function randomBetween(min, max) {
    return min + Math.random() * (max - min);
  }

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function normalizeHex(hex) {
    const value = (hex || '#0f766e').replace('#', '').trim();
    const safe = value.length === 3
      ? value.split('').map((char) => char + char).join('')
      : value.padEnd(6, '0').slice(0, 6);

    return `#${safe.toUpperCase()}`;
  }

  function toRgb(hex) {
    const safe = normalizeHex(hex).slice(1);

    return {
      r: parseInt(safe.slice(0, 2), 16),
      g: parseInt(safe.slice(2, 4), 16),
      b: parseInt(safe.slice(4, 6), 16),
    };
  }

  function mixColors(colorA, colorB, weight) {
    const a = toRgb(colorA);
    const b = toRgb(colorB);
    const ratio = clamp(weight, 0, 1);
    const inverse = 1 - ratio;

    const channel = (key) => Math.round((a[key] * inverse) + (b[key] * ratio))
      .toString(16)
      .padStart(2, '0');

    return `#${channel('r')}${channel('g')}${channel('b')}`.toUpperCase();
  }

  function buildPalette(config) {
    const ui = normalizeHex((config && config.uiColor) || (config && config.sidebarColor) || (config && config.accent));
    const accent = normalizeHex((config && config.accent) || ui);

    return [
      ui,
      mixColors(ui, '#FFFFFF', 0.32),
      mixColors(ui, '#FFFFFF', 0.58),
      mixColors(ui, accent, 0.24),
      '#FFFFFF',
    ];
  }

  function resolvePreset(config) {
    const raw = (config && config.confettiPreset) || (config && config.theme) || 'surprise';

    if (raw === 'surprise' || raw === 'random') {
      return presetNames[Math.floor(Math.random() * presetNames.length)];
    }

    return presetNames.includes(raw) ? raw : 'spray';
  }

  function shot(instance, options, delay, timers) {
    const timerId = window.setTimeout(() => {
      instance(options);
    }, delay);

    timers.push(timerId);
  }

  function launchPreset(instance, preset, palette) {
    const timers = [];
    const base = {
      colors: palette,
      disableForReducedMotion: true,
      scalar: randomBetween(0.94, 1.22),
      ticks: 440,
      drift: randomBetween(-0.22, 0.22),
    };

    if (preset === 'rain') {
      for (let index = 0; index < 10; index += 1) {
        shot(instance, {
          ...base,
          particleCount: 26,
          angle: 270,
          spread: 20,
          startVelocity: 20,
          gravity: 0.48,
          decay: 0.965,
          origin: { x: randomBetween(0.02, 0.98), y: randomBetween(-0.08, 0.02) },
        }, index * 170, timers);
      }
    } else if (preset === 'fireworks') {
      const origins = [
        { x: randomBetween(0.04, 0.2), y: randomBetween(0.02, 0.2) },
        { x: randomBetween(0.8, 0.96), y: randomBetween(0.02, 0.18) },
        { x: randomBetween(0.36, 0.64), y: randomBetween(-0.04, 0.12) },
        { x: randomBetween(0.18, 0.82), y: randomBetween(0.14, 0.3) },
      ];

      origins.forEach((origin, index) => {
        shot(instance, {
          ...base,
          particleCount: 92,
          spread: randomBetween(78, 116),
          startVelocity: randomBetween(38, 54),
          gravity: 0.68,
          decay: 0.94,
          origin,
        }, index * 220, timers);
      });
    } else if (preset === 'shower') {
      for (let index = 0; index < 8; index += 1) {
        const fromLeft = Math.random() > 0.5;
        shot(instance, {
          ...base,
          particleCount: 32,
          angle: fromLeft ? randomBetween(52, 68) : randomBetween(112, 128),
          spread: randomBetween(42, 58),
          startVelocity: randomBetween(40, 54),
          gravity: 0.66,
          decay: 0.95,
          origin: {
            x: fromLeft ? randomBetween(-0.02, 0.08) : randomBetween(0.92, 1.02),
            y: randomBetween(0.18, 0.9),
          },
        }, index * 130, timers);
      }
    } else if (preset === 'fountains') {
      [-0.02, 1.02].forEach((x, index) => {
        for (let wave = 0; wave < 5; wave += 1) {
          shot(instance, {
            ...base,
            particleCount: 24,
            angle: x < 0.5 ? randomBetween(48, 72) : randomBetween(108, 132),
            spread: 30,
            startVelocity: randomBetween(46, 60),
            gravity: 0.72,
            decay: 0.95,
            origin: { x, y: randomBetween(0.82, 0.96) },
          }, (index * 100) + (wave * 170), timers);
        }
      });
    } else {
      for (let index = 0; index < 7; index += 1) {
        const fromLeft = Math.random() > 0.5;
        shot(instance, {
          ...base,
          particleCount: 40,
          angle: fromLeft ? randomBetween(56, 72) : randomBetween(108, 124),
          spread: randomBetween(34, 62),
          startVelocity: randomBetween(46, 62),
          gravity: 0.68,
          decay: 0.95,
          origin: {
            x: fromLeft ? randomBetween(-0.04, 0.08) : randomBetween(0.92, 1.04),
            y: randomBetween(0.18, 0.88),
          },
        }, index * 140, timers);
      }
    }

    return function cleanup() {
      timers.forEach((timerId) => {
        window.clearTimeout(timerId);
      });
      if (typeof instance.reset === 'function') {
        instance.reset();
      }
    };
  }

  function applyBackgroundMarkVariation(root) {
    const x = (-4 + Math.random() * 8).toFixed(2);
    const y = (2 + Math.random() * 8).toFixed(2);
    const scale = (3.5 + Math.random() * 1.5).toFixed(2);
    const rotate = (Math.random() * 180).toFixed(2);

    root.style.setProperty('--lr-bgmark-x', `${x}%`);
    root.style.setProperty('--lr-bgmark-y', `${y}%`);
    root.style.setProperty('--lr-bgmark-scale', scale);
    root.style.setProperty('--lr-bgmark-rotate', `${rotate}deg`);
  }

  window.LrInstallExperience = {
    mount(config) {
      const root = document.getElementById(rootId);
      if (!root || root.dataset.mounted === 'true') {
        return;
      }

      root.dataset.mounted = 'true';
      root.setAttribute('aria-hidden', 'false');
      applyBackgroundMarkVariation(root);

      const canvas = root.querySelector('.lr-install-experience__canvas');
      let stopParticles = function () {};
      let removeKeydown = function () {};

      if (canvas instanceof HTMLCanvasElement) {
        const instance = confetti.create(canvas, {
          resize: true,
          useWorker: true,
        });

        stopParticles = launchPreset(
          instance,
          resolvePreset(config),
          buildPalette(config),
        );
      }

      const close = function () {
        root.classList.remove('is-visible');
        root.setAttribute('aria-hidden', 'true');
        stopParticles();
        removeKeydown();
        window.setTimeout(() => {
          root.remove();
        }, 220);
      };

      root.querySelectorAll('[data-lr-install-close]').forEach((element) => {
        element.addEventListener('click', close);
      });

      const onKeydown = function (event) {
        if (event.key !== 'Escape') {
          return;
        }

        close();
      };

      removeKeydown = function () {
        document.removeEventListener('keydown', onKeydown);
      };

      document.addEventListener('keydown', onKeydown);

      window.requestAnimationFrame(() => {
        root.classList.add('is-visible');
      });
    },
  };
})();
