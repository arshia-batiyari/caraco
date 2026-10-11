const root = document.querySelector("[data-route-map]");
const base = root?.getAttribute("data-tile-base");
if (root && base) {
    let points = [];
    try {
        points = JSON.parse(root.getAttribute("data-points") || "[]");
    } catch {
        points = [];
    }

    if (points.length > 0) {
        const lon2tile = (lon, zoom) => Math.floor(((lon + 180) / 360) * 2 ** zoom);
        const lat2tile = (lat, zoom) => {
            const radians = (lat * Math.PI) / 180;
            return Math.floor(
                ((1 - Math.log(Math.tan(radians) + 1 / Math.cos(radians)) / Math.PI) / 2) * 2 ** zoom,
            );
        };

        let zoom = 10;
        const lats = points.map((point) => point.lat);
        const lngs = points.map((point) => point.lng);
        while (zoom > 2) {
            const xs = lngs.map((lng) => lon2tile(lng, zoom));
            const ys = lats.map((lat) => lat2tile(lat, zoom));
            if (Math.max(...xs) - Math.min(...xs) <= 2 && Math.max(...ys) - Math.min(...ys) <= 2) {
                break;
            }
            zoom -= 1;
        }

        const xs = lngs.map((lng) => lon2tile(lng, zoom));
        const ys = lats.map((lat) => lat2tile(lat, zoom));
        const minX = Math.min(...xs);
        const minY = Math.min(...ys);
        const mosaic = document.createElement("div");
        mosaic.className = "k-tile-mosaic";
        for (let x = minX; x <= Math.max(...xs); x += 1) {
            for (let y = minY; y <= Math.max(...ys); y += 1) {
                const image = document.createElement("img");
                image.alt = "";
                image.src = base.replace("__z", String(zoom)).replace("__x", String(x)).replace("__y", String(y));
                image.addEventListener("error", () => mosaic.remove());
                mosaic.append(image);
            }
        }
        root.prepend(mosaic);
    }
}
