"use client";

import { useState } from "react";
import Link from "next/link";
import {
  Activity,
  Send,
  CheckCircle2,
  AlertTriangle,
  Layers,
  ShieldCheck,
  Loader2,
  ExternalLink,
  Sliders,
} from "lucide-react";

interface AdminTrackingClientProps {
  config: {
    pixelId: string;
    hasAccessToken: boolean;
    testEventCode: string;
    isEnabled: boolean;
  };
  recentEvents: any[];
}

export function AdminTrackingClient({ config, recentEvents }: AdminTrackingClientProps) {
  const [testing, setTesting] = useState(false);
  const [testResult, setTestResult] = useState<any | null>(null);
  const [testError, setTestError] = useState<string | null>(null);

  async function handleSendTestEvent() {
    setTesting(true);
    setTestResult(null);
    setTestError(null);

    try {
      const res = await fetch("/api/admin/tracking/test", { method: "POST" });
      const data = await res.json();
      if (!res.ok) {
        setTestError(data.error || "Failed to dispatch test event.");
      } else {
        setTestResult(data);
      }
    } catch {
      setTestError("Network connection error.");
    } finally {
      setTesting(false);
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Meta Pixel & CAPI Diagnostics</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Real-time server-side tracking health check, deduplication validation, and live Meta test dispatch.
          </p>
        </div>
        <Link
          href="/admin/settings"
          className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-2 transition border border-slate-700 self-start sm:self-auto"
        >
          <Sliders className="w-4 h-4 text-cyan-400" /> Edit Tracking Keys
        </Link>
      </div>

      {/* Configuration Status Strip */}
      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Meta Pixel ID
          </span>
          <div className="text-sm font-mono font-bold text-white truncate">
            {config.pixelId ? config.pixelId : "Not Configured"}
          </div>
          <span className={`inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded ${config.pixelId ? "bg-emerald-950 text-emerald-300" : "bg-red-950 text-red-300"}`}>
            {config.pixelId ? "Active" : "Missing"}
          </span>
        </div>

        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Conversions API (CAPI)
          </span>
          <div className="text-sm font-bold text-white">
            {config.hasAccessToken ? "Access Token Ready" : "Missing Token"}
          </div>
          <span className={`inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded ${config.hasAccessToken ? "bg-emerald-950 text-emerald-300" : "bg-red-950 text-red-300"}`}>
            {config.hasAccessToken ? "Server Dispatch Enabled" : "Disabled"}
          </span>
        </div>

        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Event Deduplication
          </span>
          <div className="text-sm font-bold text-white">Deterministic</div>
          <span className="inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-950 text-[#00C8F4] border border-cyan-500/30">
            Matching event_id
          </span>
        </div>

        <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-5">
          <span className="text-xs font-semibold uppercase text-slate-400 block mb-1">
            Test Event Code
          </span>
          <div className="text-sm font-mono font-bold text-white truncate">
            {config.testEventCode || "None (Production)"}
          </div>
          <span className="inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded bg-purple-950 text-purple-300">
            {config.testEventCode ? "Test Mode" : "Live Mode"}
          </span>
        </div>
      </div>

      {/* Live Dispatch Test Card */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl p-6 shadow-xl space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
          <div>
            <h2 className="text-base font-bold text-white flex items-center gap-2">
              <Activity className="w-5 h-5 text-cyan-400" />
              Live Server Event Dispatch Test
            </h2>
            <p className="text-xs text-slate-400 mt-0.5">
              Dispatches a live mock `Purchase` event directly to Meta Graph API v21.0 to verify server authentication and response codes.
            </p>
          </div>

          <button
            onClick={handleSendTestEvent}
            disabled={testing || !config.pixelId || !config.hasAccessToken}
            className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00C8F4] to-[#1677FF] hover:from-[#38d4f7] hover:to-[#2582ff] text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition disabled:opacity-50 self-start sm:self-auto"
          >
            {testing ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
            Dispatch Test CAPI Event
          </button>
        </div>

        {testError && (
          <div className="p-4 rounded-xl bg-red-950/70 border border-red-500/50 text-red-300 text-xs flex items-start gap-2">
            <AlertTriangle className="w-4 h-4 text-red-400 shrink-0 mt-0.5" />
            <div>
              <span className="font-bold block">Meta API Error:</span>
              <span>{testError}</span>
            </div>
          </div>
        )}

        {testResult && (
          <div className="p-4 rounded-xl bg-emerald-950/70 border border-emerald-500/50 text-emerald-300 text-xs space-y-2">
            <div className="flex items-center gap-2 font-bold text-sm text-white">
              <CheckCircle2 className="w-4 h-4 text-emerald-400" />
              <span>CAPI Test Event Processed by Meta!</span>
            </div>
            <p className="text-emerald-300">{testResult.message}</p>
            <div className="bg-[#05090F] p-3 rounded-lg font-mono text-[11px] text-slate-300 overflow-x-auto">
              <div>Event ID: {testResult.eventId}</div>
              <div>Pixel ID: {testResult.pixelId}</div>
              <div>Response: {JSON.stringify(testResult.metaResponse || { events_received: 1 })}</div>
            </div>
          </div>
        )}
      </div>

      {/* Recent Tracking Stream */}
      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        <div className="p-5 border-b border-slate-800 flex items-center justify-between">
          <div>
            <h2 className="text-base font-bold text-white">Recent CAPI Tracking Stream</h2>
            <p className="text-xs text-slate-400">Events forwarded server-side for attribution resilience.</p>
          </div>
        </div>

        {recentEvents.length === 0 ? (
          <div className="p-8 text-center text-slate-400 text-xs">
            No tracked checkout events recorded yet. Completed purchases will log here with verification status.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                  <th className="py-3 px-4">Event Name</th>
                  <th className="py-3 px-4">Channel</th>
                  <th className="py-3 px-4">Event ID (Deduplication)</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4">Timestamp</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 text-slate-300 font-mono text-[11px]">
                {recentEvents.map((ev) => (
                  <tr key={ev.id} className="hover:bg-slate-900/40 transition">
                    <td className="py-3 px-4 font-bold text-white">
                      {ev.eventName}
                    </td>
                    <td className="py-3 px-4 text-cyan-300">
                      {ev.channel}
                    </td>
                    <td className="py-3 px-4 text-slate-400 truncate max-w-xs">
                      {ev.eventId}
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold ${
                          ev.status === "SUCCESS"
                            ? "bg-emerald-950 text-emerald-300"
                            : "bg-amber-950 text-amber-300"
                        }`}
                      >
                        {ev.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-slate-400">
                      {new Date(ev.createdAt).toLocaleTimeString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
