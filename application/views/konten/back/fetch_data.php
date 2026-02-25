<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Fetch Progress (NDJSON)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap (opsional, biar cepat bikin progress bar) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    pre { background: #0f172a; color: #e2e8f0; padding: 10px; border-radius: 8px; max-height: 260px; overflow:auto; }
    .small { font-size: .9rem; }
    #dataCount { font-weight: 600; }
    .badge-soft { background: #eef2ff; color:#3730a3; }
  </style>
</head>
<body>
  <div class="container">
    <h3 class="mb-3">Streaming Fetch with Progress (Weekly)</h3>

    <form id="fetchForm" class="row g-2 mb-3">
      <div class="col-md-2">
        <label class="form-label">ID Logger</label>
        <input type="text" class="form-control" name="id_logger" value="10001" required />
      </div>
      <div class="col-md-2">
        <label class="form-label">Awal (YYYY-MM-DD)</label>
        <input type="text" class="form-control" name="awal" value="2025-01-01" required />
      </div>
      <div class="col-md-2">
        <label class="form-label">Akhir (YYYY-MM-DD)</label>
        <input type="text" class="form-control" name="akhir" value="2025-08-27" required />
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">Mulai Fetch</button>
        <button type="button" id="btnCancel" class="btn btn-outline-danger" disabled>Batalkan</button>
      </div>
    </form>

    <div class="mb-2 small">
      <span class="badge badge-soft me-2">Status: <span id="statusText">idle</span></span>
      <span class="me-2">Week: <span id="weekIdx">0</span>/<span id="weekTotal">0</span></span>
      <span class="me-2">Rows collected: <span id="dataCount">0</span></span>
    </div>

    <div class="progress mb-3" role="progressbar" aria-label="Progress minggu">
      <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <h6>Event Log</h6>
        <pre id="log"></pre>
      </div>
      <div class="col-md-6">
        <h6>Data Preview (final)</h6>
        <pre id="dataPreview">[menunggu complete...]</pre>
      </div>
    </div>
  </div>

<script>
(() => {
  const API_BASE = "https://demo.beacontelemetry.com/go/fetch_progress";

  const form = document.getElementById('fetchForm');
  const btnCancel = document.getElementById('btnCancel');
  const logEl = document.getElementById('log');
  const previewEl = document.getElementById('dataPreview');
  const statusText = document.getElementById('statusText');
  const weekIdxEl = document.getElementById('weekIdx');
  const weekTotalEl = document.getElementById('weekTotal');
  const progressBar = document.getElementById('progressBar');
  const dataCountEl = document.getElementById('dataCount');

  let controller = null;

  function appendLog(obj) {
    const line = typeof obj === 'string' ? obj : JSON.stringify(obj);
    logEl.textContent += line + "\n";
    logEl.scrollTop = logEl.scrollHeight;
  }

  function setProgress(current, total) {
    weekIdxEl.textContent = current;
    weekTotalEl.textContent = total;
    const pct = total > 0 ? Math.round((current / total) * 100) : 0;
    progressBar.style.width = pct + "%";
    progressBar.textContent = pct + "%";
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (controller) controller.abort();
    controller = new AbortController();

    // reset UI
    logEl.textContent = "";
    previewEl.textContent = "[menunggu complete...]";
    statusText.textContent = "connecting...";
    setProgress(0,0);
    dataCountEl.textContent = "0";
    btnCancel.disabled = false;

    const fd = new FormData(form);
    const id_logger = fd.get('id_logger');
    const awal = fd.get('awal');
    const akhir = fd.get('akhir');

    const url = new URL(API_BASE);
    url.searchParams.set('id_logger', id_logger);
    url.searchParams.set('awal', awal);
    url.searchParams.set('akhir', akhir);

    try {
      const res = await fetch(url.toString(), {
        method: 'GET',
        signal: controller.signal,
        headers: {
          'Accept': 'application/x-ndjson'
        }
      });
      if (!res.ok || !res.body) {
        statusText.textContent = `error: ${res.status}`;
        appendLog(`HTTP ${res.status}`);
        btnCancel.disabled = true;
        return;
      }

      statusText.textContent = "streaming...";
      const reader = res.body.getReader();
      const decoder = new TextDecoder();
      let buffer = "";

      while (true) {
        const { value, done } = await reader.read();
        if (done) break;

        buffer += decoder.decode(value, { stream: true });
        const lines = buffer.split("\n");
        buffer = lines.pop() || ""; // sisakan partial terakhir

        for (const line of lines) {
          if (!line.trim()) continue;
          let evt;
          try { evt = JSON.parse(line); } catch { appendLog(line); continue; }

          appendLog(evt);
          if (evt._event === "start") {
            // ada total_weeks
            const total = evt.total_weeks || 0;
            setProgress(0, total);
            statusText.textContent = "started";
          }
          if (evt._event === "progress") {
            const idx = Number(evt.idx || 0);
            const total = Number(evt.total || 0);
            const collected = Number(evt.rows_collected || 0);
            setProgress(idx, total);
            dataCountEl.textContent = String(collected);
            statusText.textContent = `progress week ${idx}/${total}`;
          }
          if (evt._event === "week_error") {
            statusText.textContent = "week error (lihat log)";
          }
          if (evt._event === "complete") {
            statusText.textContent = "complete";
            dataCountEl.textContent = String(evt.total_rows || 0);
            // tampilkan preview (truncate jika besar)
            try {
              const pretty = JSON.stringify(evt.data ?? [], null, 2);
              previewEl.textContent = pretty.length > 50000
                ? pretty.slice(0, 50000) + "\n... (truncated)"
                : pretty;
            } catch {
              previewEl.textContent = "[gagal render data]";
            }
            setProgress(Number(weekTotalEl.textContent||0), Number(weekTotalEl.textContent||0));
          }
        }
      }

      // flush last chunk if valid json
      if (buffer.trim()) {
        try {
          const evt = JSON.parse(buffer);
          appendLog(evt);
        } catch {
          appendLog(buffer);
        }
      }

      statusText.textContent = (statusText.textContent === "complete") ? "complete" : "ended";
    } catch (err) {
      statusText.textContent = "aborted/failed";
      appendLog(`ERR: ${err}`);
    } finally {
      btnCancel.disabled = true;
      controller = null;
    }
  });

  btnCancel.addEventListener('click', () => {
    if (controller) {
      controller.abort();
      appendLog("Request dibatalkan oleh user.");
      statusText.textContent = "canceled";
      btnCancel.disabled = true;
    }
  });

})();
</script>
</body>
</html>
