import { PublishOptions, BroadcastyOptions, SubscribeOptions } from './types';
import { openSse } from './transport-sse';
import { openWs } from './transport-ws';
import { OfflineQueue } from './offline';
import { applyBackpressure } from './backpressure';

export class BroadcastyClient {
  private opts: BroadcastyOptions;
  private offline: OfflineQueue;

  constructor(opts: BroadcastyOptions) {
    this.opts = { transport: 'auto', backpressure: 'queue', reconnectMinMs: 500, reconnectMaxMs: 60000, storage: localStorage, ...opts };
    this.offline = new OfflineQueue(this.opts.storage!, 'bcy-offline');
  }

  async publish(o: PublishOptions) {
    const token = await this.opts.getToken();
    const body = {
      channel: o.channel,
      payload: typeof o.payload === 'string' ? o.payload : JSON.stringify(o.payload),
      meta: o.meta ?? {},
      correlationId: o.correlationId
    };
    const ctrl = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), o.timeoutMs ?? 10000);
    try {
      const r = await fetch(this.opts.baseUrl + '/broadcasty/publish', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
        signal: ctrl.signal
      });
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      return await r.json();
    } catch (e) {
      this.offline.enqueue({ t: Date.now(), body });
      throw e;
    } finally {
      clearTimeout(timer);
    }
  }

  async subscribe(o: SubscribeOptions) {
    const token = await this.opts.getToken();
    const url = new URL(this.opts.baseUrl + '/broadcasty/sse');
    url.searchParams.set('channel', o.channel);
    if (o.fromSequence) url.searchParams.set('from', String(o.fromSequence));
    if (o.partition) url.searchParams.set('partition', String(o.partition));
    const useWs = this.opts.transport === 'ws' || (this.opts.transport === 'auto' && 'WebSocket' in globalThis);
    const open = useWs ? openWs : openSse;
    return open({ url: url.toString(), headers: { Authorization: `Bearer ${token}` }, onMessage: data => applyBackpressure(this.opts.backpressure!, o.onMessage, data), onError: o.onError, reconnectMinMs: this.opts.reconnectMinMs!, reconnectMaxMs: this.opts.reconnectMaxMs! });
  }

  async flushOffline() {
    const token = await this.opts.getToken();
    await this.offline.flush(async (item) => {
      await fetch(this.opts.baseUrl + '/broadcasty/publish', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
        body: JSON.stringify(item.body)
      });
    });
  }
}

export default BroadcastyClient;