export default function ordersMonitor(refreshMs = 5000) {
    return {
        autoRefresh: true,
        lastUpdated: null,
        timer: null,

        init() {
            this.lastUpdated = new Date();
            this.timer = setInterval(() => {
                if (this.autoRefresh) {
                    this.refresh();
                }
            }, refreshMs);
        },

        destroy() {
            if (this.timer) {
                clearInterval(this.timer);
            }
        },

        async refresh() {
            if (document.hidden) {
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('partial', '1');

            try {
                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const target = document.getElementById('orders-live');

                if (target) {
                    target.innerHTML = html;
                    this.lastUpdated = new Date();
                }
            } catch (error) {
                // Biarkan data terakhir tetap tampil saat koneksi terputus.
            }
        },
    };
}
