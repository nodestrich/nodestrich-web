(function () {
  'use strict';

  const card = document.querySelector('[data-donation]');
  if (!card) return;

  const find = (selector) => card.querySelector(selector);
  const amountForm = find('[data-donation-amount]');
  const invoicePanel = find('[data-donation-invoice]');
  const paidPanel = find('[data-donation-paid]');
  const amountInput = find('#donation-amount');
  const commentInput = find('#donation-comment');
  const submit = find('[data-donation-submit]');
  const error = find('[data-donation-error]');
  const status = find('[data-donation-status]');
  const addressQr = find('[data-donation-address-qr]');
  const addressToggle = find('[data-donation-address-toggle]');
  const dialog = document.querySelector('[data-donation-dialog]');
  const format = (amount) => amount.toLocaleString('en-US');
  let params = null;
  let invoice = '';
  let busy = false;
  let generation = 0;
  let verificationUrl = null;
  let verificationDeadline = 0;
  let pollTimer = null;

  async function fetchJson(url) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    try {
      const response = await fetch(url, {signal: controller.signal, credentials: 'omit'});
      if (!response.ok) throw new Error('Request failed');
      const result = await response.json();
      if (result.status === 'ERROR') throw new Error('Lightning service error');
      return result;
    } finally {
      clearTimeout(timeout);
    }
  }

  function secureUrl(value) {
    const url = new URL(value);
    if (url.protocol !== 'https:' || url.username || url.password) throw new Error('Invalid payment URL');
    return url;
  }

  function show(panel) {
    [amountForm, invoicePanel, paidPanel].forEach((element) => { element.hidden = element !== panel; });
  }

  function updateAmount() {
    amountInput.value = amountInput.value.replace(/[^0-9]/g, '').replace(/^0+(?=\d)/, '').slice(0, 9) || '0';
    const amount = Number(amountInput.value);
    const minimum = params ? Math.ceil(params.minSendable / 1000) : 1;
    const maximum = params ? Math.floor(params.maxSendable / 1000) : 0;
    submit.disabled = busy || !params || amount < minimum || amount > maximum;
    submit.textContent = busy ? 'Getting invoice…' : amount > 0 ? `Donate ${format(amount)} sats` : 'Donate';
    error.textContent = amount > 0 && (amount < minimum || amount > maximum)
      ? `Choose between ${format(minimum)} and ${format(maximum)} sats.` : '';
  }

  function renderQr(target, value) {
    const code = window.qrcode(0, 'M');
    code.addData(value.toUpperCase(), 'Alphanumeric');
    code.make();
    target.innerHTML = code.createSvgTag({cellSize: 6, margin: 24, scalable: true});
    target.querySelector('svg').setAttribute('aria-label', 'Lightning invoice QR code');
    target.querySelector('svg').setAttribute('role', 'img');
  }

  async function copy(value) {
    const message = find('[data-donation-copy-status]');
    try {
      await navigator.clipboard.writeText(value);
      message.className = 'small';
      message.textContent = 'Copied!';
    } catch (_) {
      message.className = 'small';
      message.textContent = 'Could not copy. Open in your wallet or scan the QR code instead.';
    }
  }

  function stopPolling() {
    clearTimeout(pollTimer);
    pollTimer = null;
  }

  async function checkPayment() {
    stopPolling();
    if (!verificationUrl || document.hidden || invoicePanel.hidden) return;
    if (Date.now() >= verificationDeadline) {
      status.textContent = 'Payment confirmation timed out. Check your wallet before sending again.';
      return;
    }
    const requestGeneration = generation;
    try {
      const result = await fetchJson(verificationUrl);
      if (requestGeneration !== generation) return;
      if (result.settled === true && result.pr === invoice) {
        verificationUrl = null;
        show(paidPanel);
        paidPanel.querySelector('button').focus();
        return;
      }
    } catch (_) {
      if (requestGeneration !== generation) return;
    }
    if (!document.hidden && !invoicePanel.hidden) pollTimer = setTimeout(checkPayment, 3000);
  }

  function reset() {
    generation++;
    stopPolling();
    verificationUrl = null;
    show(amountForm);
    updateAmount();
    amountInput.focus();
  }

  amountInput.addEventListener('input', updateAmount);
  card.querySelectorAll('[data-sats]').forEach((button) => button.addEventListener('click', () => {
    if (busy) return;
    amountInput.value = button.dataset.sats;
    updateAmount();
  }));
  card.querySelectorAll('[data-key]').forEach((button) => button.addEventListener('click', () => {
    if (busy) return;
    amountInput.value = button.dataset.key === 'delete' ? amountInput.value.slice(0, -1) : amountInput.value + button.dataset.key;
    updateAmount();
  }));

  amountForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    updateAmount();
    if (submit.disabled) return;
    const amount = Number(amountInput.value);
    busy = true;
    amountInput.readOnly = true;
    updateAmount();
    try {
      const callback = secureUrl(params.callback);
      callback.searchParams.set('amount', String(amount * 1000));
      if (params.commentAllowed > 0) callback.searchParams.set('comment', Array.from(commentInput.value).slice(0, params.commentAllowed).join(''));
      const result = await fetchJson(callback);
      if (typeof result.pr !== 'string' || !/^lnbc[0-9a-z]+$/i.test(result.pr) || result.pr.length > 8000) throw new Error('Invalid invoice');
      renderQr(find('[data-donation-zoom]'), result.pr);
      invoice = result.pr;
      find('[data-open-invoice]').href = `lightning:${invoice}`;
      find('[data-donation-total]').textContent = `${format(amount)} sats`;
      status.textContent = 'Pay with your Lightning wallet. Check your wallet for confirmation.';
      show(invoicePanel);
      find('[data-copy-invoice]').focus();
      if (result.verify) {
        try {
          verificationUrl = secureUrl(result.verify).href;
          verificationDeadline = Date.now() + 300000;
          status.textContent = 'Waiting for payment…';
          checkPayment();
        } catch (_) {
          verificationUrl = null;
        }
      }
    } catch (_) {
      error.textContent = 'Could not create an invoice. Try again or use the Lightning address below.';
      addressQr.hidden = false;
      addressToggle.textContent = 'Hide address QR code';
    } finally {
      busy = false;
      amountInput.readOnly = false;
      submit.disabled = false;
      submit.textContent = 'Donate';
    }
  });

  find('[data-copy-invoice]').addEventListener('click', () => copy(invoice));
  find('[data-copy-address]').hidden = false;
  find('[data-copy-address]').addEventListener('click', () => copy('nodestrich@nodestrich.com'));
  find('[data-donation-back]').addEventListener('click', reset);
  find('[data-donation-again]').addEventListener('click', () => { amountInput.value = '0'; reset(); });
  addressToggle.addEventListener('click', () => {
    addressQr.hidden = !addressQr.hidden;
    addressToggle.textContent = addressQr.hidden ? 'Show address QR code' : 'Hide address QR code';
  });
  find('[data-donation-zoom]').addEventListener('click', () => {
    renderQr(document.querySelector('[data-donation-large-qr]'), invoice);
    dialog.showModal();
  });
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
  document.addEventListener('visibilitychange', () => { document.hidden ? stopPolling() : checkPayment(); });
  window.addEventListener('pagehide', () => { generation++; stopPolling(); });

  fetchJson('https://nodestrich.com/.well-known/lnurlp/nodestrich').then((result) => {
    if (result.tag !== 'payRequest' || !Number.isSafeInteger(result.minSendable) || !Number.isSafeInteger(result.maxSendable)
      || result.minSendable < 1 || result.maxSendable < result.minSendable || typeof window.qrcode !== 'function') throw new Error('Invalid payment settings');
    secureUrl(result.callback);
    params = result;
    commentInput.maxLength = Number.isSafeInteger(result.commentAllowed) && result.commentAllowed > 0 ? Math.min(result.commentAllowed, 255) : 0;
    commentInput.hidden = commentInput.maxLength === 0;
    find('[data-donation-limits]').textContent = `${format(Math.ceil(result.minSendable / 1000))}–${format(Math.floor(result.maxSendable / 1000))} sats`;
    addressQr.hidden = true;
    addressToggle.hidden = false;
    show(amountForm);
    updateAmount();
  }).catch(() => {
    find('[data-donation-help]').textContent = 'The amount picker is unavailable. Scan, copy, or open this Lightning address in your wallet to donate.';
  });
}());
