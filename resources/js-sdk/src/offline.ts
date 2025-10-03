export class OfflineQueue {
  constructor(private storage: Storage, private key: string) {}
  enqueue(item: any) {
    const q = this.load(); q.push(item); this.storage.setItem(this.key, JSON.stringify(q));
  }
  async flush(fn: (item:any)=>Promise<void>) {
    const q = this.load(); const remaining: any[] = [];
    for (const it of q) { try { await fn(it); } catch { remaining.push(it); } }
    this.storage.setItem(this.key, JSON.stringify(remaining));
  }
  private load(): any[] { try { return JSON.parse(this.storage.getItem(this.key) ?? '[]'); } catch { return []; } }
}