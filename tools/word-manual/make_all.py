# -*- coding: utf-8 -*-
"""Produce the four Word manuals: admin TH/EN and customer TH/EN."""
import os, sys, io
sys.stdout.reconfigure(encoding="utf-8")

import parse_blade as pb
from build_docs import Builder, ROOT, OUT, TH_FONT

ADMIN_BLADE = os.path.join(ROOT, "resources/views/admin/manual/index.blade.php")
GUIDE_BLADE = os.path.join(ROOT, "resources/views/customer/guide.blade.php")
ADMIN_IMG = os.path.join(ROOT, "public/docs/manual/img")
GUIDE_IMG = os.path.join(ROOT, "public/docs/guide/img")

META = {
    ("admin", "th"): dict(
        title="คู่มือการใช้งานระบบหลังบ้าน",
        subtitle="ระบบจัดการสตูดิโอพิลาทิส — ฉบับผู้ดูแลระบบ",
        audience="สำหรับเจ้าของระบบ ผู้จัดการ และพนักงานหน้าร้าน",
        file="คู่มือแอดมิน-TH.docx"),
    ("admin", "en"): dict(
        title="Administrator Manual",
        subtitle="Pilates Studio Management System — Back Office",
        audience="For owners, managers and front-desk staff",
        file="Admin-Manual-EN.docx"),
    ("customer", "th"): dict(
        title="คู่มือการใช้งานสำหรับลูกค้า",
        subtitle="จองคลาส ซื้อแพ็กเกจ และจัดการบัญชีของคุณ",
        audience="สำหรับสมาชิกของสตูดิโอ",
        file="คู่มือลูกค้า-TH.docx"),
    ("customer", "en"): dict(
        title="Member Guide",
        subtitle="Book classes, buy packages and manage your account",
        audience="For studio members",
        file="Member-Guide-EN.docx"),
}


def build(kind, lang):
    m = META[(kind, lang)]
    if kind == "admin":
        blocks = pb.parse(ADMIN_BLADE, lang, chapter_cls=["man-h"])
        img_dir, img_pat, width = ADMIN_IMG, "%s.png", 15.6
    else:
        blocks = pb.parse(GUIDE_BLADE, lang, chapter_cls=["g-sec-title"])
        img_dir, img_pat, width = GUIDE_IMG, "%s-" + lang + ".png", 6.4
    b = Builder(lang, TH_FONT, img_dir, img_pat, width,
                m["title"], m["subtitle"], m["audience"])
    b.render(blocks)
    path = b.save(os.path.join(OUT, m["file"]))
    print("  %-28s chapters=%-3d figures=%-3d %s"
          % (kind + "/" + lang, b.chapter_no, b.fig_no,
             os.path.basename(path)))
    return path


if __name__ == "__main__":
    print("Building Word manuals ->", OUT)
    # the admin manual exists in Thai only in the source templates
    blocked = []
    for kind, lang in (("admin", "th"), ("customer", "th"), ("customer", "en")):
        try:
            build(kind, lang)
        except PermissionError as e:
            blocked.append(str(e))
            print("  %-28s ! ถูกล็อก (เปิดอยู่ใน Word)" % (kind + "/" + lang))
    if blocked:
        print("\nไฟล์ต่อไปนี้เขียนไม่ได้ ให้ปิดใน Word แล้วรันสคริปต์ใหม่:")
        for b in blocked:
            print(" ", b.splitlines()[-1].strip())
    print("\nDone.")
    for f in sorted(os.listdir(OUT)):
        p = os.path.join(OUT, f)
        print("  %8.1f KB  %s" % (os.path.getsize(p) / 1024, f))
