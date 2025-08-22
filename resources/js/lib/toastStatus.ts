// utils/toastStatus.ts
import { toast } from 'vue-sonner'

type ToastType = 'success' | 'warning' | 'info' | 'danger'

const gradients: Record<ToastType, string> = {
  success: 'linear-gradient(to right, #4ade80, #22c55e)', // green-400 → green-500
  warning: 'linear-gradient(to right, #fbbf24, #f59e0b)', // amber-400 → amber-500
  info: 'linear-gradient(to right, #60a5fa, #3b82f6)',    // blue-400 → blue-500
  danger: 'linear-gradient(to right, #f87171, #dc2626)',  // red-400 → red-600
}

export function toastStatus(
  type: ToastType,
  message: string,
  description?: string,
  duration?: number,
  actionLabel?: string,
  actionCallback?: () => void
) {
  toast(message, {
    description, 
    duration: duration,
    style: {
      background: gradients[type],
      color: 'white',
    },
    action: actionLabel && actionCallback
      ? {
          label: actionLabel,
          onClick: actionCallback,
        }
      : undefined,
    position: 'top-right',
  })
}
