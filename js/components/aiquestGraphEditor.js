(function () {
  var FACTION = { civilian: '#3d8f6e', business: '#c4a35b', state: '#5b8cff', crime: '#c45c5c', finale: '#d4a017' };
  var boot = window.AIQUEST_GRAPH_BOOT || null;
  if (!boot) {
    return;
  }

  var graph = boot.graph || { nodes: [], edges: [], entry_in_act: [], summary: '', focus_node: null };
  var palette = [];
  var network = null;
  var nodesDS = null;
  var edgesDS = null;
  var linkFrom = null;
  /** { type:'node', id } | { type:'edge', from, to } | null */
  var selection = null;

  function isFinaleNode(n) {
    if (!n) return false;
    if (n.kind === 'finale' || n.prefix === 'finale' || n.faction === 'finale') return true;
    var base = baseNpcId(n.id);
    return base === 'finale' || base.indexOf('finale#') === 0;
  }

  function status(msg, kind) {
    var el = document.getElementById('aqGraphStatus');
    if (!el) return;
    el.textContent = msg || '';
    el.className = 'aq-graph-status' + (kind ? ' is-' + kind : '');
  }

  function apiUrl(action) {
    var base = typeof dirName !== 'undefined' ? dirName : '';
    return base + '/?category=' + encodeURIComponent(boot.category) +
      '&component=' + encodeURIComponent(boot.component) +
      '&element=' + encodeURIComponent(boot.element) +
      '&action=' + encodeURIComponent(action) +
      '&dataType=json';
  }

  function usedIds() {
    var s = {};
    (graph.nodes || []).forEach(function (n) { s[n.id] = true; });
    return s;
  }

  /** Базовый id NPC без суффикса экземпляра (~2, ~3…). */
  function baseNpcId(id) {
    return String(id || '').replace(/~\d+$/, '');
  }

  /** Уникальный id на канве: farm#2, farm#2~2, farm#2~3… */
  function allocNodeId(baseId) {
    var base = baseNpcId(baseId);
    var used = usedIds();
    if (!used[base]) {
      return base;
    }
    var n = 2;
    while (used[base + '~' + n]) {
      n++;
    }
    return base + '~' + n;
  }

  function countOnCanvas(baseId) {
    var base = baseNpcId(baseId);
    var c = 0;
    (graph.nodes || []).forEach(function (n) {
      if (baseNpcId(n.id) === base) c++;
    });
    return c;
  }

  function pair(a, b) { return a + '->' + b; }
  function pathPairs(path) {
    var s = {};
    for (var i = 0; i < (path || []).length - 1; i++) s[pair(path[i], path[i + 1])] = true;
    return s;
  }

  /** vis-network плохо переваривает '#' в id (DOM/selectors) — на канве безопасный id. */
  function visId(npcId) {
    return String(npcId || '').replace(/#/g, '__');
  }
  function fromVisId(vid) {
    return String(vid || '').replace(/__/g, '#');
  }

  function ensureCanvasSize() {
    var el = document.getElementById('aqGraphCanvas');
    if (!el) return;
    if (el.clientHeight < 200) {
      el.style.height = '560px';
    }
  }

  function rebuildNetwork() {
    var el = document.getElementById('aqGraphCanvas');
    if (!el || !window.vis) {
      status('vis-network не загружен', 'error');
      return;
    }
    ensureCanvasSize();

    var hasSavedPos = (graph.nodes || []).some(function (n) {
      return typeof n.x === 'number' && typeof n.y === 'number';
    });

    var nodeRows = (graph.nodes || []).map(function (n) {
      var finale = isFinaleNode(n);
      var c = finale ? FACTION.finale : (FACTION[n.faction] || '#888');
      var focus = n.id === graph.focus_node;
      var cut = hasCutscene(n);
      var row = {
        id: visId(n.id),
        label: (finale
          ? ('ФИНАЛ\n' + (n.id || 'finale'))
          : ((n.label || n.id) + '\n' + n.id)) + cutsceneBadge(n),
        title: (finale
          ? ('Финал — конец квеста' + (n.prize_good_id ? ('\nПриз концовки: #' + n.prize_good_id + ' ×' + (n.prize_col || 1)) : '\nПриз: общий из t_aiquest'))
          : [
            n.question ? 'Вопрос: ' + n.question : '',
            n.why_in_this_act || '',
            n.dramatic_role || ''
          ].filter(Boolean).join('\n'))
          + (cut ? '\nCut-сцена: ' + (cutsceneVideo(n) || n.cutscene.prompt) : ''),
        color: {
          background: focus ? '#8b3030' : c,
          border: finale ? '#f0d78c' : ((graph.entry_in_act || []).indexOf(n.id) >= 0 ? '#b8e6d4' : '#3a4560'),
          highlight: { background: c, border: '#fff' }
        },
        borderWidth: finale || focus || (graph.entry_in_act || []).indexOf(n.id) >= 0 ? 3 : 2,
        font: { color: '#fff', size: 13, multi: true, face: 'Segoe UI' },
        shape: finale ? 'diamond' : 'box',
        margin: 14,
        widthConstraint: { maximum: 180 },
        fixed: { x: false, y: false }
      };
      if (typeof n.x === 'number' && typeof n.y === 'number') {
        row.x = n.x;
        row.y = n.y;
      }
      return row;
    });

    // Invisible native edges — hit-test only. Visible path drawn in afterDrawing
    // (bottom-center → top-center), because vis attaches curves to the nearest side.
    var edgeRows = [];
    (graph.edges || []).forEach(function (e, i) {
      edgeRows.push({
        id: 'e' + i,
        from: visId(e.from),
        to: visId(e.to),
        title: [
          e.player_answer ? 'Игрок: ' + e.player_answer : '',
          e.npc_answer ? 'NPC: ' + e.npc_answer : '',
          e.require_good_id ? 'требует #' + e.require_good_id : '',
          e.give_good_id ? 'даёт #' + e.give_good_id : '',
          e.talk_need_good_id ? 'для общения #' + e.talk_need_good_id : '',
          e.require_kpi ? 'KPI ≥ ' + e.require_kpi : '',
          e.kpi_text ? 'KPI: ' + e.kpi_text : '',
          e.label || ''
        ].filter(Boolean).join('\n') || (e.kind || ''),
        arrows: { to: { enabled: false } },
        color: {
          color: 'rgba(0,0,0,0)',
          highlight: 'rgba(0,0,0,0)',
          hover: 'rgba(0,0,0,0)',
          opacity: 0
        },
        font: { color: 'rgba(0,0,0,0)', strokeWidth: 0, size: 1 },
        smooth: { enabled: true, type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.35 },
        width: 12,
        selectionWidth: 12,
        hoverWidth: 12
      });
    });

    if (network) {
      try { network.destroy(); } catch (err) {}
      network = null;
    }
    el.innerHTML = '';

    nodesDS = new vis.DataSet(nodeRows);
    edgesDS = new vis.DataSet(edgeRows);
    network = new vis.Network(el, { nodes: nodesDS, edges: edgesDS }, {
      autoResize: true,
      interaction: { multiselect: true, navigationButtons: true, hover: true, dragNodes: true },
      physics: {
        enabled: !hasSavedPos,
        solver: 'forceAtlas2Based',
        stabilization: { enabled: !hasSavedPos, iterations: 100 }
      },
      layout: {
        hierarchical: { enabled: false },
        improvedLayout: !hasSavedPos,
        randomSeed: 42
      },
      edges: {
        arrows: { to: { enabled: false } },
        color: { color: 'rgba(0,0,0,0)', opacity: 0 },
        smooth: { enabled: true, type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.35 },
        width: 12
      },
      nodes: {
        shape: 'box',
        margin: 14
      }
    });

    function finishView() {
      try {
        if (!hasSavedPos) {
          network.setOptions({ physics: { enabled: false } });
          persistPositionsFromNetwork();
        }
        network.fit({ animation: false, padding: 48 });
        network.redraw();
      } catch (err) {}
    }
    if (hasSavedPos) {
      setTimeout(finishView, 40);
    } else {
      network.once('stabilizationIterationsDone', finishView);
      setTimeout(finishView, 800);
    }

    network.on('afterDrawing', function (ctx) {
      drawBottomTopEdges(ctx);
    });

    network.on('dragEnd', function (params) {
      if (!params || !params.nodes || !params.nodes.length) return;
      persistPositionsFromNetwork(params.nodes);
    });

    network.on('click', function (params) {
      if (linkFrom) {
        if (params.nodes && params.nodes.length === 1) {
          var to = fromVisId(params.nodes[0]);
          if (to === linkFrom) {
            status('Выберите другой узел (to)', 'error');
            return;
          }
          if (addEdge(linkFrom, to)) {
            status('Ребро добавлено', 'ok');
          }
          linkFrom = null;
        }
        return;
      }
      if (params.nodes && params.nodes.length === 1) {
        selection = { type: 'node', id: fromVisId(params.nodes[0]) };
        renderProps();
        network.redraw();
        return;
      }
      if (params.edges && params.edges.length === 1) {
        var eo = edgesDS.get(params.edges[0]);
        if (eo) {
          selection = { type: 'edge', from: fromVisId(eo.from), to: fromVisId(eo.to) };
          renderProps();
          network.redraw();
        }
        return;
      }
      selection = null;
      renderProps();
      network.redraw();
    });
  }

  /** Рёбра: центр нижнего ребра from → центр верхнего ребра to. */
  function drawBottomTopEdges(ctx) {
    if (!network || !ctx) return;
    var selectedKey = selection && selection.type === 'edge'
      ? (selection.from + '->' + selection.to)
      : '';

    (graph.edges || []).forEach(function (e) {
      var bbFrom = network.getBoundingBox(visId(e.from));
      var bbTo = network.getBoundingBox(visId(e.to));
      if (!bbFrom || !bbTo) return;

      var x1 = (bbFrom.left + bbFrom.right) / 2;
      var y1 = bbFrom.bottom;
      var x2 = (bbTo.left + bbTo.right) / 2;
      var y2 = bbTo.top;
      var midY = y1 + (y2 - y1) * 0.5;
      var hi = (e.from + '->' + e.to) === selectedKey;
      var stroke = hi ? '#ffffff' : '#8a9bb8';

      ctx.save();
      ctx.strokeStyle = stroke;
      ctx.fillStyle = stroke;
      ctx.lineWidth = hi ? 2.6 : 2;
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      ctx.beginPath();
      ctx.moveTo(x1, y1);
      ctx.bezierCurveTo(x1, midY, x2, midY, x2, y2);
      ctx.stroke();

      // arrow into top-center of target
      var ah = 9;
      ctx.beginPath();
      ctx.moveTo(x2, y2);
      ctx.lineTo(x2 - 5.5, y2 - ah);
      ctx.lineTo(x2 + 5.5, y2 - ah);
      ctx.closePath();
      ctx.fill();

      var label = e.player_answer
        ? (String(e.player_answer).length > 28 ? String(e.player_answer).slice(0, 28) + '…' : e.player_answer)
        : (e.kind || '');
      if (label) {
        ctx.font = '10px "Segoe UI", sans-serif';
        ctx.fillStyle = hi ? '#e8eaed' : '#9aa3b2';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        ctx.fillText(label, (x1 + x2) / 2, midY - 3);
      }
      ctx.restore();
    });
  }

  function persistPositionsFromNetwork(onlyVisIds) {
    if (!network) return;
    var ids = onlyVisIds && onlyVisIds.length
      ? onlyVisIds
      : (graph.nodes || []).map(function (n) { return visId(n.id); });
    ids.forEach(function (vid) {
      var pos = network.getPositions([vid])[vid];
      if (!pos) return;
      var n = findNode(fromVisId(vid));
      if (!n) return;
      n.x = Math.round(pos.x);
      n.y = Math.round(pos.y);
      if (nodesDS) {
        try { nodesDS.update({ id: vid, x: n.x, y: n.y }); } catch (err) {}
      }
    });
  }

  /** Разовая иерархия UD → снова свободный XY, рёбра рисуются низ→верх. */
  function autoLayoutUd() {
    if (!network || !nodesDS) return;
    status('Автораскладка…');
    network.setOptions({
      physics: { enabled: false },
      layout: {
        hierarchical: {
          enabled: true,
          direction: 'UD',
          sortMethod: 'directed',
          shakeTowards: 'roots',
          levelSeparation: 160,
          nodeSpacing: 200,
          treeSpacing: 220,
          blockShifting: true,
          edgeMinimization: true,
          parentCentralization: true
        }
      }
    });
    setTimeout(function () {
      persistPositionsFromNetwork();
      network.setOptions({
        layout: { hierarchical: { enabled: false } },
        physics: { enabled: false }
      });
      (graph.nodes || []).forEach(function (n) {
        if (typeof n.x === 'number' && typeof n.y === 'number') {
          try {
            nodesDS.update({ id: visId(n.id), x: n.x, y: n.y, fixed: { x: false, y: false } });
          } catch (err) {}
        }
      });
      try { network.fit({ animation: false, padding: 48 }); } catch (err) {}
      network.redraw();
      status('Раскладка готова. Рёбра: низ → верх. Узлы свободно по X/Y.', 'ok');
    }, 120);
  }

  function findNode(id) {
    var list = graph.nodes || [];
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === id) return list[i];
    }
    return null;
  }

  function findEdge(from, to) {
    var list = graph.edges || [];
    for (var i = 0; i < list.length; i++) {
      if (list[i].from === from && list[i].to === to) return list[i];
    }
    return null;
  }

  function loreForNpc(nodeId) {
    var base = baseNpcId(nodeId);
    for (var i = 0; i < palette.length; i++) {
      if (palette[i].id === base) return palette[i].questlore || palette[i].lore_short || '';
    }
    return '';
  }

  function nodeContext(n) {
    if (!n) return null;
    if (isFinaleNode(n)) {
      return {
        id: n.id,
        npc_id: 'finale',
        label: 'Финал',
        kind: 'finale',
        question: ''
      };
    }
    var base = n.npc_id || baseNpcId(n.id);
    var prefix = n.prefix || (String(base).indexOf('#') >= 0 ? String(base).split('#')[0] : '');
    return {
      id: n.id,
      npc_id: base,
      prefix: prefix,
      label: n.label || n.id,
      faction: n.faction || '',
      dramatic_role: n.dramatic_role || '',
      why_in_this_act: n.why_in_this_act || '',
      place: n.place || '',
      question: n.question || '',
      questlore: loreForNpc(n.id)
    };
  }

  function incomingEdgesFor(nodeId) {
    var out = [];
    (graph.edges || []).forEach(function (e) {
      if (e.to !== nodeId) return;
      out.push({
        from: e.from,
        to: e.to,
        from_node: nodeContext(findNode(e.from)),
        from_question: (findNode(e.from) && findNode(e.from).question) || '',
        player_answer: e.player_answer || '',
        npc_answer: e.npc_answer || '',
        path: e.path || 'both',
        kind: e.kind || ''
      });
    });
    return out;
  }

  /** Входящие от того же NPC и без require_kpi на ребре — продолжение без приветствия. */
  function isSameNpcContinuation(nodeId, incoming) {
    var base = baseNpcId(nodeId);
    if (!base || !incoming || !incoming.length) return false;
    for (var i = 0; i < incoming.length; i++) {
      if (baseNpcId(incoming[i].from) !== base) return false;
      var edge = findEdge(incoming[i].from, incoming[i].to);
      if (edge && edge.require_kpi != null && Number(edge.require_kpi) > 0) return false;
    }
    return true;
  }

  function aiBtnsHtml(field) {
    return '<div class="aq-ai-btns" data-ai-field="' + field + '">' +
      '<button type="button" class="btn btn-sm aq-ai-btn" data-ai-mode="regenerate" title="Сгенерировать заново">AI · заново</button>' +
      '<button type="button" class="btn btn-sm aq-ai-btn" data-ai-mode="expand" title="Расширить текущий текст">AI · расширить</button>' +
      '</div><div class="aq-props-ai-status" data-ai-status="' + field + '"></div>';
  }

  function setAiStatus(field, msg, isError) {
    var el = document.querySelector('.aq-props-ai-status[data-ai-status="' + field + '"]');
    if (!el) return;
    el.textContent = msg || '';
    el.className = 'aq-props-ai-status' + (isError ? ' is-error' : '');
  }

  function goodsPickerHtml(fieldKey, goodId) {
    return '<div class="aq-good-picker" data-good-field="' + fieldKey + '">' +
      '<div class="aq-good-selected" data-good-selected>' +
      (goodId ? 'id ' + goodId + '…' : '— не выбран —') +
      '</div>' +
      '<div class="aq-good-row">' +
      '<input type="search" class="form-control form-control-sm aq-good-q" placeholder="Поиск предмета…" autocomplete="off" />' +
      '<button type="button" class="btn btn-sm btn-outline-secondary aq-good-clear" title="Сбросить">×</button>' +
      '</div>' +
      '<div class="aq-good-results" data-good-results></div>' +
      '</div>';
  }

  function fetchGoods(q, id) {
    var url = apiUrl('goodsSearch');
    if (id) url += '&id=' + encodeURIComponent(id);
    else if (q) url += '&q=' + encodeURIComponent(q);
    return fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'goods fail');
        return data.results || [];
      });
  }

  function bindGoodsPicker(root, edge, fieldKey) {
    var box = root.querySelector('.aq-good-picker[data-good-field="' + fieldKey + '"]');
    if (!box || !edge) return;
    var selectedEl = box.querySelector('[data-good-selected]');
    var resultsEl = box.querySelector('[data-good-results]');
    var qInput = box.querySelector('.aq-good-q');
    var clearBtn = box.querySelector('.aq-good-clear');
    var timer = null;

    function setLabel(id, text) {
      if (!selectedEl) return;
      if (!id) {
        selectedEl.textContent = '— не выбран —';
        selectedEl.classList.remove('has-value');
        return;
      }
      selectedEl.textContent = (text || ('#' + id)) + ' · id ' + id;
      selectedEl.classList.add('has-value');
    }

    function applyId(id, text) {
      var v = id ? parseInt(id, 10) : null;
      if (!v || isNaN(v)) v = null;
      edge[fieldKey] = v;
      setLabel(v, text || '');
      if (resultsEl) resultsEl.innerHTML = '';
      if (qInput) qInput.value = '';
    }

    var cur = edge[fieldKey];
    if (cur) {
      fetchGoods('', cur).then(function (rows) {
        if (rows[0]) setLabel(rows[0].id, rows[0].text);
        else setLabel(cur, '');
      }).catch(function () { setLabel(cur, ''); });
    } else {
      setLabel(null);
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        applyId(null);
      });
    }

    function showResults(rows) {
      if (!resultsEl) return;
      if (!rows.length) {
        resultsEl.innerHTML = '<div class="aq-muted">Ничего не найдено</div>';
        return;
      }
      resultsEl.innerHTML = rows.map(function (r) {
        return '<button type="button" class="aq-good-item" data-id="' + r.id + '" data-text="' +
          escapeHtml(r.text) + '">' + escapeHtml(r.text) + ' <code>#' + r.id + '</code></button>';
      }).join('');
    }

    if (resultsEl) {
      resultsEl.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.aq-good-item');
        if (!btn) return;
        applyId(btn.getAttribute('data-id'), btn.getAttribute('data-text'));
      });
    }

    if (qInput) {
      qInput.addEventListener('input', function () {
        var q = qInput.value || '';
        clearTimeout(timer);
        timer = setTimeout(function () {
          fetchGoods(q.trim(), 0).then(showResults).catch(function (err) {
            if (resultsEl) resultsEl.innerHTML = '<div class="aq-muted">' + escapeHtml(err.message || err) + '</div>';
          });
        }, 250);
      });
      qInput.addEventListener('focus', function () {
        if (!(qInput.value || '').trim()) {
          fetchGoods('', 0).then(showResults).catch(function () {});
        }
      });
    }
  }

  function runGraphAi(field, mode) {
    var textareaId = field === 'question' ? 'aqPropQuestion'
      : (field === 'player_answer' ? 'aqPropPlayerAnswer'
        : (field === 'kpi_text' ? 'aqPropKpiText' : 'aqPropNpcAnswer'));
    var ta = document.getElementById(textareaId);
    if (!ta) return;
    var current = ta.value || '';
    if (mode === 'expand' && !String(current).trim()) {
      setAiStatus(field, 'Для «расширить» сначала напишите черновик (или жмите «заново»).', true);
      return;
    }

    var context = { graph_summary: graph.summary || '' };
    if (selection && selection.type === 'node') {
      var node = findNode(selection.id);
      context.node = nodeContext(node);
      context.incoming_edges = incomingEdgesFor(selection.id);
      context.has_incoming = context.incoming_edges.length > 0;
      context.same_npc_continuation = isSameNpcContinuation(selection.id, context.incoming_edges);
      if (context.same_npc_continuation) {
        context.conversation_so_far = context.incoming_edges.map(function (inc) {
          return {
            from: inc.from,
            player_answer: inc.player_answer,
            npc_answer: inc.npc_answer,
            from_question: inc.from_question
          };
        });
      }
      // AI · заново на продолжении: сразу очищаем question без вызова модели
      if (field === 'question' && mode === 'regenerate' && context.same_npc_continuation) {
        ta.value = '';
        var evClear = document.createEvent('Event');
        evClear.initEvent('input', true, true);
        ta.dispatchEvent(evClear);
        ta.dispatchEvent(new Event('change'));
        setAiStatus(field, 'Продолжение того же NPC — приветствие очищено (пусто = ок). Сохраните граф.', false);
        return;
      }
    } else if (selection && selection.type === 'edge') {
      var e = findEdge(selection.from, selection.to);
      var fromN = findNode(selection.from);
      var toN = findNode(selection.to);
      context.edge = {
        from: selection.from,
        to: selection.to,
        kind: e ? (e.kind || '') : '',
        path: e ? (e.path || '') : '',
        label: e ? (e.label || '') : '',
        player_answer: e ? (e.player_answer || '') : '',
        npc_answer: e ? (e.npc_answer || '') : '',
        require_good_id: e ? (e.require_good_id || null) : null,
        give_good_id: e ? (e.give_good_id || null) : null,
        talk_need_good_id: e ? (e.talk_need_good_id || null) : null,
        require_kpi: e ? (e.require_kpi || null) : null,
        kpi_text: e ? (e.kpi_text || '') : ''
      };
      // Имена предметов из UI-пикеров (сервер всё равно резолвит по id)
      var reqSel = document.querySelector('.aq-good-picker[data-good-field="require_good_id"] [data-good-selected]');
      var giveSel = document.querySelector('.aq-good-picker[data-good-field="give_good_id"] [data-good-selected]');
      var talkSel = document.querySelector('.aq-good-picker[data-good-field="talk_need_good_id"] [data-good-selected]');
      if (reqSel && reqSel.classList.contains('has-value')) {
        context.edge.require_good_label = (reqSel.textContent || '').replace(/\s*·\s*id\s+\d+\s*$/, '').trim();
      }
      if (giveSel && giveSel.classList.contains('has-value')) {
        context.edge.give_good_label = (giveSel.textContent || '').replace(/\s*·\s*id\s+\d+\s*$/, '').trim();
      }
      if (talkSel && talkSel.classList.contains('has-value')) {
        context.edge.talk_need_good_label = (talkSel.textContent || '').replace(/\s*·\s*id\s+\d+\s*$/, '').trim();
      }
      if (context.edge.require_good_id || context.edge.give_good_id || context.edge.talk_need_good_id) {
        context.items_note = 'На ребре заданы предметы — они должны изменить текст реплики.';
      }
      context.from_node = nodeContext(fromN);
      context.to_node = nodeContext(toN);
      context.speaker_node = nodeContext(fromN);
      context.destination_node = nodeContext(toN);
      var fromIncoming = incomingEdgesFor(selection.from);
      context.same_npc_continuation = isSameNpcContinuation(selection.from, fromIncoming);
      if (context.same_npc_continuation) {
        context.incoming_edges = fromIncoming;
        context.conversation_so_far = fromIncoming.map(function (inc) {
          return {
            from: inc.from,
            player_answer: inc.player_answer,
            npc_answer: inc.npc_answer,
            from_question: inc.from_question
          };
        });
      }
      context.next_npc_handoff = toN
        ? ('Игрок после этого ответа должен пойти к: ' + (toN.label || toN.id) +
          (toN.place ? ' (' + toN.place + ')' : '') +
          (toN.dramatic_role ? ', роль: ' + toN.dramatic_role : '') +
          '. В npc_answer явно направь туда.')
        : '';
      if (toN && isFinaleNode(toN)) {
        context.next_npc_handoff = 'to_node — ФИНАЛ квеста. npc_answer — завершающая реплика, без направления к другому NPC.';
        context.finale = true;
      } else if (toN && fromN && baseNpcId(toN.id) === baseNpcId(fromN.id)) {
        context.next_npc_handoff = 'to_node — тот же NPC (продолжение цепочки). Не отсылай к другому персонажу.';
      }
      context.npc_question = (fromN && fromN.question) || '';
      // Пустой question у from: если тот же NPC (или есть входящий диалог) — AI берёт контекст с входящего ребра
      if (field === 'player_answer' && !String(context.npc_question).trim()) {
        if (!context.same_npc_continuation && fromIncoming.length) {
          // fallback: всё равно отдадим входящие реплики модели
          context.incoming_edges = fromIncoming;
          context.conversation_so_far = fromIncoming.map(function (inc) {
            return {
              from: inc.from,
              player_answer: inc.player_answer,
              npc_answer: inc.npc_answer,
              from_question: inc.from_question
            };
          });
          context.dialogue_hint = 'speaker_node.question пуст — генерируй player_answer как продолжение conversation_so_far / npc_answer входящего ребра.';
        }
        if (!context.same_npc_continuation && !fromIncoming.length) {
          setAiStatus(field, 'Сначала задайте вопрос NPC на узле «' + selection.from + '», либо сделайте входящее ребро от того же NPC (продолжение без приветствия).', true);
          return;
        }
      }
    }

    var wrap = ta.closest('.aq-props-field');
    var btns = document.querySelectorAll('#aqProps .aq-ai-btn');
    if (wrap) wrap.classList.add('is-busy');
    btns.forEach(function (b) { b.disabled = true; });
    setAiStatus(field, mode === 'expand' ? 'AI расширяет…' : 'AI генерирует…', false);

    fetch(apiUrl('aiGraphAssist'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        mode: mode,
        field: field,
        current: current,
        questTitle: boot.questTitle || '',
        questDescription: boot.questDescription || '',
        context: context
      })
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'AI fail');
        ta.value = data.value;
        var ev = document.createEvent('Event');
        ev.initEvent('input', true, true);
        ta.dispatchEvent(ev);
        ta.dispatchEvent(new Event('change'));
        setAiStatus(field, mode === 'expand' ? 'Готово — расширено. Сохраните граф.' : 'Готово — сгенерировано. Сохраните граф.', false);
      })
      .catch(function (err) {
        setAiStatus(field, String(err.message || err), true);
      })
      .finally(function () {
        if (wrap) wrap.classList.remove('is-busy');
        btns.forEach(function (b) { b.disabled = false; });
      });
  }

  function bindPropAi(box) {
    if (!box) return;
    box.querySelectorAll('.aq-ai-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var group = btn.closest('[data-ai-field]');
        var field = group && group.getAttribute('data-ai-field');
        var mode = btn.getAttribute('data-ai-mode');
        if (field && mode) runGraphAi(field, mode);
      });
    });
  }

  var CUTSCENE_LIMIT = 10;

  function hasCutscene(n) {
    return !!(n && n.cutscene &&
      (String(n.cutscene.prompt || '').trim() !== '' || String(n.cutscene.video || '').trim() !== ''));
  }

  function cutsceneVideo(n) {
    return (n && n.cutscene && String(n.cutscene.video || '').trim()) || '';
  }

  function cutsceneBadge(n) {
    if (!hasCutscene(n)) return '';
    return cutsceneVideo(n) ? '\n[cut-сцена: видео]' : '\n[cut-сцена: промпт]';
  }

  function cutsceneCount(exceptNodeId) {
    var used = 0;
    (graph.nodes || []).forEach(function (node) {
      if (exceptNodeId && node.id === exceptNodeId) return;
      if (node.cutscene && String(node.cutscene.prompt || '').trim() !== '') used++;
    });
    return used;
  }

  function shortText(s, max) {
    s = String(s || '').replace(/\s+/g, ' ').trim();
    if (!s) return '';
    return s.length > max ? s.slice(0, max - 1) + '…' : s;
  }

  function edgeItemsText(e) {
    if (!e) return '';
    var parts = [];
    if (e.require_good_id) parts.push('нужен предмет id ' + e.require_good_id);
    if (e.give_good_id) parts.push('NPC даёт предмет id ' + e.give_good_id);
    if (e.talk_need_good_id) parts.push('принести предмет id ' + e.talk_need_good_id);
    if (e.require_kpi) parts.push('KPI ' + e.require_kpi);
    return parts.join(', ');
  }

  /**
   * До трёх последних действий ветки, ведущей в nodeId через ребро из contextFrom.
   * Дальше по цепочке идём по первому входящему ребру (ветвление выбирается только на последнем шаге).
   */
  function cutsceneActions(nodeId, contextFrom) {
    var chain = [];
    var cursorTo = nodeId;
    var cursorFrom = contextFrom;
    var seen = {};
    while (chain.length < 3 && cursorFrom && cursorTo && !seen[cursorFrom + '>' + cursorTo]) {
      seen[cursorFrom + '>' + cursorTo] = true;
      var e = findEdge(cursorFrom, cursorTo);
      if (!e) break;
      var fromN = findNode(cursorFrom);
      var toN = findNode(cursorTo);
      chain.push({
        from_node: (fromN && (fromN.label || fromN.id)) || cursorFrom,
        from_place: (fromN && fromN.place) || '',
        from_lore: shortText(loreForNpc(cursorFrom), 400),
        npc_question: (fromN && fromN.question) || '',
        player_answer: e.player_answer || '',
        npc_answer: e.npc_answer || '',
        kpi_text: e.kpi_text || '',
        items: edgeItemsText(e),
        to_node: (toN && (toN.label || toN.id)) || cursorTo,
        to_place: (toN && toN.place) || ''
      });
      var prev = incomingEdgesFor(cursorFrom);
      if (!prev.length) break;
      cursorTo = cursorFrom;
      cursorFrom = prev[0].from;
    }
    return chain.reverse();
  }

  function cutsceneSelectedFrom(n) {
    var incoming = incomingEdgesFor(n.id);
    if (!incoming.length) return '';
    var want = (n.cutscene && n.cutscene.context_from) || '';
    for (var i = 0; i < incoming.length; i++) {
      if (incoming[i].from === want) return want;
    }
    return incoming[0].from;
  }

  function cutsceneBlockHtml(n) {
    var incoming = incomingEdgesFor(n.id);
    var cs = n.cutscene || {};
    var prompt = String(cs.prompt || '');
    var used = cutsceneCount(n.id);
    if (!incoming.length) {
      return '<div class="aq-props-field">' +
        '<label>Cut-сцена перед узлом</label>' +
        '<p class="aq-props-meta">Входящих линий нет — подключите узел ребром, чтобы AI собрал контекст. ' +
        'Ролик можно залить и сейчас.</p>' +
        cutsceneVideoHtml(n, cutsceneCount(n.id) >= CUTSCENE_LIMIT && !hasCutscene(n)) +
        '</div>';
    }
    var locked = !hasCutscene(n) && used >= CUTSCENE_LIMIT;
    var selected = cutsceneSelectedFrom(n);
    var opts = incoming.map(function (inc) {
      var label = ((findNode(inc.from) && (findNode(inc.from).label || inc.from)) || inc.from);
      var hint = shortText(inc.player_answer || inc.npc_answer, 46);
      return '<option value="' + escapeHtml(inc.from) + '"' + (inc.from === selected ? ' selected' : '') + '>' +
        escapeHtml(label + (hint ? ' — ' + hint : '')) + '</option>';
    }).join('');
    return '<div class="aq-props-field">' +
      '<label for="aqPropCutFrom">Cut-сцена перед узлом</label>' +
      '<p class="aq-props-meta">Промпт для Kling (image-to-video). Камера только на этом NPC, без вторых персонажей. '
      + 'В Kling обязательно Duration = <b>10</b> sec (дефолт 5). Использовано ' + used +
      (hasCutscene(n) ? ' + эта' : '') + ' из ' + CUTSCENE_LIMIT + '.</p>' +
      (locked ? '<p class="aq-props-ai-status is-error">Лимит cut-сцен исчерпан — освободите одну на другом узле.</p>' : '') +
      '<select id="aqPropCutFrom" class="form-control"' + (locked ? ' disabled' : '') + '>' + opts + '</select>' +
      '<p class="aq-props-meta">Ветка контекста: до трёх последних действий до этого узла.</p>' +
      '<textarea id="aqPropCutPrompt" class="form-control" rows="7" placeholder="English motion prompt for Kling…"' +
      (locked ? ' disabled' : '') + '>' + escapeHtml(prompt) + '</textarea>' +
      (locked ? '' :
        '<div class="aq-ai-btns" data-cut-field="cutscene">' +
        '<button type="button" class="btn btn-sm aq-ai-btn aq-cut-btn" data-cut-mode="regenerate" title="Сгенерировать промпт заново">AI · заново</button>' +
        '<button type="button" class="btn btn-sm aq-ai-btn aq-cut-btn" data-cut-mode="expand" title="Доработать текущий промпт">AI · расширить</button>' +
        '</div><div class="aq-props-ai-status" data-ai-status="cutscene"></div>') +
      cutsceneVideoHtml(n, locked) +
      '</div>';
  }

  function cutsceneVideoHtml(n, locked) {
    var rel = cutsceneVideo(n);
    return '<div class="aq-cut-video">' +
      '<label>Ролик cut-сцены</label>' +
      '<p class="aq-props-meta">mp4/webm до 48 МБ. Файл ложится в <code>/aiquest/{дата}/</code>, ' +
      'на CDN уходит при «Загрузить в игру».</p>' +
      '<div class="aq-cut-video-state" data-cut-video>' +
      (rel
        ? '<code>' + escapeHtml(rel) + '</code>' +
          '<video class="aq-cut-video-player" src="' + escapeHtml(rel) + '" controls preload="metadata"></video>'
        : '<span class="aq-muted">— ролик не загружен —</span>') +
      '</div>' +
      (locked ? '' :
        '<div class="aq-good-row">' +
        '<input type="file" id="aqPropCutVideoFile" class="form-control" accept="video/mp4,video/webm" />' +
        '<button type="button" class="btn btn-sm btn-outline-secondary" id="aqPropCutVideoClear" title="Отвязать ролик">×</button>' +
        '</div>' +
        '<div class="aq-props-ai-status" data-ai-status="cutvideo"></div>') +
      '</div>';
  }

  function renderCutsceneVideoState(n) {
    var box = document.querySelector('[data-cut-video]');
    if (!box) return;
    var rel = cutsceneVideo(n);
    box.innerHTML = rel
      ? '<code>' + escapeHtml(rel) + '</code>' +
        '<video class="aq-cut-video-player" src="' + escapeHtml(rel) + '" controls preload="metadata"></video>'
      : '<span class="aq-muted">— ролик не загружен —</span>';
  }

  function uploadCutsceneVideo(n, file) {
    if (!file) return;
    if (file.size > 48 * 1024 * 1024) {
      setAiStatus('cutvideo', 'Файл больше 48 МБ — сожмите ролик.', true);
      return;
    }
    var fd = new FormData();
    fd.append('cutscene_video', file);
    fd.append('questId', boot.questId);
    fd.append('nodeId', n.id);
    setAiStatus('cutvideo', 'Загрузка ролика…', false);
    fetch(apiUrl('uploadCutsceneVideo'), {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'upload fail');
        n.cutscene = n.cutscene || { prompt: '', context_from: cutsceneSelectedFrom(n) };
        n.cutscene.video = data.video;
        renderCutsceneVideoState(n);
        if (!isFinaleNode(n)) patchNodeVisual(n);
        setAiStatus('cutvideo', 'Готово: ' + Math.round((data.bytes || 0) / 1048576 * 10) / 10 +
          ' МБ. Сохраните граф.', false);
      })
      .catch(function (err) {
        setAiStatus('cutvideo', String(err.message || err), true);
      });
  }

  function cutsceneSync(n) {
    var ta = document.getElementById('aqPropCutPrompt');
    var sel = document.getElementById('aqPropCutFrom');
    if (!ta) return;
    var prompt = String(ta.value || '').trim();
    var video = cutsceneVideo(n);
    if (!prompt && !video) {
      delete n.cutscene;
      return;
    }
    n.cutscene = {
      prompt: ta.value,
      context_from: sel ? (sel.value || '') : '',
      video: video
    };
  }

  function runCutsceneAi(n, mode) {
    var ta = document.getElementById('aqPropCutPrompt');
    var sel = document.getElementById('aqPropCutFrom');
    if (!ta) return;
    var current = String(ta.value || '').trim();
    if (mode === 'expand' && !current) {
      setAiStatus('cutscene', 'Нет черновика промпта — сначала «AI · заново».', true);
      return;
    }
    var contextFrom = sel ? sel.value : cutsceneSelectedFrom(n);
    var actions = cutsceneActions(n.id, contextFrom);
    if (!actions.length) {
      setAiStatus('cutscene', 'Не удалось собрать контекст — заполните реплики на входящем ребре.', true);
      return;
    }
    var btns = document.querySelectorAll('#aqProps .aq-cut-btn');
    btns.forEach(function (b) { b.disabled = true; });
    setAiStatus('cutscene', mode === 'expand' ? 'AI дорабатывает промпт…' : 'AI пишет промпт…', false);

    fetch(apiUrl('aiCutscenePrompt'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        mode: mode,
        current: current,
        questTitle: boot.questTitle || '',
        questDescription: boot.questDescription || '',
        node: nodeContext(n),
        actions: actions
      })
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'AI fail');
        ta.value = data.value;
        cutsceneSync(n);
        setAiStatus('cutscene', 'Готово (действий в контексте: ' + (data.actions_used || actions.length) +
          '). Сохраните граф.', false);
      })
      .catch(function (err) {
        setAiStatus('cutscene', String(err.message || err), true);
      })
      .finally(function () {
        btns.forEach(function (b) { b.disabled = false; });
      });
  }

  function bindCutscene(box, n) {
    var ta = document.getElementById('aqPropCutPrompt');
    var sel = document.getElementById('aqPropCutFrom');
    if (ta) {
      ta.addEventListener('input', function () { cutsceneSync(n); });
      ta.addEventListener('change', function () {
        if (!isFinaleNode(n)) patchNodeVisual(n);
      });
    }
    if (sel) {
      sel.addEventListener('change', function () { cutsceneSync(n); });
    }
    box.querySelectorAll('.aq-cut-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        runCutsceneAi(n, btn.getAttribute('data-cut-mode'));
      });
    });
    var fileIn = document.getElementById('aqPropCutVideoFile');
    if (fileIn) {
      fileIn.addEventListener('change', function () {
        uploadCutsceneVideo(n, this.files && this.files[0]);
        this.value = '';
      });
    }
    var clearBtn = document.getElementById('aqPropCutVideoClear');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (n.cutscene) n.cutscene.video = '';
        cutsceneSync(n);
        renderCutsceneVideoState(n);
        if (!isFinaleNode(n)) patchNodeVisual(n);
        setAiStatus('cutvideo', 'Ролик отвязан (файл на диске остался). Сохраните граф.', false);
      });
    }
  }

  var FINALE_SLIDE_LIMIT = 8;

  function finaleSlides(n) {
    if (!Array.isArray(n.slides)) n.slides = [];
    return n.slides;
  }

  function finaleSlidesBlockHtml() {
    return '<div class="aq-props-field" id="aqFinSlidesWrap">' +
      '<label>Слайды этой концовки</label>' +
      '<p class="aq-props-meta">Текст + картинка (png/jpg/webp/gif до 8 МБ). Игрок увидит их в финале ' +
      'именно этой концовки. В t_aiquest_slide (ending_id) попадают при «Загрузить в игру». До ' +
      FINALE_SLIDE_LIMIT + ' слайдов.</p>' +
      '<div id="aqFinSlides"></div>' +
      '<button type="button" class="btn btn-sm aq-ai-btn" id="aqFinSlideAdd">+ Добавить слайд</button>' +
      '<div class="aq-props-ai-status" data-ai-status="finslides"></div>' +
      '</div>';
  }

  function renderFinaleSlides(n) {
    var box = document.getElementById('aqFinSlides');
    if (!box) return;
    var slides = finaleSlides(n);
    if (!slides.length) {
      box.innerHTML = '<p class="aq-muted">— слайдов нет — будет общий финал без слайдшоу этой концовки —</p>';
      return;
    }
    box.innerHTML = slides.map(function (s, i) {
      return '<div class="aq-fin-slide" data-slide-idx="' + i + '">' +
        '<div class="aq-fin-slide-head"><b>Слайд ' + (i + 1) + '</b>' +
        '<span class="aq-fin-slide-btns">' +
        (i > 0 ? '<button type="button" class="btn btn-sm btn-outline-secondary" data-slide-act="up" title="Выше">↑</button>' : '') +
        (i < slides.length - 1 ? '<button type="button" class="btn btn-sm btn-outline-secondary" data-slide-act="down" title="Ниже">↓</button>' : '') +
        '<button type="button" class="btn btn-sm btn-outline-secondary" data-slide-act="del" title="Удалить слайд">×</button>' +
        '</span></div>' +
        '<textarea class="form-control" rows="3" data-slide-field="text" placeholder="Текст слайда…">' +
        escapeHtml(s.text || '') + '</textarea>' +
        '<div class="aq-fin-slide-img">' +
        (s.image
          ? '<code>' + escapeHtml(s.image) + '</code>' +
            '<img class="aq-fin-slide-preview" src="' + escapeHtml(s.image) + '" alt="" />'
          : '<span class="aq-muted">— картинки нет —</span>') +
        '</div>' +
        '<div class="aq-good-row">' +
        '<input type="file" class="form-control" data-slide-field="image" accept="image/png,image/jpeg,image/webp,image/gif" />' +
        (s.image ? '<button type="button" class="btn btn-sm btn-outline-secondary" data-slide-act="imgclear" title="Убрать картинку">×</button>' : '') +
        '</div>' +
        '</div>';
    }).join('');
  }

  function slideIdxFromEvent(ev) {
    var el = ev.target && ev.target.closest ? ev.target.closest('[data-slide-idx]') : null;
    if (!el) return -1;
    var i = parseInt(el.getAttribute('data-slide-idx'), 10);
    return isNaN(i) ? -1 : i;
  }

  function uploadFinaleSlideImage(n, idx, file) {
    if (!file) return;
    if (file.size > 8 * 1024 * 1024) {
      setAiStatus('finslides', 'Файл больше 8 МБ — сожмите картинку.', true);
      return;
    }
    var fd = new FormData();
    fd.append('slide_image', file);
    fd.append('questId', boot.questId);
    fd.append('nodeId', n.id);
    setAiStatus('finslides', 'Загрузка картинки…', false);
    fetch(apiUrl('uploadSlideImage'), {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'upload fail');
        var slides = finaleSlides(n);
        if (slides[idx]) slides[idx].image = data.image;
        renderFinaleSlides(n);
        setAiStatus('finslides', 'Готово: ' + Math.round((data.bytes || 0) / 1024) + ' КБ. Сохраните граф.', false);
      })
      .catch(function (err) {
        setAiStatus('finslides', String(err.message || err), true);
      });
  }

  function bindFinaleSlides(n) {
    var wrap = document.getElementById('aqFinSlidesWrap');
    if (!wrap) return;
    var addBtn = document.getElementById('aqFinSlideAdd');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        var slides = finaleSlides(n);
        if (slides.length >= FINALE_SLIDE_LIMIT) {
          setAiStatus('finslides', 'Лимит ' + FINALE_SLIDE_LIMIT + ' слайдов на концовку.', true);
          return;
        }
        slides.push({ text: '', image: '' });
        renderFinaleSlides(n);
      });
    }
    wrap.addEventListener('input', function (ev) {
      if (!ev.target || ev.target.getAttribute('data-slide-field') !== 'text') return;
      var i = slideIdxFromEvent(ev);
      var slides = finaleSlides(n);
      if (i >= 0 && slides[i]) slides[i].text = ev.target.value || '';
    });
    wrap.addEventListener('change', function (ev) {
      if (!ev.target || ev.target.getAttribute('data-slide-field') !== 'image') return;
      var i = slideIdxFromEvent(ev);
      if (i < 0) return;
      uploadFinaleSlideImage(n, i, ev.target.files && ev.target.files[0]);
      ev.target.value = '';
    });
    wrap.addEventListener('click', function (ev) {
      var btn = ev.target && ev.target.closest ? ev.target.closest('[data-slide-act]') : null;
      if (!btn) return;
      var act = btn.getAttribute('data-slide-act');
      var i = slideIdxFromEvent(ev);
      var slides = finaleSlides(n);
      if (i < 0 || !slides[i]) return;
      if (act === 'del') {
        slides.splice(i, 1);
      } else if (act === 'up' && i > 0) {
        var a = slides[i - 1]; slides[i - 1] = slides[i]; slides[i] = a;
      } else if (act === 'down' && i < slides.length - 1) {
        var b = slides[i + 1]; slides[i + 1] = slides[i]; slides[i] = b;
      } else if (act === 'imgclear') {
        slides[i].image = '';
      } else {
        return;
      }
      renderFinaleSlides(n);
    });
    renderFinaleSlides(n);
  }

  function renderProps() {
    var box = document.getElementById('aqProps');
    if (!box) return;
    if (!selection) {
      box.innerHTML = '<p class="aq-muted">Кликните узел или ребро на графе</p>';
      return;
    }
    if (selection.type === 'node') {
      var n = findNode(selection.id);
      if (!n) {
        selection = null;
        box.innerHTML = '<p class="aq-muted">Кликните узел или ребро на графе</p>';
        return;
      }
      if (isFinaleNode(n)) {
        if (n.prize_good_id == null) n.prize_good_id = null;
        if (!n.prize_col || n.prize_col < 1) n.prize_col = 1;
        box.innerHTML =
          '<p class="aq-props-head"><b>Финал</b> <code>' + escapeHtml(n.id) + '</code></p>' +
          '<p class="aq-props-meta">В runtime выбор ответа с ребром → этот узел завершает задание (climax).</p>' +
          '<div class="aq-props-field">' +
          '<label>Приз этой концовки</label>' +
          '<p class="aq-props-meta">Игрок не видит приз заранее. Пусто = общий приз квеста (t_aiquest)</p>' +
          goodsPickerHtml('prize_good_id', n.prize_good_id) +
          '</div>' +
          '<div class="aq-props-field">' +
          '<label for="aqPropPrizeCol">Количество приза</label>' +
          '<input id="aqPropPrizeCol" class="form-control" type="number" min="1" step="1" value="' +
          (parseInt(n.prize_col, 10) || 1) + '" />' +
          '</div>' +
          finaleSlidesBlockHtml() +
          cutsceneBlockHtml(n) +
          '<p class="aq-muted">Из финала исходящих рёбер быть не должно.</p>';
        bindGoodsPicker(box, n, 'prize_good_id');
        bindFinaleSlides(n);
        bindCutscene(box, n);
        var pc = document.getElementById('aqPropPrizeCol');
        if (pc) {
          pc.addEventListener('input', function () {
            var v = parseInt(this.value, 10);
            n.prize_col = (!v || v < 1) ? 1 : v;
          });
        }
        return;
      }
      var incoming = incomingEdgesFor(selection.id);
      var sameCont = isSameNpcContinuation(selection.id, incoming);
      var inHint;
      if (!incoming.length) {
        inHint = 'Входящих линий нет — AI сгенерирует входной вопрос';
      } else if (sameCont) {
        inHint = 'Продолжение того же NPC — приветствие можно оставить пустым; AI · заново очистит поле. Диалог идёт через ответ на входящем ребре.';
      } else {
        inHint = 'Входящих линий: ' + incoming.length + ' — AI оперётся на их диалоги (handoff)';
      }
      box.innerHTML =
        '<p class="aq-props-head"><b>' + escapeHtml(n.label || n.id) + '</b> <code>' + escapeHtml(n.id) + '</code></p>' +
        '<p class="aq-props-meta">' + escapeHtml(n.faction || '') +
        (n.dramatic_role ? ' · ' + escapeHtml(n.dramatic_role) : '') + '</p>' +
        '<p class="aq-props-meta">' + escapeHtml(inHint) + '</p>' +
        '<div class="aq-props-field">' +
        '<label for="aqPropQuestion">Вопрос NPC (приветствие)</label>' +
        '<textarea id="aqPropQuestion" class="form-control" rows="4" placeholder="' +
        (sameCont
          ? 'Пусто = продолжение разговора после ответа на входящем ребре…'
          : 'Что спросит NPC, когда к нему подойдёт игрок…') +
        '">' +
        escapeHtml(n.question || '') + '</textarea>' +
        aiBtnsHtml('question') +
        '</div>' +
        cutsceneBlockHtml(n);
      var q = document.getElementById('aqPropQuestion');
      if (q) {
        q.addEventListener('input', function () {
          n.question = this.value || '';
        });
        q.addEventListener('change', function () {
          patchNodeVisual(n);
        });
      }
      bindPropAi(box);
      bindCutscene(box, n);
      return;
    }
    if (selection.type === 'edge') {
      var e = findEdge(selection.from, selection.to);
      if (!e) {
        selection = null;
        box.innerHTML = '<p class="aq-muted">Кликните узел или ребро на графе</p>';
        return;
      }
      if (e.require_good_id == null) e.require_good_id = null;
      if (e.give_good_id == null) e.give_good_id = null;
      if (e.talk_need_good_id == null) e.talk_need_good_id = null;
      if (e.require_kpi == null) e.require_kpi = null;
      if (e.kpi_text == null) e.kpi_text = '';
      var fromN = findNode(e.from);
      var toN = findNode(e.to);
      var fromIncoming = incomingEdgesFor(e.from);
      var fromSameCont = isSameNpcContinuation(e.from, fromIncoming);
      var qHint;
      if (fromN && fromN.question) {
        qHint = 'Вопрос NPC: ' + fromN.question;
      } else if (fromSameCont || (fromN && !String(fromN.question || '').trim() && fromIncoming.length)) {
        qHint = 'Узел «from» без приветствия (продолжение того же NPC) — AI оперётся на входящий ответ NPC';
      } else {
        qHint = 'На узле «from» ещё нет вопроса — сначала заполните его (или оставьте пустым при продолжении того же NPC)';
      }
      var handoffHint = toN
        ? (isFinaleNode(toN)
          ? '→ Финал — ответ NPC завершает квест'
          : ('Дальше по ребру → ' + (toN.label || toN.id) + ' — ответ NPC должен направить туда'))
        : '';
      box.innerHTML =
        '<p class="aq-props-head">Ребро <code>' + escapeHtml(e.from) + ' → ' + escapeHtml(e.to) + '</code></p>' +
        '<p class="aq-props-meta">' + escapeHtml(
          (toN && isFinaleNode(toN)) ? 'finale' : (e.kind || 'leads_to')
        ) + '</p>' +
        '<p class="aq-props-meta">' + escapeHtml(qHint) + '</p>' +
        (handoffHint ? '<p class="aq-props-meta">' + escapeHtml(handoffHint) + '</p>' : '') +
        '<div class="aq-props-field">' +
        '<label for="aqPropPlayerAnswer">Ответ игрока</label>' +
        '<textarea id="aqPropPlayerAnswer" class="form-control" rows="3" placeholder="Реплика / вариант ответа игрока…">' +
        escapeHtml(e.player_answer || '') + '</textarea>' +
        aiBtnsHtml('player_answer') +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label for="aqPropNpcAnswer">Ответ NPC</label>' +
        '<textarea id="aqPropNpcAnswer" class="form-control" rows="3" placeholder="Ответ NPC на эту реплику…">' +
        escapeHtml(e.npc_answer || '') + '</textarea>' +
        aiBtnsHtml('npc_answer') +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label>Требует предмет</label>' +
        '<p class="aq-props-meta">Без этого предмета ответ не появится в списке</p>' +
        goodsPickerHtml('require_good_id', e.require_good_id) +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label>Даёт предмет</label>' +
        '<p class="aq-props-meta">Что NPC выдаст за этот ответ</p>' +
        goodsPickerHtml('give_good_id', e.give_good_id) +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label>Требуемый предмет для общения</label>' +
        '<p class="aq-props-meta">Справка для AI: раздобыть и принести к следующему NPC (не гейт ответа сейчас)</p>' +
        goodsPickerHtml('talk_need_good_id', e.talk_need_good_id) +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label for="aqPropRequireKpi">Выполнить KPI (число)</label>' +
        '<p class="aq-props-meta">Порог KPI для этого ответа; пусто = не требуется. Runtime позже.</p>' +
        '<input type="number" id="aqPropRequireKpi" class="form-control" min="0" step="1" placeholder="например 10" value="' +
        (e.require_kpi != null && e.require_kpi !== '' ? escapeHtml(String(e.require_kpi)) : '') + '">' +
        '</div>' +
        '<div class="aq-props-field">' +
        '<label for="aqPropKpiText">Описание KPI</label>' +
        '<p class="aq-props-meta">Что значит эта цифра (из Java не достать) — для дизайна и подсказок</p>' +
        '<textarea id="aqPropKpiText" class="form-control" rows="3" placeholder="Например: собрать N ягод на ферме…">' +
        escapeHtml(e.kpi_text || '') + '</textarea>' +
        aiBtnsHtml('kpi_text') +
        '</div>';
      var pa = document.getElementById('aqPropPlayerAnswer');
      var na = document.getElementById('aqPropNpcAnswer');
      var kpiIn = document.getElementById('aqPropRequireKpi');
      var kpiTa = document.getElementById('aqPropKpiText');
      if (pa) {
        pa.addEventListener('input', function () {
          e.player_answer = this.value || '';
        });
        pa.addEventListener('change', function () {
          patchEdgeVisual(e);
        });
      }
      if (na) {
        na.addEventListener('input', function () {
          e.npc_answer = this.value || '';
        });
        na.addEventListener('change', function () {
          patchEdgeVisual(e);
        });
      }
      if (kpiIn) {
        kpiIn.addEventListener('input', function () {
          var v = String(this.value || '').trim();
          e.require_kpi = v === '' ? null : (parseInt(v, 10) || null);
          if (e.require_kpi !== null && e.require_kpi <= 0) e.require_kpi = null;
        });
        kpiIn.addEventListener('change', function () {
          patchEdgeVisual(e);
        });
      }
      if (kpiTa) {
        kpiTa.addEventListener('input', function () {
          e.kpi_text = this.value || '';
        });
        kpiTa.addEventListener('change', function () {
          patchEdgeVisual(e);
        });
      }
      bindPropAi(box);
      bindGoodsPicker(box, e, 'require_good_id');
      bindGoodsPicker(box, e, 'give_good_id');
      bindGoodsPicker(box, e, 'talk_need_good_id');
    }
  }

  /** Обновить подпись ребра на канве без полного rebuild. */
  function patchEdgeVisual(e) {
    if (!edgesDS) return;
    var rows = edgesDS.get();
    for (var i = 0; i < rows.length; i++) {
      var row = rows[i];
      if (fromVisId(row.from) === e.from && fromVisId(row.to) === e.to) {
        edgesDS.update({
          id: row.id,
          label: e.player_answer
            ? (String(e.player_answer).length > 28 ? String(e.player_answer).slice(0, 28) + '…' : e.player_answer)
            : (e.kind || ''),
          title: [
            e.player_answer ? 'Игрок: ' + e.player_answer : '',
            e.npc_answer ? 'NPC: ' + e.npc_answer : '',
            e.require_good_id ? 'требует #' + e.require_good_id : '',
            e.give_good_id ? 'даёт #' + e.give_good_id : '',
            e.talk_need_good_id ? 'для общения #' + e.talk_need_good_id : '',
            e.require_kpi ? 'KPI ≥ ' + e.require_kpi : '',
            e.kpi_text ? 'KPI: ' + e.kpi_text : '',
            e.label || ''
          ].filter(Boolean).join('\n') || (e.kind || '')
        });
        break;
      }
    }
  }

  function patchNodeVisual(n) {
    if (!nodesDS || !n) return;
    nodesDS.update({
      id: visId(n.id),
      label: (n.label || n.id) + '\n' + n.id + cutsceneBadge(n),
      title: [
        n.question ? 'Вопрос: ' + n.question : '',
        n.why_in_this_act || '',
        n.dramatic_role || '',
        hasCutscene(n) ? 'Cut-сцена: ' + (cutsceneVideo(n) || n.cutscene.prompt) : ''
      ].filter(Boolean).join('\n')
    });
  }

  function refreshSide() {
    var desc = document.getElementById('aqQuestDesc');
    if (desc) {
      desc.textContent = boot.questDescription || boot.questTitle || '—';
    }
    var sum = document.getElementById('aqGraphSummary');
    if (sum) sum.value = graph.summary || '';
    var focus = document.getElementById('aqFocusNode');
    if (focus) {
      var cur = graph.focus_node || '';
      focus.innerHTML = '<option value="">—</option>' +
        (graph.nodes || []).map(function (n) {
          return '<option value="' + n.id + '"' + (n.id === cur ? ' selected' : '') + '>' +
            (n.label || n.id) + '</option>';
        }).join('');
    }
    var ul = document.getElementById('aqNodeList');
    if (ul) {
      ul.innerHTML = (graph.nodes || []).map(function (n) {
        if (isFinaleNode(n)) {
          return '<li><b>Финал</b> <code>' + escapeHtml(n.id) + '</code><br>конец квеста</li>';
        }
        return '<li><b>' + escapeHtml(n.label || n.id) + '</b> <code>' + escapeHtml(n.id) + '</code><br>' +
          escapeHtml(n.faction || '') + ' · ' + escapeHtml(n.dramatic_role || '') + '<br>' +
          escapeHtml(n.why_in_this_act || '') + '</li>';
      }).join('') || '<li class="aq-muted">Пусто — добавьте из палитры или AI кастинг</li>';
    }
    renderProps();
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function renderPalette() {
    var box = document.getElementById('aqPaletteList');
    var qRaw = (document.getElementById('aqPaletteFilter').value || '').trim();
    var q = qRaw.toLowerCase();
    var qNum = /^\d+$/.test(qRaw) ? parseInt(qRaw, 10) : null;
    var html = '';
    palette.forEach(function (n) {
      var firmId = n.firmorg_id != null ? String(n.firmorg_id) : '';
      var blob = (
        n.id + ' ' +
        n.label + ' ' +
        (n.lore_short || '') + ' ' +
        (n.firmorg_title || '') + ' ' +
        firmId + ' ' +
        (n.item_id != null ? String(n.item_id) : '')
      ).toLowerCase();
      if (q) {
        var hit = blob.indexOf(q) >= 0;
        // точный номер бизнеса / n_id NPC
        if (!hit && qNum != null) {
          hit = (n.firmorg_id === qNum) || (n.item_id === qNum);
        }
        if (!hit) return;
      }
      var cnt = countOnCanvas(n.id);
      var meta = escapeHtml(n.id);
      if (n.firmorg_id) {
        meta += ' · biz #' + n.firmorg_id;
        if (n.firmorg_title) meta += ' ' + escapeHtml(n.firmorg_title);
      }
      if (cnt) meta += ' · на канве: ' + cnt;
      html += '<button type="button" class="aq-palette-item" data-npc-id="' +
        escapeHtml(n.id) + '">' +
        '<b>' + escapeHtml(n.label) + '</b><code>' + meta + '</code>' +
        '<span style="display:block;color:#9aa3b2;margin-top:4px">' + escapeHtml(n.lore_short || '') + '</span></button>';
    });
    box.innerHTML = html || '<div class="aq-muted">Нет NPC с lore</div>';
  }

  function addNodeFromPalette(npc, opts) {
    if (!npc || !npc.id) return null;
    opts = opts || {};
    var instanceId = allocNodeId(npc.id);
    graph.nodes = graph.nodes || [];
    graph.nodes.push({
      id: instanceId,
      npc_id: baseNpcId(npc.id),
      prefix: npc.prefix,
      item_id: npc.item_id,
      label: npc.label || npc.id,
      faction: 'civilian',
      dramatic_role: '',
      why_in_this_act: '',
      place: '',
      question: '',
      kind: ''
    });
    selection = { type: 'node', id: instanceId };
    if (!opts.silent) {
      refreshAll();
      status('Добавлен ' + instanceId, 'ok');
    }
    return instanceId;
  }

  /**
   * Импорт цепочек с карты: узлы + рёбра по порядку.
   * Развилка: ребро от ближайшего NPC родительской ветки к первому NPC дочерней.
   */
  function applyMapChains(payload) {
    var chains = (payload && payload.chains) || [];
    var branches = (payload && payload.branches) || [];
    var chainNodeIds = {};
    var addedNodes = 0;
    var addedEdges = 0;

    function ensureEdge(from, to) {
      if (!from || !to || from === to) return;
      if (findIncomingEdge(to)) return;
      if ((graph.edges || []).some(function (e) { return e.from === from && e.to === to; })) return;
      graph.edges = graph.edges || [];
      graph.edges.push({
        from: from,
        to: to,
        kind: 'leads_to',
        label: '',
        path: 'both',
        player_answer: '',
        npc_answer: '',
        require_good_id: null,
        give_good_id: null,
        talk_need_good_id: null,
        require_kpi: null,
        kpi_text: ''
      });
      addedEdges++;
    }

    chains.forEach(function (chain) {
      var ids = [];
      (chain.npcs || []).forEach(function (npc) {
        var instanceId = addNodeFromPalette(npc, { silent: true });
        if (instanceId) {
          ids.push(instanceId);
          addedNodes++;
        }
      });
      chainNodeIds[chain.branchIndex] = ids;
      for (var i = 0; i < ids.length - 1; i++) {
        ensureEdge(ids[i], ids[i + 1]);
      }
    });

    chains.forEach(function (chain) {
      if (chain.parentBranch == null || chain.parentBranch === undefined) return;
      var childIds = chainNodeIds[chain.branchIndex] || [];
      if (!childIds.length) return;
      var parentIds = chainNodeIds[chain.parentBranch] || [];
      if (!parentIds.length) return;

      var br = branches[chain.branchIndex];
      var junction = br && br.points && br.points[0] ? br.points[0] : null;
      var fromId = parentIds[parentIds.length - 1];
      if (junction) {
        var best = null;
        var bestD = Infinity;
        parentIds.forEach(function (nid) {
          var n = findNode(nid);
          if (!n) return;
          var pal = null;
          palette.forEach(function (p) {
            if (p.id === baseNpcId(n.id) || p.id === n.npc_id) pal = p;
          });
          if (!pal || typeof pal.x !== 'number') return;
          var d = (pal.x - junction.x) * (pal.x - junction.x) +
            (pal.y - junction.y) * (pal.y - junction.y);
          if (d < bestD) {
            bestD = d;
            best = nid;
          }
        });
        if (best) fromId = best;
      }
      ensureEdge(fromId, childIds[0]);
    });

    refreshAll();
    status('С карты: узлов ' + addedNodes + ', рёбер ' + addedEdges + '. Проверьте и сохраните.', 'ok');
  }

  function openMapRoute() {
    if (!window.AiquestGraphMap || typeof window.AiquestGraphMap.open !== 'function') {
      status('Модуль карты не загружен (aiquestGraphMap.js)', 'error');
      return;
    }
    var withGeo = (palette || []).filter(function (n) {
      return n && (Math.abs(n.x) > 0.01 || Math.abs(n.y) > 0.01);
    }).length;
    if (!withGeo) {
      status('У NPC в палитре нет координат (x/y). Проверьте questlore и GPS/двери.', 'error');
      return;
    }
    window.AiquestGraphMap.open({
      palette: palette,
      onApply: applyMapChains
    });
  }

  function addFinaleNode() {
    var instanceId = allocNodeId('finale');
    graph.nodes = graph.nodes || [];
    graph.nodes.push({
      id: instanceId,
      npc_id: 'finale',
      prefix: 'finale',
      item_id: 0,
      label: 'Финал',
      faction: 'finale',
      dramatic_role: '',
      why_in_this_act: '',
      place: '',
      question: '',
      kind: 'finale',
      prize_good_id: null,
      prize_col: 1
    });
    selection = { type: 'node', id: instanceId };
    refreshAll();
    status('Добавлен финал ' + instanceId, 'ok');
  }

  function addEdge(from, to) {
    graph.edges = graph.edges || [];
    var exists = graph.edges.some(function (e) { return e.from === from && e.to === to; });
    if (exists) {
      status('Такое ребро уже есть', 'error');
      return false;
    }
    var fromN = findNode(from);
    if (isFinaleNode(fromN)) {
      status('Из финала нельзя вести рёбра — это конец квеста', 'error');
      return false;
    }
    var incoming = findIncomingEdge(to);
    if (incoming) {
      status('У узла «' + to + '» уже есть входящее ребро от «' + incoming.from + '». Входящая стрелка может быть только одна.', 'error');
      return false;
    }
    var toN = findNode(to);
    graph.edges.push({
      from: from,
      to: to,
      kind: isFinaleNode(toN) ? 'finale' : 'leads_to',
      label: '',
      path: 'both',
      artifact_good_id: null,
      require_good_id: null,
      give_good_id: null,
      talk_need_good_id: null,
      require_kpi: null,
      kpi_text: '',
      player_answer: '',
      npc_answer: ''
    });
    selection = { type: 'edge', from: from, to: to };
    refreshAll();
    return true;
  }

  /** Входящее ребро в узел (не больше одного разрешено). */
  function findIncomingEdge(nodeId) {
    var list = graph.edges || [];
    for (var i = 0; i < list.length; i++) {
      if (list[i].to === nodeId) return list[i];
    }
    return null;
  }

  function refreshAll() {
    if (document.getElementById('aqGraphSummary')) {
      graph.summary = document.getElementById('aqGraphSummary').value || '';
    }
    if (document.getElementById('aqFocusNode')) {
      var v = document.getElementById('aqFocusNode').value;
      graph.focus_node = v || null;
    }
    graph.act_id = null;
    rebuildNetwork();
    refreshSide();
    renderPalette();
  }

  function loadPalette() {
    return fetch(apiUrl('npcPalette'), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'palette fail');
        palette = data.npcs || [];
        renderPalette();
      });
  }

  function saveGraph() {
    persistPositionsFromNetwork();
    refreshAll();
    status('Сохранение…');
    return fetch(apiUrl('saveGraph'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        questId: boot.questId,
        graph: graph
      })
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'save fail');
        var msg = 'Граф сохранён (' + data.nodes + ' узлов, ' + data.edges + ' рёбер' +
          (data.cutscenes ? ', cut-сцен ' + data.cutscenes + '/' + (data.cutscene_limit || CUTSCENE_LIMIT) : '') + ')';
        if (typeof data.verified_nodes === 'number' && data.verified_nodes !== data.nodes) {
          msg += ' ⚠ в БД после записи: ' + data.verified_nodes + ' узлов — проверьте размер поля TEXT';
        }
        status(msg, 'ok');
        return data;
      })
      .catch(function (e) {
        status(String(e.message || e), 'error');
        throw e;
      });
  }

  function dissolveGraph() {
    if (!window.confirm('Сначала сохранится граф, затем заливка в игру.\n\n• node/edge перезапишутся\n• курсоры игроков = NULL')) {
      return;
    }
    var btn = document.getElementById('aqDissolveGraph');
    if (btn) btn.disabled = true;
    status('Сохранение + загрузка в игру…');
    saveGraph()
      .then(function () {
        return fetch(apiUrl('dissolveGraph'), {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ questId: boot.questId })
        }).then(function (r) { return r.json(); });
      })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'dissolve fail');
        var msg = 'В игре: ' + data.nodes + ' нод, ' + data.edges + ' рёбер. Курсоры сброшены.';
        if (data.cutscene_videos_copied || data.cutscene_videos_missing || data.cutscene_src_written) {
          msg += ' Cut-сцены: в ноды ' + (data.cutscene_src_written || 0) +
            ', на CDN ' + (data.cutscene_videos_copied || 0) +
            (data.cutscene_videos_missing ? ', не найдено ' + data.cutscene_videos_missing : '') + '.';
        }
        if (data.finale_slides_written) {
          msg += ' Слайдов концовок: ' + data.finale_slides_written + '.';
        }
        if (data.empty_npc_type) {
          msg += ' ⚠ пустой npc_type: ' + data.empty_npc_type + (data.hint ? ' — ' + data.hint : '');
        }
        status(msg, data.empty_npc_type ? 'error' : 'ok');
      })
      .catch(function (e) {
        status(String(e.message || e), 'error');
      })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function aiCast() {
    status('AI кастинг…');
    document.getElementById('aqAiCast').disabled = true;
    fetch(apiUrl('aiCast'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        questTitle: boot.questTitle,
        questDescription: boot.questDescription || '',
        existingIds: Object.keys(usedIds())
      })
    }).then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) throw new Error((data && data.error) || 'cast fail');
        var added = 0;
        (data.nodes || []).forEach(function (n) {
          var instanceId = allocNodeId(n.id);
          graph.nodes.push(Object.assign({}, n, {
            id: instanceId,
            npc_id: baseNpcId(n.id)
          }));
          added++;
        });
        (data.suggested_edges || []).forEach(function (e) {
          // рёбра от AI ссылаются на базовые id — цепляем к последним экземплярам
          var fromId = null;
          var toId = null;
          (graph.nodes || []).forEach(function (n) {
            if (baseNpcId(n.id) === baseNpcId(e.from)) fromId = n.id;
            if (baseNpcId(n.id) === baseNpcId(e.to)) toId = n.id;
          });
          if (!fromId || !toId) return;
          var exists = (graph.edges || []).some(function (x) { return x.from === fromId && x.to === toId; });
          if (exists) return;
          if (findIncomingEdge(toId)) return; // только одна входящая на узел
          graph.edges = graph.edges || [];
          graph.edges.push(Object.assign({}, e, {
            from: fromId,
            to: toId,
            require_good_id: e.require_good_id || null,
            give_good_id: e.give_good_id || null,
            talk_need_good_id: e.talk_need_good_id || null,
            require_kpi: e.require_kpi || null,
            kpi_text: e.kpi_text || '',
            player_answer: e.player_answer || '',
            npc_answer: e.npc_answer || ''
          }));
        });
        if (data.summary && !graph.summary) graph.summary = data.summary;
        refreshAll();
        status('AI добавил узлов: ' + added + '. Проверьте и сохраните.', 'ok');
      })
      .catch(function (e) {
        status(String(e.message || e), 'error');
      })
      .finally(function () {
        document.getElementById('aqAiCast').disabled = false;
      });
  }

  function isFullscreen() {
    var page = document.querySelector('.aq-graph-page');
    return !!(page && page.classList.contains('is-fullscreen'));
  }

  function resizeCanvas() {
    if (!network) return;
    try {
      network.redraw();
      setTimeout(function () {
        try { network.fit({ animation: false, padding: 48 }); } catch (err) {}
      }, 40);
    } catch (err) {}
  }

  function setFullscreen(on) {
    var page = document.querySelector('.aq-graph-page');
    var btn = document.getElementById('aqGraphFullscreen');
    if (!page) return;
    if (on) {
      page.classList.add('is-fullscreen');
      document.body.classList.add('aq-graph-fs-active');
      if (btn) btn.textContent = '⛶ Свернуть';
      if (page.requestFullscreen) {
        page.requestFullscreen().catch(function () {});
      }
    } else {
      page.classList.remove('is-fullscreen');
      document.body.classList.remove('aq-graph-fs-active');
      if (btn) btn.textContent = '⛶ На весь экран';
      if (document.fullscreenElement && document.exitFullscreen) {
        document.exitFullscreen().catch(function () {});
      }
    }
    setTimeout(resizeCanvas, 50);
  }

  function toggleFullscreen() {
    setFullscreen(!isFullscreen());
  }

  function bind() {
    document.getElementById('aqPaletteList').addEventListener('click', function (e) {
      var btn = e.target.closest('.aq-palette-item');
      if (!btn || btn.disabled) return;
      var id = btn.getAttribute('data-npc-id');
      var npc = null;
      palette.forEach(function (n) { if (n.id === id) npc = n; });
      if (npc) addNodeFromPalette(npc);
    });
    document.getElementById('aqPaletteFilter').addEventListener('input', renderPalette);
    document.getElementById('aqGraphSave').addEventListener('click', saveGraph);
    var dissolveBtn = document.getElementById('aqDissolveGraph');
    if (dissolveBtn) dissolveBtn.addEventListener('click', dissolveGraph);
    document.getElementById('aqAiCast').addEventListener('click', aiCast);
    var fsBtn = document.getElementById('aqGraphFullscreen');
    if (fsBtn) fsBtn.addEventListener('click', toggleFullscreen);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (document.getElementById('aqMapOverlay') && window.AiquestGraphMap) {
          e.preventDefault();
          window.AiquestGraphMap.close();
          return;
        }
        if (isFullscreen()) {
          e.preventDefault();
          setFullscreen(false);
        }
      }
    });
    var autoBtn = document.getElementById('aqAutoLayout');
    if (autoBtn) autoBtn.addEventListener('click', autoLayoutUd);
    document.getElementById('aqGraphSummary').addEventListener('change', function () {
      graph.summary = this.value || '';
    });
    document.getElementById('aqFocusNode').addEventListener('change', function () {
      graph.focus_node = this.value || null;
      rebuildNetwork();
    });
    document.getElementById('aqAddEdge').addEventListener('click', function () {
      var sel = network && network.getSelectedNodes();
      if (sel && sel.length === 1) {
        linkFrom = fromVisId(sel[0]);
        status('Выбран from=' + linkFrom + '. Кликните целевой узел.', 'ok');
      } else {
        linkFrom = null;
        status('Сначала выберите один узел (from)', 'error');
      }
    });
    var mapBtn = document.getElementById('aqOpenMap');
    if (mapBtn) mapBtn.addEventListener('click', openMapRoute);
    var addFinaleBtn = document.getElementById('aqAddFinale');
    if (addFinaleBtn) {
      addFinaleBtn.addEventListener('click', addFinaleNode);
    }
    document.getElementById('aqDelSel').addEventListener('click', function () {
      if (!network) return;
      var sn = (network.getSelectedNodes() || []).map(fromVisId);
      var se = network.getSelectedEdges() || [];
      if (sn.length) {
        var drop = {};
        sn.forEach(function (id) { drop[id] = true; });
        graph.nodes = (graph.nodes || []).filter(function (n) { return !drop[n.id]; });
        graph.edges = (graph.edges || []).filter(function (e) { return !drop[e.from] && !drop[e.to]; });
      }
      if (se.length) {
        var edgeObjs = se.map(function (eid) { return edgesDS.get(eid); });
        graph.edges = (graph.edges || []).filter(function (e) {
          return !edgeObjs.some(function (eo) {
            return eo && fromVisId(eo.from) === e.from && fromVisId(eo.to) === e.to;
          });
        });
      }
      refreshAll();
      selection = null;
      renderProps();
      status('Удалено', 'ok');
    });
  }

  function bootApp() {
    if (!window.vis) {
      status('Ждём vis-network…');
      setTimeout(bootApp, 200);
      return;
    }
    bind();
    refreshSide();
    rebuildNetwork();
    var n = (graph.nodes || []).length;
    var e = (graph.edges || []).length;
    status(n || e
      ? ('Загружено: ' + n + ' узлов, ' + e + ' рёбер')
      : 'Граф пуст — добавьте NPC из палитры или AI кастинг', n || e ? 'ok' : '');
    loadPalette().catch(function (err) {
      status('Палитра: ' + (err.message || err), 'error');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
  } else {
    bootApp();
  }
})();
