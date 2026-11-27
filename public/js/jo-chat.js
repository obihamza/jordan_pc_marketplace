
(function (w) {
    w.joChat = function (o) {
        var body = o.body, input = o.input, form = o.form, chips = o.chips;
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var history = [], busy = false, timer = null;

        try { history = JSON.parse(sessionStorage.getItem(o.storageKey) || '[]'); } catch (e) { history = []; }
        function save() { try { sessionStorage.setItem(o.storageKey, JSON.stringify(history.slice(-30))); } catch (e) {} }
        function scroll() { body.scrollTop = body.scrollHeight; }

        function addMessage(role, text, extraClass) {
            var el = document.createElement('div');
            el.className = 'jo-chat-msg ' + (role === 'user' ? 'user' : 'bot') + (extraClass ? ' ' + extraClass : '');
            if (role === 'user' || extraClass) { el.textContent = text; } else { joRenderMarkdown(el, text); }
            body.appendChild(el);
            scroll();
            return el;
        }

        function render() {
            clearInterval(timer);
            body.innerHTML = '';
            addMessage('assistant', o.greeting);
            history.forEach(function (m) { addMessage(m.role, m.content); });
        }

        function reveal(el, full, done) {
            var i = 0, step = Math.max(3, Math.ceil(full.length / 90));
            timer = setInterval(function () {
                i = Math.min(full.length, i + step);
                joRenderMarkdown(el, full.slice(0, i));
                scroll();
                if (i >= full.length) { clearInterval(timer); done(); }
            }, 16);
        }

        function send(text) {
            text = (text || '').trim();
            if (!text || busy) { return; }
            busy = true;

            addMessage('user', text);
            var context = history.slice(-o.maxHistory);
            history.push({ role: 'user', content: text });

            var typing = addMessage('assistant', 'Thinking...', 'typing');

            fetch(o.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message: text, history: context })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { ok: res.ok, data: data }; });
            })
            .then(function (r) {
                typing.remove();
                if (!r.ok || !r.data.reply) { throw new Error('bad response'); }

                var el = addMessage('assistant', '');
                history.push({ role: 'assistant', content: r.data.reply });
                save();
                reveal(el, r.data.reply, function () { busy = false; });
            })
            .catch(function () {
                if (typing.parentNode) { typing.remove(); }
                addMessage('assistant', "I'm sorry, I couldn't reach the assistant right now. Please check your connection and try again.");
                history.pop();
                busy = false;
            });
        }

        if (chips) {
            (o.suggestions || []).forEach(function (q) {
                var b = document.createElement('button');
                b.type = 'button'; b.className = 'jo-chat-chip'; b.textContent = q;
                b.addEventListener('click', function () { send(q); });
                chips.appendChild(b);
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var t = input.value;
            input.value = '';
            send(t);
        });

        render();

        return {
            render: render,
            clear: function () { history = []; save(); render(); busy = false; }
        };
    };
})(window);
