"""QR code for each rep site -> reps/qr/<subdomain>.png  (run locally: pip install qrcode pillow; python3 tools/make_qr.py)"""
import json, os, qrcode
from qrcode.constants import ERROR_CORRECT_Q
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.makedirs(os.path.join(ROOT, "reps", "qr"), exist_ok=True)
for r in json.load(open(os.path.join(ROOT, "content", "reps.json"), encoding="utf8"))["reps"]:
    q = qrcode.QRCode(error_correction=ERROR_CORRECT_Q, box_size=48, border=4)
    sub = (r.get("subdomains") or [r["slug"]])[0]
    q.add_data(f"https://{sub}.afnanfaeqasa.sa/?ref={r['code']}")
    q.make(fit=True)
    q.make_image(fill_color="#0b3a27", back_color="#ffffff").convert("RGB").save(os.path.join(ROOT, "reps", "qr", f"{sub}.png"), optimize=True)
    print(sub)
