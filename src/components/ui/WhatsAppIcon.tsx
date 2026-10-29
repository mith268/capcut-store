import React from "react";

interface WhatsAppIconProps extends React.SVGProps<SVGSVGElement> {
  className?: string;
  size?: number;
}

/**
 * Authentic official WhatsApp brand icon SVG.
 * Renders the recognizable speech bubble + telephone handset mark.
 */
export function WhatsAppIcon({
  className = "w-5 h-5",
  size = 20,
  ...props
}: WhatsAppIconProps) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      width={size}
      height={size}
      fill="currentColor"
      aria-hidden="true"
      className={className}
      {...props}
    >
      <path d="M12.01 2.002c-5.516 0-9.99 4.474-9.99 9.99 0 1.763.459 3.486 1.332 5.006L2 22l5.132-1.346a9.948 9.948 0 004.878 1.268h.005c5.515 0 9.988-4.474 9.988-9.99 0-2.67-1.04-5.18-2.927-7.067A9.92 9.92 0 0012.01 2.002zm5.82 14.152c-.24.675-1.393 1.288-1.944 1.37-.504.075-1.127.108-1.815-.113-.418-.134-.954-.312-1.637-.607-2.88-1.246-4.75-4.148-4.894-4.34-.143-.191-1.171-1.558-1.171-2.97 0-1.413.74-2.108 1.002-2.394.263-.287.574-.359.765-.359.191 0 .383.002.55.01.178.008.416-.067.65.495.24.574.814 1.99.885 2.134.072.143.12.31.024.498-.096.188-.144.305-.287.473-.144.167-.303.373-.433.5-.144.144-.294.3-.127.587.168.286.745 1.228 1.6 1.989 1.101.98 2.029 1.283 2.316 1.426.287.143.454.12.622-.072.167-.191.717-.837.909-1.124.191-.286.383-.239.646-.143.263.095 1.674.789 1.961.932.287.144.478.215.55.335.072.12.072.694-.168 1.369z" />
    </svg>
  );
}
