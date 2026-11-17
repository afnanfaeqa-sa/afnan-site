"""Make a QR code PNG for each rep in content/reps.json -> static/qr/<slug>.png
   Run locally after adding a rep:  python3 tools/make_qr.py   (needs: pip install qrcode pillow)"""
import json, os, qrcode
from qrcode.constants import ERROR_CORRECT_Q
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SITE = "https://afnanfaeqasa.sa"
for r in json.load(open(os.path.join(ROOT, "content", "reps.json"), encoding="utf8"))["reps"]:
    q = qrcode.QRCode(error_correction=ERROR_CORRECT_Q, box_size=24, border=4)
    q.add_data(f"{SITE}/{r['slug']}?ref={r['code']}")
    q.make(fit=True)
    q.make_image(fill_color="#0b3a27", back_color="#ffffff").convert("RGB").save(os.path.join(ROOT, "static", "qr", f"{r['slug']}.png"), optimize=True)
    print(r["slug"], q.version)
