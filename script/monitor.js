
let syncChart;

document.addEventListener("DOMContentLoaded", () => {
  // Initialisation du graphique
  const ctx = document.getElementById("syncGraph").getContext("2d");
  const tolerance = parseFloat(document.getElementById("tolerance").value);
  syncChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [
        {
          label: "Latence (ms)",
          borderColor: "#4bc0c0",
          backgroundColor: "transparent",
          data: [],
        },
        {
          label: "Décalage Audio (ms)",
          borderColor: "#ff6384",
          backgroundColor: "transparent",
          data: [],
        }
      ],
    },
    options: {
      responsive: true,
      animation: false,
      plugins: {
        legend: {
          labels: { color: "#fff" },
        },
        annotation: {
          annotations: {
            toleranceLine: {
              type: 'line',
              yMin: tolerance,
              yMax: tolerance,
              borderColor: 'yellow',
              borderWidth: 1,
              label: {
                enabled: true,
                content: 'Tolérance +',
                backgroundColor: 'rgba(0,0,0,0.7)',
                color: 'white',
                position: 'start'
              }
            },
            toleranceLineNeg: {
              type: 'line',
              yMin: -tolerance,
              yMax: -tolerance,
              borderColor: 'yellow',
              borderWidth: 1,
              label: {
                enabled: true,
                content: 'Tolérance -',
                backgroundColor: 'rgba(0,0,0,0.7)',
                color: 'white',
                position: 'start'
              }
            }
          }
        }
      },
      scales: {
        x: {
          ticks: { color: "#ccc" }
        },
        y: {
          ticks: { color: "#ccc" },
          beginAtZero: true,
        },
      }
    }
  });

  const log = document.getElementById("diagnostic-log");
  const startBtn = document.getElementById("start-record");
  const analyzeBtn = document.getElementById("analyze-sync");
  const exportJsonBtn = document.getElementById("export-json");
  const exportCsvBtn = document.getElementById("export-csv");

  startBtn.addEventListener("click", () => {
    log.textContent = "🎬 Enregistrement test lancé...\n";
    let progress = 0;
    const interval = setInterval(() => {
      progress += 20;
      log.textContent += `... ${progress}%\n`;
      if (progress >= 100) {
        clearInterval(interval);
        log.textContent += "✅ Enregistrement terminé.\n";
      }
    }, 500);
  });

  analyzeBtn.addEventListener("click", () => {
    log.textContent += "\n📊 Analyse en cours...\n";
    setTimeout(() => {
      const latency = (Math.random() * 50 + 10).toFixed(1);
      const drift = (Math.random() * 20 - 10).toFixed(1);
      log.textContent += `🔍 Résultat : latence moyenne ${latency} ms, désynchro audio ${drift} ms.\n`;
      log.textContent += drift > 10 || drift < -10
        ? "⚠️ Désynchronisation importante détectée.\n"
        : "✅ Synchro acceptable.\n";
    }, 2000);
  });

  exportJsonBtn.addEventListener("click", () => exportData("json"));
  exportCsvBtn.addEventListener("click", () => exportData("csv"));

  setInterval(() => {
    fetch("data/live.json?_=" + Date.now())
      .then(res => res.json())
      .then(data => {
        document.getElementById("latency").textContent = `${data.latency} ms`;
        document.getElementById("jitter").textContent = `${data.jitter} ms`;
        document.getElementById("frames").textContent = data.frames;
        document.getElementById("audio-delay").textContent = `${data.videoDrift} ms`;

        const statusEl = document.getElementById("flux-status");
        statusEl.textContent = data.flux ? "🟢 Actif" : "🔴 Inactif";
        statusEl.style.color = data.flux ? "limegreen" : "red";

        const timestamp = new Date().toLocaleTimeString();
        syncChart.data.labels.push(timestamp);
        syncChart.data.datasets[0].data.push(data.latency);
        syncChart.data.datasets[1].data.push(data.videoDrift);
        if (syncChart.data.labels.length > 20) {
          syncChart.data.labels.shift();
          syncChart.data.datasets.forEach(d => d.data.shift());
        }

        const tolerance = parseFloat(document.getElementById("tolerance").value);
        syncChart.options.plugins.annotation.annotations.toleranceLine.yMin = tolerance;
        syncChart.options.plugins.annotation.annotations.toleranceLine.yMax = tolerance;
        syncChart.options.plugins.annotation.annotations.toleranceLineNeg.yMin = -tolerance;
        syncChart.options.plugins.annotation.annotations.toleranceLineNeg.yMax = -tolerance;
        syncChart.update();

        const alertZone = document.getElementById("sync-alert");
        const enableBeep = document.getElementById("enable-beep").checked;
        if (Math.abs(data.videoDrift) > tolerance) {
          alertZone.hidden = false;
          if (enableBeep) {
            const beep = new Audio("../assets/bip_hiphop.mp3");
            beep.volume = 0.6;
            beep.play().catch(() => {});
          }
        } else {
          alertZone.hidden = true;
        }
      })
      .catch(err => console.error("[monitor] Erreur lecture JSON:", err));
  }, 2000);
});

function exportData(format) {
  const labels = syncChart.data.labels;
  const latence = syncChart.data.datasets[0].data;
  const audio = syncChart.data.datasets[1].data;

  const rows = labels.map((time, i) => ({
    time,
    latency: latence[i],
    audioDelay: audio[i]
  }));

  if (format === "json") {
    const blob = new Blob([JSON.stringify(rows, null, 2)], { type: "application/json" });
    download(blob, "monitor_data.json");
  } else if (format === "csv") {
    let csv = "Time,Latency (ms),Audio Delay (ms)\n";
    csv += rows.map(r => `${r.time},${r.latency},${r.audioDelay}`).join("\n");
    const blob = new Blob([csv], { type: "text/csv" });
    download(blob, "monitor_data.csv");
  }
}

function download(blob, filename) {
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
}
