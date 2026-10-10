# Hachland PWA v3

The Chrome DevTools screenshot showed the actual blocker: all manifest icon URLs under `/icons/` returned failed-to-load, so Chrome reported no supplied icon >=144px and did not consider the app installable.

This version puts the PWA icons at the public root (`/pwa-icon-*.png`) and updates every manifest to use those root URLs. The icons are generated from the existing Hachland favicon/logo mark.

After upload, verify these URLs directly:
- `/pwa-icon-192.png`
- `/pwa-icon-512.png`
- `/pwa-icon-maskable-192.png`
- `/pwa-icon-maskable-512.png`
- `/manifest.json`
- `/sw.js`

Then Chrome DevTools > Application > Manifest should no longer show the icon failed-to-load errors. Clear the old service worker/site data once before retesting if Chrome has cached the previous manifest.
