type OpenArgs = { url: string; headers: Record<string,string>; onMessage: (d:any)=>void; onError?: (e:Error)=>void; reconnectMinMs:number; reconnectMaxMs:number; };
export function openSse(a: OpenArgs) {
  let es: EventSource | null = null;
  let stopped = false;
  const open = () => {
    const u = a.url + (a.url.includes('?') ? '&' : '?') + 'ts=' + Date.now();
    es = new EventSource(u);
    es.onmessage = e => a.onMessage(e.data);
    es.onerror = () => {
      es?.close();
      if (stopped) return;
      const delay = Math.min(a.reconnectMaxMs, Math.max(a.reconnectMinMs, Math.random() * a.reconnectMaxMs));
      setTimeout(open, delay);
      a.onError?.(new Error('sse_error'));
    };
  };
  open();
  return { close: ()=>{ stopped = true; es?.close(); } };
}