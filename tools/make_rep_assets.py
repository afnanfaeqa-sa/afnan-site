"""WebP assets for the rep sites (run locally after changing the logo or chairman photo; needs Pillow)."""
import os
from PIL import Image
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
for src, out, size in [("logo.png", "logo.webp", 264), ("mark.png", "mark.webp", 96), ("qutaiba-alkhaled.jpg", "chairman.webp", 240)]:
    im = Image.open(os.path.join(ROOT, "static", "img", src)); im.thumbnail((size, size))
    im.save(os.path.join(ROOT, "reps", "assets", out), "WEBP", quality=82, method=6)
    print(out, os.path.getsize(os.path.join(ROOT, "reps", "assets", out)))
