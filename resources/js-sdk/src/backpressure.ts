export function applyBackpressure(mode: 'drop'|'queue'|'slow-start', next: (d:any)=>void, data: any) {
  if (mode === 'drop') { try { next(data); } catch {} return; }
  if (mode === 'queue') { queueMicrotask(()=>next(data)); return; }
  if (mode === 'slow-start') { setTimeout(()=>next(data), 25); return; }
}