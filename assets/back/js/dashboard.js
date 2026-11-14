const CACHE_KEY = 'dashboard_stats_cache';
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

export class Dashboard {
  constructor() {
    const container = document.getElementById('dashboard-stats');
    if (!container) return;

    // Load cached data immediately
    const cached = this.loadCache();
    if (cached) {
      this.renderStats(container, cached);
    }

    // Fetch fresh data if cache expired
    if (!cached || Date.now() - cached.timestamp > CACHE_TTL) {
      this.fetchStats(container);
    }

    // Refresh every 5 minutes
    setInterval(() => this.fetchStats(container), CACHE_TTL);
  }

  loadCache() {
    try {
      const raw = localStorage.getItem(CACHE_KEY);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (Date.now() - data.timestamp > CACHE_TTL) return null;
      return data;
    } catch (e) {
      return null;
    }
  }

  saveCache(stats) {
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify({ stats, timestamp: Date.now() }));
    } catch (e) {
      // ignore
    }
  }

  renderStats(container, cached) {
    const allStats = cached.stats || cached;
    (allStats || []).forEach((stat) => {
      const card = container.querySelector(`[data-stat="${stat.label}"]`);
      if (!card) return;
      const countEl = card.querySelector('.stat-count');
      if (countEl) countEl.textContent = Number(stat.count).toLocaleString('fr-FR');
      const link = card.querySelector('a.stat-link, .stat-index-link');
      if (link && stat.url) link.href = stat.url;
      const trashEl = card.querySelector('.stat-trash-link');
      const trashCountEl = card.querySelector('.stat-trash-count');
      if (trashCountEl && stat.trashCount > 0) {
        trashCountEl.textContent = stat.trashCount;
        if (trashEl) trashEl.style.display = '';
        if (trashEl && stat.trashUrl) trashEl.href = stat.trashUrl;
      }
    });
  }

  fetchStats(container) {
    const cards = container.querySelectorAll('[data-stat]');
    const stats = [];
    let pending = cards.length;
    if (pending === 0) return;

    cards.forEach((card) => {
      const statUrl = card.getAttribute('data-stat-url');
      if (!statUrl) {
        pending--;
        if (pending === 0) this.saveCache(stats);
        return;
      }
      fetch(statUrl, { credentials: 'same-origin' })
        .then((r) => r.json())
        .then((stat) => {
          stats.push(stat);
          const countEl = card.querySelector('.stat-count');
          if (countEl) countEl.textContent = Number(stat.count).toLocaleString('fr-FR');
          const link = card.querySelector('a.stat-link, .stat-index-link');
          if (link && stat.url) link.href = stat.url;
          const trashEl = card.querySelector('.stat-trash-link');
          const trashCountEl = card.querySelector('.stat-trash-count');
          if (trashCountEl && stat.trashCount > 0) {
            trashCountEl.textContent = stat.trashCount;
            if (trashEl) trashEl.style.display = '';
            if (trashEl && stat.trashUrl) trashEl.href = stat.trashUrl;
          }
          pending--;
          if (pending === 0) this.saveCache(stats);
        })
        .catch(() => {
          pending--;
          if (pending === 0) this.saveCache(stats);
        });
    });
  }
}
