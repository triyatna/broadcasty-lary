export type BackpressureMode = 'drop' | 'queue' | 'slow-start';
export type Transport = 'ws' | 'sse' | 'auto';

export type BroadcastyOptions = {
  baseUrl: string;
  getToken: () => Promise<string> | string;
  tenantId?: string;
  transport?: Transport;
  backpressure?: BackpressureMode;
  reconnectMinMs?: number;
  reconnectMaxMs?: number;
  storage?: Storage;
};

export type PublishOptions = {
  channel: string;
  payload: string | object;
  meta?: Record<string, any>;
  correlationId?: string;
  timeoutMs?: number;
};

export type SubscribeOptions = {
  channel: string;
  fromSequence?: number;
  partition?: number;
  wildcard?: boolean;
  onMessage: (data: any) => void;
  onError?: (e: Error) => void;
};