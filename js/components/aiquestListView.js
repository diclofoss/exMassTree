(function () {
  function apiUrl(action) {
    var base = typeof dirName !== 'undefined' ? dirName : '';
    var params = window.location.search.replace(/^\?/, '').split('&').filter(function (p) {
      return p && p.indexOf('action=') !== 0 && p.indexOf('dataType=') !== 0 && p.indexOf('view=') !== 0;
    });
    params.push('action=' + encodeURIComponent(action));
    params.push('dataType=json');
    return base + '/?' + params.join('&');
  }

  function fieldInput(name) {
    return document.querySelector('#aiquestListViewForm [name="' + name + '"]')
      || document.querySelector('.aiquest-editor [name="' + name + '"]');
  }

  function wrapField(name, labelHint) {
    var input = fieldInput(name);
    if (!input || input.getAttribute('data-aq-ai') === '1') return;
    input.setAttribute('data-aq-ai', '1');

    var host = document.createElement('div');
    host.className = 'aiquest-ai-wrap';
    host.setAttribute('data-ai-field', name);

    var btns = document.createElement('div');
    btns.className = 'aiquest-ai-btns';
    btns.innerHTML =
      '<button type="button" class="btn btn-sm aiquest-ai-btn" data-ai-mode="regenerate" title="Сгенерировать заново">AI · заново</button>' +
      '<button type="button" class="btn btn-sm aiquest-ai-btn" data-ai-mode="expand" title="Расширить текущий текст">AI · расширить</button>';

    var status = document.createElement('div');
    status.className = 'aiquest-ai-status';

    var parent = input.parentNode;
    parent.insertBefore(host, input.nextSibling);
    host.appendChild(btns);
    host.appendChild(status);

    if (labelHint) {
      var hint = document.createElement('p');
      hint.className = 'aiquest-ai-hint';
      hint.textContent = labelHint;
      host.insertBefore(hint, btns);
    }

    btns.querySelectorAll('.aiquest-ai-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        runAi(name, btn.getAttribute('data-ai-mode'), host, status);
      });
    });
  }

  function setStatus(el, msg, isError) {
    if (!el) return;
    el.textContent = msg || '';
    el.className = 'aiquest-ai-status' + (isError ? ' is-error' : '');
  }

  function runAi(field, mode, host, statusEl) {
    var input = fieldInput(field);
    if (!input) return;
    var titleEl = fieldInput('title');
    var descEl = fieldInput('description');
    var current = (input.value || '').trim();

    if (mode === 'expand' && !current) {
      setStatus(statusEl, 'Для «расширить» сначала напишите черновик (или жмите «заново»).', true);
      return;
    }

    var btns = host.querySelectorAll('.aiquest-ai-btn');
    btns.forEach(function (b) { b.disabled = true; });
    host.classList.add('aiquest-ai-busy');
    setStatus(statusEl, mode === 'expand' ? 'AI расширяет…' : 'AI генерирует…', false);

    fetch(apiUrl('aiAssist'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        mode: mode,
        field: field,
        current: current,
        questTitle: titleEl ? (titleEl.value || '') : '',
        questDescription: descEl ? (descEl.value || '') : ''
      })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'AI fail');
        input.value = data.value || '';
        if (typeof input.dispatchEvent === 'function') {
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        setStatus(statusEl, 'Готово. Нажмите «Сохранить», чтобы записать в БД.', false);
      })
      .catch(function (err) {
        setStatus(statusEl, String(err.message || err), true);
      })
      .finally(function () {
        host.classList.remove('aiquest-ai-busy');
        btns.forEach(function (b) { b.disabled = false; });
      });
  }

  function dissolveGraph() {
    var idMatch = /[?&]id=(\d+)/.exec(window.location.search);
    var questId = idMatch ? parseInt(idMatch[1], 10) : 0;
    if (!questId) {
      alert('Нет id квеста');
      return;
    }
    if (!window.confirm('Загрузить граф в игру?\n\n• берётся сохранённый t_aiquest.graph\n• t_aiquest_node / _edge перезапишутся\n• у всех игроков aiquest_node_edge_id = NULL')) {
      return;
    }
    var btn = document.getElementById('aqDissolveGraph');
    if (btn) btn.disabled = true;
    fetch(apiUrl('dissolveGraph'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ questId: questId })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'dissolve fail');
        var msg = 'Залито: ' + data.nodes + ' нод, ' + data.edges + ' рёбер. Курсоры сброшены.';
        if (data.empty_npc_type) {
          msg += '\n⚠ пустой npc_type у ' + data.empty_npc_type + ' нод. ' + (data.hint || '');
        }
        alert(msg);
        window.location.reload();
      })
      .catch(function (err) {
        alert(String(err.message || err));
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function exportQuest() {
    var idMatch = /[?&]id=(\d+)/.exec(window.location.search);
    var questId = idMatch ? parseInt(idMatch[1], 10) : 0;
    if (!questId) {
      alert('Нет id квеста');
      return;
    }
    var base = typeof dirName !== 'undefined' ? dirName : '';
    var params = window.location.search.replace(/^\?/, '').split('&').filter(function (p) {
      return p && p.indexOf('action=') !== 0 && p.indexOf('dataType=') !== 0 && p.indexOf('view=') !== 0;
    });
    params.push('action=exportQuest');
    params.push('dataType=json');
    params.push('download=1');
    params.push('id=' + encodeURIComponent(String(questId)));
    window.location.href = base + '/?' + params.join('&');
  }

  function importQuest(questId) {
    var fileInput = document.getElementById('aqImportQuestFile');
    if (!fileInput) return;
    fileInput.value = '';
    fileInput.onchange = function () {
      var file = fileInput.files && fileInput.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function () {
        var text = reader.result;
        var payload;
        try {
          payload = JSON.parse(text);
        } catch (e) {
          alert('Файл не JSON');
          return;
        }
        if (!payload || !payload.quest) {
          alert('Неверный формат экспорта');
          return;
        }
        var msg = questId > 0
          ? 'Импортировать в текущий квест #' + questId + '?\n\nПерезапишутся: название, описание, приз, граф, слайды.\nНоды/рёбра игры не трогаем — потом «Загрузить в игру».'
          : 'Создать новый квест из файла?\n\nenabled=0. Потом откройте квест и нажмите «Загрузить в игру».';
        if (!window.confirm(msg)) return;

        var btn = document.getElementById('aqImportQuest') || document.getElementById('aqImportQuestNew');
        if (btn) btn.disabled = true;
        fetch(apiUrl('importQuest'), {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ questId: questId || 0, export: payload })
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data || !data.ok) throw new Error((data && data.error) || 'import fail');
            alert(
              'Импорт ок: quest #' + data.questId +
              ', граф ' + data.nodes + '/' + data.edges +
              ', слайдов ' + data.slides +
              (data.slides_with_images ? (' (картинок ' + data.slides_with_images + ')') : '') +
              '.\n\n' + (data.hint || '')
            );
            var base = typeof dirName !== 'undefined' ? dirName : '';
            var params = window.location.search.replace(/^\?/, '').split('&').filter(function (p) {
              return p && p.indexOf('action=') !== 0 && p.indexOf('id=') !== 0 && p.indexOf('view=') !== 0;
            });
            params.push('action=edit');
            params.push('id=' + encodeURIComponent(String(data.questId)));
            window.location.href = base + '/?' + params.join('&');
          })
          .catch(function (err) {
            alert(String(err.message || err));
          })
          .finally(function () {
            if (btn) btn.disabled = false;
          });
      };
      reader.readAsText(file);
    };
    fileInput.click();
  }

  function boot() {
    if (!document.querySelector('.aiquest-editor')) return;
    wrapField('title', '');
    wrapField('description', '');
    var dissolveBtn = document.getElementById('aqDissolveGraph');
    if (dissolveBtn) dissolveBtn.addEventListener('click', dissolveGraph);
    var exportBtn = document.getElementById('aqExportQuest');
    if (exportBtn) exportBtn.addEventListener('click', exportQuest);
    var importBtn = document.getElementById('aqImportQuest');
    if (importBtn) {
      importBtn.addEventListener('click', function () {
        var idMatch = /[?&]id=(\d+)/.exec(window.location.search);
        importQuest(idMatch ? parseInt(idMatch[1], 10) : 0);
      });
    }
    var importNewBtn = document.getElementById('aqImportQuestNew');
    if (importNewBtn) {
      importNewBtn.addEventListener('click', function () {
        importQuest(0);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
