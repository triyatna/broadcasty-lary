type OpenArgs = { url: string; headers: Record<string,string>; onMessage: (d:any)=>void; onError?: (e:Error)=>void; reconnectMinMs:number; reconnectMaxMs:number; };
export function openWs(a: OpenArgs) {
  let ws: WebSocket | null = null;
  let stopped = false;
  const open = () => {
    const u = a.url.replace(/^http/, 'ws');
    ws = new WebSocket(u);
    ws.onmessage = e => a.onMessage(e.data);
    ws.onerror = () => a.onError?.(new Error('ws_error'));
    ws.onclose = () => {
      if (stopped) return;
      const delay = Math.min(a.reconnectMaxMs, Math.max(a.reconnectMinMs, Math.random() * a.reconnectMaxMs));
      setTimeout(open, delay);
    };
  };
  open();
  return { close: ()=>{ stopped = true; ws?.close(); } };
}