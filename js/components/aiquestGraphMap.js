/**
 * Карта SA (Yandex Cartesian + тайлы samp-net.com) для черчения траекторий квеста.
 * Открывается из aiquestGraphEditor через window.AiquestGraphMap.open(opts).
 *
 * opts: {
 *   palette: [{id,prefix,item_id,label,x,y,…}],
 *   onApply: function({ chains: [[{npc},…], …] }) — каждая цепочка = ветка пути
 * }
 */
(function () {
  var TILE_BASE = 'https://samp-net.com/web/images/map/tile-';
  var YMAPS_SRC = 'https://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU';

  var state = null;

  function dist2(ax, ay, bx, by) {
    var dx = ax - bx;
    var dy = ay - by;
    return dx * dx + dy * dy;
  }

  function dist(ax, ay, bx, by) {
    return Math.sqrt(dist2(ax, ay, bx, by));
  }

  /** Расстояние от точки P до отрезка AB + проекция t∈[0,1]. */
  function pointToSeg(px, py, ax, ay, bx, by) {
    var abx = bx - ax;
    var aby = by - ay;
    var len2 = abx * abx + aby * aby;
    var t = 0;
    if (len2 > 1e-6) {
      t = ((px - ax) * abx + (py - ay) * aby) / len2;
      if (t < 0) t = 0;
      else if (t > 1) t = 1;
    }
    var qx = ax + abx * t;
    var qy = ay + aby * t;
    return { d: dist(px, py, qx, qy), t: t, qx: qx, qy: qy };
  }

  function ensureYmaps(cb) {
    if (window.ymaps) {
      window.ymaps.ready(cb);
      return;
    }
    var s = document.createElement('script');
    s.src = YMAPS_SRC;
    s.async = true;
    s.onload = function () {
      if (window.ymaps) window.ymaps.ready(cb);
      else cb(new Error('ymaps load failed'));
    };
    s.onerror = function () { cb(new Error('ymaps script error')); };
    document.head.appendChild(s);
  }

  function overlayHtml() {
    return '' +
      '<div class="aq-map-overlay" id="aqMapOverlay">' +
      '  <div class="aq-map-panel">' +
      '    <div class="aq-map-toolbar">' +
      '      <strong>Карта маршрута</strong>' +
      '      <span class="aq-map-hint" id="aqMapHint">Клик по карте — точка траектории. Shift+клик по вершине — развилка.</span>' +
      '      <label class="aq-map-radius">Радиус захвата' +
      '        <input type="range" id="aqMapRadius" min="40" max="300" step="10" value="120" />' +
      '        <b id="aqMapRadiusVal">120</b>' +
      '      </label>' +
      '      <button type="button" class="btn btn-sm btn-outline-light" id="aqMapNewBranch">Новая ветка</button>' +
      '      <button type="button" class="btn btn-sm btn-outline-light" id="aqMapUndo">↩ Точка</button>' +
      '      <button type="button" class="btn btn-sm btn-outline-warning" id="aqMapClear">Очистить</button>' +
      '      <button type="button" class="btn btn-sm btn-success" id="aqMapCapture">Захватить NPC</button>' +
      '      <button type="button" class="btn btn-sm btn-info" id="aqMapApply" disabled>В граф</button>' +
      '      <button type="button" class="btn btn-sm btn-dark" id="aqMapClose">Закрыть</button>' +
      '    </div>' +
      '    <div class="aq-map-body">' +
      '      <div id="aqMapCanvas" class="aq-map-canvas"></div>' +
      '      <aside class="aq-map-side">' +
      '        <h3>Захват</h3>' +
      '        <div id="aqMapCaptureList" class="aq-map-capture-list"><p class="aq-muted">Начертите путь и нажмите «Захватить NPC»</p></div>' +
      '      </aside>' +
      '    </div>' +
      '  </div>' +
      '</div>';
  }

  function close() {
    var el = document.getElementById('aqMapOverlay');
    if (el) el.parentNode.removeChild(el);
    if (state && state.map) {
      try { state.map.destroy(); } catch (e) {}
    }
    state = null;
  }

  function setHint(msg) {
    var el = document.getElementById('aqMapHint');
    if (el) el.textContent = msg || '';
  }

  function currentBranch() {
    if (!state || !state.branches.length) return null;
    return state.branches[state.activeBranch];
  }

  function saToYmap(x, y) {
    // Как на сайте: Placemark([y, x])
    return [y, x];
  }

  function ymapToSa(coords) {
    return { x: coords[1], y: coords[0] };
  }

  function redrawPaths() {
    if (!state || !state.pathCollection) return;
    state.pathCollection.removeAll();
    state.vertexCollection.removeAll();
    state.branches.forEach(function (br, bi) {
      if (br.points.length < 1) return;
      var coords = br.points.map(function (p) { return saToYmap(p.x, p.y); });
      if (coords.length >= 2) {
        state.pathCollection.add(new ymaps.Polyline(coords, {}, {
          strokeColor: bi === state.activeBranch ? '#3aa0ff' : '#f0a030',
          strokeWidth: bi === state.activeBranch ? 4 : 3,
          strokeOpacity: 0.9
        }));
      }
      br.points.forEach(function (p, pi) {
        var pm = new ymaps.Placemark(saToYmap(p.x, p.y), {
          hintContent: 'Ветка ' + (bi + 1) + ' · т.' + (pi + 1) + ' (Shift — развилка)'
        }, {
          iconImageHref: 'https://samp-net.com/web/images/mapicon/20.png',
          iconImageSize: bi === state.activeBranch ? [20, 20] : [14, 14],
          iconImageOffset: bi === state.activeBranch ? [-10, -10] : [-7, -7]
        });
        pm.events.add('click', function (e) {
          e.stopPropagation();
          var dom = e.get('domEvent');
          var shift = dom && (dom.get && dom.get('shiftKey') || dom.shiftKey);
          if (shift) {
            startForkAt(bi, pi);
          } else {
            state.activeBranch = bi;
            redrawPaths();
            setHint('Активна ветка ' + (bi + 1) + '. Клик по карте — продолжить. Shift+клик по вершине — развилка.');
          }
        });
        state.vertexCollection.add(pm);
      });
    });
  }

  function startForkAt(branchIndex, pointIndex) {
    var br = state.branches[branchIndex];
    var p = br.points[pointIndex];
    state.branches.push({
      id: 'b' + (state.branchSeq++),
      parentBranch: branchIndex,
      parentPoint: pointIndex,
      points: [{ x: p.x, y: p.y }]
    });
    state.activeBranch = state.branches.length - 1;
    redrawPaths();
    setHint('Развилка от ветки ' + (branchIndex + 1) + ' т.' + (pointIndex + 1) + '. Чертите новую ветку.');
  }

  function addPoint(x, y) {
    var br = currentBranch();
    if (!br) {
      state.branches.push({ id: 'b' + (state.branchSeq++), parentBranch: null, parentPoint: null, points: [] });
      state.activeBranch = 0;
      br = currentBranch();
    }
    // не дублировать почти ту же точку
    if (br.points.length) {
      var last = br.points[br.points.length - 1];
      if (dist2(last.x, last.y, x, y) < 25) return;
    }
    br.points.push({ x: x, y: y });
    redrawPaths();
  }

  function undoPoint() {
    var br = currentBranch();
    if (!br || !br.points.length) return;
    // у дочерней ветки первую точку (junction) не снимаем, если есть продолжение — снимаем хвост
    if (br.parentBranch != null && br.points.length <= 1) {
      setHint('Это точка развилки — удалите ветку через «Очистить» или начните новую.');
      return;
    }
    br.points.pop();
    redrawPaths();
  }

  function clearAll() {
    state.branches = [];
    state.activeBranch = 0;
    state.captures = [];
    state.pathCollection.removeAll();
    state.vertexCollection.removeAll();
    state.captureCollection.removeAll();
    document.getElementById('aqMapCaptureList').innerHTML =
      '<p class="aq-muted">Начертите путь и нажмите «Захватить NPC»</p>';
    document.getElementById('aqMapApply').disabled = true;
    setHint('Клик по карте — точка. Shift+клик по вершине — развилка.');
  }

  function geoNpcs() {
    return (state.opts.palette || []).filter(function (n) {
      return n && typeof n.x === 'number' && typeof n.y === 'number' &&
        (Math.abs(n.x) > 0.01 || Math.abs(n.y) > 0.01);
    });
  }

  function showNpcMarkers() {
    if (!state.npcCollection) return;
    state.npcCollection.removeAll();
    geoNpcs().forEach(function (n) {
      var pm = new ymaps.Placemark(saToYmap(n.x, n.y), {
        hintContent: n.label || n.id,
        balloonContentHeader: n.label || n.id,
        balloonContentBody: (n.lore_short || n.id)
      }, {
        iconImageHref: 'https://samp-net.com/web/images/mapicon/20.png',
        iconImageSize: [16, 16],
        iconImageOffset: [-8, -8]
      });
      state.npcCollection.add(pm);
    });
  }

  /**
   * Для одной полилинии: NPC в радиусе отрезков, по порядку вдоль пути, без повторов внутри ветки.
   */
  function captureAlongPolyline(points, radius, usedGlobal) {
    var out = [];
    if (!points || points.length < 2) return out;
    var npcs = geoNpcs();
    var along = []; // { npc, s } s = distance along path
    var prefixLen = 0;
    for (var i = 0; i < points.length - 1; i++) {
      var a = points[i];
      var b = points[i + 1];
      var segLen = dist(a.x, a.y, b.x, b.y);
      npcs.forEach(function (n) {
        if (usedGlobal[n.id]) return;
        var hit = pointToSeg(n.x, n.y, a.x, a.y, b.x, b.y);
        if (hit.d <= radius) {
          along.push({ npc: n, s: prefixLen + hit.t * segLen, d: hit.d });
        }
      });
      prefixLen += segLen;
    }
    along.sort(function (u, v) {
      if (u.s !== v.s) return u.s - v.s;
      return u.d - v.d;
    });
    var seen = {};
    along.forEach(function (row) {
      if (seen[row.npc.id] || usedGlobal[row.npc.id]) return;
      // если несколько попаданий одного NPC — уже отфильтровано seen
      seen[row.npc.id] = true;
      usedGlobal[row.npc.id] = true;
      out.push(row.npc);
    });
    return out;
  }

  function runCapture() {
    var radius = parseInt(document.getElementById('aqMapRadius').value, 10) || 120;
    var used = {};
    state.captures = [];
    state.captureCollection.removeAll();

    state.branches.forEach(function (br, bi) {
      var chain = captureAlongPolyline(br.points, radius, used);
      state.captures.push({
        branchIndex: bi,
        parentBranch: br.parentBranch,
        npcs: chain
      });
      chain.forEach(function (n, ni) {
        var pm = new ymaps.Placemark(saToYmap(n.x, n.y), {
          hintContent: (bi + 1) + '.' + (ni + 1) + ' ' + (n.label || n.id),
          balloonContentHeader: n.label || n.id
        }, {
          iconImageHref: 'https://samp-net.com/web/images/mapicon/32.png',
          iconImageSize: [22, 22],
          iconImageOffset: [-11, -11]
        });
        state.captureCollection.add(pm);
      });
    });

    renderCaptureList();
    var total = state.captures.reduce(function (s, c) { return s + c.npcs.length; }, 0);
    document.getElementById('aqMapApply').disabled = total === 0;
    setHint(total ? ('Захвачено NPC: ' + total + '. Можно «В граф».') : 'В радиусе никого — увеличьте радиус или путь.');
  }

  function renderCaptureList() {
    var box = document.getElementById('aqMapCaptureList');
    if (!box) return;
    if (!state.captures.length) {
      box.innerHTML = '<p class="aq-muted">Пусто</p>';
      return;
    }
    var html = '';
    state.captures.forEach(function (c, i) {
      html += '<div class="aq-map-chain"><b>Ветка ' + (i + 1) + '</b>';
      if (c.parentBranch != null) html += ' <span class="aq-muted">(развилка от ' + (c.parentBranch + 1) + ')</span>';
      if (!c.npcs.length) {
        html += '<p class="aq-muted">нет NPC</p></div>';
        return;
      }
      html += '<ol>' + c.npcs.map(function (n) {
        return '<li><code>' + escapeHtml(n.id) + '</code> ' + escapeHtml(n.label || '') + '</li>';
      }).join('') + '</ol></div>';
    });
    box.innerHTML = html;
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function applyToGraph() {
    if (!state || !state.opts.onApply) return;
    var chains = state.captures.map(function (c) {
      return {
        branchIndex: c.branchIndex,
        parentBranch: c.parentBranch,
        npcs: c.npcs.slice()
      };
    });
    var total = chains.reduce(function (s, c) { return s + c.npcs.length; }, 0);
    if (!total) return;
    state.opts.onApply({ chains: chains, branches: state.branches });
    close();
  }

  function initMap() {
    var projection = new ymaps.projection.Cartesian([
      [-4096, -4096],
      [4096, 4096]
    ]);
    var MyLayer = function () {
      return new ymaps.Layer(function (tile, zoom) {
        return TILE_BASE + zoom + '-' + tile[1] + '-' + tile[0] + '.jpg';
      });
    };
    ymaps.layer.storage.add('aq#layer', MyLayer);
    ymaps.mapType.storage.add('aq#type', new ymaps.MapType('SA', ['aq#layer']));

    state.map = new ymaps.Map('aqMapCanvas', {
      center: [0, 0],
      zoom: 3,
      type: 'aq#type',
      behaviors: ['default', 'scrollZoom']
    }, {
      maxZoom: 5,
      minZoom: 2,
      projection: projection,
      restrictMapArea: [[-4096, -4096], [4096, 4096]]
    });
    state.map.controls.add('smallZoomControl', { right: 5, top: 5 });

    state.npcCollection = new ymaps.GeoObjectCollection();
    state.pathCollection = new ymaps.GeoObjectCollection();
    state.vertexCollection = new ymaps.GeoObjectCollection();
    state.captureCollection = new ymaps.GeoObjectCollection();
    state.map.geoObjects.add(state.npcCollection);
    state.map.geoObjects.add(state.pathCollection);
    state.map.geoObjects.add(state.vertexCollection);
    state.map.geoObjects.add(state.captureCollection);

    showNpcMarkers();

    state.map.events.add('click', function (e) {
      var sa = ymapToSa(e.get('coords'));
      addPoint(sa.x, sa.y);
    });
  }

  function bindUi() {
    document.getElementById('aqMapClose').addEventListener('click', close);
    document.getElementById('aqMapClear').addEventListener('click', clearAll);
    document.getElementById('aqMapUndo').addEventListener('click', undoPoint);
    document.getElementById('aqMapCapture').addEventListener('click', runCapture);
    document.getElementById('aqMapApply').addEventListener('click', applyToGraph);
    document.getElementById('aqMapNewBranch').addEventListener('click', function () {
      state.branches.push({
        id: 'b' + (state.branchSeq++),
        parentBranch: null,
        parentPoint: null,
        points: []
      });
      state.activeBranch = state.branches.length - 1;
      setHint('Новая независимая ветка ' + (state.activeBranch + 1) + '.');
    });
    var rad = document.getElementById('aqMapRadius');
    var radVal = document.getElementById('aqMapRadiusVal');
    rad.addEventListener('input', function () {
      radVal.textContent = this.value;
    });
  }

  function open(opts) {
    if (document.getElementById('aqMapOverlay')) close();
    opts = opts || {};
    var wrap = document.createElement('div');
    wrap.innerHTML = overlayHtml();
    document.body.appendChild(wrap.firstChild);

    state = {
      opts: opts,
      map: null,
      branches: [],
      activeBranch: 0,
      branchSeq: 1,
      captures: [],
      npcCollection: null,
      pathCollection: null,
      vertexCollection: null,
      captureCollection: null
    };

    bindUi();
    setHint('Загрузка карты…');
    ensureYmaps(function (err) {
      if (err) {
        setHint('Не удалось загрузить Яндекс.Карты: ' + err.message);
        return;
      }
      try {
        initMap();
        setHint('Клик — точка траектории. Shift+клик по вершине — развилка. Затем «Захватить NPC».');
      } catch (e) {
        setHint('Ошибка карты: ' + (e.message || e));
      }
    });
  }

  window.AiquestGraphMap = { open: open, close: close };
})();
