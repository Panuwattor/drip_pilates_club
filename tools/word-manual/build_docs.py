# -*- coding: utf-8 -*-
"""Build the admin manual and customer guide as Word documents."""
import os, re, sys, datetime
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_BREAK
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.section import WD_SECTION
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

import parse_blade as pb

ROOT = r"c:/xampp/htdocs/drip_pilates_club"
OUT = os.path.join(ROOT, "public", "docs", "word")

TH_FONT = "Leelawadee UI"     # ships with Windows, covers Thai + Latin
EN_FONT = "Segoe UI"

ACCENT = RGBColor(0x7A, 0x5A, 0xF8)
INK = RGBColor(0x24, 0x1F, 0x3A)
SOFT = RGBColor(0x6B, 0x66, 0x80)
FAINT = RGBColor(0x8E, 0x8A, 0x9E)

NOTE_STYLE = {
    "tip":    ("EEF2FF", "3730A3", "\u2691"),
    "warn":   ("FFF7ED", "9A3412", "\u26A0"),
    "danger": ("FEF2F2", "991B1B", "\u26D4"),
    "ok":     ("F0FDF4", "166534", "\u2714"),
    "info":   ("F5F5F7", "3F3F46", "\u2139"),
}

LBL = {
    "th": {"note_tip": "เคล็ดลับ", "note_warn": "ข้อควรระวัง",
           "note_danger": "คำเตือน", "note_ok": "ทำได้", "note_info": "หมายเหตุ",
           "menu": "เมนู", "step": "ขั้นที่", "toc": "สารบัญ",
           "fig": "ภาพ", "page": "หน้า"},
    "en": {"note_tip": "Tip", "note_warn": "Caution",
           "note_danger": "Warning", "note_ok": "Good to know", "note_info": "Note",
           "menu": "Menu", "step": "Step", "toc": "Contents",
           "fig": "Figure", "page": "Page"},
}


# ── low-level docx helpers ───────────────────────────────────────────────────

def set_cell_bg(cell, hexcolor):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:fill"), hexcolor)
    tcPr.append(shd)


def set_cell_border(cell, **kw):
    tcPr = cell._tc.get_or_add_tcPr()
    borders = OxmlElement("w:tcBorders")
    for edge in ("top", "left", "bottom", "right"):
        spec = kw.get(edge)
        el = OxmlElement("w:" + edge)
        if spec is None:
            el.set(qn("w:val"), "nil")
        else:
            el.set(qn("w:val"), "single")
            el.set(qn("w:sz"), str(spec.get("sz", 6)))
            el.set(qn("w:color"), spec.get("color", "D8D5E0"))
        borders.append(el)
    tcPr.append(borders)


def no_space(par, before=0, after=0):
    par.paragraph_format.space_before = Pt(before)
    par.paragraph_format.space_after = Pt(after)


def field(par, instr):
    """Insert a Word field code (used for TOC and PAGE numbers)."""
    r = par.add_run()
    fc = OxmlElement("w:fldChar"); fc.set(qn("w:fldCharType"), "begin")
    it = OxmlElement("w:instrText"); it.set(qn("xml:space"), "preserve")
    it.text = instr
    fs = OxmlElement("w:fldChar"); fs.set(qn("w:fldCharType"), "separate")
    fe = OxmlElement("w:fldChar"); fe.set(qn("w:fldCharType"), "end")
    r._r.append(fc); r._r.append(it); r._r.append(fs); r._r.append(fe)


def style_fonts(doc, font):
    for name in ("Normal", "Heading 1", "Heading 2", "Heading 3", "Title",
                 "Caption", "List Bullet", "List Number"):
        try:
            st = doc.styles[name]
        except KeyError:
            continue
        st.font.name = font
        rpr = st.element.get_or_add_rPr()
        rf = rpr.find(qn("w:rFonts"))
        if rf is None:
            rf = OxmlElement("w:rFonts"); rpr.append(rf)
        for a in ("w:ascii", "w:hAnsi", "w:cs", "w:eastAsia"):
            rf.set(qn(a), font)


def runfont(run, font, size=None, bold=None, color=None, italic=None):
    run.font.name = font
    rpr = run._r.get_or_add_rPr()
    rf = rpr.find(qn("w:rFonts"))
    if rf is None:
        rf = OxmlElement("w:rFonts"); rpr.append(rf)
    for a in ("w:ascii", "w:hAnsi", "w:cs", "w:eastAsia"):
        rf.set(qn(a), font)
    if size is not None:
        run.font.size = Pt(size)
        sz = OxmlElement("w:szCs"); sz.set(qn("w:val"), str(int(size * 2)))
        rpr.append(sz)
    if bold is not None:
        run.font.bold = bold
        b = OxmlElement("w:bCs"); b.set(qn("w:val"), "1" if bold else "0")
        rpr.append(b)
    if italic is not None:
        run.font.italic = italic
    if color is not None:
        run.font.color.rgb = color
    return run


# ── document builder ─────────────────────────────────────────────────────────

class Builder:
    def __init__(self, lang, font, img_dir, img_pat, img_width_cm, title,
                 subtitle, audience):
        self.lang = lang
        self.L = LBL[lang]
        self.font = font
        self.img_dir = img_dir
        self.img_pat = img_pat
        self.img_w = img_width_cm
        self.doc = Document()
        self.fig_no = 0
        self.chapter_no = 0
        self.toc = []            # (no, title, bookmark, subtitle)
        self._bm_id = 1000
        self._setup(title, subtitle, audience)

    def _bookmark(self, par, name):
        start = OxmlElement("w:bookmarkStart")
        start.set(qn("w:id"), str(self._bm_id))
        start.set(qn("w:name"), name)
        end = OxmlElement("w:bookmarkEnd")
        end.set(qn("w:id"), str(self._bm_id))
        par._p.insert(0, start)
        par._p.append(end)
        self._bm_id += 1

    def _setup(self, title, subtitle, audience):
        doc = self.doc
        style_fonts(doc, self.font)
        n = doc.styles["Normal"]
        n.font.size = Pt(10.5)
        n.paragraph_format.space_after = Pt(6)
        n.paragraph_format.line_spacing = 1.25
        for s in doc.sections:
            s.page_width, s.page_height = Cm(21.0), Cm(29.7)
            s.left_margin = s.right_margin = Cm(2.2)
            s.top_margin = Cm(2.0)
            s.bottom_margin = Cm(2.0)
        self.cover(title, subtitle, audience)
        self.toc_page()
        self.footer()

    # -- cover / toc / footer
    def cover(self, title, subtitle, audience):
        d = self.doc
        for _ in range(4):
            no_space(d.add_paragraph())
        p = d.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        runfont(p.add_run("DRIP PILATES CLUB"), self.font, 13, True, ACCENT)
        no_space(p, after=2)

        p = d.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        runfont(p.add_run(title), self.font, 30, True, INK)
        no_space(p, before=10, after=6)

        p = d.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        runfont(p.add_run(subtitle), self.font, 12, False, SOFT)
        no_space(p, after=26)

        p = d.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        runfont(p.add_run(audience), self.font, 10.5, False, FAINT)
        no_space(p, after=4)

        stamp = datetime.date.today().strftime("%d/%m/%Y")
        p = d.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        runfont(p.add_run(stamp), self.font, 9.5, False, FAINT)
        d.add_page_break()

    def toc_page(self):
        """Reserve the contents page; real entries are written in finish()."""
        d = self.doc
        p = d.add_paragraph()
        runfont(p.add_run(self.L["toc"]), self.font, 18, True, INK)
        no_space(p, after=4)
        self._toc_anchor = d.add_paragraph()   # entries get inserted before this
        no_space(self._toc_anchor, after=0)
        d.add_page_break()

    def toc_entry(self, no, title, bookmark, sub=None):
        """One contents line, inserted above the reserved anchor."""
        p = self._toc_anchor.insert_paragraph_before()
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(5)
        p.paragraph_format.left_indent = Cm(0.9)
        p.paragraph_format.first_line_indent = Cm(-0.9)
        # internal hyperlink to the chapter bookmark
        link = OxmlElement("w:hyperlink")
        link.set(qn("w:anchor"), bookmark)
        r_no = OxmlElement("w:r"); link.append(r_no)
        r_ti = OxmlElement("w:r"); link.append(r_ti)
        p._p.append(link)
        from docx.text.run import Run
        run_no = Run(r_no, p)
        run_ti = Run(r_ti, p)
        runfont(run_no, self.font, 11, True, ACCENT).text = "%d." % no
        runfont(run_ti, self.font, 11, False, INK).text = "  " + title
        if sub:
            runfont(p.add_run("   " + sub), self.font, 9, False, FAINT)
        return p

    def footer(self):
        for s in self.doc.sections:
            f = s.footer.paragraphs[0]
            f.alignment = WD_ALIGN_PARAGRAPH.CENTER
            runfont(f.add_run(""), self.font, 9, False, FAINT)
            field(f, "PAGE")
            for r in f.runs:
                runfont(r, self.font, 9, False, FAINT)

    # -- blocks
    def runs_into(self, par, runs, size, color=INK, base_bold=False):
        for text, bold in runs:
            runfont(par.add_run(text), self.font, size,
                    bool(bold) or base_bold, color)

    def chapter(self, runs, sub=None, where=None):
        self.chapter_no += 1
        d = self.doc
        if self.chapter_no > 1:
            d.add_page_break()
        title = "".join(t for t, _ in runs).strip()
        h = d.add_heading(level=1)
        h.paragraph_format.space_before = Pt(0)
        h.paragraph_format.space_after = Pt(2)
        bookmark = "ch%d" % self.chapter_no
        self._bookmark(h, bookmark)
        runfont(h.add_run("%d. %s" % (self.chapter_no, title)),
                self.font, 19, True, INK)
        self.toc.append((self.chapter_no, title, bookmark,
                         "".join(t for t, _ in sub).strip() if sub else None))
        if sub:
            p = d.add_paragraph()
            runfont(p.add_run("".join(t for t, _ in sub)), self.font, 10.5,
                    False, SOFT)
            no_space(p, after=3)
        if where:
            p = d.add_paragraph()
            runfont(p.add_run("%s: %s" % (self.L["menu"],
                                          "".join(t for t, _ in where))),
                    self.font, 9, False, FAINT)
            no_space(p, after=8)
        self._rule()

    def _rule(self):
        p = self.doc.add_paragraph()
        no_space(p, after=8)
        pPr = p._p.get_or_add_pPr()
        bd = OxmlElement("w:pBdr")
        b = OxmlElement("w:bottom")
        b.set(qn("w:val"), "single"); b.set(qn("w:sz"), "6")
        b.set(qn("w:color"), "E3E0EB"); b.set(qn("w:space"), "1")
        bd.append(b); pPr.append(bd)

    def h3(self, runs):
        h = self.doc.add_heading(level=2)
        h.paragraph_format.space_before = Pt(13)
        h.paragraph_format.space_after = Pt(3)
        runfont(h.add_run("".join(t for t, _ in runs)), self.font, 13, True,
                RGBColor(0x3B, 0x2E, 0x6E))

    def h4(self, runs):
        p = self.doc.add_paragraph()
        p.paragraph_format.space_before = Pt(9)
        p.paragraph_format.space_after = Pt(2)
        runfont(p.add_run("".join(t for t, _ in runs)), self.font, 11, True,
                ACCENT)

    def para(self, runs):
        p = self.doc.add_paragraph()
        self.runs_into(p, runs, 10.5)

    def bullet(self, runs):
        p = self.doc.add_paragraph(style="List Bullet")
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.left_indent = Cm(0.8)
        self.runs_into(p, runs, 10.5)

    def numbered(self, n, runs):
        p = self.doc.add_paragraph()
        p.paragraph_format.space_after = Pt(3)
        p.paragraph_format.left_indent = Cm(1.0)
        p.paragraph_format.first_line_indent = Cm(-1.0)
        runfont(p.add_run("%d.  " % n), self.font, 10.5, True, ACCENT)
        self.runs_into(p, runs, 10.5)

    def step(self, no, runs):
        p = self.doc.add_paragraph()
        p.paragraph_format.space_before = Pt(10)
        p.paragraph_format.space_after = Pt(2)
        runfont(p.add_run("%s %s  " % (self.L["step"], no)),
                self.font, 11, True, ACCENT)
        runfont(p.add_run("".join(t for t, _ in runs)), self.font, 12, True, INK)

    def note(self, kind, runs):
        bg, fg, icon = NOTE_STYLE.get(kind, NOTE_STYLE["info"])
        label = self.L.get("note_" + kind, self.L["note_info"])
        t = self.doc.add_table(rows=1, cols=1)
        t.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = t.cell(0, 0)
        cell.width = Cm(16.6)
        set_cell_bg(cell, bg)
        set_cell_border(cell,
                        left={"sz": 24, "color": fg},
                        top={"sz": 4, "color": bg},
                        bottom={"sz": 4, "color": bg},
                        right={"sz": 4, "color": bg})
        p = cell.paragraphs[0]
        no_space(p, before=3, after=3)
        p.paragraph_format.left_indent = Cm(0.2)
        runfont(p.add_run("%s %s  " % (icon, label)), self.font, 9.5, True,
                RGBColor.from_string(fg))
        self.runs_into(p, runs, 10, RGBColor.from_string(fg))
        self.doc.add_paragraph().paragraph_format.space_after = Pt(2)

    def table(self, rows):
        head = rows[0]["head"] if rows else False
        ncols = max(len(r["cells"]) for r in rows)
        t = self.doc.add_table(rows=0, cols=ncols)
        t.style = "Table Grid"
        t.alignment = WD_TABLE_ALIGNMENT.CENTER
        for r in rows:
            cells = t.add_row().cells
            for i in range(ncols):
                c = cells[i]
                c.paragraphs[0].paragraph_format.space_before = Pt(2)
                c.paragraphs[0].paragraph_format.space_after = Pt(2)
                runs = r["cells"][i] if i < len(r["cells"]) else []
                if r["head"]:
                    set_cell_bg(c, "F3F1F8")
                self.runs_into(c.paragraphs[0], runs, 9.5,
                               SOFT if not r["head"] else INK,
                               base_bold=r["head"])
        self.doc.add_paragraph().paragraph_format.space_after = Pt(2)

    def image(self, slug):
        path = os.path.join(self.img_dir, self.img_pat % slug)
        if not os.path.exists(path):
            print("  ! missing image:", path)
            return
        p = self.doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        no_space(p, before=6, after=2)
        p.add_run().add_picture(path, width=Cm(self.img_w))
        self.fig_no += 1

    def caption(self, runs):
        p = self.doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        no_space(p, after=10)
        runfont(p.add_run("%s %d — " % (self.L["fig"], max(self.fig_no, 1))),
                self.font, 9, True, FAINT)
        self.runs_into(p, runs, 9, FAINT)

    # -- driver
    def render(self, blocks):
        i = 0
        pending_step = None
        olno = 0
        while i < len(blocks):
            b = blocks[i]
            k = b["k"]
            if k != "oli":
                olno = 0        # a non-list block ends the current ol run
            if k == "sec":
                i += 1; continue
            if k == "chapter":
                sub = where = None
                j = i + 1
                while j < len(blocks) and blocks[j]["k"] in ("sub", "where"):
                    if blocks[j]["k"] == "sub":
                        sub = blocks[j]["runs"]
                    else:
                        where = blocks[j]["runs"]
                    j += 1
                self.chapter(b["runs"], sub, where)
                i = j; continue
            if k in ("sub", "where"):
                i += 1; continue
            if k == "stepno":
                pending_step = "".join(t for t, _ in b["runs"]).strip()
                i += 1; continue
            if k == "h3":
                if pending_step:
                    self.step(pending_step, b["runs"]); pending_step = None
                else:
                    self.h3(b["runs"])
                i += 1; continue
            if k == "h4":
                self.h4(b["runs"]); i += 1; continue
            if k == "p":
                self.para(b["runs"]); i += 1; continue
            if k == "li":
                self.bullet(b["runs"]); i += 1; continue
            if k == "oli":
                olno += 1
                self.numbered(olno, b["runs"]); i += 1; continue
            if k == "cap":
                self.caption(b["runs"]); i += 1; continue
            if k == "img":
                self.image(b["slug"]); i += 1; continue
            if k == "table":
                self.table(b["rows"]); i += 1; continue
            if k.startswith("note:"):
                self.note(k.split(":", 1)[1], b["runs"]); i += 1; continue
            i += 1

    def finish(self):
        """Write the collected chapter list into the reserved contents page."""
        for no, title, bm, sub in self.toc:
            self.toc_entry(no, title, bm, sub)

    def save(self, path):
        self.finish()
        d = os.path.dirname(path)
        if d:
            os.makedirs(d, exist_ok=True)
        try:
            self.doc.save(path)
        except PermissionError:
            raise PermissionError(
                "เขียนไฟล์ไม่ได้ — กรุณาปิดไฟล์นี้ใน Word ก่อนแล้วรันใหม่:\n    %s"
                % path) from None
        return path
