/* phpcs:ignoreFile */
(function () {
  function syncFormData() {
      const embeddedSelector = 'script.ivyforms-ssr-data[type="application/json"]';
      const embedded = document.querySelectorAll(embeddedSelector);
      embedded.forEach(function (script) {
      try {
          const txt = script.textContent || script.innerText || '';
          let parsed;
          try {
          parsed = JSON.parse(txt);
        } catch (e) {
          parsed = null;
        }
        if (parsed && typeof window.wpIvyFormDataList === 'object') {
            const counter = script.getAttribute('data-ivyforms-counter') || (parsed.counter || null);
            if (counter) {
            window.wpIvyFormDataList[counter] = parsed;
          } else {
            for (let k in parsed) {
              if (Object.prototype.hasOwnProperty.call(parsed, k)) {
                window.wpIvyFormDataList[k] = parsed[k];
              }
            }
          }
        }
      } catch (e) {
        // ignore
      }
    });

      const wrappers = document.querySelectorAll('[data-ivyforms-data]');
      wrappers.forEach(function (wrapper) {
      try {
          const raw = wrapper.getAttribute('data-ivyforms-data');
          const data = tryParse(raw);
          if (data && typeof window.wpIvyFormDataList === 'object') {
          for (let k in data) {
            if (Object.prototype.hasOwnProperty.call(data, k)) {
              window.wpIvyFormDataList[k] = data[k];
            }
          }
        }
      } catch (e) {
          // ignore
      }
    });

    disableFormInteractivity();
  }

  function tryParse(raw) {
    if (!raw || typeof raw !== 'string') return null;
    try {
      return JSON.parse(raw);
    } catch (e) {
      try {
          let decoded = raw.replace(/&quot;/g, '"');
          decoded = decoded.replace(/&amp;/g, '&');
          decoded = decoded.replace(/&#039;/g, "'");
          decoded = decoded.replace(/&lt;/g, '<');
          decoded = decoded.replace(/&gt;/g, '>');
        return JSON.parse(decoded);
      } catch (e2) {
        try {
          if (/^[A-Za-z0-9+\/=\n\r]+$/.test(raw) && raw.length > 50) {
              const txt = atob(raw);
              return JSON.parse(txt);
          }
        } catch (e3) {
          return null;
        }
        return null;
      }
    }
  }

  function disableFormInteractivity() {
      const wrapperSelector = '.ivyforms-block-editor-preview, [data-ivyforms-data]';
      const wrappers = document.querySelectorAll(wrapperSelector);

      wrappers.forEach(function (wrapper) {
        const forms = wrapper.querySelectorAll('form');
        if (forms.length === 0) {
          const standaloneSelector = 'input, textarea, select, button, a[href], [tabindex]';
          const standalone = wrapper.querySelectorAll(standaloneSelector);
          applyDisableToElements(standalone);
      }

      forms.forEach(function (form) {
        if (!form.__ivyformsPreviewBound) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }, true);
          form.__ivyformsPreviewBound = true;
        }

          const elemsSelector = 'input, textarea, select, button, a[href], [tabindex]';
          const elems = form.querySelectorAll(elemsSelector);
          applyDisableToElements(elems);
      });
    });
  }

  function applyDisableToElements(elements) {
    elements.forEach(function (el) {
      if (el.hasAttribute('data-ivyforms-ignore')) {
        return;
      }
      if (el.hasAttribute('data-ivyforms-preview-disabled')) {
        return;
      }
      el.setAttribute('data-ivyforms-preview-disabled', '1');
      try {
        if (el.hasAttribute('tabindex')) {
          el.setAttribute('data-ivyforms-original-tabindex', el.getAttribute('tabindex'));
        }
        el.setAttribute('tabindex', '-1');
      } catch (e) {
        // ignore
      }

        const tag = (el.tagName || '').toLowerCase();
        if (tag === 'a') {
        if (el.hasAttribute('href')) {
          el.setAttribute('data-ivyforms-original-href', el.getAttribute('href'));
          el.removeAttribute('href');
        }
      } else if (tag === 'button' || tag === 'input' || tag === 'select' || tag === 'textarea') {
        try {
          if ('disabled' in el) {
            el.disabled = true;
          } else {
            el.setAttribute('aria-disabled', 'true');
          }
        } catch (e) {
          // ignore
        }
      } else {
        el.setAttribute('aria-hidden', 'true');
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncFormData);
  } else {
    syncFormData();
  }

    const observer = new MutationObserver(function () {
        syncFormData();
    });

    observer.observe(document.body, {
    childList: true,
    subtree: true
  });
})();
