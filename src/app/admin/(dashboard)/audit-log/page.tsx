import { prisma } from "@/lib/prisma";
import { History, Shield, User, Clock } from "lucide-react";

export const dynamic = "force-dynamic";

export default async function AdminAuditLogPage() {
  const logs = await prisma.auditLog.findMany({
    include: {
      user: {
        select: { name: true, email: true, role: true },
      },
    },
    orderBy: { createdAt: "desc" },
    take: 100,
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-white">Security & Audit Trail</h1>
          <p className="text-xs sm:text-sm text-slate-400 mt-1">
            Immutable log of staff actions, credential replacements, settings updates, and inventory changes.
          </p>
        </div>
      </div>

      <div className="bg-[#0C111B] border border-cyan-500/20 rounded-2xl shadow-xl overflow-hidden">
        {logs.length === 0 ? (
          <div className="p-12 text-center text-slate-400 text-xs">
            No audit events recorded yet.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-[#070B12] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                  <th className="py-3.5 px-4">Action</th>
                  <th className="py-3.5 px-4">Entity</th>
                  <th className="py-3.5 px-4">Staff Member</th>
                  <th className="py-3.5 px-4">Details</th>
                  <th className="py-3.5 px-4">Timestamp</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 text-slate-300">
                {logs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-900/40 transition">
                    <td className="py-3.5 px-4">
                      <span className="font-mono font-bold text-cyan-300 px-2 py-0.5 rounded bg-cyan-950/60 border border-cyan-500/30 text-[11px]">
                        {log.action}
                      </span>
                    </td>
                    <td className="py-3.5 px-4 font-semibold text-white">
                      {log.entityType}
                    </td>
                    <td className="py-3.5 px-4">
                      <div className="font-medium text-white">{log.user?.name || "System"}</div>
                      <div className="text-[10px] text-slate-500">{log.user?.role}</div>
                    </td>
                    <td className="py-3.5 px-4 text-slate-400 max-w-sm truncate font-mono text-[11px]">
                      {log.details || "—"}
                    </td>
                    <td className="py-3.5 px-4 text-slate-400 whitespace-nowrap">
                      {new Date(log.createdAt).toLocaleDateString("en-IN", {
                        day: "numeric",
                        month: "short",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
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
