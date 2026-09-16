# -*- coding: utf-8 -*-
"""Parse the Blade manual/guide templates into a plain document model."""
import re, html
from html.parser import HTMLParser

# ── Blade -> HTML ────────────────────────────────────────────────────────────

TT = re.compile(
    r"__t\(\s*(['\"])(?P<th>(?:\\.|(?!\1).)*)\1\s*,\s*(['\"])(?P<en>(?:\\.|(?!\3).)*)\3\s*\)",
    re.S)


def _unq(s):
    return s.replace("\\'", "'").replace('\\"', '"').replace("\\\\", "\\")


def resolve_t(text, lang):
    """Replace __t('th','en') with the chosen language."""
    def sub(m):
        v = m.group('th') if lang == 'th' else m.group('en')
        return _unq(re.sub(r"\s*\n\s*", " ", v))
    return TT.sub(sub, text)


IMG_CALL = re.compile(r"\{\{\s*\$(?:img|shot)\(\s*['\"]([^'\"]+)['\"]\s*\)\s*\}\}")


def deblade(src, lang):
    """Strip Blade constructs, leaving HTML with __IMG:slug__ markers."""
    s = src
    s = re.sub(r"\{\{--.*?--\}\}", "", s, flags=re.S)          # blade comments
    s = re.sub(r"<!--.*?-->", "", s, flags=re.S)               # html comments
    s = re.sub(r"<script\b.*?</script>", "", s, flags=re.S | re.I)
    s = re.sub(r"<style\b.*?</style>", "", s, flags=re.S | re.I)
    s = re.sub(r"@php\b.*?@endphp", "", s, flags=re.S)         # inline php setup
    s = re.sub(r"<nav\b.*?</nav>", "", s, flags=re.S | re.I)   # sidebar TOC
    # separate <code> badges so they do not glue onto neighbouring text
    s = re.sub(r"<code\b[^>]*>", " ", s, flags=re.I)
    s = re.sub(r"</code>", " ", s, flags=re.I)
    s = IMG_CALL.sub(lambda m: "__IMG:%s__" % m.group(1), s)
    # resolve __t() inside its {{ }} echo and unwrap in one step, so the
    # generic {{ ... }} cleanup below cannot swallow the resolved text
    s = re.sub(r"\{\{\s*" + TT.pattern + r"\s*\}\}",
               lambda m: _unq(re.sub(r"\s*\n\s*", " ",
                                     m.group('th') if lang == 'th' else m.group('en'))),
               s, flags=re.S)
    s = resolve_t(s, lang)
    # drop control directives but keep the body of conditionals
    s = re.sub(r"@(if|elseif|unless|foreach|for|while|isset|empty|php|endphp|"
               r"endif|endforeach|endfor|endwhile|endunless|else|continue|break)"
               r"\b(\s*\((?:[^()]|\([^()]*\))*\))?", "", s)
    # remaining blade echoes -> their inner literal if it is a plain string
    s = re.sub(r"\{\{\s*['\"]([^'\"]*)['\"]\s*\}\}", r"\1", s)
    s = re.sub(r"\{\{.*?\}\}", "", s, flags=re.S)
    s = re.sub(r"\{!!.*?!!\}", "", s, flags=re.S)
    return s


def section_body(src, marker="@section('content')"):
    i = src.index(marker) + len(marker)
    j = src.find("@endsection", i)
    if j == -1:
        j = src.find("@push", i)
    return src[i:j if j != -1 else len(src)]


# ── HTML -> blocks ───────────────────────────────────────────────────────────

BLOCK_TAGS = {"p", "li", "h1", "h2", "h3", "h4", "h5", "figcaption",
              "td", "th", "summary", "div", "span", "section", "figure",
              "table", "tr", "thead", "tbody", "ul", "ol", "details", "nav", "a"}


class Doc(HTMLParser):
    """Collect an ordered list of blocks from the manual markup."""

    def __init__(self, cfg):
        super().__init__(convert_charrefs=True)
        self.cfg = cfg
        self.blocks = []
        self.stack = []          # (tag, classes)
        self.buf = []            # inline runs: (text, bold)
        self.bold = 0
        self.mode = None         # current block kind
        self.row = None
        self.table = None
        self.skip = 0
        self.pending_no = None

    # -- helpers
    def _cls(self, attrs):
        d = dict(attrs)
        return (d.get("class") or "").split(), d

    def _flush(self, kind=None):
        runs = [(t, b) for t, b in self.buf if t.strip()]
        self.buf = []
        if not runs:
            return
        kind = kind or self.mode or "p"
        # merge adjacent runs of the same weight
        merged = []
        for t, b in runs:
            t = re.sub(r"\s+", " ", t)
            if merged and merged[-1][1] == b:
                merged[-1][0] = (merged[-1][0].rstrip() + " " + t.lstrip())
            else:
                merged.append([t, b])
        merged[0][0] = merged[0][0].lstrip()
        merged[-1][0] = merged[-1][0].rstrip()
        merged = [(t, b) for t, b in merged if t.strip()]
        if merged:
            self.blocks.append({"k": kind, "runs": merged})

    def emit(self, block):
        self.blocks.append(block)

    # -- tags
    def handle_starttag(self, tag, attrs):
        cls, d = self._cls(attrs)
        if self.skip:
            self.stack.append((tag, cls))
            return
        cfg = self.cfg

        if tag in ("script", "style"):
            self.skip += 1

        # section heading (admin: .man-h h2 / customer: .g-sec-title)
        if tag == "section" and d.get("id"):
            self._flush()
            self.emit({"k": "sec", "id": d.get("id")})
        if any(c in cfg["chapter_cls"] for c in cls):
            self._flush()
            self.mode = "chapter"
        if tag == "h2":
            self._flush(); self.mode = "chapter"
        if tag == "h3":
            self._flush(); self.mode = "h3"
        if tag == "h4":
            self._flush(); self.mode = "h4"
        if tag == "summary":
            self._flush(); self.mode = "h4"
        if "sub" in cls and self.blocks and self.blocks[-1]["k"] == "chapter":
            self._flush(); self.mode = "sub"
        if "where" in cls:
            self._flush(); self.mode = "where"
        if tag == "p":
            self._flush(); self.mode = "p"
        if tag == "li":
            self._flush()
            inol = any(t == "ol" for t, _ in self.stack)
            self.mode = "oli" if inol else "li"
        if tag == "figcaption":
            self._flush(); self.mode = "cap"
        if tag in ("strong", "b"):
            self.bold += 1
        if "g-step-no" in cls:
            self._flush(); self.mode = "stepno"
        # note boxes
        note = None
        for c in cls:
            if c in ("note", "g-note"):
                note = "info"
        if note:
            self._flush()
            kinds = {"tip": "tip", "warn": "warn", "dang": "danger", "bad": "danger",
                     "ok": "ok", "good": "ok"}
            for c in cls:
                if c in kinds:
                    note = kinds[c]
            self.mode = "note:" + note
        # tables
        if tag == "table":
            self._flush()
            self.table = {"k": "table", "rows": [], "head": False}
            self.mode = None
        if tag == "thead":
            if self.table:
                self.table["head"] = True
        if tag == "tr" and self.table is not None:
            self.row = []
        if tag in ("td", "th") and self.table is not None:
            self._flush(); self.buf = []; self.mode = "cell"
        if tag == "br":
            self.buf.append((" — ", False))
        if tag == "img":
            src = d.get("src", "")
            m = re.match(r"__IMG:(.+)__$", src.strip())
            if m:
                self._flush()
                self.emit({"k": "img", "slug": m.group(1)})
        self.stack.append((tag, cls))

    def handle_endtag(self, tag):
        while self.stack:
            t, cls = self.stack.pop()
            if t == tag:
                break
        if tag in ("script", "style") and self.skip:
            self.skip -= 1
            return
        if self.skip:
            return
        if tag in ("strong", "b"):
            self.bold = max(0, self.bold - 1)
            return
        if tag in ("td", "th") and self.table is not None:
            runs = [(re.sub(r"\s+", " ", t), b) for t, b in self.buf if t.strip()]
            self.buf = []
            if self.row is not None:
                self.row.append(runs)
            self.mode = None
            return
        if tag == "tr" and self.table is not None:
            if self.row:
                self.table["rows"].append({"cells": self.row,
                                           "head": self.table["head"]})
            self.row = None
            return
        if tag == "thead" and self.table is not None:
            self.table["head"] = False
            return
        if tag == "table" and self.table is not None:
            if self.table["rows"]:
                self.emit(self.table)
            self.table = None
            return
        if self.mode in ("cell", "chapter") and tag in ("span", "div", "a", "figure"):
            return          # inline wrapper: keep accumulating the current block
        if tag in BLOCK_TAGS:
            self._flush()
            self.mode = None

    def handle_data(self, data):
        if self.skip or not data.strip():
            if data.strip("\n") and self.buf:
                self.buf.append((" ", bool(self.bold)))
            return
        self.buf.append((data, bool(self.bold)))

    def close(self):
        super().close()
        self._flush()
        return self.blocks


def parse(path, lang, chapter_cls=()):
    src = open(path, encoding="utf-8").read()
    body = section_body(src)
    html_src = deblade(body, lang)
    d = Doc({"chapter_cls": set(chapter_cls)})
    d.feed(html_src)
    return d.close()
