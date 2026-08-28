document.addEventListener('DOMContentLoaded', () => {
  const ivyformsRow = document.querySelector('tr[data-slug="ivyforms"]')
  if (!ivyformsRow) return
  const deactivateLink = ivyformsRow.querySelector('.deactivate a')
  if (!deactivateLink) return
  window.ivyformsDeactivateUrl = deactivateLink.href
  deactivateLink.addEventListener('click', (e) => {
    e.preventDefault()
    showIvyDeactivationModal()
  })

  // Helper: create element with props
  function el(tag, props = {}, ...children) {
    const node = document.createElement(tag)
    Object.entries(props).forEach(([k, v]) => {
      if (k === 'style' && typeof v === 'object') Object.assign(node.style, v)
      else if (k.startsWith('on') && typeof v === 'function') node[k] = v
      else node[k] = v
    })
    children.forEach((child) => {
      if (child)
        node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child)
    })
    return node
  }

  function showIvyDeactivationModal() {
    const pluginUrl =
      typeof window.ivyformsData !== 'undefined' && window.ivyformsData.pluginUrl
        ? window.ivyformsData.pluginUrl
        : ''
    document.getElementById('ivyforms-deactivation-modal-mount')?.remove()
    const overlay = el('div', {
      id: 'ivyforms-deactivation-modal-mount',
      style: {
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100vw',
        height: '100vh',
        background: 'rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 9999,
      },
    })
    const modal = el('div', {
      style: {
        background: '#fff',
        borderRadius: '12px',
        boxShadow: '0 10px 25px rgba(0,0,0,0.15)',
        width: '420px',
        maxWidth: '95vw',
        padding: '40px 32px',
        position: 'relative',
        fontFamily: 'inherit',
      },
    })
    // Scoped styles
    modal.appendChild(
      el(
        'style',
        { type: 'text/css' },
        `
#ivyforms-deactivation-modal-mount { --checkbox-border: #a78bfa; --checkbox-bg: #f3f0ff; --checkbox-checked: #7c3aed; --checkbox-checked-hover: #6d28d9; }
#ivyforms-deactivation-modal-mount .ivy-option-row { display: flex; flex-direction: row; align-items: center; gap: 12px; min-height: 24px; background: transparent !important; margin: 0; padding: 10px 0; transition: background 0.15s; }
#ivyforms-deactivation-modal-mount .ivy-option-row:last-child { border-bottom: none; }
#ivyforms-deactivation-modal-mount .ivy-option-row:hover { background: #fafafa !important; }
#ivyforms-deactivation-modal-mount .ivy-option-row label { display: flex; flex-direction: row; align-items: center; gap: 12px; width: 100%; font-size: 14px; color: #1f2937; font-weight: 500; cursor: pointer; margin: 0; padding: 0; height: unset; min-height: 20px; white-space: normal; position: relative; }
#ivyforms-deactivation-modal-mount .ivy-option-row input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; cursor: pointer; margin: 0; padding: 0; }
#ivyforms-deactivation-modal-mount .ivy-custom-checkbox { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 4px; border: 1.5px solid #C0C5CB; box-sizing: border-box; flex-shrink: 0; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; -webkit-tap-highlight-color: transparent; }
#ivyforms-deactivation-modal-mount .ivy-option-row:hover .ivy-custom-checkbox:not(.checked) { background: var(--checkbox-hover-bg); border-color: #c4b5fd; }
#ivyforms-deactivation-modal-mount .ivy-custom-checkbox.checked { background: var(--checkbox-checked); border-color: var(--checkbox-checked); }
#ivyforms-deactivation-modal-mount .ivy-option-row:hover .ivy-custom-checkbox.checked { background: var(--checkbox-checked-hover); border-color: var(--checkbox-checked-hover); }
#ivyforms-deactivation-modal-mount .ivy-custom-checkbox svg { display: block; width: 12px; height: 10px; flex-shrink: 0; }
#ivyforms-deactivation-modal-mount .ivy-custom-checkbox:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
#ivyforms-deactivation-modal-mount .ivyforms-modal-btn { border-radius: 6px; font-size: 15px; font-weight: 600; padding: 10px 20px; cursor: pointer; border: 1.5px solid #e5e7eb; transition: all 0.2s ease; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; -webkit-tap-highlight-color: transparent; }
#ivyforms-deactivation-modal-mount .ivyforms-modal-btn.skip { background: #f9fafb; color: #4b5563; border-color: #e5e7eb; }
#ivyforms-deactivation-modal-mount .ivyforms-modal-btn.skip:hover { background: #f3f4f6; border-color: #d1d5db; }
#ivyforms-deactivation-modal-mount .ivyforms-modal-btn.submit { background: #06A192; color: #fff; border-color: #06A192; }
#ivyforms-deactivation-modal-mount .ivyforms-modal-btn.submit:hover { background: #036a73; border-color: #036a73; }
        `,
      ),
    )
    // Close button
    modal.appendChild(
      el('button', {
        innerHTML: '&times;',
        style: {
          position: 'absolute',
          top: '16px',
          right: '16px',
          background: 'none',
          border: 'none',
          fontSize: '24px',
          cursor: 'pointer',
          color: '#888',
        },
        onclick: () => overlay.remove(),
      }),
    )
    // Mascot
    const mascotWrapper = el('div', {
      style: { width: '120px', margin: '0 auto 20px', display: 'block' },
    })
    mascotWrapper.innerHTML = '<div class="mascot-placeholder" aria-hidden="true"></div>'
    fetch(pluginUrl + '/frontend/assets/img/mascot/IvyMascot.svg')
      .then((r) => (r.ok ? r.text() : ''))
      .then((svg) => {
        mascotWrapper.innerHTML = svg
          ? svg.replace(/xmlns=["']https?:\/\/www\.w3\.org\/2000\/svg["']/g, '')
          : ''
      })
    modal.appendChild(mascotWrapper)
    // Title & desc
    modal.appendChild(
      el(
        'h2',
        { style: { marginBottom: '10px', textAlign: 'center', fontWeight: 600, fontSize: '20px' } },
        "We're sorry to see you go!",
      ),
    )
    modal.appendChild(
      el(
        'p',
        { style: { marginBottom: '20px', textAlign: 'center', fontSize: '15px', color: '#444' } },
        "Can you tell us why you're deactivating the plugin?",
      ),
    )
    // Options
    const options = [
      { label: 'I no longer need it', value: 'no_need' },
      { label: 'I found a better plugin', value: 'better_plugin' },
      { label: 'I experienced issue/bugs', value: 'bugs' },
      { label: 'Missing Features', value: 'missing_features' },
      { label: 'Temporary deactivation', value: 'temporary' },
      { label: 'Other', value: 'other' },
    ]
    let checked = {},
      textareaDiv = null
    const optionsBox = el('div', {
      style: {
        border: 'none',
        borderRadius: 0,
        background: 'transparent',
        padding: 0,
        marginBottom: 0,
        display: 'flex',
        flexDirection: 'column',
        gap: 0,
      },
    })
    options.forEach((opt) => {
      const row = el('div', { className: 'ivy-option-row' })
      const label = el('label')
      const input = el('input', {
        type: 'checkbox',
        value: opt.value,
        style: {
          position: 'absolute',
          opacity: 0,
          width: '16px',
          height: '16px',
          left: 0,
          top: '50%',
          transform: 'translateY(-50%)',
        },
      })
      const customBox = el('span', {
        className: 'ivy-custom-checkbox',
        role: 'checkbox',
        tabIndex: 0,
        'aria-checked': 'false',
      })
      function renderCustomChecked(el, isChecked) {
        if (isChecked) {
          el.classList.add('checked')
          el.setAttribute('aria-checked', 'true')
          el.innerHTML =
            '<svg width="12" height="10" viewBox="0 0 12 10" style="display:block;margin:2px"><path d="M1 5L4 8L11 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        } else {
          el.classList.remove('checked')
          el.setAttribute('aria-checked', 'false')
          el.innerHTML = ''
        }
      }
      input.addEventListener('change', () => {
        if (input.checked) {
          options.forEach((otherOpt) => {
            if (otherOpt.value !== opt.value) checked[otherOpt.value] = false
          })
          document
            .querySelectorAll('#ivyforms-deactivation-modal-mount .ivy-custom-checkbox')
            .forEach((box) => {
              if (box !== customBox && box.classList.contains('checked')) {
                box.classList.remove('checked')
                box.setAttribute('aria-checked', 'false')
                box.innerHTML = ''
                const correspondingInput = box.parentElement.querySelector('input[type="checkbox"]')
                if (correspondingInput) correspondingInput.checked = false
              }
            })
        }
        checked[opt.value] = input.checked
        if (opt.value === 'other' && textareaDiv)
          textareaDiv.style.display = input.checked ? 'block' : 'none'
        renderCustomChecked(customBox, input.checked)
      })
      customBox.addEventListener('click', (ev) => {
        ev.preventDefault()
        input.checked = !input.checked
        input.dispatchEvent(new Event('change', { bubbles: true }))
      })
      customBox.addEventListener('keydown', (ev) => {
        if (ev.key === ' ' || ev.key === 'Enter') {
          ev.preventDefault()
          input.checked = !input.checked
          input.dispatchEvent(new Event('change', { bubbles: true }))
        }
      })
      label.appendChild(input)
      label.appendChild(customBox)
      label.appendChild(el('span', {}, opt.label))
      row.appendChild(label)
      optionsBox.appendChild(row)
      renderCustomChecked(customBox, !!input.checked)
    })
    // Textarea for 'Other'
    textareaDiv = el(
      'div',
      { style: { marginTop: '16px', display: 'none' } },
      el('textarea', {
        placeholder: 'Write your comment here',
        style: {
          width: '100%',
          height: '80px',
          resize: 'none',
          borderRadius: '6px',
          border: '1px solid #e5e7eb',
          padding: '12px',
          fontSize: '14px',
          boxSizing: 'border-box',
          fontFamily: 'inherit',
        },
      }),
    )
    const groupContainer = el(
      'div',
      { style: { marginBottom: '20px', textAlign: 'left' } },
      optionsBox,
      textareaDiv,
    )
    modal.appendChild(groupContainer)

    // Buttons
    const btnRow = el('div', {
      style: {
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'flex-end',
        gap: '12px',
        marginTop: '24px',
        paddingTop: '16px',
        borderTop: '1px solid #e5e7eb',
      },
    })
    btnRow.appendChild(
      el('button', {
        className: 'ivyforms-modal-btn skip',
        textContent: 'Skip & Deactivate',
        onclick: () => {
          window.location.href = window.ivyformsDeactivateUrl
        },
      }),
    )
    btnRow.appendChild(
      el('button', {
        className: 'ivyforms-modal-btn submit',
        textContent: 'Submit & Deactivate',
        onclick: () => {
          const selectedReasons = Object.keys(checked).filter((k) => checked[k])
          const selectedReason = selectedReasons.length > 0 ? selectedReasons[0] : ''
          const feedbackComment = checked['other']
            ? textareaDiv.querySelector('textarea').value
            : ''
          const pluginVersion =
            typeof window.ivyformsData !== 'undefined' && window.ivyformsData.version
              ? window.ivyformsData.version
              : ''
          const restApiUrl =
            typeof window.ivyformsData !== 'undefined' && window.ivyformsData.restApiUrl
              ? window.ivyformsData.restApiUrl
              : ''

          // Always deactivate - helper function
          const deactivatePlugin = () => {
            window.location.href = window.ivyformsDeactivateUrl
          }

          // Try to send feedback, but always deactivate regardless of result
          if (restApiUrl && selectedReason) {
            fetch(restApiUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce':
                  typeof window.ivyformsData !== 'undefined' && window.ivyformsData.restNonce
                    ? window.ivyformsData.restNonce
                    : '',
              },
              body: JSON.stringify({
                api_version: pluginVersion,
                feedback_key: selectedReason,
                feedback: feedbackComment,
              }),
            })
              .then(() => deactivatePlugin())
              .catch(() => deactivatePlugin()) // Always deactivate even on error
          } else {
            deactivatePlugin()
          }
        },
      }),
    )
    modal.appendChild(btnRow)
    overlay.appendChild(modal)
    document.body.appendChild(overlay)
  }
})
