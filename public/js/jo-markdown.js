/* Tiny, safe markdown renderer for chatbot replies: **bold**, *italic*, `code`, lists, headings.
   Builds DOM nodes (never innerHTML), so a reply can't inject HTML. */
(function (w) {
    function inline(parent, text) {
        var re = /(\*\*[^*]+\*\*|`[^`]+`|\*[^*\s][^*]*\*)/g, last = 0, m;
        while ((m = re.exec(text)) !== null) {
            if (m.index > last) { parent.appendChild(document.createTextNode(text.slice(last, m.index))); }
            var t = m[0], el;
            if (t.indexOf('**') === 0) { el = document.createElement('strong'); el.textContent = t.slice(2, -2); }
            else if (t.charAt(0) === '`') { el = document.createElement('code'); el.textContent = t.slice(1, -1); }
            else { el = document.createElement('em'); el.textContent = t.slice(1, -1); }
            parent.appendChild(el);
            last = m.index + t.length;
        }
        if (last < text.length) { parent.appendChild(document.createTextNode(text.slice(last))); }
    }

    w.joRenderMarkdown = function (container, text) {
        container.textContent = '';
        var list = null, listType = null;
        String(text).replace(/\r/g, '').split('\n').forEach(function (line) {
            var b = line.match(/^\s*[-*•]\s+(.*)$/), n = line.match(/^\s*\d+[.)]\s+(.*)$/), h = line.match(/^\s*#{1,6}\s+(.*)$/);
            var item = b || n;
            if (item) {
                var type = b ? 'UL' : 'OL';
                if (!list || listType !== type) { list = document.createElement(type.toLowerCase()); listType = type; container.appendChild(list); }
                var li = document.createElement('li'); inline(li, item[1]); list.appendChild(li);
                return;
            }
            list = null; listType = null;
            if (line.trim() === '') { return; }
            var p = document.createElement('div');
            if (h) { p.className = 'fw-bold'; inline(p, h[1]); } else { inline(p, line); }
            container.appendChild(p);
        });
    };
})(window);
