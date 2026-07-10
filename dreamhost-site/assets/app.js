(function () {
  const SATS_PER_BTC = 100000000;
  const NODESTRICH_PUBKEY_HEX = 'b9936c40a862f752f9ea1edd35773c65ecd53b497c19bac3e7aa0e8fdc46a70b';
  const RELAYS = ['wss://relay.damus.io', 'wss://relay.primal.net', 'wss://nos.lol'];
  const BECH32_CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';

  document.addEventListener('DOMContentLoaded', () => {
    setupNavigation();
    setupSearchBoxes();
    setupFilters();
    setupCounters();
    setupMemberDirectory();
    setupNostrLatestPost();
    setupBolt11Tool();
    setupConverterTool();
    setupChannelCalculator();
  });

  function setupNavigation() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-mobile-nav]');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
      const isOpen = nav.hasAttribute('hidden');
      nav.toggleAttribute('hidden', !isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    });
  }

  function setupSearchBoxes() {
    document.querySelectorAll('[data-search-box]').forEach((box) => {
      const input = box.querySelector('[data-search-input]');
      const results = box.querySelector('[data-search-results]');
      if (!input || !results) return;

      let timer = null;

      input.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();

        if (query.length < 2) {
          results.hidden = true;
          results.innerHTML = '';
          return;
        }

        timer = setTimeout(async () => {
          try {
            const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
            const data = res.ok ? await res.json() : [];
            renderSearchResults(results, data, query);
          } catch {
            renderSearchResults(results, [], query);
          }
        }, 250);
      });

      input.addEventListener('focus', () => {
        if (results.innerHTML.trim() !== '') results.hidden = false;
      });

      document.addEventListener('mousedown', (event) => {
        if (!box.contains(event.target)) results.hidden = true;
      });
    });
  }

  function renderSearchResults(container, items, query) {
    if (!items.length) {
      container.innerHTML = `<div class="search-result"><p>No results found for "${escapeHtml(query)}"</p></div>`;
      container.hidden = false;
      return;
    }

    container.innerHTML = items.map((item) => {
      const tags = (item.tags || []).slice(0, 2).map((tag) => `<span>${escapeHtml(tag)}</span>`).join('');
      return `
        <a class="search-result" href="/learn/${escapeAttr(item.slug)}">
          <strong>${escapeHtml(item.title)}</strong>
          <p>${escapeHtml(item.description)}</p>
          <div class="tag-row"><span>${escapeHtml(item.category)}</span>${tags}</div>
        </a>
      `;
    }).join('');
    container.hidden = false;
  }

  function setupFilters() {
    document.querySelectorAll('[data-filter-scope]').forEach((scope) => {
      const buttons = Array.from(scope.querySelectorAll('[data-filter]'));
      const cards = Array.from(scope.querySelectorAll('[data-category]'));
      const empty = scope.querySelector('[data-filter-empty]');

      buttons.forEach((button) => {
        button.addEventListener('click', () => {
          const filter = button.getAttribute('data-filter');
          buttons.forEach((item) => item.classList.toggle('is-active', item === button));

          let visible = 0;
          cards.forEach((card) => {
            const show = filter === 'all' || card.getAttribute('data-category') === filter;
            card.hidden = !show;
            if (show) visible++;
          });

          if (empty) empty.hidden = visible !== 0;
        });
      });
    });
  }

  function setupCounters() {
    document.querySelectorAll('.js-counter').forEach((node) => {
      const target = parseInt(node.getAttribute('data-target') || '0', 10);
      if (!target || target < 0) return;

      const duration = 900;
      const start = performance.now();

      function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        node.textContent = Math.floor(target * eased).toLocaleString();
        if (progress < 1) requestAnimationFrame(tick);
      }

      requestAnimationFrame(tick);
    });
  }

  function setupMemberDirectory() {
    const input = document.querySelector('[data-member-search]');
    const grid = document.querySelector('[data-member-grid]');
    const count = document.querySelector('[data-member-count]');
    const empty = document.querySelector('[data-member-empty]');
    if (!input || !grid) return;

    const cards = Array.from(grid.querySelectorAll('[data-member]'));
    const total = cards.length;

    input.addEventListener('input', () => {
      const query = input.value.trim().toLowerCase();
      let visible = 0;

      cards.forEach((card) => {
        const show = !query || (card.getAttribute('data-member') || '').includes(query);
        card.hidden = !show;
        if (show) visible++;
      });

      if (count) count.textContent = `${visible} of ${total}`;
      if (empty) empty.hidden = visible !== 0;
    });
  }

  function setupNostrLatestPost() {
    const panel = document.querySelector('[data-nostr-latest]');
    if (!panel) return;

    let cancelled = false;
    let relayIndex = 0;
    let latest = null;

    function tryRelay() {
      if (cancelled) return;
      if (relayIndex >= RELAYS.length) {
        if (!latest) panel.remove();
        return;
      }

      const relay = RELAYS[relayIndex++];
      let ws;
      let timeout;

      try {
        ws = new WebSocket(relay);
      } catch {
        tryRelay();
        return;
      }

      timeout = setTimeout(() => {
        try { ws.close(); } catch {}
        tryRelay();
      }, 5000);

      ws.onopen = () => {
        ws.send(JSON.stringify(['REQ', 'nodestrich-latest', {
          kinds: [1],
          authors: [NODESTRICH_PUBKEY_HEX],
          limit: 20,
        }]));
      };

      ws.onmessage = (message) => {
        try {
          const data = JSON.parse(message.data);
          if (data[0] === 'EVENT' && data[2] && isTopLevelPost(data[2])) {
            if (!latest || data[2].created_at > latest.created_at) latest = data[2];
          }

          if (data[0] === 'EOSE') {
            clearTimeout(timeout);
            try { ws.close(); } catch {}
            if (latest) renderNostrPost(panel, latest);
            else panel.remove();
          }
        } catch {}
      };

      ws.onerror = () => {
        clearTimeout(timeout);
        try { ws.close(); } catch {}
        tryRelay();
      };
    }

    tryRelay();
    window.addEventListener('beforeunload', () => { cancelled = true; });
  }

  function isTopLevelPost(event) {
    return !(event.tags || []).some((tag) => tag[0] === 'e');
  }

  function renderNostrPost(panel, note) {
    const date = new Date(note.created_at * 1000).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
    const noteId = encodeBech32('note', convertBits(hexToBytes(note.id), 8, 5, true));
    const content = renderPostContent(note.content || '');

    panel.innerHTML = `
      <div class="card-heading">
        <h2>Latest from Nodestrich</h2>
        <span class="muted small">${escapeHtml(date)}</span>
      </div>
      <div class="post-content">${content}</div>
      <p><a href="https://jumble.social/${escapeAttr(noteId)}" target="_blank" rel="noopener noreferrer">View full post on Jumble</a></p>
    `;
  }

  function renderPostContent(content) {
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return escapeHtml(content).replace(urlRegex, (url) => {
      const cleanUrl = url.replace(/[).,;:!?]+$/, '');
      if (/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(cleanUrl)) {
        return `<img src="${escapeAttr(cleanUrl)}" alt="" loading="lazy">`;
      }
      return `<a href="${escapeAttr(cleanUrl)}" target="_blank" rel="noopener noreferrer">${cleanUrl}</a>`;
    });
  }

  function setupBolt11Tool() {
    const tool = document.querySelector('[data-bolt11-tool]');
    if (!tool) return;

    const input = tool.querySelector('[data-bolt11-input]');
    const decodeButton = tool.querySelector('[data-bolt11-decode]');
    const clearButton = tool.querySelector('[data-bolt11-clear]');
    const errorBox = tool.querySelector('[data-bolt11-error]');
    const resultBox = tool.querySelector('[data-bolt11-result]');

    decodeButton.addEventListener('click', () => {
      hide(errorBox);
      hide(resultBox);

      try {
        const invoice = input.value.trim();
        if (!invoice) throw new Error('Please enter a BOLT11 invoice.');
        const decoded = decodeBolt11(invoice);
        renderBolt11Result(resultBox, decoded);
      } catch (error) {
        errorBox.textContent = error.message || 'Failed to decode invoice.';
        show(errorBox);
      }
    });

    clearButton.addEventListener('click', () => {
      input.value = '';
      hide(errorBox);
      hide(resultBox);
    });
  }

  function renderBolt11Result(container, decoded) {
    const rows = [];
    rows.push(['Network', decoded.network]);
    if (decoded.amountMsat) rows.push(['Amount', formatMsats(decoded.amountMsat)]);
    rows.push(['Timestamp', new Date(decoded.timestamp * 1000).toLocaleString()]);

    decoded.tags.forEach((tag) => {
      if (tag.name === 'payment_hash') rows.push(['Payment Hash', tag.value, true]);
      if (tag.name === 'description') rows.push(['Description', tag.value]);
      if (tag.name === 'description_hash') rows.push(['Description Hash', tag.value, true]);
      if (tag.name === 'expiry') rows.push(['Expiry', `${tag.value}s (${formatExpiry(tag.value)})`]);
      if (tag.name === 'min_final_cltv_expiry') rows.push(['Min Final CLTV Expiry', `${tag.value} blocks`]);
      if (tag.name === 'payment_secret') rows.push(['Payment Secret', tag.value, true]);
      if (tag.name === 'feature_bits') rows.push(['Features', tag.value]);
      if (tag.name === 'route_hint') rows.push([tag.label, tag.value, true]);
      if (tag.name === 'payee') rows.push(['Payee', tag.value, true]);
    });

    container.innerHTML = rows.map(([label, value, mono]) => `
      <div class="result-row">
        <span>${escapeHtml(label)}</span>
        <strong>${mono ? `<code>${escapeHtml(String(value))}</code>` : escapeHtml(String(value))}</strong>
      </div>
    `).join('') + '<p class="muted small">This decoder does not verify invoice signatures. Do not use it to validate payment security.</p>';
    show(container);
  }

  function setupConverterTool() {
    const tool = document.querySelector('[data-converter-tool]');
    if (!tool) return;

    const satsInput = tool.querySelector('[data-sats-input]');
    const btcInput = tool.querySelector('[data-btc-input]');
    const usdInput = tool.querySelector('[data-usd-input]');
    const status = tool.querySelector('[data-price-status]');
    let btcPrice = null;

    async function fetchPrice() {
      try {
        const res = await fetch('/api/btc-price');
        if (!res.ok) throw new Error('Failed to fetch price');
        const data = await res.json();
        btcPrice = Number(data.usd);
        usdInput.disabled = !btcPrice;
        status.textContent = `1 BTC = ${btcPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} USD`;
        recalcFromActive();
      } catch {
        status.textContent = 'Price unavailable - BTC/sats conversion still works';
      }
    }

    function recalcFromActive() {
      const active = document.activeElement;
      if (active === usdInput) updateFromUsd(usdInput.value);
      else if (active === btcInput) updateFromBtc(btcInput.value);
      else if (active === satsInput) updateFromSats(satsInput.value);
    }

    function updateFromSats(value) {
      const cleaned = value.replace(/[^0-9]/g, '');
      satsInput.value = cleaned;
      if (!cleaned) {
        btcInput.value = '';
        usdInput.value = '';
        return;
      }
      const btc = parseInt(cleaned, 10) / SATS_PER_BTC;
      btcInput.value = btc.toFixed(8);
      usdInput.value = btcPrice ? (btc * btcPrice).toFixed(2) : '';
    }

    function updateFromBtc(value) {
      const cleaned = sanitizeDecimal(value);
      btcInput.value = cleaned;
      if (!cleaned || cleaned === '.') {
        satsInput.value = '';
        usdInput.value = '';
        return;
      }
      const btc = Number(cleaned);
      if (!Number.isFinite(btc)) return;
      satsInput.value = String(Math.round(btc * SATS_PER_BTC));
      usdInput.value = btcPrice ? (btc * btcPrice).toFixed(2) : '';
    }

    function updateFromUsd(value) {
      const cleaned = sanitizeDecimal(value);
      usdInput.value = cleaned;
      if (!cleaned || cleaned === '.' || !btcPrice) {
        satsInput.value = '';
        btcInput.value = '';
        return;
      }
      const usd = Number(cleaned);
      if (!Number.isFinite(usd)) return;
      const btc = usd / btcPrice;
      btcInput.value = btc.toFixed(8);
      satsInput.value = String(Math.round(btc * SATS_PER_BTC));
    }

    satsInput.addEventListener('input', () => updateFromSats(satsInput.value));
    btcInput.addEventListener('input', () => updateFromBtc(btcInput.value));
    usdInput.addEventListener('input', () => updateFromUsd(usdInput.value));

    fetchPrice();
    setInterval(fetchPrice, 60000);
  }

  function setupChannelCalculator() {
    const tool = document.querySelector('[data-channel-tool]');
    if (!tool) return;

    const avgInput = tool.querySelector('[data-avg-tx]');
    const countInput = tool.querySelector('[data-monthly-tx]');
    const feeInput = tool.querySelector('[data-fee-rate]');
    const routingInput = tool.querySelector('[data-routing-node]');
    const result = tool.querySelector('[data-channel-result]');

    [avgInput, countInput, feeInput].forEach((input) => {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/[^0-9]/g, '');
        calculate();
      });
    });
    routingInput.addEventListener('change', calculate);

    function calculate() {
      const avg = parseInt(avgInput.value, 10);
      const count = parseInt(countInput.value, 10);
      const fee = parseInt(feeInput.value || '0', 10);
      const routing = routingInput.checked;

      if (!avg || !count || avg <= 0 || count <= 0) {
        hide(result);
        return;
      }

      const monthlyVolume = avg * count;
      let minimum = monthlyVolume * 2;
      let recommended = monthlyVolume * 3;
      if (routing) {
        minimum = Math.round(minimum * 2.5);
        recommended = Math.round(recommended * 3);
      }

      minimum = Math.max(minimum, 20000);
      recommended = Math.max(recommended, 20000);

      const openCost = fee > 0 ? fee * 154 : 0;
      const needsWumbo = recommended > 16777215;
      const suggestedChannels = routing ? '4-8' : '2-3';

      result.innerHTML = `
        <div class="result-grid">
          ${resultItem('Monthly Volume', formatSats(monthlyVolume))}
          ${resultItem('Est. Channel Open Cost', openCost > 0 ? `~${openCost.toLocaleString()} sats` : 'N/A')}
          ${resultItem('Minimum Channel Size', formatSats(minimum), 'warning')}
          ${resultItem('Recommended Channel Size', formatSats(recommended), 'success')}
          ${resultItem('Suggested # of Channels', suggestedChannels)}
          ${needsWumbo ? resultItem('Note', 'Wumbo channels required', 'warning') : ''}
        </div>
        <p class="muted small">Recommendations include headroom for inbound/outbound balance.${routing ? ' Routing nodes need larger channels to handle bidirectional flow.' : ''} Avoid opening channels below 200K-500K sats as they may be uneconomical to close during high fee periods.</p>
      `;
      show(result);
    }
  }

  function resultItem(label, value, className) {
    return `<div class="result-item"><span>${escapeHtml(label)}</span><strong class="${className || ''}">${escapeHtml(value)}</strong></div>`;
  }

  function decodeBolt11(invoice) {
    const decoded = decodeBech32(invoice);
    if (!decoded.hrp.startsWith('ln')) throw new Error('Not a proper lightning payment request.');
    if (decoded.data.length < 111) throw new Error('Invoice is too short.');

    const prefix = parseBolt11Prefix(decoded.hrp);
    const words = decoded.data.slice(0, -104);
    const timestamp = wordsToInt(words.slice(0, 7));
    const tags = [];
    let i = 7;

    while (i + 2 < words.length) {
      const tagCode = words[i];
      const length = words[i + 1] * 32 + words[i + 2];
      const tagWords = words.slice(i + 3, i + 3 + length);
      if (tagWords.length < length) break;
      tags.push(...parseBolt11Tag(tagCode, tagWords));
      i += 3 + length;
    }

    return {
      network: prefix.network,
      amountMsat: prefix.amountMsat,
      timestamp,
      tags,
    };
  }

  function parseBolt11Prefix(hrp) {
    const rest = hrp.slice(2);
    const networks = [
      ['bcrt', 'Regtest'],
      ['tbs', 'Signet'],
      ['tb', 'Testnet'],
      ['bc', 'Bitcoin'],
      ['sb', 'Simnet'],
    ];
    const network = networks.find(([prefix]) => rest.startsWith(prefix));
    if (!network) throw new Error('Unknown Lightning network.');

    const amountPart = rest.slice(network[0].length);
    return {
      network: network[1],
      amountMsat: amountPart ? parseBolt11Amount(amountPart) : null,
    };
  }

  function parseBolt11Amount(amountPart) {
    const match = amountPart.match(/^(\d+)([munp]?)$/);
    if (!match) throw new Error('Invalid invoice amount.');

    const value = BigInt(match[1]);
    const unit = match[2];
    const millisatsPerBtc = 100000000000n;
    const divisors = { m: 1000n, u: 1000000n, n: 1000000000n, p: 1000000000000n };
    return unit ? (value * millisatsPerBtc) / divisors[unit] : value * millisatsPerBtc;
  }

  function parseBolt11Tag(code, words) {
    const bytes = wordsToBytes(words);
    const hex = bytesToHex(bytes);

    if (code === 1) return [{ name: 'payment_hash', value: hex }];
    if (code === 16) return [{ name: 'payment_secret', value: hex }];
    if (code === 13) return [{ name: 'description', value: utf8Decode(bytes) }];
    if (code === 19) return [{ name: 'payee', value: hex }];
    if (code === 23) return [{ name: 'description_hash', value: hex }];
    if (code === 6) return [{ name: 'expiry', value: wordsToInt(words) }];
    if (code === 24) return [{ name: 'min_final_cltv_expiry', value: wordsToInt(words) }];
    if (code === 5) return [{ name: 'feature_bits', value: parseFeatureBits(words) }];
    if (code === 3) return parseRouteHints(bytes);
    return [];
  }

  function parseFeatureBits(words) {
    const names = [
      'data_loss_protect',
      'initial_routing_sync',
      'upfront_shutdown_script',
      'gossip_queries',
      'var_onion',
      'gossip_queries_ex',
      'static_remotekey',
      'payment_secret',
      'basic_mpp',
      'large_channel',
    ];
    const bools = [];

    words.slice().reverse().forEach((word) => {
      for (let bit = 0; bit < 5; bit++) bools.push(Boolean(word & (1 << bit)));
    });

    const enabled = [];
    names.forEach((name, index) => {
      const required = bools[index * 2];
      const supported = bools[index * 2 + 1];
      if (required || supported) enabled.push(name);
    });

    return enabled.length ? enabled.join(', ') : 'None advertised';
  }

  function parseRouteHints(bytes) {
    const hints = [];
    for (let i = 0, index = 1; i + 51 <= bytes.length; i += 51, index++) {
      const pubkey = bytesToHex(bytes.slice(i, i + 33));
      const shortChannelId = bytesToHex(bytes.slice(i + 33, i + 41));
      const feeBase = bytesToInt(bytes.slice(i + 41, i + 45));
      const ppm = bytesToInt(bytes.slice(i + 45, i + 49));
      hints.push({
        name: 'route_hint',
        label: `Route Hint ${index}`,
        value: `pubkey: ${truncate(pubkey)} | channel: ${shortChannelId} | base_fee: ${feeBase}msat | ppm: ${ppm}`,
      });
    }
    return hints;
  }

  function decodeBech32(value) {
    if (value !== value.toLowerCase() && value !== value.toUpperCase()) {
      throw new Error('Mixed-case bech32 strings are invalid.');
    }

    const lower = value.toLowerCase();
    const separator = lower.lastIndexOf('1');
    if (separator < 1 || separator + 7 > lower.length) {
      throw new Error('Invalid bech32 string.');
    }

    const hrp = lower.slice(0, separator);
    const data = [];
    for (const char of lower.slice(separator + 1)) {
      const word = BECH32_CHARSET.indexOf(char);
      if (word === -1) throw new Error('Invalid bech32 character.');
      data.push(word);
    }

    if (!verifyBech32Checksum(hrp, data)) {
      throw new Error('Invalid invoice checksum.');
    }

    return { hrp, data: data.slice(0, -6) };
  }

  function verifyBech32Checksum(hrp, data) {
    return bech32Polymod([...bech32HrpExpand(hrp), ...data]) === 1;
  }

  function bech32Polymod(values) {
    const gen = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
    let chk = 1;
    values.forEach((value) => {
      const top = chk >> 25;
      chk = ((chk & 0x1ffffff) << 5) ^ value;
      for (let i = 0; i < 5; i++) {
        if ((top >> i) & 1) chk ^= gen[i];
      }
    });
    return chk;
  }

  function bech32HrpExpand(hrp) {
    const ret = [];
    for (let i = 0; i < hrp.length; i++) ret.push(hrp.charCodeAt(i) >> 5);
    ret.push(0);
    for (let i = 0; i < hrp.length; i++) ret.push(hrp.charCodeAt(i) & 31);
    return ret;
  }

  function encodeBech32(hrp, data) {
    const combined = [...data, ...bech32CreateChecksum(hrp, data)];
    return `${hrp}1${combined.map((word) => BECH32_CHARSET[word]).join('')}`;
  }

  function bech32CreateChecksum(hrp, data) {
    const values = [...bech32HrpExpand(hrp), ...data, 0, 0, 0, 0, 0, 0];
    const polymod = bech32Polymod(values) ^ 1;
    const checksum = [];
    for (let i = 0; i < 6; i++) checksum.push((polymod >> (5 * (5 - i))) & 31);
    return checksum;
  }

  function convertBits(data, fromBits, toBits, pad) {
    let acc = 0;
    let bits = 0;
    const ret = [];
    const maxv = (1 << toBits) - 1;

    data.forEach((value) => {
      acc = (acc << fromBits) | value;
      bits += fromBits;
      while (bits >= toBits) {
        bits -= toBits;
        ret.push((acc >> bits) & maxv);
      }
    });

    if (pad && bits > 0) ret.push((acc << (toBits - bits)) & maxv);
    return ret;
  }

  function wordsToBytes(words) {
    return convertBits(words, 5, 8, false);
  }

  function wordsToInt(words) {
    return words.reduce((total, word) => total * 32 + word, 0);
  }

  function bytesToInt(bytes) {
    return bytes.reduce((total, byte) => total * 256 + byte, 0);
  }

  function bytesToHex(bytes) {
    return bytes.map((byte) => byte.toString(16).padStart(2, '0')).join('');
  }

  function hexToBytes(hex) {
    const bytes = [];
    for (let i = 0; i < hex.length; i += 2) bytes.push(parseInt(hex.slice(i, i + 2), 16));
    return bytes;
  }

  function utf8Decode(bytes) {
    try {
      return new TextDecoder().decode(Uint8Array.from(bytes));
    } catch {
      return String.fromCharCode(...bytes);
    }
  }

  function formatMsats(msats) {
    const value = typeof msats === 'bigint' ? msats : BigInt(msats);
    const sats = Number(value) / 1000;
    const btc = sats / SATS_PER_BTC;
    if (sats >= 1000000) return `${sats.toLocaleString()} sats (${btc.toFixed(8)} BTC)`;
    return `${sats.toLocaleString()} sats`;
  }

  function formatExpiry(seconds) {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
    return `${Math.floor(seconds / 86400)}d`;
  }

  function formatSats(sats) {
    if (sats >= SATS_PER_BTC) return `${sats.toLocaleString()} sats (${(sats / SATS_PER_BTC).toFixed(8)} BTC)`;
    return `${sats.toLocaleString()} sats`;
  }

  function sanitizeDecimal(value) {
    const cleaned = value.replace(/[^0-9.]/g, '');
    const parts = cleaned.split('.');
    return parts.length > 2 ? `${parts[0]}.${parts.slice(1).join('')}` : cleaned;
  }

  function truncate(value, chars = 12) {
    return value.length <= chars * 2 + 3 ? value : `${value.slice(0, chars)}...${value.slice(-chars)}`;
  }

  function show(element) {
    if (element) element.hidden = false;
  }

  function hide(element) {
    if (element) element.hidden = true;
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeAttr(value) {
    return escapeHtml(value);
  }
})();
