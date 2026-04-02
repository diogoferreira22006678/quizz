import { router } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';

type UseLiveReloadOptions = {
    only: string[];
    intervalMs?: number;
    runWhenHidden?: boolean;
};

export function useLiveReload({
    only,
    intervalMs = 1000,
    runWhenHidden = false,
}: UseLiveReloadOptions): void {
    const onlyKey = useMemo(() => only.join('|'), [only]);

    useEffect(() => {
        const onlyProps = onlyKey.split('|').filter(Boolean);

        const reload = () => {
            router.reload({
                only: onlyProps,
            });
        };

        const interval = window.setInterval(() => {
            if (!runWhenHidden && document.visibilityState === 'hidden') {
                return;
            }

            reload();
        }, intervalMs);

        const onFocus = () => reload();
        const onVisibilityChange = () => {
            if (document.visibilityState === 'visible') {
                reload();
            }
        };
        const onOnline = () => reload();

        window.addEventListener('focus', onFocus);
        document.addEventListener('visibilitychange', onVisibilityChange);
        window.addEventListener('online', onOnline);

        return () => {
            window.clearInterval(interval);
            window.removeEventListener('focus', onFocus);
            document.removeEventListener('visibilitychange', onVisibilityChange);
            window.removeEventListener('online', onOnline);
        };
    }, [intervalMs, onlyKey, runWhenHidden]);
}
